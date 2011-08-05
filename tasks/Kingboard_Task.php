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
        "feed_add" => "add a edk feed to the feeds to be pulled",
        "idfeed_add" => "add a idfeed to the idfeeds to be pulled",
        "file_import" => "import kills from files named *.txt, 1 parameter == directory",
        "ek_import" => "import range from <param1> to <param2> from eve-kill"
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


    public function file_import(array $options)
    {
        if(count($options) != 1 || !is_dir($options[0]))
        {
            $this->cli->error('file_import requires 1 parameter, which needs to be a directory');
            return;
        }

        $files = glob($options[0] . "*.txt");
        $processed = 0;
        $killcount = count($files);
        foreach($files as $file)
        {
            $this->cli->message('processing ' . ++$processed . ' out of ' .  $killcount);
            $mailtext = trim(join('', file($file)));

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
    }

    public function ek_import(array $options)
    {
        if(count($options) != 2 || !is_numeric($options[0]) || !is_numeric($options[1]))
        {
            $this->cli->error('need two numbers as parameters');
            return;
        }

        $processed = 0;
        $killcount = $options[1] - $options[0] +1;
        for($i = $options[0]; $i <= $options[1]; $i++)
        {
            $this->cli->message('processing ' . ++$processed . ' out of ' .  $killcount);
            $mailtext = trim(join('', file("http://eve-kill.net/kingboard.php?kllid=" . $i)));

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
    }

    public function test(array $options)
    {
        var_dump(Kingboard_Kill_MapReduce_KillsByShipByAlliance::mapReduceKills(99000652));
    }

}