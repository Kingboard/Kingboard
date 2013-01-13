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

        $destinations[] = '/topic/location.solarsystem.' . $kill['location']['solarSystem'];
        $destinations[] = '/topic/location.region.' . $kill['location']['region'];

        foreach($kill['involvedCharacters'] as $characterID)
            if($characterID > 0)
                $destinations[] = '/topic/involved.character.' . $characterID;

        foreach($kill['involvedCorporations'] as $corporationID)
            if($corporationID > 0)
                $destinations[] = '/topic/involved.corporation.' . $corporationID;

        foreach($kill['involvedFactions'] as $factionID)
            if($factionID > 0 )
                $destinations[] = '/topic/involved.faction.' . $factionID;

        foreach($kill['involvedAlliances'] as $allianceID)
            if($allianceID > 0 )
                $destinations[] = '/topic/involved.alliance.' . $allianceID;

        $destinations = array_merge(
            $destinations,
            \King23\Core\Registry::getInstance()->stomp['destination_post']
        );
        return join(',', $destinations);
    }
}