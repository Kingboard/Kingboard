<?php
namespace Kingboard\Lib\Stomp;

/**
 * class to interact with the stomp client
 */
class Publisher
{
    private $stomp;
    private $user;
    private $password;
    private $url;

    /**
     * read the config and create stomp object according to config
     */
    public function __construct()
    {
        $reg = \King23\Core\Registry::getInstance();
        $this->user = $reg->stomp["user"];
        $this->password = $reg->stomp["passwd"];
        $this->url = $reg->stomp["url"];
        $this->stomp = new \Stomp($this->url, $this->user, $this->password);

    }

    /**
     * sends $message to topic/queue identified by $destination
     * @param string $destination for example "topic/kills"
     * @param string $message
     */
    public function send($destination, $message)
    {
        $this->stomp->send($destination, $message);
    }
}