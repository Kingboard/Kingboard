<?php
namespace Kingboard\Lib\EveCentral;

class Update
{
    private $itemID;
    private $isk;
    
    public static function updateValue($itemID, $isk)
    {
        // Insert it to mongo
        $instance = \Kingboard\Model\EveItem::getByItemId($itemID);
        $instance->iskValue = $isk;
        $instance->save();
    }
}