<?php
class Kingboard_Auth_Jabber extends Kingboard_Auth {

    public static function login($username, $password) {
        try {
            $reg = King23_Registry::getInstance();
            $host = $reg->authJabberHost;
            $port = $reg->authJabberPort;
            $domain = !is_null($reg->authJabberDomain) ? $reg->authJabberDomain : $reg->authJabberHost;
            $xmpphp = XMPPHPWrapper::getXMPPHP($host, $port,$username, $password,"Kingboard", $domain);
            $xmpphp->connect();
            $xmpphp->processUntil('session_start');
            $xmpphp->disconnect();
            if(!($user = Kingboard_User::findOne(array('username' => $username))))
            {
                $user = new Kingboard_User();
                $user->username =  $username;
                $user->save();
            }
            $_SESSION["Kingboard_Auth"] = array("User" => $user);
            return $_SESSION["Kingboard_Auth"]["User"];
        } catch (Exception $e) {

        }
        return false;
    }
}