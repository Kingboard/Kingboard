<?php
namespace Kingboard\Lib\EveCentral;

class Update
{
    private $itemID;
    public static function updateValue($itemID, $isk)
    {
        // Insert it to mongo
        $instance = \Kingboard\Model\EveItem::getByItemId($itemID);
        $instance->iskvalue = $isk;
        $instance->save();
    }
}