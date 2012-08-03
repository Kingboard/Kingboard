<?php
namespace Kingboard\Lib\Stomp;
class KillPublisher
{
    public static $publisherInstance;

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