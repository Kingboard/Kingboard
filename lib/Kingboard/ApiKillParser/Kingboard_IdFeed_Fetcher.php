<?php
class Kingboard_IdFeed_Fetcher
{
    private $url;
    private $type;

    public function __construct($url, $type = "eveid")
    {
        $this->type = $type;
        $this->url = $url;
    }

    public function fetch($lastid)
    {
        if($this->type == "eveid")
            return $this->fetchEVEID($lastid);
        else
            return $this->fetchOld($lastid);
    }

    public function fetchEVEID($lastid)
    {
        $url = $this->url . '&lastID=' . $lastid;
        return new PhealResult(simplexml_load_file($url));
    }

    public function fetchOld($lastid)
    {
        $url = $this->url . '&allkills=1&lastintID=' . $lastid;
        return new PhealResult(simplexml_load_file($url));
    }
}