<?php
class Kingboard_EveRegion extends King23_MongoObject
{
    protected $_className = __CLASS__;

    public static function getById($id)
    {
        return self::_getInstanceById(__CLASS__, $id);
    }

    public static function find($criteria = array(), $fields = array())
    {
        return self::_find(__CLASS__, $criteria,$fields);
    }

    public static function getInstanceByCriteria($criteria)
    {
        return self::_getInstanceByCriteria(__CLASS__, $criteria);
    }

} 
