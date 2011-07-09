<?php
/**
 * creates / allows access to stats about which shiptype has been killed how often
 */
class Kingboard_Kill_MapReduce_KillsByShipByAlliance extends Kingboard_Kill_MapReduce_KillsByEntity implements ArrayAccess
{
    protected $_className = "Kingboard_Kill_MapReduce_KillsByShipByAlliance";

    public static function mapReduceKills($allianceId)
    {
        return self::mapReduce(array('attackers.allianceID' => (int) $allianceId));
    }

    public static function mapReduceLosses($allianceId)
    {
        return self::mapReduce(array('victim.allianceID' => (int) $allianceId));
    }
}
