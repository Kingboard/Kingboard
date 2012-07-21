<?php
namespace Kingboard\Model;

class EveSolarSystem extends \King23\Mongo\MongoObject
{
    protected $_className = "Kingboard_EveSolarSystem";

    public static function getById($id)
    {
        return self::_getInstanceById(__CLASS__, $id);
    }

    public static function find($criteria = array(), $fields = array())
    {
        return self::_find(__CLASS__, $criteria, $fields);
    }

    public static function getInstanceByCriteria($criteria)
    {
        return self::_getInstanceByCriteria(__CLASS__, $criteria);
    }

    public static function getBySolarSystemId($solarSystemID)
    {
        return self::_getInstanceByCriteria(__CLASS__, array('itemID' => (int)$solarSystemID));
    }
} 
