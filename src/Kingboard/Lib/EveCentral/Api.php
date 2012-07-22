<?php
namespace Kingboard\Lib\EveCentral;

class Api
{
    private $itemID;
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

    public static function updateDB($itemID, $isk)
    {
        // Insert it to mongo
        $instance = self::getByItemId($itemID);
        $instance->iskvalue = $isk;
        $instance->save();
    }
}