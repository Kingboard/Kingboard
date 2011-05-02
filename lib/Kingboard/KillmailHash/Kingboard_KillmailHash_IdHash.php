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
 * Calculates the ID hash of a killmail
 *
 * @author Georg Grossberger
 * @package Kingboard
 */
class Kingboard_KillmailHash_IdHash
{
    /**
     * Unix timestamp of the killtime
     *
     * @var integer
     */
    protected $time = 0;

    /**
     * Character ID of the victim
     *
     * @var integer
     */
    protected $victimId = 0;
    
    /**
     * Type ID of the victim's destroyed ship
     * 
     * @var integer
     */
    protected $victimShip = 0;

    /**
     * Character IDs of the attackers
     *
     * @var array
     */
    protected $attackerIds = array();

    /**
     * Character ID of the character that landed the final blow
     *
     * @var integer
     */
    protected $finalBlowAttackerId = 0;
    
    /**
     * Items destroyed and dropped
     * 
     * @var array
     */
    protected $items = array();

    /**
     * Getter for killtime
     *
     * @return integer
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Setter for killtime
     *
     * @param MongoDate $time
     * @return Kingboard_KillmailHash_IdHash
     */
    public function setTime(MongoDate $time)
    {
        // Extract the seconds
        $timeString = (string) $time;
        $parts = explode(' ', $timeString);
        $time = (int) $parts[1];
        if ($time > 0)
        {
            $this->time = $time;
        }
        return $this;
    }

    /**
     * Getter for victim character ID
     *
     * @return integer
     */
    public function getVictimId()
    {
        return $this->victimId;
    }

    /**
     * Setter for victim character ID
     *
     * @param integer $victimId
     * @return Kingboard_KillmailHash_IdHash
     */
    public function setVictimId($victimId)
    {
        $victimId = (int) $victimId;
        if ($victimId > 0)
        {
            $this->victimId = $victimId;
        }
        return $this;
    }
    
    /**
     * Setter for victim ship ID
     * 
     * @param type $shipId
     * @return Kingboard_KillmailHash_IdHash 
     */
    public function setVictimShip($shipId)
    {
        $shipId = (int) $shipId;
        if ($shipId > 0)
        {
            $this->victimShip = $shipId;
        }
        return $this;
    }
    
    /**
     * Getter for victim ship ID
     * 
     * @return integer
     */
    public function getVictimShip()
    {
        return $this->victimShip;
    }

    /**
     * Getter for all attacker character Ids
     *
     * @return array
     */
    public function getAttackerIds()
    {
        return $this->attackerIds;
    }

    /**
     * Setter for all attacker character IDs
     *
     * @param array $attackerIds
     * @return Kingboard_KillmailHash_IdHash
     */
    public function setAttackerIds(array $attackerIds)
    {
        foreach ($attackerIds as $id)
        {
            $this->addAttackerId($id);
        }
        return $this;
    }

    /**
     * Setter for a single attacker character ID
     *
     * @param integer $id
     * @return Kingboard_KillmailHash_IdHash
     */
    public function addAttackerId($id)
    {
        if ((int) $id > 0 && !in_array($id, $this->attackerIds, true))
        {
            $this->attackerIds[] = $id;
        }
        return $this;
    }

    /**
     * Getter for the character ID that landed the final blow
     *
     * @return integer
     */
    public function getFinalBlowAttackerId()
    {
        return $this->finalBlowAttackerId;
    }

    /**
     * Setter for the attacker character ID that landed the final blow
     *
     * @param integer $finalBlowAttackerId
     * @return Kingboard_KillmailHash_IdHash
     */
    public function setFinalBlowAttackerId($finalBlowAttackerId)
    {
        $finalBlowAttackerId = (int) $finalBlowAttackerId;
        if ($finalBlowAttackerId > 0 && in_array($finalBlowAttackerId, $this->attackerIds, true))
        {
            $this->finalBlowAttackerId = (int) $finalBlowAttackerId;
        }
        return $this;
    }

    /**
     * Validate given parameters and generate a hash
     *
     * @return string
     */
    public function generateHash()
    {
        if (count($this->attackerIds) < 1 || !$this->time || !$this->victimId || !$this->finalBlowAttackerId || empty($this->victimShip))
        {
            throw new Kingboard_KillmailHash_ErrorException('Needed hash arguments are missing');
        }
        natsort($this->attackerIds);
        natsort($this->items);
        return sha1($this->time . $this->victimId . implode('', $this->attackerIds) . $this->finalBlowAttackerId . implode('', $this->items));
    }
    
    /**
     * Add an item
     * 
     * @param array $item
     * @return Kingboard_KillmailHash_IdHash 
     */
    public function addItem(array $item)
    {
        $key = $item['typeID'] . $item['flag'] . $item['qtyDropped'] . $item['qtyDestroyed'];
        $this->items[] = $key;
        return $this;
    }

    /**
     * Magic stringify method
     *
     * @return string
     */
    public function __toString()
    {
        return $this->generateHash();
    }

    /**
     * Method that takes an associative array with killmail data and creates a hash for that.
     * @static
     * @param  $data
     * @return Kingboard_KillmailHash_IdHash
     */
    public static function getByData($data)
    {
        $victimId = !empty($data['victim']['characterID']) ? $data['victim']['characterID'] : $data['victim']['corporationID'];
        $idHash = new Kingboard_KillmailHash_IdHash();
        $idHash->setVictimId($victimId)
                     ->setVictimShip($data['victim']['shipTypeID'])
                     ->setTime($data['killTime']);

        foreach ($data['items'] as $item)
        {
            $idHash->addItem($item);
        }

        foreach ($data['attackers'] as $attacker)
        {
            $idHash->addAttackerId($attacker['characterID']);
            if ($attacker['finalBlow'])
            {
                $idHash->setFinalBlowAttackerId($attacker['characterID']);
            }
        }
        
        return $idHash;
    }
}
