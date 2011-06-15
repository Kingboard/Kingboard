<?php
class Kingboard_IdFeed_Fetcher
{
    private $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function fetch($lastid)
    {
        $url = $this->url . '&lastID=' . $lastid;
        return new PhealResult(simplexml_load_file($url));
    }
}