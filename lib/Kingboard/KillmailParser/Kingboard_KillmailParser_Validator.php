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
 * Validate the data of a killmail for missing information
 *
 * @author Georg Grossberger
 * @package Kingboard
 */
class Kingboard_KillmailParser_Validator
{
    /**
     * Validate the entire killmail data, which must be an array, the way
     * it will be stored in the database
     *
     * @param array $data
     * @return boolean
     */
    public function validateKillmailData(array $data)
    {
        // Validate the killtime
        if (empty($data['killTime']))
        {
            throw new Kingboard_KillmailParser_KillmailErrorException('No killtime');
        }
        $this->validateKilltime($data['killTime']);

        // Validate the basic structure of the array
        if (empty($data['victim']) || empty($data['location']) || empty($data['attackers']))
        {
            throw new Kingboard_KillmailParser_KillmailErrorException('Required data entry is missing');
        }

        // Validate the victim data
        if (!is_array($data['victim']) || count($data['victim']) < 5)
        {
            throw new Kingboard_KillmailParser_KillmailErrorException('Victim is missing data entries');
        }
        $this->validateVictim($data['victim']);

        // Validate the attackers
        if (!is_array($data['attackers']) || count($data['attackers']) < 1)
        {
            throw new Kingboard_KillmailParser_KillmailErrorException('Attackers are missing');
        }

        foreach($data['attackers'] as $attacker)
        {
            $this->validateAttacker($attacker);
        }

        // Validate items
        if (!is_array($data['items']))
        {
            throw new Kingboard_KillmailParser_KillmailErrorException('Items not set');
        }

        foreach ($data['items'] as $item)
        {
            if (!is_array($item))
            {
                throw new Kingboard_KillmailParser_KillmailErrorException('Invalid item entry');
            }
            $this->validateItem($item);
        }

        return true;
    }

    /**
     * Validates the killtime, as a UNIX timestamp in a valid range
     *
     * @param MongoDate $time
     * @return boolean
     */
    public function validateKilltime(MongoDate $time)
    {
        $minTime = mktime(0, 0, 0, 5, 1, 2003); // Must be after the creation of the universe
        $maxTime = time() - 10; // Must be older than now

        // Extract timestamp
        $time = (string) $time;
        $parts = explode(' ', $time);
        $time    = (int) $parts[1];

        if (!is_int($time) || $time < $minTime || $time > $maxTime)
        {
            throw new Kingboard_KillmailParser_KillmailErrorException('Invalid date given');
        }
        return true;
    }

    /**
     * Validate the content of an item entry
     *
     * @param array $item
     * @return boolean
     */
    public function validateItem(array $item)
    {
        if (count($item) < 4)
        {
            throw new Kingboard_KillmailParser_KillmailErrorException('Invalid item data');
        }

        if (!$this->isTypeEntryValid($item, 'type'))
        {
            throw new Kingboard_KillmailParser_KillmailErrorException('Unknown type entry for item');
        }

        if (!is_int($item['qtyDropped']) || !is_int($item['qtyDestroyed']) || ($item['qtyDropped'] == 0 && $item['qtyDestroyed'] == 0))
        {
            throw new Kingboard_KillmailParser_KillmailErrorException('Invalid qty value for item');
        }

        return true;
    }

