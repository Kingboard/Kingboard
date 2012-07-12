<?php
namespace Kingboard\Lib\Auth\OAuth2;
class Consumer
{
    /**
     * method used to build the url that users have to visit in order to authenticate
     * @static
     * @param string $hostUrl
     * @param string $clientId
     * @param string $redirectUri callback uri to this app
     * @param string $state xsrf protection token
     * @param string $scope
     * @param string $accessType
     * @return string
     */
    public static function getCodeRedirect($hostUrl, $clientId, $redirectUri = null, $state = null, $scope = null, $accessType ="online")
    {
        $requestString = $hostUrl."?response_type=code&client_id=$clientId";
        $requestString .= is_null($redirectUri)? "": '&redirect_uri='. urlencode($redirectUri);
        $requestString .= is_null($state) ? "": "&state=$state";
        $requestString .= is_null($scope) ? "" : "&scope=$scope";
        $requestString .= "&access_type=".$accessType;
        return $requestString;
    }

    /**
     * fetch tokens from oauth provider
     * @static
     * @param string $hostUrl
     * @param string $clientId
     * @param string $clientSecret
     * @param string $code
     * @param string $redirectUri
     * @return array
     * @throws \Exception
     */
    public static function getTokens($hostUrl, $clientId, $clientSecret, $code, $redirectUri)
    {
        $data = array(
            "client_id" => $clientId,
            "client_secret" => $clientSecret,
            "code" => $code,
            "redirect_uri" => $redirectUri,
            "grant_type" => "authorization_code"
        );
        $data = self::doPost($hostUrl, $data);
        $data = json_decode($data);
        if(isset($data->error))
            throw new \Exception("token request caused error: " . $data->error);
        return $data;
    }

    /**
     * simple post execution method
     * @static
     * @param string $url
     * @param array $data
     * @return string
     * @throws \Exception
     */
    public static function doPost($url, $data)
    {
        $params = array(
            'http' => array(
                'method' => 'POST',
                'content' => http_build_query($data),
                'header' => "Content-Type: application/x-www-form-urlencoded",
                'ignore_errors' => true
            )
        );

        $context = stream_context_create($params);
        $fp = fopen($url, 'rb', false, $context);
        if(!$fp)
            throw new \Exception("could not open: $url");

        return stream_get_contents($fp);
    }

}