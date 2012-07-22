<?php
namespace Kingboard\Model;
class User extends \King23\Mongo\MongoObject
{
    const STATUS_NEW = "NEW";
    const STATUS_EMAIL_VALID = "EMAIL_VALID";
    const STATUS_API_ADDED = "API_ADDED";
    const STATUS_VERIFIED_CHARACTER = "VERIFIED_CHARACTER";
    const STATUS_INACTIVE = "INACTIVE";
    
    protected $_className = "Kingboard_User";

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

    public static function findOneByUsernameAndPassword($username, $password)
    {
        return self::findOne(array('username' => $username, 'password' => $password));
    }

}
