<?php
namespace Kingboard\Lib\Fetcher;

/**
 * class to fetch from an edk style idfeed
 * this method itself is problematic since it might skip kills
 * that where added out of order
 */
use Pheal\Core\Result;

class IdFeed
{
    private $url;
    private $type;

    /**
     * @param string $url
     * @param string $type
     */
    public function __construct($url, $type = "eveid")
    {
        $this->type = $type;
        $this->url = $url;
    }

    /**
     * @param string $lastid
     * @return \PhealResult
     */
    public function fetch($lastid)
    {
        if($this->type == "eveid")
            return $this->fetchEVEID($lastid);
        else
            return $this->fetchOld($lastid);
    }

    /**
     * fetch with eve kill id
     * @param $lastid
     * @return \PhealResult
     * @throws \Exception
     */
    public function fetchEVEID($lastid)
    {
        $url = $this->url . '&lastID=' . $lastid;
        if($sxe = @simplexml_load_file($url))
            return new Result($sxe);
        throw new \Exception("could not load $url");
    }

    /**
     * fetch with local kill id
     * @param $lastid
     * @return \PhealResult
     * @throws \Exception
     */
    public function fetchOld($lastid)
    {
        $url = $this->url . '&allkills=1&lastintID=' . $lastid;
        if($sxe = @simplexml_load_file($url))
            return new Result($sxe);
        throw new \Exception("could not load $url");
    }
}