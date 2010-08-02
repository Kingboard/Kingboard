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
    );

    /**
     * Name of the module
     */
    protected $name = "Kingboard";

    public function add_key(array $options)
    {
        $key = new Kingboard_EveApiKey();
        $key['userid'] = $options[0];
        $key['apikey'] = $options[1];
        $key->save();
        $this->cli->positive("key saved");
    }

    public function list_keys(array $options)
    {
        
    }

    public function test(array $options)
    {
        $res = Kingboard_Kill::find();
        foreach($res as $kill)
        {
            echo $kill["victim"]["characterName"];
        }
    }

    public function import(array $options)
    {
        $this->cli->message("import running");
        //$pheal = new Pheal('329963', 'tgVRqZdLCYPu6X8dk9YBu2wEzn1T4u4JNL99XmctulJHCQRHGCQ1fQOAn3D5isd7');
        $pheal = new Pheal('417795', 'kF6hgObGjfmTCCwOVOOxb4p0rctF1nvbSKMpgDbiCO02F92eShtp3WjFc4xBLpxC');
        $pheal->scope = 'char';
        $kills = $pheal->Killlog(array('characterID' => '268946627'))->kills;
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
                    "shipTypeID"  => $kill->victim->shipTypeID 
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
                    "shipTypeID" => $attacker->shipTypeID
                );
            }
            $killdata['items'] = array();
            foreach($kill->items as $item)
            {
                $killdata['items'][] = array(
                    "typeID" => $item->typeID,
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
}
