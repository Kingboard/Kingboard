<?php
namespace Kingboard\Lib\Stomp;
/**
 * simple class to publish kills by sending them to a stomp queue/topic
 */
class KillPublisher
{
    public static $publisherInstance;

    /**
     * json encodes and sends the kill array to the stomp queue
     * @static
     * @param array $kill
     */
    public static function send($kill)
    {
        if(is_null(self::$publisherInstance))
            self::$publisherInstance = new Publisher();
        self::$publisherInstance->send(self::getDestinations($kill), json_encode($kill));
    }

    /**
     * get a combination of the configured destinations as well as the automated post
     * location, such as solarSystem and involved entities
     * @param array $kill
     * @return string
     */
    private static function getDestinations(array $kill)
    {
        $destinations = array();

        $destinations[] = '/topic/location.solarsystem.' . $kill['solarSystemID'];

        // victim
        if($kill['victim']['characterID'] > 0)
            $destinations[] = '/topic/involved.character.' . $kill['victim']['characterID'];
        if($kill['victim']['corporationID'] > 0)
            $destinations[] = '/topic/involved.corporation.' . $kill['victim']['corporationID'];
        if($kill['victim']['factionID'] > 0)
            $destinations[] = '/topic/involved.faction.' . $kill['victim']['factionID'];
        if($kill['victim']['allianceID'] > 0)
            $destinations[] = '/topic/involved.alliance.' . $kill['victim']['allianceID'];

        // attackers
        foreach($kill['attackers'] as $attacker)
        {
            if($attacker['characterID'] > 0)
                $destinations[] = '/topic/involved.character.' . $attacker['characterID'];
            if($attacker['corporationID'] > 0)
                $destinations[] = '/topic/involved.corporation.' . $attacker['corporationID'];
            if($attacker['factionID'] > 0)
                $destinations[] = '/topic/involved.faction.' . $attacker['factionID'];
            if($attacker['allianceID'] > 0)
                $destinations[] = '/topic/involved.alliance.' . $attacker['allianceID'];
        }

        $destinations = array_merge(
            $destinations,
            \King23\Core\Registry::getInstance()->stomp['destination_post']
        );
        return join(',', $destinations);
    }
}