    /**
     * Check if an attacker has all data set
     * 
     * @param array $attacker
     * @return boolean
     */
    public function validateAttacker(array $attacker)
    {
        if (!$this->isTypeEntryValid($attacker, 'character'))
        {
            throw new Kingboard_KillmailParser_KillmailErrorException('No character for attacker given');
        }

        if (!$this->validateCharacterName($attacker['characterName']) || !$this->isIdValid($attacker, 'character'))
        {
            throw new Kingboard_KillmailParser_KillmailErrorException('Invalid character for attacker given');
        }

        $isSleeper = false;
        $isNpc = false;
        $result = Kingboard_EveItem::getInstanceByCriteria(array('typeName' => $attacker['characterName']));

        if ($result)
        {
            if ($result->typeID && isset($result->Group['categoryID']))
            {
                $isNpc = true;
                $isSleeper =
                    stripos($attacker['characterName'], 'sleep') !== false &&
                    (int) $result->typeID === $attacker['characterID'] &&
                    (int) $result->Group['categoryID'] === 11;
            }
        }
        $isBubble = false;
        if (isset($attacker['weaponType']))
        {
            $result = Kingboard_EveItem::getInstanceByCriteria(array('typeName' => $attacker['weaponType']));
            if ($result)
            {
                if (isset($result->Group['categoryID']))
                {
                    $isBubble = (int) $result->Group['categoryID'] === 8;
                }
            }
        }

        if (!$isSleeper)
        {
            if (!$this->isTypeEntryValid($attacker, 'corporation'))
            {
                throw new Kingboard_KillmailParser_KillmailErrorException('No corporation data for attacker found');
            }

            if (!$this->isIdValid($attacker, 'corporation') || !$this->validateOrganisationName($attacker['corporationName']))
            {
                throw new Kingboard_KillmailParser_KillmailErrorException('Invalid corporation data for attacker');
            }
        }
        elseif ($this->isTypeEntryValid($attacker, 'corporation'))
        {
            throw new Kingboard_KillmailParser_KillmailErrorException('Sleepers must not have a corporation');
        }

        // Alliance and faction are optional, but must be valid if set

        if ($this->isTypeEntryValid($attacker, 'alliance'))
        {
           if (!$this->isIdValid($attacker, 'alliance') || !$this->validateOrganisationName($attacker['allianceName']))
           {
               throw new Kingboard_KillmailParser_KillmailErrorException('Invalid alliance data for attacker');
           }
        }

        if ($this->isTypeEntryValid($attacker, 'faction'))
        {
           if (!$this->isIdValid($attacker, 'faction') || !$this->validateOrganisationName($attacker['factionName']))
           {
               throw new Kingboard_KillmailParser_KillmailErrorException('Invalid faction data for attacker');
           }
        }

        if (!isset($attacker['damageDone']))
        {
            throw new Kingboard_KillmailParser_KillmailErrorException('Damage done of attacker is missing');
        }

        if (!is_int($attacker['damageDone']) || $attacker['damageDone'] < 0)
        {
            throw new Kingboard_KillmailParser_KillmailErrorException('Invalid value for damage done');
        }

        if (!$isBubble && !$isNpc)
        {
            if (!$this->isTypeEntryValid($attacker, 'shipType'))
            {
                throw new Kingboard_KillmailParser_KillmailErrorException('Invalid ship for attacker given');
            }
        }
        if (!$isNpc)
        {
            if (!$this->isTypeEntryValid($attacker, 'weaponType'))
            {
                throw new Kingboard_KillmailParser_KillmailErrorException('Invalid weapon for attacker given');
            }

            if (!is_float($attacker['securityStatus']) || $attacker['securityStatus'] < -10.0 || $attacker['securityStatus'] > 10.0)
            {
                throw new Kingboard_KillmailParser_KillmailErrorException('Invalid security status for attacker');
            }
        }

        if (!is_bool($attacker['finalBlow']))
        {
            throw new Kingboard_KillmailParser_KillmailErrorException('No final blow information');
        }
        
        return true;
    }

