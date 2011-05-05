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
        "key_activation" => "task to check for new key activates, and activate if so"
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
            $keys[$token['apiuserid']]['active'] = true;
            $user['keys'] = $keys;
            $user->save();
            $token->delete();
        }
    }
}
