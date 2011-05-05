<?php
class Kingboard_ApiActivationToken extends King23_MongoObject
{
    const TOKEN_LENGTH=8;
    protected $_className = __class__;

    public static function getById($id)
    {
        return self::_getInstanceById(__class__, $id);
    }

    public static function find($criteria = array())
    {
        return self::_find(__class__, $criteria);
    }

    public static function findOne($criteria = array())
    {
        return self::_getInstanceByCriteria(__class__, $criteria);
    }

    public static function create($userid, $apiuserid)
    {
        $token = new Kingboard_ApiActivationToken();
        $token['userid'] = $userid;
        $token['apiuserid'] = $apiuserid;

        // ensure this key isnt in use yet
        do
        {
            $keyset  = "abcdefghijklmABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            $randkey = "";
            for ($i=0; $i < Kingboard_ApiActivationToken::TOKEN_LENGTH; $i++)
                $randkey .= substr($keyset, rand(0, strlen($keyset)-1), 1);
        } while(!is_null(Kingboard_ApiActivationToken::findOneByToken($randkey)));

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