    /**
     * Check if a victim has all required data set
     *
     * @param array $victim
     * @return boolean
     */
    public function validateVictim($victim)
    {
        $isStructure = false;
        if (isset($victim['shipType']))
        {
            $result = Kingboard_EveItem::getInstanceByCriteria(array('typeName' => $victim['shipType']));
            if ($result)
            {
                $isStructure =
                    $result->typeName == $victim['shipType'] &&
                    (int) $victim['shipTypeID'] === (int) $result->typeID &&
                    in_array((int) $result->Group['categoryID'], array(23,39,40));
            }
        }

        if (!$isStructure || $victim['characterName'] != '' || $victim['characterID'] > 0)
        {
            if (!$this->isTypeEntryValid($victim, 'character'))
            {
                throw new Kingboard_KillmailParser_KillmailErrorException('No character information for victim found');
            }

            if (!$this->validateCharacterName($victim['characterName']))
            {
                throw new Kingboard_KillmailParser_KillmailErrorException('Invalid victim character name');
            }

            if (!$this->isIdValid($victim, 'character'))
            {
                throw new Kingboard_KillmailParser_KillmailErrorException('Invalid character ID for victim');
            }
        }

        if (!$this->isTypeEntryValid($victim, 'corporation'))
        {
            throw new Kingboard_KillmailParser_KillmailErrorException('No corporation data for victim found');
        }

        if (!$this->isIdValid($victim, 'corporation') || !$this->validateOrganisationName($victim['corporationName']))
        {
            throw new Kingboard_KillmailParser_KillmailErrorException('Invalid corporation data for victim');
        }

        if ($this->isTypeEntryValid($victim, 'alliance'))
        {
           if (!$this->isIdValid($victim, 'alliance') || !$this->validateOrganisationName($victim['allianceName']))
           {
               throw new Kingboard_KillmailParser_KillmailErrorException('Invalid alliance data for victim');
           }
        }

        if ($this->isTypeEntryValid($victim, 'faction'))
        {
           if (!$this->isIdValid($victim, 'faction') || !$this->validateOrganisationName($victim['factionName']))
           {
               throw new Kingboard_KillmailParser_KillmailErrorException('Invalid faction data for victim');
           }
        }

        if (empty($victim['damageTaken']))
        {
            throw new Kingboard_KillmailParser_KillmailErrorException('Damage taken of victim is missing');
        }

        if (!is_int($victim['damageTaken']) || $victim['damageTaken'] < 1)
        {
            throw new Kingboard_KillmailParser_KillmailErrorException('Invalid value for taken damage');
        }

        if (!$this->isTypeEntryValid($victim, 'shipType'))
        {
            throw new Kingboard_KillmailParser_KillmailErrorException('Invalid ship for victim given');
        }

        return true;
    }

    /**
     * Check if an ID is present that makes sense
     *
     * @param array $data
     * @param string $key
     * @return boolean
     */
    protected function isIdValid($data, $key)
    {
        $data = $data[$key . 'ID'];
        return (!empty($data) && is_int($data) && $data > 1000);
    }

    /**
     * Validate if a name and an ID is present in the
     * data array for the given key
     *
     * @param array $data
     * @param string $key
     * @return boolean
     */
    protected function isTypeEntryValid($data, $key)
    {
        if (!empty($data[$key . 'Name']) || !empty($data[$key]))
        {
            return !empty($data[$key . 'ID']);
        }
        return false;
    }

    /**
     * Validate a character name
     * Validation according to http://support.eveonline.com/Pages/KB/Article.aspx?id=37
     *
     * @param string $name
     * @return boolean
     */
    public function validateCharacterName($name)
    {
        // Only chars, numbers spaces and single quotes
        // Length between 4 and 24
        if (!preg_match("/^([a-zA-Z0-9 \']{4,24})$/", $name))
        {
            throw new Kingboard_KillmailParser_KillmailErrorException('Name has invalid length or invalid characters');
        }
        // No spaces and quotes at the start or the end
        if (preg_match("/^(\s|\')|(\s|\')$/", $name)) // Spaces at start and end are trimmed anyway, validate this though?
        {
            throw new Kingboard_KillmailParser_KillmailErrorException('Name has invalid character at start or end');
        }

        // Only one space per name
        // @TODO Check if it is safe to disable the one space only check. Disabled because typeNames can be attacker names (eg: a POS or a NPC)
//        $hits = array();
//        if (preg_match_all('/\s+/', $name, $hits) > 1)
//        {
//            throw new Kingboard_KillmailParser_KillmailErrorException('Name has more than one space character');
//        }

        // Passed every test, must be valid
        return true;
    }

    /**
     * Validate an organisation name, can be a corporation, alliance or faction
     * Validation according to http://support.eveonline.com/Pages/KB/Article.aspx?id=37
     * However, there are player corps with only 3 letters, so the minimal length checked
     * is two characters
     *
     * @param string $name
     * @return boolean
     */
    public function validateOrganisationName($name)
    {
        // Only letters, numbers, spaces, dots, single quotes and hyphens
        // In a range between 2 and 50 characters
        if (!preg_match("/^([a-zA-Z0-9 \-\.\']{2,50})$/", $name))
        {
            throw new Kingboard_KillmailParser_KillmailErrorException('Name has invalid length or contains invalid characters');
        }

        // No spaces, hypens, dots and single quotes at the start
        // The same applies for the end, except for a dot, it can be the last letter
        if (preg_match('/^(\s|\-|\.|\')|(\s|\-|\')$/', $name))
        {
            throw new Kingboard_KillmailParser_KillmailErrorException('Name has invalid characters at the start or the end');
        }
        return true;
    }
}
