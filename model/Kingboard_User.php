<?php
class Kingboard_User extends King23_MongoObject
{
    const STATUS_NEW = "NEW";
    const STATUS_EMAIL_VALID = "EMAIL_VALID";
    const STATUS_API_ADDED = "API_ADDED";
    const STATUS_VERIFIED_CHARACTER = "VERIFIED_CHARACTER";
    const STATUS_INACTIVE = "INACTIVE";
    
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

    public static function findOneByUsernameAndPassword($username, $password)
    {
        return self::findOne(array('username' => $username, 'password' => $password));
    }

    public function __wakeup()
    {
        if(!($mongo = King23_Registry::getInstance()->mongo))
            throw new King23_MongoException('mongodb is not configured');

        $colname = $this->_className;
        $this->_collection = $mongo['db']->$colname;
    }

}
