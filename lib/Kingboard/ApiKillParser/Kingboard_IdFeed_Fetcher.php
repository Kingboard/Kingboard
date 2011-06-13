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
        return new PhealResult(simplexml_load_file($this->url . '&lastID=' . $lastid));
    }
}