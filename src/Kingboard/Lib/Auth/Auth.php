<?php
namespace Kingboard\Lib\Auth;

/**
 * Simple Authentication class which can be used if no other
 * Authentication Option is available.
 */
class Auth
{
    /**
     * Execute a Login for $username with $password
     * @static
     * @param string $username
     * @param string $password
     * @return \Kingboard\Model\User
     */
    public static function login($username, $password)
    {
        $user = \Kingboard\Model\User::findOneByUsernameAndPassword($username, $password);
        if ($user) {
            $_SESSION["Kingboard_Auth"] = array("User" => $user);
        }
        return $user;
    }

    /**
     * logout current user
     * @static
     */
    public static function logout()
    {
        session_destroy(); // kill all session data!
    }

    /**
     * Perform check if the current session contains a logged in user
     * @static
     * @return bool
     */
    public static function isLoggedIn()
    {
        if (isset($_SESSION["Kingboard_Auth"]) && isset($_SESSION["Kingboard_Auth"]["User"])) {
            return true;
        }
        return false;
    }

    /**
     * Get user object, will return false if currently no user is logged in
     * @static
     * @return \Kingboard\Model\User|bool
     */
    public static function getUser()
    {
        if (isset($_SESSION["Kingboard_Auth"]) && isset($_SESSION["Kingboard_Auth"]["User"])) {
            $_SESSION["Kingboard_Auth"]["User"]->refresh();
            return $_SESSION["Kingboard_Auth"]["User"];
        }
        return false;
    }
}
