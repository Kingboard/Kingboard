<?php
class Kingboard_User_View extends Kingboard_Base_View
{
    public function __construct()
    {
        // ensure login
        parent::__construct(true);
    }

    public function myKingboard(array $parameters)
    {
        $user = Kingboard_Auth::getUser();
        $activeKeys = false;
        $pendingKeys = false;
        $context = array();
        if(isset($_POST['XSRF']) && Kingboard_Form::getXSRFToken() == $_POST['XSRF'])
        {
            try {
                $pheal = new Pheal($_POST['apiuserid'], $_POST['apikey']);
                $pheal->accountScope->AccountStatus();

                if(!isset($user['keys']))
                    $keys = array();
                else
                    $keys = $user['keys'];

                // ensure to remove existing activation keys if this is an update
                if($activationkey = Kingboard_ApiActivationToken::findOneByUseridAndApiUserid($user->_id, $_POST['apiuserid']))
                    $activationkey->delete();

                $activationkey = Kingboard_ApiActivationToken::create($user->_id, $_POST['apiuserid']);
                
                $keys[$_POST['apiuserid']] = array(
                    'apiuserid' => $_POST['apiuserid'],
                    'apikey' => $_POST['apikey'],
                    'active' => false
                );
                $user['keys'] = $keys;
                $user->save();
                // ensure user is refreshed in session
                Kingboard_Auth::getUser();

            } catch (PhealApiException $e) {
                $context = $_POST;
                $context['error'] = "the key could not be validated as a full apikey";
            }
        }
        elseif(isset($_POST['XSRF']))
            die('XSRF detected');

        if(isset($user['keys']))
            foreach($user['keys'] as $key)
            {
                if($key['active'])
                {
                    if(!is_array($activeKeys))
                        $activeKeys = array();
                    $activeKeys[] = $key;
                }
                else
                {
                    if(!is_array($pendingKeys))
                        $pendingKeys = array();
                    $key['activationkey'] = (String) Kingboard_ApiActivationToken::findOneByUseridAndApiUserid($user->_id, $key['apiuserid']);
                    $pendingKeys[] = $key;
                }
            }
        $context = array_merge($context, array(
            'active_keys' => $activeKeys,
            'pending_keys' => $pendingKeys,
            'apimailreceiver' => King23_Registry::getInstance()->apimailreceiver
        ));
        $this->render('user/index.html', $context);
    }

}