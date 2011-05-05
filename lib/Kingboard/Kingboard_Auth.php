<?php
class Kingboard_Auth
{
    public static function login($username, $password)
    {
        $user = Kingboard_User::findOneByUsernameAndPassword($username, $password);
        if($user)
            $_SESSION["Kingboard_Auth"] = array("User" => $user);
        return $user;
    }

    public static function logout()
    {
        session_destroy(); // kill all session data!
    }

    public static function isLoggedIn()
    {
        if(isset($_SESSION["Kingboard_Auth"]) && isset($_SESSION["Kingboard_Auth"]["User"]))
            return true;
        return false;
    }

    public static function getUser()
    {
        if(isset($_SESSION["Kingboard_Auth"]) && isset($_SESSION["Kingboard_Auth"]["User"]))
        {
            $_SESSION["Kingboard_Auth"]["User"]->refresh();
            return $_SESSION["Kingboard_Auth"]["User"];
        }
        return false;
    }
}