<?php
require_once 'conf/config.php';
class KingboardMaintenance_Task extends King23_CLI_Task
{
    /**
     * documentation for the single tasks
     * @var array
     */
    protected $tasks = array(
        "info" => "General Informative Task",
        "setup_indexes" => "Setup the indexes for MongoDB",
        "key_check" => "run through all keys, check for validity",
        "key_purgelist" => "list all keys that key_purge would remove, incl amount of markers.",
        "key_purge" => "remove all keys who have more than x markers, where x is a parameter",
    );

    /**
     * Name of the module
     */
    protected $name = "KingboardMaintenance";

    /**
     * runs through all api keys registered, testing them for validity,
     * will up failcount if failed
     * @param array $options
     * @return void
     */
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

    /**
     * return a list of keys that have <amount> failmarkers, all that have at least 1 if no amount is given
     * @param array $options, array(amount)
     * @return void
     */
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

    /**
     * purge all keys from the database that have more than <amount> failcounters
     * @param array $options array(amount)
     * @return
     */
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

    /**
     * setup all indexes for Kingboard
     * @param array $options
     * @return
     */
    public function setup_indexes(array $options)
    {
        if(count($options) != 0)
        {
            $this->cli->error('this task takes no arguments');
            return;
        }

        $reg = King23_Registry::getInstance();

        $this->cli->message("Setting Killmail_Kill indexes");
        // Kingboard_Kill indexes
        $col = $reg->mongo['db']->Kingboard_Kill;

        // idHash, index unique
        $col->ensureIndex(array('idHash' => 1), array("unique" => true));

        // victim Names
        $col->ensureIndex(array('victim.characterName' => 1));

        // attacker Names
        $col->ensureIndex(array('attackers.characterName' =>1));

        // victim ID
        $col->ensureIndex(array('victim.characterID' => 1));

        // attacker ID
        $col->ensureIndex(array('attackers.characterID' =>1 ));


        // killtime Index
        $col->ensureIndex(array('killTime' => 1));

        // location.solarSystem
        $col->ensureIndex(array('location.solarSystem' => 1));



        $this->cli->message("Setting Killmail_EveItem indexes");
        // Kingboard_EveItem
        $col = $reg->mongo['db']->Kingboard_EveItem;

        // typeID
        $col->ensureIndex(array('typeID' => 1), array('unique' => true));

        // typeName
        $col->ensureIndex(array('typeName' => 1));

        $this->cli->message("Setting Killmail_EveSolarSystem indexes");

        // Kingboard_EveSolarSystem
        $col = $reg->mongo['db']->Kingboard_EveSolarSystem;

        // itemID
        $col->ensureIndex(array('itemID' => 1), array('unique' => true));

        // itemName
        $col->ensureIndex(array('itemName' => 1));

    }
}