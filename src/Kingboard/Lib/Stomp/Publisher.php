<?php
namespace Kingboard\Lib\Stomp;

class Publisher
{
    private $stomp;
    private $user;
    private $password;
    private $url;

    public function __construct()
    {
        $reg = \King23\Core\Registry::getInstance();
        $this->user = $reg->stomp["user"];
        $this->password = $reg->stomp["passwd"];
        $this->url = $reg->stomp["url"];
        $this->stomp = new \Stomp($this->url, $this->user, $this->password);

    }

    public function send($destination, $message)
    {
        $this->stomp->send($destination, $message);
    }
}