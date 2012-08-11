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
        $destinations = \King23\Core\Registry::getInstance()->stomp['destination_post'];

        // post to multiple destinations
        foreach($destinations as $destination)
        {
            self::$publisherInstance->send($destination, json_encode($kill));
        }
    }
}