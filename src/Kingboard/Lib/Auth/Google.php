<?php
namespace Kingboard\Lib\Auth;

/**
 * Google based authentication
 */
class Google extends Auth
{
    /**
     * @var string oauth2 token url
     */
    public static $host_url_token = "https://accounts.google.com/o/oauth2/token";

    /**
     * @var string oauth2 auth url (redirects here)
     */
    public static $host_url_code = "https://accounts.google.com/o/oauth2/auth";

    /**
     * @var string scope required for the api call, in googles case auth/userinfo.email
     */
    public static $scope = "https://www.googleapis.com/auth/userinfo.email";

    /**
     * get scope
     * @static
     * @return string
     */
    public static function getScope()
    {
        return self::$scope;
    }

    /**
     * get code Url
     * @static
     * @return string
     */
    public static function getCodeUrl()
    {
        return self::$host_url_code;
    }

    /**
     * get token url
     * @static
     * @return string
     */
    public static function getTokenUrl()
    {
        return self::$host_url_token;
    }

    /**
     * execute the login
     * @static
     * @param array $config this providers config array from the registry
     * @param string $fake
     * @throws \Exception
     * @return \Kingboard\Model\User
     */
    public static function login($config, $fake = null)
    {
        if (isset($_GET['error'])) {
            throw new \Exception("Could not login: " . $_GET['error']);
        }

        $tokens = \Kingboard\Lib\Auth\OAuth2\Consumer::getTokens(
            self::getTokenUrl(),
            $config['client_id'],
            $config['client_secret'],
            $_GET['code'],
            $config['redirect_url']
        );

        if (is_null($tokens)) {
            throw new \Exception("Error: could not access tokens");
        }

        var_dump($tokens);

        $userinfo = json_decode(
            file_get_contents("https://www.googleapis.com/oauth2/v1/userinfo?access_token=" . $tokens->access_token)
        );

        if (is_null($userinfo)) {
            throw new \Exception("Error: could not access userinfo");
        }

        $user = \Kingboard\Model\User::findOne(array("username" => $userinfo->email));
        if (is_null($user)) {
            $user = new \Kingboard\Model\User();
            $user->username = $userinfo->email;
            $user->save();
        }

        $_SESSION["Kingboard_Auth"] = array("User" => $user);
        return $user;
    }
}
