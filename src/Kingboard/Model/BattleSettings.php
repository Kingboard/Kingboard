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

    /**
     * get all settings for battles which have started within the time span from $hours to now, 7 days by default

     * @param int $hours
     * @return \King23\Mongo\MongoResult
     */
    public static function getActiveSettings($hours = 168)
    {
        $ts = 3600 * $hours;
        // lets only refresh the last 3 days for now
        $qry = array(
            "startdate" => array('$gt' => new \MongoDate(time() - $ts))
        );

        return parent::doFind(__CLASS__, $qry);
    }

    public function toArray()
    {
        return $this->_data;
    }
}
