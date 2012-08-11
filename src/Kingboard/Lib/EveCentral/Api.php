<?php
namespace Kingboard\Lib\EveCentral;

/**
 * Class to get values from EveCentral API
 */
class Api
{
    /**
     * get isk value for given itemID
     * @static
     * @param string $itemID
     * @return float|null
     */
    public static function getValue($itemID)
    {
        if(empty($itemID))
            return null;
        
        $url = "http://api.eve-central.com/api/marketstat?usesystem=30000142&typeid=".$itemID;
        $xml = file_get_contents($url);
        $simplexml = simplexml_load_string($xml);
        $isk = (float) $simplexml->marketstat->type->sell->median;
        return $isk;
    }
}