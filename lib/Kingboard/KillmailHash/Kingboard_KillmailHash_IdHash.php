<?php
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
    protected $attackers = array();

    /**
     * Character ID of the character that landed the final blow
     *
     * @var string
     */
    protected $finalBlowAttacker = 0;
    
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
    public function getAttackers()
    {
        return $this->attackers;
    }

    /**
     * Setter for all attacker character IDs
     *
     * @param array $attackers
     * @return Kingboard_KillmailHash_IdHash
     */
    public function setAttackers(array $attackers)
    {
        foreach ($attackers as $id)
        {
            $this->addAttacker($id);
        }
        return $this;
    }
    
    protected function cleanName($name)
    {
        $name = Kingboard_Helper_String::getInstance()->lower($name);
        return preg_replace('/[^a-z0-9]+/', '', $name);
    }
    
    /**
     * Setter for a single attacker character ID
     *
     * @param string $name
     * @return Kingboard_KillmailHash_IdHash
     */
    public function addAttacker($name)
    {
        $name = $this->cleanName($name);
        if (strlen($name) > 0 && !in_array($name, $this->attackers, true))
        {
            $this->attackers[] = $name;
        }
        return $this;
    }
    
    /**
     * Add an attacker by it's data
     * 
     * @param array $attacker
     * @return Kingboard_KillmailHash_IdHash 
     */
    public function pushAttackerData(array $attacker)
    {
        $key = $attacker['characterName'] . $attacker['corporationName'] . $attacker['shipType'] . $attacker['damageDone'];
        $this->addAttacker($key);
        if ($attacker['finalBlow']) 
        {
            $this->setFinalBlowAttacker($key);
        }
        return $this;
    }

    /**
     * Getter for the character ID that landed the final blow
     *
     * @return string
     */
    public function getFinalBlowAttacker()
    {
        return $this->finalBlowAttacker;
    }

    /**
     * Setter for the attacker character ID that landed the final blow
     *
     * @param integer $finalBlowAttacker
     * @return Kingboard_KillmailHash_IdHash
     */
    public function setFinalBlowAttacker($finalBlowAttacker)
    {
        $finalBlowAttacker = $this->cleanName($finalBlowAttacker);
        if (strlen($finalBlowAttacker) && in_array($finalBlowAttacker, $this->attackers, true))
        {
            $this->finalBlowAttacker = $finalBlowAttacker;
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
        if (count($this->attackers) < 1 || !$this->time || !$this->victimId || !$this->finalBlowAttacker || !$this->victimShip)
        {
            throw new Kingboard_KillmailHash_ErrorException('Needed hash arguments are missing');
        }
        natsort($this->attackers);
        natsort($this->items);
        return sha1($this->time . $this->victimId . $this->victimShip . implode('', $this->attackers) . $this->finalBlowAttacker . implode('', $this->items));
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
        try
        {
            return $this->generateHash();
        }
        catch (Exception $e)
        {
            return null;
        }
    }

    /**
     * Method that takes an associative array with killmail data and creates a hash for that.
     * 
     * @static
     * @param array $data
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
            $idHash->pushAttackerData($attacker);
        }
        
        return $idHash;
    }
}
