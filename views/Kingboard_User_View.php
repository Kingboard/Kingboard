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

                $keys[$_POST['apiuserid']] = array(
                    'apiuserid' => $_POST['apiuserid'],
                    'apikey' => $_POST['apikey'],
                    'active' => false
                );
                $user['keys'] = $keys;
                $user->save();

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
                    $activeKeys = true;
                else
                    $pendingKeys = true;
            }

        $context = array_merge($context, array(
            'active_keys' => $activeKeys,
            'pending_keys' => $pendingKeys
        ));
        $this->render('user/index.html', $context);
    }

}