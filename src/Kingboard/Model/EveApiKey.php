<?php
namespace Kingboard\Model;
class EveApiKey extends \King23\Mongo\MongoObject
{
    protected $_className = "Kingboard_EveApiKey";

    public static function getById($id)
    {
        return self::_getInstanceById(__CLASS__, $id);
    }

    public static function find($criteria = array())
    {
        return self::_find(__CLASS__, $criteria);
    }

    public static function getByUserId($userID)
    {
        return self::_getInstanceByCriteria(__CLASS__, array('userid' => $userID));
    }
}
