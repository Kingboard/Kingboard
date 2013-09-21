<?php
namespace Kingboard\Model;

class BattleSettings extends \King23\Mongo\MongoObject
{
    protected $_className = "Kingboard_BattleSettings";

    public static function getById($id)
    {
        return parent::doGetInstanceById(__CLASS__, $id);
    }

    public static function find($criteria = array())
    {
        return parent::doFind(__CLASS__, $criteria);
    }

    public static function getActiveSettings()
    {
        // lets only refresh the last 3 days for now
        $qry = array(
            "startdate" => array('$gt' => \MongoDate(now() - (3600 * 24 * 3)))
        );

        return parent::doFind(__CLASS__, $qry);
    }
}
