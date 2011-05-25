<?php
require_once 'conf/config.php';
class KingboardCron_Task extends King23_CLI_Task
{
    /**
     * documentation for the single tasks
     * @var array
     */
    protected $tasks = array(
        "info" => "General Informative Task",
        "update_stats" => "task to update stats which will be map/reduced from the database",
        "key_activation" => "task to check for new key activates, and activate if so",
        "api_import" => "import",
        "feed_pull" => "pull edk feeds",
    );

    /**
     * Name of the module
     */
    protected $name = "KingboardCron";

    /**
     * @param array $options
     * @return void
     */
    public function update_stats(array $options)
    {
        $this->cli->header('updating stats');

        $this->cli->message('updating Kills by Shiptype');
        // stats table of how often all ships have been killed
        Kingboard_Kill_MapReduce_KillsByShip::mapReduce();

        $this->cli->positive('update of stats done.');
    }

    public function key_activation(array $options)
    {
        $this->cli->header('updating key activates');
        $reg = King23_Registry::getInstance();

        $pheal = new Pheal($reg->apimailreceiverApiUserID, $reg->apimailreceiverApiKey, 'char');
        $messages = $pheal->MailMessages(array('characterID' => $reg->apimailreceiverCharacterID))->messages;
        foreach($messages as $message)
        {
            if($message->toCharacterIDs != $reg->apimailreceiverCharacterID)
                continue;

            $token = trim($message->title);

            if(strlen($token) != Kingboard_ApiActivationToken::TOKEN_LENGTH)
                continue;

            if(!$token = Kingboard_ApiActivationToken::findOneByToken($token))
                continue;

            $user = Kingboard_User::getById($token['userid']);
            $keys = $user['keys'];
            $apiuserid = $token['apiuserid'];
            $phealactivate = new Pheal($keys[$apiuserid]['apiuserid'], $keys[$apiuserid]['apikey']);
            $characters = $phealactivate->Characters()->characters;
            foreach($characters as $character)
            {
                if($character->characterID == $message->senderID)
                {
                    $keys[$apiuserid]['active'] = true;
                    $user['keys'] = $keys;
                    $user->save();
                    $token->delete();

                    $body = King23_Registry::getInstance()->sith->cachedGet('mails/activate_apikey.html')->render(array('username' => $user['username'], 'apiuserid' => $apiuserid ), King23_Registry::getInstance()->sith);
                    mail($user['username'], "Kingboard API Key Activation", $body);
                    break;
                }
            }
        }
    }

    public function api_import(array $options)
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
                                "characterID" => (int) $kill->victim->characterID,
                                "characterName" => $kill->victim->characterName,
                                "corporationID" => (int) $kill->victim->corporationID,
                                "corporationName" => $kill->victim->corporationName,
                                "allianceID" => (int) $kill->victim->allianceID,
                                "allianceName" => $kill->victim->allianceName,
                                "factionID" => (int) $kill->victim->factionID,
                                "factionName" => $kill->victim->factionName,
                                "damageTaken" => $kill->victim->damageTaken,
                                "shipTypeID"  => (int)$kill->victim->shipTypeID,
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
