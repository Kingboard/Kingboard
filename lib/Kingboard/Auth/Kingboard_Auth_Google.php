<?php
class Kingboard_Auth_Google extends Kingboard_Auth
{
    /**
     * @var string oauth2 token url
     */
    public static $host_url_token = "https://accounts.google.com/o/oauth2/token";

    /**
     * @var string oauth2 auth url (redirects here)
     */
    public static $host_url_code  = "https://accounts.google.com/o/oauth2/auth";

    /**
     * @var string scope required for the api call, in googles case auth/userinfo.email
     */
    public static $scope = "https://www.googleapis.com/auth/userinfo.email";

    /**
     * get scope
     * @static
     * @return string
     */
    public static function getScope() { return self::$scope; }

    /**
     * get code Url
     * @static
     * @return string
     */
    public static function getCodeUrl() { return self::$host_url_code; }

    /**
     * get token url
     * @static
     * @return string
     */
    public static function getTokenUrl() { return self::$host_url_token; }

    /**
     * execute the login
     * @static
     * @param array $config this providers config array from the registry
     * @return Kingboard_User
     */
    public static function login($config)
    {
        $tokens = Kingboard_OAuth2_Consumer::getTokens(
            self::getCodeUrl(),
            $config['client_id'],
            $config['client_secret'],
            $_GET['code'],
            $config['redirect_url'],
            self::getScope()
        );

        $userinfo = json_decode(
            file_get_contents("https://www.googleapis.com/oauth2/v1/userinfo?access_token=" . $tokens->access_token)
        );

        $user = Kingboard_User::findOne(array("username" => $userinfo->email));
        if(is_null($user))
        {
            $user = new Kingboard_User();
            $user->username = $userinfo->email;
        }

        $_SESSION["Kingboard_Auth"] = array("User" => $user);
        return $user;
    }
}