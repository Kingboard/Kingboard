<?php
namespace Kingboard\Views;

class User extends \Kingboard\Views\Base
{
    public function __construct()
    {
        // ensure login
        parent::__construct(true);
    }

    public function myKingboard(array $parameters)
    {
        $user = \Kingboard\Lib\Auth\Auth::getUser();
        $activeKeys = array();
        $pendingKeys = false;
        $context = array();
        if(isset($_POST['XSRF']) && \Kingboard\Lib\Form::getXSRFToken() == $_POST['XSRF'])
        {
            try {
                $pheal = new \Pheal($_POST['apiuserid'], $_POST['apikey']);
                $pheal->detectAccess();

                $keyinfo = $pheal->accountScope->ApiKeyInfo();
                $keytype = $keyinfo->key->type;

                $accessmask = $keyinfo->key->accessMask;

                if(!($accessmask & 272))
                    throw new \PhealAPIException(0, "fake exception, key invalid", "");

                if(!isset($user['keys']))
                    $keys = array();
                else
                    $keys = $user['keys'];

                // ensure to remove existing activation keys if this is an update
                if($activationkey = \Kingboard\Model\ApiActivationToken::findOneByUseridAndApiUserid($user->_id, $_POST['apiuserid']))
                    $activationkey->delete();

                $activationkey = \Kingboard\Model\ApiActivationToken::create($user->_id, $_POST['apiuserid']);

                $keys[$_POST['apiuserid']] = array(
                    'apiuserid' => $_POST['apiuserid'],
                    'apikey' => $_POST['apikey'],
                    'type' => $keytype,
                    'active' => false
                );
                $user['keys'] = $keys;
                $user->save();
                // ensure user is refreshed in session
                \Kingboard\Lib\Auth\Auth::getUser();

            } catch (\PhealApiException $e) {
                $context = $_POST;
                $context['error'] = $e->getMessage();
                //$context['error'] = "the key could not be validated as a valid apikey";
            }
        }
        elseif(isset($_POST['XSRF']))
            die('XSRF detected');

        if(isset($user['keys']))
            foreach($user['keys'] as $key)
            {
                if($key['active'])
                    $activeKeys[] = $key;
                else
                {
                    if(!is_array($pendingKeys))
                        $pendingKeys = array();
                    $key['activationkey'] = (String) \Kingboard\Model\ApiActivationToken::findOneByUseridAndApiUserid($user->_id, $key['apiuserid']);
                    $pendingKeys[] = $key;
                }
            }
        $charkeylist = array();
        foreach($activeKeys as $key)
        {
            try {
                $pheal = new \Pheal($key['apiuserid'], $key['apikey']);
                $chars = $pheal->accountScope->Characters()->characters->toArray();
                foreach($chars as $char)
                    $charkeylist[$key['apiuserid'] . "|" . $char['characterID']] = $char['name'];
            } catch (\PhealAPIException $e) {
                print_r($e);
            }
        }
        $context = array_merge($context, array(
            'active_keys' => $activeKeys,
            'pending_keys' => $pendingKeys,
            'apimailreceiver' => \King23\Core\Registry::getInstance()->apimailreceiver,
            'active_characters' => $charkeylist
        ));
        $this->render('user/index.html', $context);
    }

}
