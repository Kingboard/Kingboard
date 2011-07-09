<?php
/**
 * creates / allows access to stats about which shiptype has been killed how often
 */
class Kingboard_Kill_MapReduce_KillsByShipByCorporation extends Kingboard_Kill_MapReduce_KillsByEntity implements ArrayAccess
{
    protected $_className = "Kingboard_Kill_MapReduce_KillsByShipByCorporation";

    public static function mapReduceKills($corpid)
    {
        return self::mapReduce(array('attackers.corporationID' => (int) $corpid));
    }

    public static function mapReduceLosses($corpid)
    {
        return self::mapReduce(array('victim.corporationID' => (int) $corpid));
    }
}
