<?php
namespace Kingboard\ApiActivationToken;

class ApiActivationToken extends \King23\Mongo\MongoObject
{
    const TOKEN_LENGTH=8;
    protected $_className = "Kingboard_ApiActivationToken";

    public static function getById($id)
    {
        return self::_getInstanceById(__CLASS__, $id);
    }

    public static function find($criteria = array())
    {
        return self::_find(__CLASS__, $criteria);
    }

    public static function findOne($criteria = array())
    {
        return self::_getInstanceByCriteria(__CLASS__, $criteria);
    }

    public static function create($userid, $apiuserid)
    {
        $token = new ApiActivationToken();
        $token['userid'] = $userid;
        $token['apiuserid'] = $apiuserid;

        // ensure this key isnt in use yet
        do
        {
            $keyset  = "abcdefghijklmABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            $randkey = "";
            for ($i=0; $i < ApiActivationToken::TOKEN_LENGTH; $i++)
                $randkey .= substr($keyset, rand(0, strlen($keyset)-1), 1);
        } while(!is_null(ApiActivationToken::findOneByToken($randkey)));

        $token['token'] = $randkey;
        $token->save();
        return $token;
    }

    public static function findOneByUseridAndApiUserid($userid, $apiuserid)
    {
        return self::_getInstanceByCriteria(__CLASS__, array('userid' => $userid, 'apiuserid' => $apiuserid));
    }

    /**
     * return instance for token string
     * @static
     * @param string $token
     * @return King23_ApiActivationToken
     */
    public static function findOneByToken($token)
    {
        return self::_getInstanceByCriteria(__CLASS__, array('token' => $token));
    }

    /**
     * @return string token
     */
    public function __toString()
    {
        return $this['token'];
    }
}
