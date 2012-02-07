<?php
class Kingboard_EveItem extends King23_MongoObject
{
    protected $_className = __CLASS__;

    public static function getById($id)
    {
        return self::_getInstanceById(__CLASS__, $id);
    }

    public static function getInstanceByCriteria($criteria)
    {
        return self::_getInstanceByCriteria(__CLASS__, $criteria);
    }

    public static function find($criteria = array())
    {
        return self::_find(__CLASS__, $criteria);
    }

    public static function getByItemId($invItemID)
    {
        return self::_getInstanceByCriteria(__CLASS__, array('typeID' => (int)$invItemID));
    }
    public static function getShipIDs($typeName)
    {
		return self::_find(__CLASS__, array('$and' => array(
		array('marketGroup.0.parentGroup.0.marketGroupName' => $typeName),
		array('$or' => array(array('marketGroup.0.parentGroup.0.parentGroup.0.parentGroup.0.marketGroupName' => 'Ships'), array('marketGroup.0.parentGroup.0.parentGroup.0.parentGroup.0.parentGroup.0.marketGroupName' => 'Ships')))
		)), array(
			'typeName' => 1,
			'typeID' => 1
		));
    }
}
