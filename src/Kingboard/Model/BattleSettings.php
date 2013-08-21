<?php
namespace Kingboard\Model;

class BattleSettings extends \King23\Mongo\MongoObject
{
    protected $_className = "Kingboard_BattleSettings";

    public static function getById($id)
    {
        return self::_getInstanceById(__CLASS__, $id);
    }

    public static function find($criteria = array())
    {
        return self::_find(__CLASS__, $criteria);
    }

    public static function getActiveSettings()
    {
        // lets only refresh the last 3 days for now
        $qry = array(
            "startdate" => array('$gt' => \MongoDate(now() - (3600 * 24 * 3)))
        );

        return self::_find(__CLASS__, $qry);
    }
}
