<?php
namespace Kingboard\Model;

class EveSolarSystem extends \King23\Mongo\MongoObject
{
    protected $_className = "Kingboard_EveSolarSystem";

    public static function getById($id)
    {
        return parent::doGetInstanceById(__CLASS__, $id);
    }

    public static function find($criteria = array(), $fields = array())
    {
        return parent::doFind(__CLASS__, $criteria, $fields);
    }

    public static function getInstanceByCriteria($criteria)
    {
        return parent::doGetInstanceByCriteria(__CLASS__, $criteria);
    }

    public static function getBySolarSystemId($solarSystemID)
    {
        return parent::doGetInstanceByCriteria(__CLASS__, array('itemID' => (int)$solarSystemID));
    }
}
