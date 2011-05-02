<?php
/*
 MIT License
 Copyright (c) 2011 Peter Petermann

 Permission is hereby granted, free of charge, to any person
 obtaining a copy of this software and associated documentation
 files (the "Software"), to deal in the Software without
 restriction, including without limitation the rights to use,
 copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the
 Software is furnished to do so, subject to the following
 conditions:

 The above copyright notice and this permission notice shall be
 included in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 OTHER DEALINGS IN THE SOFTWARE.

*/
/**
 * Define constanst that describe the nature of an attacker
 * and functions to determine them
 *
 * @author Georg Grossberger
 * @package Kingboard
 */
class Kingboard_Helper_EntityType
{
    const ENTITY_CHARACTER  = 'char';       // The way it's supposed to be
    const ENTITY_NPC        = 'npc';        // A rat, sleeper
    const ENTITY_DEPLOYABLE = 'deploy';     // A deployed item, eg. a warp disrupt probe
    const ENTITY_STRUCTURE  = 'structure';  // A player structure, eg a POS
    const ENTITY_UNKNOWN    = 'na';         // A type that cannot be determined thus is invalid in most cases

    /**
     * Market group id of deployable inventory items
     * 
     * @var integer
     */
    private static $groupIdDeployable = 548;

    /**
     * Category ID of structure types (POS, TCU etc.)
     * 
     * @var integer
     */
    private static $categoryIdStructure = 23;

    /**
     * Category ID of a NPC type
     *
     * @var integer
     */
    private static $categoryIdNpc = 11;

    /**
     * Get the entity type by it's ID
     *
     * @param integer $id
     * @return string
     */
    public static function getEntityTypeByEntityId($id)
    {
        $id = (int) $id;
        $item = Kingboard_EveItem::getByItemId($id);

        if ($item)
        {
            if ((int) $item->Group['groupID'] === self::$groupIdDeployable)
            {
                return self::ENTITY_DEPLOYABLE;
            }

            switch ((int) $item->Category['categoryID'])
            {
                case self::$categoryIdStructure: return self::ENTITY_STRUCTURE;
                case self::$categoryIdNpc: return self::ENTITY_NPC;
            }
        }

        try
        {
            $api = new Pheal();
            $apiResult = $api->eveScope->CharacterName(array('ids' => $id))->characters->toArray();

            if (is_array($apiResult) && !empty($apiResult))
            {
                $name = $apiResult[0]['name'];
                $item = Kingboard_EveItem::getInstanceByCriteria(array('typeName' => $name));

                if ($item)
                {
                    if ((int) $item->typeID === $id) {
                        return self::ENTITY_NPC;
                    }
                }
                return self::ENTITY_CHARACTER;
            }
        }
        catch (PhealException $e) {}

        return self::ENTITY_UNKNOWN;
    }
}
