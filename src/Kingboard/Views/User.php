<?php
namespace Kingboard\Views;

use Pheal\Exceptions\APIException;
use Pheal\Pheal;

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
        $context = array();
        if (isset($_POST['XSRF']) && \Kingboard\Lib\Form::getXSRFToken() == $_POST['XSRF']) {
            try {
                $pheal = new Pheal($_POST['apiuserid'], $_POST['apikey']);
                $pheal->detectAccess();

                $keyinfo = $pheal->accountScope->ApiKeyInfo();
                $keytype = $keyinfo->key->type;

                $accessmask = $keyinfo->key->accessMask;

                if (!($accessmask & 272)) {
                    throw new APIException(0, "fake exception, key invalid", "");
                }

                if (!isset($user['keys'])) {
                    $keys = array();
                } else {
                    $keys = $user['keys'];
                }

                $keys[$_POST['apiuserid']] = array(
                    'apiuserid' => $_POST['apiuserid'],
                    'apikey' => $_POST['apikey'],
                    'type' => $keytype,
                    'active' => true
                );
                $user['keys'] = $keys;
                $user->save();
                // ensure user is refreshed in session
                \Kingboard\Lib\Auth\Auth::getUser();

            } catch (ApiException $e) {
                $context = $_POST;
                $context['error'] = $e->getMessage();
                //$context['error'] = "the key could not be validated as a valid apikey";
            }
        } elseif (isset($_POST['XSRF'])) {
            return $this->error('XSRF detected');
        }

        if (isset($user['keys'])) {
            $activeKeys = $user['keys'];
        } else {
            $activeKeys = array();
        }
        foreach ($activeKeys as $id => $key) {
            try {
                $pheal = new Pheal($key['apiuserid'], $key['apikey']);
                $chars = $pheal->accountScope->Characters()->characters->toArray();
                $charlist = array();
                foreach ($chars as $char) {
                    $charlist[] = $char['name'];
                }
                $activeKeys[$id]["chars"] = join(', ', $charlist);
            } catch (\Exception $e) {
                //print_r($e);
            }
        }
        $context = array_merge(
            $context,
            array(
                'active_keys' => $activeKeys,
            )
        );
        $this->render('user/index.html', $context);
    }

    public function delete(array $params)
    {
        if (\Kingboard\Lib\Form::getXSRFToken() != $params['xsrf']) {
            return $this->error('xsrf token missmatch');
        }

        $user = \Kingboard\Lib\Auth\Auth::getUser();

        if (isset($user['keys'])) {
            $keys = $user['keys'];
            unset($keys[$params['keyid']]);
            $user->keys = $keys;
            $user->save();
        }

        $this->myKingboard(array());
    }
}
