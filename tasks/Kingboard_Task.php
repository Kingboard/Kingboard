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
        "key_purge" => "remove all keys who have more than x markers, where x is a parameter"
    );

    /**
     * Name of the module
     */
    protected $name = "Kingboard";

    public function key_add(array $options)
    {
        if(isset($options[0]) && !empty($options[0]) && isset($options[1]) && !empty($options[1]))
        {
            $key = new Kingboard_EveApiKey();
            $key['userid'] = $options[0];
            $key['apikey'] = $options[1];
            $key->save();
            $this->cli->positive("key saved");
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
                    $key['failed'] = 0;
                $key['failed']++;
                $key->save();
            }
        }
    }

    public function key_purge(array $options)
    {
        if(!isset($options[0]) || empty($options[0]))
        {
            $this->cli->error('fail value required');
        }
        $keys = Kingboard_EveApiKey::find(array('failed' => array('$gt' => $options[0])));
        foreach($keys as $key)
        {
            $this->cli->message("purging {$key['userid']}");
            $key->delete();
        }
    }
    
    public function import(array $options)
    {
        $this->cli->message("import running");

        $keys = Kingboard_EveApiKey::find();
        foreach($keys as $key)
        {
            $pheal = new Pheal($key['userid'], $key['apikey']);
            //$pheal = new Pheal('329963', 'tgVRqZdLCYPu6X8dk9YBu2wEzn1T4u4JNL99XmctulJHCQRHGCQ1fQOAn3D5isd7');
            //$pheal = new Pheal('417795', 'kF6hgObGjfmTCCwOVOOxb4p0rctF1nvbSKMpgDbiCO02F92eShtp3WjFc4xBLpxC');
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
                        $killdata = array(
                            "killID" => $kill->killID,
                            "solarSystemID" => $kill->solarSystemID,
                            "killTime" => $kill->killTime,
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
                                "characterID" => $attacker->characterID,
                                "characterName" => $attacker->characterName,
                                "corporationID" => $attacker->corporationID,
                                "corporationName" => $attacker->corporationName,
                                "allianceID" => $attacker->allianceID,
                                "allianceName" => $attacker->allianceName,
                                "factionID" => $attacker->factionID,
                                "factionName" => $attacker->factionName,
                                "securityStatus" => $attacker->securityStatus,
                                "damageDone" => $attacker->damageDone,
                                "finalBlow"  => $attacker->finalBlow,
                                "weaponTypeID" => $attacker->weaponTypeID,
                                "weaponType" => Kingboard_EveItem::getByItemId($attacker->weaponTypeID)->typeName,
                                "shipTypeID" => $attacker->shipTypeID,
                                "shipType"  => Kingboard_EveItem::getByItemId($attacker->shipTypeID)->typeName
                            );
                        }
                        $killdata['items'] = array();
                        foreach($kill->items as $item)
                        {
                            $killdata['items'][] = array(
                                "typeID" => $item->typeID,
                                "typeName" => Kingboard_EveItem::getByItemId($item->typeID)->typeName,
                                "flag" => $item->flag,
                                "qtyDropped" => $item->qtyDropped,
                                "qtyDestroyed" => $item->qtyDestroyed
                            );
                        }
                        if(is_null(Kingboard_Kill::getByKillId($killdata['killID'])))
                        {
                            $this->cli->message("new kill, saving");
                            $killObject = new Kingboard_Kill();
                            $killObject->injectDataFromMail($killdata);
                            $killObject->save();
                        } else {
                            $this->cli->message("kill allready in database");
                        }
                    }
                }
            } catch (PhealApiException $e) {
                if(!isset($key['failed']))
                    $key['failed'] = 0;
                $key['failed']++;
                $key->save();
            }
        }
    }
}
