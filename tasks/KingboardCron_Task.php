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
        $this->cli->positive('update of KillsByShip stats completed');

        $this->cli->message('updating pilot loss stats');
        Kingboard_Kill_MapReduce_LossesByShipByPilot::mapReduce();
        $this->cli->positive('update of pilot loss stats completed');

        $this->cli->message('updating pilot kill stats');
        Kingboard_Kill_MapReduce_KillsByShipByPilot::mapReduce();
        $this->cli->positive('update of pilot kill stats completed');

        $this->cli->message('updating corporation loss stats');
        Kingboard_Kill_MapReduce_LossesByShipByCorporation::mapReduce();
        $this->cli->positive('update of corporation loss stats completed');

        $this->cli->message('updating corporation kill stats');
        Kingboard_Kill_MapReduce_KillsByShipByCorporation::mapReduce();
        $this->cli->positive('update of corporation kill stats completed');

        $this->cli->message('updating alliance loss stats');
        Kingboard_Kill_MapReduce_LossesByShipByAlliance::mapReduce();
        $this->cli->positive('update of alliance loss stats completed');

        $this->cli->message('updating alliance kill stats');
        Kingboard_Kill_MapReduce_KillsByShipByAlliance::mapReduce();
        $this->cli->positive('update of alliance kill stats completed');

        $this->cli->message('updating faction loss stats');
        Kingboard_Kill_MapReduce_LossesByShipByFaction::mapReduce();
        $this->cli->positive('update of faction loss stats completed');

        $this->cli->message('updating faction kill stats');
        Kingboard_Kill_MapReduce_KillsByShipByFaction::mapReduce();
        $this->cli->positive('update of faction kill stats completed');

        $this->cli->message('updating name lists for search');
        Kingboard_Kill_MapReduce_NameSearch::mapReduce();
        $this->cli->positive("name list updated");
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

    public function idfeed_import(array $options)
    {
        if(count($options) < 1)
        {
            $this->cli->error('1 parameter required: handle');
            return;
        }
        $this->cli->message('starting import of handle: ' . $options[0]);

        $idfeed = Kingboard_IdFeed::findByHandle($options[0]);
        if(is_null($idfeed))
        {
            $this->cli->error('no such handle');
            return;
        }

        $lastid =  $idfeed->lastId;
        if(is_null($lastid))
            $lastid = 0;

        if(!is_null($idfeed->type))
            $kidf = new Kingboard_IdFeed_Fetcher($idfeed->url, $idfeed->type);
        else
            $kidf = new Kingboard_IdFeed_Fetcher($idfeed->url);

        try {
            $kills = $kidf->fetch($lastid)->kills;
            $kakp = new Kingboard_ApiKillParser();
            $info = $kakp->parseKills($kills);
            $total = $info['oldkills'] + $info['newkills'];
            $this->cli->message("fetched $total kills, " . $info['oldkills'] . " allready known, " . $info['newkills'] . " new");
            $this->cli->message("Last id was: " . $info['lastID'] . " internal: " . $info['lastIntID']);

            if(!is_null($idfeed->type) &&  $idfeed->type == "intid")
                $idfeed->lastId = $info['lastIntID'];
            else
                $idfeed->lastId = $info['lastID'];

            $idfeed->save();
        } catch (Exception $e )
        {
            $this->cli->error("idfetch exception occured: " . $e->getMessage());
        }
    }

    public function api_import(array $options)
    {
        $this->cli->message("api import running");
        $newkills = 0;
        $oldkills = 0;
        $errors = 0;
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
                    $this->cli->message("fetch done, parsing now");
                    $kakp = new Kingboard_ApiKillParser();
                    $info = $kakp->parseKills($kills);
                    $oldkills += $info['oldkills'];
                    $newkills += $info['newkills'];
                    $errors += $info['errors'];
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
        $this->cli->message("found $totalkills kills, $oldkills where allready in database, $newkills added ( errors: $errors)");
    }
}
