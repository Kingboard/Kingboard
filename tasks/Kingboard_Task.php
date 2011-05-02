<?php
require_once 'conf/config.php';
class Kingboard_Task extends King23_CLI_Task
{
    /**
     * documentation for the single tasks
     * @var array
     */
    protected $tasks = array(
        "info" => "General Informative Task",
        "import" => "import",
        "key_add" => "add an apikey, requires userid, apikey",
        "key_check" => "run through all keys, add marker to those not responding",
        "key_purgelist" => "list all keys that key_purge would remove, incl amount of markers.",
        "key_purge" => "remove all keys who have more than x markers, where x is a parameter",
        "feed_add" => "add a feed to the feeds to be pulled",
        "feed_pull" => "pull feeds"
    );

    /**
     * Name of the module
     */
    protected $name = "Kingboard";

    public function key_add(array $options)
    {
        if(isset($options[0]) && !empty($options[0]) && isset($options[1]) && !empty($options[1]))
        {
            // Load the key if it exists (we might already know it)
            $key = Kingboard_EveApiKey::getByUserId($options[0]);
            if (!is_null($key))
            {
                $key['apikey'] = $options[1];
                $key->save();
                $this->cli->positive("key updated");
            }
            else
            {
                $key = new Kingboard_EveApiKey();
                $key['userid'] = $options[0];
                $key['apikey'] = $options[1];
                $key->save();
                $this->cli->positive("key saved");
            }

        } else {
            $this->cli->error('two parameters needed (userid, apikey)');
        }
    }

    public function key_check(array $options)
    {
        $keys = Kingboard_EveApiKey::find();
        foreach($keys as $key)
        {
            $this->cli->message("testing {$key['userid']}");
            $pheal = new Pheal($key['userid'], $key['apikey']);
            try {
                $pheal->Characters();
                $this->cli->positive('ok');
            } catch(PhealApiException $e) {
                $this->cli->error('failed');
                if(!isset($key['failed']))
                    $key->failed = 0;
                $key->failed++;
                $key->save();
            }
        }
    }

    public function key_purgelist(array $options)
    {
        if(!isset($options[0]) || empty($options[0]))
        {
            $this->cli->message('no parameter given, assuming to show all who have a fail marker');
            $options[0] = 0;
        }
        $criteria = array('failed' => array('$gt' => (int) $options[0]));
        $keys = Kingboard_EveApiKey::find($criteria);
        foreach($keys as $key)
        {
            $this->cli->message("{$key['userid']} has {$key['failed']} markers");
        }
    }

    public function key_purge(array $options)
    {
        if(!isset($options[0]) || empty($options[0]))
        {
            $this->cli->error('fail value required');
            return;
        }
        $criteria = array('failed' => array('$gt' => (int) $options[0]));
        $keys = Kingboard_EveApiKey::find($criteria);
        foreach($keys as $key)
        {
            $this->cli->message("purging {$key['userid']}");
            $key->delete();
        }
    }


    public function standings(array $options)
    {
        foreach(Kingboard_EveApiKey::find() as $key)
        {
            $userid = $key['userid'];
            $apikey = $key['apikey'];
            $pheal = new Pheal($userid, $apikey, 'account');

            foreach($pheal->Characters()->characters as $char)
            {
                $pheal->scope = "corp";
                print_r($pheal->ContactList(array('characterID' => $char->characterID))->toArray());
            }
        }

    }
    
