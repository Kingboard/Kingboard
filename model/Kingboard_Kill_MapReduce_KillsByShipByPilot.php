<?php
/**
 * creates / allows access to stats about which shiptype has been killed how often
 */
class Kingboard_Kill_MapReduce_KillsByShipByPilot extends Kingboard_Kill_MapReduce_KillsByEntity implements ArrayAccess
{
    protected $_className = "Kingboard_Kill_MapReduce_KillsByShipByPilot";

    public static function mapReduceKills($pilotid)
    {
        return self::mapReduce(array('attackers.characterID' => (int) $pilotid));
    }

    public static function mapReduceLosses($pilotid)
    {
        return self::mapReduce(array('victim.characterID' => (int) $pilotid));
    }
}
