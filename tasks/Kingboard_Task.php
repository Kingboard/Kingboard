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
        "key_add" => "add an apikey, requires userid, apikey",
        "idfeed_add" => "add a idfeed to the idfeeds to be pulled",
    );

    /**
     * Name of the module
     */
    protected $name = "Kingboard";

    /**
     * add an apikey to be used in imports
     * @deprecated keys should be added through users interface
     * @param array $options
     * @return void
     */
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

    public function idfeed_add($options)
    {
        $this->cli->header('adding new idfeed');
        if(count($options) < 2)
        {
            $this->cli->error('atleast two parameters (url, handle) should be given');
            return;
        }

        if(!is_null(Kingboard_IdFeed::findByUrl($options[0])))
        {
            $this->cli->error('a feed by this url allready exists!');
            return;
        }


        $feed = new Kingboard_IdFeed();
        $feed->url = $options[0];
        $feed->handle = $options[1];

        if(count($options) == 3)
            $feed->type = $options[2];

        $feed->save();
        $this->cli->positive('done');
    }

    public function test(array $options)
    {
        var_dump(Kingboard_Kill_MapReduce_KillsByShipByAlliance::mapReduceKills(99000652));
    }

}