<?php
namespace Kingboard\Lib\Stomp;
class KillPublisher
{
    public static $publisherInstance;

    public static function send($kill)
    {
        if(is_null(self::$publisherInstance))
            self::$publisherInstance = new Publisher();
        self::$publisherInstance->send("/topic/kills", json_encode($kill));
    }
}