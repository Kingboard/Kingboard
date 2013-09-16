<?php
namespace Kingboard\Model;

class EveItem extends \King23\Mongo\MongoObject
{
    protected $_className = "Kingboard_EveItem";

    public static function getById($id)
    {
        return parent::getInstanceById(__CLASS__, $id);
    }

    public static function getInstanceByCriteria($criteria)
    {
        return parent::getInstanceByCriteria(__CLASS__, $criteria);
    }

    public static function find($criteria = array())
    {
        return parent::find(__CLASS__, $criteria);
    }

    public static function getByItemId($invItemID)
    {
        return parent::getInstanceByCriteria(__CLASS__, array('typeID' => (int)$invItemID));
    }

    public static function getShipIDs($typeName)
    {
        return parent::find(
            __CLASS__,
            array(
                '$and' =>
                array(
                    array('marketGroup.0.parentGroup.0.marketGroupName' => $typeName),
                    array(
                        '$or' =>
                        array(
                            array('marketGroup.0.parentGroup.0.parentGroup.0.parentGroup.0.marketGroupName' => 'Ships'),
                            array('marketGroup.0.parentGroup.0.parentGroup.0.parentGroup.0.parentGroup.0.marketGroupName' => 'Ships'),
                            array('marketGroup.0.parentGroup.0.parentGroup.0.marketGroupName' => 'Starbase & Sovereignty Structures'),
                            array('marketGroup.0.parentGroup.0.parentGroup.0.parentGroup.0.marketGroupName' => 'Starbase & Sovereignty Structures')
                        )
                    )
                )
            ),
            array(
                'typeName' => 1,
                'typeID' => 1
            )
        );
    }

    /**
     * returns MongoResult of type EveItem, stripped down to
     * type ID contents.
     * @static
     * @return \King23\Mongo\MongoResult
     */
    public static function getMarketIDs()
    {
        // Only needed when we update, all the market IDs here are stuff that are on the market ingame
        // 2 blueprints 4 ships 9 ship equipment 10 turrets & bays 11 ammo 24 implants & boosters 27 implants
        return parent::find(__CLASS__, array("marketGroupID" => array('$gt' => 0)), array("typeID" => 1));
    }

    public static function getItemValue($itemID)
    {
        $item = self::getByItemId($itemID);
        if (!is_null($item->iskValue)) {
            return $item->iskValue;
        } else {
            return (int)0;
        }
    }
}