    public function import(array $options)
    {
        $this->cli->message("import running");
        $newkills = 0;
        $oldkills = 0;
        $keys = Kingboard_EveApiKey::find();
        foreach($keys as $key)
        {
            $pheal = new Pheal($key['userid'], $key['apikey']);
            $pheal->scope = "account";
            try {
                foreach($pheal->Characters()->characters as $char)
                {
                    try {
                        $this->cli->message('trying corp import on ' . $char->name ."...");
                        $pheal->scope = 'corp';
                        $kills = $pheal->Killlog(array('characterID' => $char->characterID))->kills;
                    } catch(PhealAPIException $e) {
                        $this->cli->message('corp failed, trying char import now..');
                        $pheal->scope = 'char';
                        try {
                            $kills = $pheal->Killlog(array('characterID' => $char->characterID))->kills;
                        } catch (PhealAPIException $e) {
                            continue;
                        }
                    }
                    foreach($kills as $kill)
                    {
                        $this->cli->message("import of " . $kill->killID);
                        if(!is_null(Kingboard_Kill::getByKillId($kill->killID)))
                        {
    	        		    $oldkills++;
                            $this->cli->message("kill allready in database");	
    			            continue;
        	    		}
		            	$killdata = array(
                            "killID" => $kill->killID,
                            "solarSystemID" => $kill->solarSystemID,
                            "location" => array(
                                "solarSystem" => Kingboard_EveSolarSystem::getBySolarSystemId($kill->solarSystemID)->itemName,
                                "security" => Kingboard_EveSolarSystem::getBySolarSystemId($kill->solarSystemID)->security,
                                "region" => Kingboard_EveSolarSystem::getBySolarSystemId($kill->solarSystemID)->Region['itemName'],
                            ),
                            "killTime" => new MongoDate(strtotime($kill->killTime)),
                            "moonID" => $kill->moonID,
                            "victim" => array(
                                "characterID" => $kill->victim->characterID,
                                "characterName" => $kill->victim->characterName,
                                "corporationID" => $kill->victim->corporationID,
                                "corporationName" => $kill->victim->corporationName,
                                "allianceID" => $kill->victim->allianceID,
                                "allianceName" => $kill->victim->allianceName,
                                "factionID" => $kill->victim->factionID,
                                "factionName" => $kill->victim->factionName,
                                "damageTaken" => $kill->victim->damageTaken,
                                "shipTypeID"  => $kill->victim->shipTypeID,
                                "shipType"  => Kingboard_EveItem::getByItemId($kill->victim->shipTypeID)->typeName
                            )
                        );
                        $killdata['attackers'] = array();
                        foreach($kill->attackers as $attacker)
                        {
                            $killdata['attackers'][] = array(
                                "characterID" => (int) $attacker->characterID,
                                "characterName" => $attacker->characterName,
                                "entityType" => Kingboard_Helper_EntityType::getEntityTypeByEntityId((int) $attacker->characterID),
                                "corporationID" => (int) $attacker->corporationID,
                                "corporationName" => $attacker->corporationName,
                                "allianceID" => (int) $attacker->allianceID,
                                "allianceName" => $attacker->allianceName,
                                "factionID" => (int) $attacker->factionID,
                                "factionName" => $attacker->factionName,
                                "securityStatus" => $attacker->securityStatus,
                                "damageDone" => $attacker->damageDone,
                                "finalBlow"  => $attacker->finalBlow,
                                "weaponTypeID" => (int) $attacker->weaponTypeID,
                                "weaponType" => Kingboard_EveItem::getByItemId($attacker->weaponTypeID)->typeName,
                                "shipTypeID" => (int) $attacker->shipTypeID,
                                "shipType"  => Kingboard_EveItem::getByItemId($attacker->shipTypeID)->typeName
                            );
                        }
                        $killdata['items'] = array();
                        
                        foreach($kill->items as $item)
                        {
                            $killdata['items'][] = $this->ParseItem($item);
                        }
                        
                        $hash = Kingboard_KillmailHash_IdHash::getByData($killdata);
                        $killdata['idHash'] = (String) $hash;

                        if(is_null(Kingboard_Kill::getInstanceByIdHash($killdata['idHash'])))
                        {
                            $this->cli->message("new kill, saving");
                            $killObject = new Kingboard_Kill();
                            $killObject->injectDataFromMail($killdata);
                            $killObject->save();
                            $newkills++;
                        } else {
                            $oldkills++;
                            $this->cli->message("kill allready in database");
                        }
                    }
                }
            } catch (PhealApiException $e) {
                if(!isset($key['failed']))
                    $key->failed = 0;
                $key->failed++;
                $key->save();
            } catch (PhealException $pe) {
        		$this->cli->message("PhealException caught, auch!");
    	    	continue;
	        }
        }
        $totalkills = $oldkills + $newkills;
        $this->cli->message("found $totalkills kills, $oldkills where allready in database, $newkills added");
    }

    private function ParseItem($row)
    {
        // Build the standard item
        $item = array(
            "typeID" => $row->typeID,
            "typeName" => Kingboard_EveItem::getByItemId($row->typeID)->typeName,
            "flag" => $row->flag,
            "qtyDropped" => $row->qtyDropped,
            "qtyDestroyed" => $row->qtyDestroyed
        );

        // Check for nested items (container)
        if (isset($row['items']))
        {
            $item['items'] = array();
            foreach($row['items'] as $innerRow)
            {
                $item['items'][] = $this->ParseItem($innerRow);
            }
        }
        return $item;
    }

    public function feed_add($options)
    {
        $this->cli->header('adding new feed');
        if(count($options) != 1)
        {
            $this->cli->error('exactly one parameter (url) should be given');
            return;
        }

        if(!is_null(Kingboard_EdkFeed::findByUrl($options[0])))
        {
            $this->cli->error('a feed by this url allready exists!');
            return;
        }

        $feed = new Kingboard_EdkFeed();
        $feed->url = $options[0];
        $feed->save();
        $this->cli->positive('done');
    }

    public function feed_pull($options)
    {
        $this->cli->header('pulling all feeds');
        $feeds = Kingboard_EdkFeed::find();
        foreach($feeds as $feed)
        {

            $url = $feed->url;
            $this->cli->message('pulling ' . $url);
            $sxo =simplexml_load_file($url);
            $processed = 0;
            $killcount = count($sxo->channel->item);
            $this->cli->message("processing $killcount kills.");
            foreach($sxo->channel->item as $item)
            {
                $this->cli->message('processing ' . ++$processed . ' out of ' .  $killcount);
                $mailtext = trim((string) $item->description);
                if(isset($item->apiID))
                    $apiId = (string) $item->apiID;
                else
                    $apiId = null;

                try {
                    $mail = Kingboard_KillmailParser_Factory::parseTextMail($mailtext);
                    $mail->killID = $apiId;
                    $mail->save();
                } catch(Kingboard_KillmailParser_KillmailErrorException $e) {
                    $this->cli->error("Exception caught, mail was not processed");
                    $this->cli->error($e->getMessage());
                    $this->cli->error($e->getFile() . '::' . $e->getLine());
                }

            }
            $this->cli->positive('done');
        }
    }
}
