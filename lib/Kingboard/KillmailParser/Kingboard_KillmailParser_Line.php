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
 * Clean a killmail for further processing
 *
 * @author Georg Grossberger
 * @package Kingboard
 */
class Kingboard_KillmailParser_Line
{
    const TYPE_UNKNOWN          = 1;
    const TYPE_EMPTY            = 2;
    const TYPE_TIME             = 3;
    const TYPE_NAME             = 4; // Both victim and attackers
    const TYPE_CORP             = 5;
    const TYPE_ALLIANCE         = 6;
    const TYPE_FACTION          = 7;
    const TYPE_SHIP             = 8; // Both attacker and victim
    const TYPE_SECURITY         = 9;
    const TYPE_SYSTEM           = 10;
    const TYPE_MOON             = 11;
    const TYPE_DAMAGE           = 12; // Both damage taken and done
    const TYPE_WEAPON           = 13;
    const TYPE_SWITCH_ATTACKERS = 14;
    const TYPE_SWITCH_ITEMS     = 15;
    const TYPE_SWITCH_DROPPED   = 16;
    const TYPE_ITEM             = 17; // All three, cargo, fitted and drone bay. where an item belongs to is set by the according properties

    /**
     *
     * @var string
     */
    protected $type;

    /**
     *
     * @var string
     */
    protected $line;

    /**
     *
     * @var array
     */
    protected $error = array();

    /**
     *
     * @var string
     */
    protected $value;

    /**
     *
     * @var integer
     */
    protected $qty = 1;

    /**
     *
     * @var boolean
     */
    protected $cargo = false;
    
    /**
     * 
     * @var boolean
     */
    protected $drone = false;

    /**
     *
     * @var boolean
     */
    protected $finalBlow = false;

    /**
     *
     * @var Kingboard_KillmailParser_Validator
     */
    protected static $validator;

    /**
     * If the item was inside a container
     * 
     * @var boolean
     */
    protected $container = false;

    /**
     * Constructor
     * Parse the line and set the according properties
     */
    public function __construct($line, Kingboard_KillmailParser_TokenInterface $tokens)
    {
        // Set as a property
        $this->line = $line;
        
        // Clean it up a little
        $line = preg_replace('/\s+/', ' ', $line);
        $line = preg_replace('/\s*\:\s*/', ':', $line);
        $line = trim($line);

        // If not empty, search for values
        if ($line != '')
        {
            $dateHits = array();

            // If we find a time line
            // Use regex, strtotime is not strict enough
            if (preg_match('/([0-9]{4})\.([0-9]{2})\.([0-9]{2})\s+([0-9]{2})\:([0-9]{2})/', $line, $dateHits))
            {
                array_shift($dateHits);
                
                // Generate a timestamp
                $killtime = mktime((int) $dateHits[3], (int) $dateHits[4], 0, (int) $dateHits[1], (int) $dateHits[2], (int) $dateHits[0]);
                $mintime = mktime(0, 0, 0, 5, 1, 2003);

                // Only valid if it is after the eve launch and before now
                if ($killtime <= $mintime)
                {
                    $this->error = "Time seems to be before the birth of the universe";
                } 
                // Not valid if it is in the future
                // @todo: We need an API to work with EVE time!
                elseif ($killtime >= time())
                {
                    $this->error = "Time is in the future, you cannot post your planned kills";
                }
                // In between is allowed
                else
                {
                    $this->type = self::TYPE_TIME;
                    $this->value = $killtime;
                }
                unset($dateHits, $mintime);
            }
            else
            {
                // Test if we have a victim line
                if ($this->match($tokens->victim()))
                {
                    $value = trim($this->extractValue($tokens->victim()));
                    if ($this->validCharacterName($value))
                    {
                        $this->type = self::TYPE_NAME;
                        $this->value = $value;
                    }
                    else
                    {
                        $this->error = 'Victim detected, but invalid value given';
                    }
                    unset($value);
                }
                // Test if we have a corporation
                elseif ($this->match($tokens->corp()))
                {
                    $value = $this->extractValue($tokens->corp());
                    if ($this->validOrganisationValue($value))
                    {
                        $this->value = $value;
                        $this->type = self::TYPE_CORP;
                    }
                    else
                    {
                        $this->error = 'Invalid corporation value';
                    }
                    unset($value);
                }
                // Test if we have an alliance
                elseif ($this->match($tokens->alliance()))
                {
                    $value = $this->extractValue($tokens->alliance());
                    if ($this->validOrganisationValue($value))
                    {
                        $this->value = $value;
                        $this->type = self::TYPE_ALLIANCE;
                    }
                    else
                    {
                        $this->error = 'Invalid alliance value';
                    }
                    unset($value);
                }
                // Test if we have a faction
                elseif ($this->match($tokens->faction()))
                {
                    $value = $this->extractValue($tokens->faction());
                    if ($this->validOrganisationValue($value))
                    {
                        $this->value = $value;
                        $this->type = self::TYPE_FACTION;
                    }
                    else
                    {
                        $this->error = 'Invalid faction value';
                    }
                }
                // Test if we have a destroyed ship
                elseif ($this->match($tokens->destroyed()))
                {
                    $value = $this->extractValue($tokens->destroyed());
                    if (!empty($value))
                    {
                        $this->value = $value;
                        $this->type = self::TYPE_SHIP;
                    }
                    else
                    {
                        $this->error = 'Invalid destroyed ship value';
                    }
                }
                // Test if we have a system
                elseif ($this->match($tokens->system()))
                {
                    $value = $this->extractValue($tokens->system());
                    if (Kingboard_Helper_String::getInstance()->strlen($value) > 1)
                    {
                        $this->value = $value;
                        $this->type = self::TYPE_SYSTEM;
                    }
                    else
                    {
                        $this->error = 'Invalid system name value';
                    }
                    unset($value);
                }
                // Test if there is a moon
                elseif ($this->match($tokens->moon()))
                {
                    $value = $this->extractValue($tokens->moon());
                    if (Kingboard_Helper_String::getInstance()->strlen($value) > 1)
                    {
                        $this->value = $value;
                        $this->type = self::TYPE_MOON;
                    }
                    else
                    {
                        $this->error = 'Invalid moon value';
                    }
                }
                // Test if we have a security value
                elseif ($this->match($tokens->security()))
                {
                    $value = trim($this->extractValue($tokens->security()));
                    $floatVal = (float) $value;
                    if (Kingboard_Helper_String::getInstance()->strlen($value) > 0 && $floatVal <= 10.0 && $floatVal >= -10.0)
                    {
                        $this->value = $floatVal;
                        $this->type = self::TYPE_SECURITY;
                    }
                    else
                    {
                        $this->error = 'Invalid security value';
                    }
                    unset($value, $floatVal);
                }
                // Test if we have a damage taken value
                elseif ($this->match($tokens->damageTaken()))
                {
                    $value = $this->extractValue($tokens->damageTaken());
                    $intVal = (int) $value;
                    if ($intVal > 10 && $intVal < 100000000)
                    {
                        $this->type = self::TYPE_DAMAGE;
                        $this->value = $intVal;
                    }
                    else
                    {
                        $this->error = 'Invalid damage taken value';
                    }
                }
                // Test if we have a damage done
                elseif ($this->match($tokens->damageDone()))
                {
                    $value = $this->extractValue($tokens->damageDone());
                    $intVal = (int) $value;
                    if ($intVal >= 0 && $intVal < 100000000)
                    {
                        $this->type = self::TYPE_DAMAGE;
                        $this->value = $intVal;
                    }
                    else
                    {
                        $this->error = 'Invalid damage done value';
                    }
                }
                // Test if we have an attacker name
                elseif ($this->match($tokens->name()))
                {
                    $value = $this->extractValue($tokens->name());
                    $finalBlow = false;
                    if ($this->match($tokens->finalBlow()))
                    {
                        $finalBlow = true;
                        $value = trim(str_replace($tokens->finalBlow(), '', $value));
                    }
                    if ($this->validCharacterName($value))
                    {
                        $this->value = $value;
                        $this->type = self::TYPE_NAME;
                        $this->finalBlow = $finalBlow;
                    } else {
                        $this->error = 'Invalid attacker name';
                    }
                }
                // Test if we have an attacker ship
                elseif ($this->match($tokens->ship()))
                {
                    $value = $this->extractValue($tokens->ship());
                    if (!empty($value))
                    {
                        $this->value = $value;
                        $this->type = self::TYPE_SHIP;
                    }
                    else
                    {
                        $this->error = 'Invalid attacker ship value';
                    }
                    unset($value);
                }
                // Test for a main weapon
                elseif ($this->match($tokens->weapon()))
                {
                    $value = $this->extractValue($tokens->weapon());
                    if (Kingboard_Helper_String::getInstance()->strlen($value) > 3)
                    {
                        $this->value = $value;
                        $this->type = self::TYPE_WEAPON;
                    }
                }
                // Look for attackers switch
                elseif ($this->match($tokens->involvedParties()))
                {
                    $this->type = self::TYPE_SWITCH_ATTACKERS;
                }
                // Look for dropped items switch
                elseif ($this->match($tokens->droppedItems()))
                {
                    $this->type = self::TYPE_SWITCH_DROPPED;
                }
                // Look for destroyed items switch
                elseif ($this->match($tokens->destroyedItems()))
                {
                    $this->type = self::TYPE_SWITCH_ITEMS;
                }
                // Everything else is an item
                else {
                    $value = $line;
                    // Look if this item belongs inside a container
                    if ($this->match($tokens->container()))
                    {
                        $this->container = true;
                        $value = trim(str_replace($tokens->container(), '', $value));
                    }
                    // Look if this item was in cargo bay
                    if ($this->match($tokens->cargo()))
                    {
                        $this->cargo = true;
                        $value = trim(str_replace($tokens->cargo(), '', $value));
                    }
                    // Log if this item was in drone bay
                    if ($this->match($tokens->drone()))
                    {
                        $this->drone = true;
                        $value = trim(str_replace($tokens->drone(), '', $value));
                    }
                    // Look if we have a qty value
                    if ($this->match($tokens->qty()))
                    {
                        $this->qty = $this->extractQty($tokens->qty());
                        $offset = Kingboard_Helper_String::getInstance()->stripos($tokens->qty(), $value);
                        $value = trim(Kingboard_Helper_String::getInstance()->substr($value, 0, $offset));
                        unset($parts);
                    }
                    if (Kingboard_Helper_String::getInstance()->strlen($value) > 2)
                    {
                        $this->value = $value;
                        $this->type = self::TYPE_ITEM;
                    }
                    unset($value);
                }
            }
        }
        else
        {
            $this->type = self::TYPE_EMPTY;
        }
        unset($line);
        // If no type value has been set yet, the lines properties are unknown
        if (!$this->type)
        {
            $this->type = self::TYPE_UNKNOWN;
        }
    }

    /**
     * Validates a character name to determine if this can be true
     *
     * @param string $name
     * @return boolean
     */
    protected function validCharacterName($name)
    {
        try
        {
            return $this->getValidator()->validateCharacterName($name);
        }
        catch (Kingboard_KillmailParser_KillmailErrorException $e)
        {
            return false;
        }
    }

    /**
     * Check if the given value is a valid name for an organisation
     * For corps, alliances, factions
     *
     * @param string $value
     * @return boolean
     */
    protected function validOrganisationValue($value)
    {
        return $this->validCorporationName($value) || 
               $this->isEmptyToken($value);
    }

    /**
     * Get a validator instance
     *
     * @return Kingboard_KillmailParser_Validator
     */
    protected function getValidator()
    {
        if (!self::$validator)
        {
            self::$validator = new Kingboard_KillmailParser_Validator();
        }
        return self::$validator;
    }
    
    /**
     * Determne if the given name is a valid corporation name
     * according to http://support.eveonline.com/Pages/KB/Article.aspx?id=37
     * 
     * @param string $name
     * @return boolean
     */
    protected function validCorporationName($name)
    {
        try
        {
            return $this->getValidator()->validateOrganisationName($name);
        }
        catch(Kingboard_KillmailParser_KillmailErrorException $e)
        {
            return false;
        }
    }

    /**
     * Get the error message
     * Empty if none set, check with hasError
     *
     * @return string
     */
    public function getError()
    {
        return (string) $this->error;
    }

    /**
     * Check if an error message has been set
     *
     * @return boolean
     */
    public function hasError()
    {
        return !empty($this->error);
    }

    /**
     * If there is an empty value
     * Can also occur on valid lines, eg Faction: None validates to an empty faction value
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return $this->type == self::TYPE_EMPTY ||
               $this->type == self::TYPE_UNKNOWN ||
               $this->isEmptyToken($this->value);
    }

    /**
     * Check if a token indicates an empty value
     *
     * @param string $str
     * @return boolean
     */
    protected function isEmptyToken($str)
    {
        return trim($str) === '' || in_array(
            Kingboard_Helper_String::getInstance()->lower($str),
            array(
                'unknown', 'none',      // English
                'неизвестно', 'нет',    // Russian
                'unbekannt', 'keine'    // German
            )
        );
    }

    /**
     * If the given string is present in the current line
     * not case sensitive
     *
     * @param string $string
     * @return boolean
     */
    protected function match($string)
    {
        return Kingboard_Helper_String::getInstance()->stripos($string, $this->line) !== false;
    }

    /**
     * Extract the value of the line
     *
     * @param string $token
     * @return string
     */
    protected  function extractValue($token)
    {
        $start = Kingboard_Helper_String::getInstance()->strlen($token);
        $value = Kingboard_Helper_String::getInstance()->substr($this->line, $start);
        return trim($value);
    }

    /**
     * Extract the quantity number
     *
     * @param string $token
     * @return integer
     */
    protected function extractQty($token)
    {
        $start = Kingboard_Helper_String::getInstance()->strpos($token, $this->line) +
                  Kingboard_Helper_String::getInstance()->strlen($token);
        $qty = (int) Kingboard_Helper_String::getInstance()->substr($this->line,  $start);
        
        if ($qty < 1)
        {
            $qty = 1;
        }
        return $qty;
    }

    /**
     * Type of the line value
     * 
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Getter for quantity
     *
     * @return integer
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * Get the extracted value of the line
     * Can be string, integer or float
     * 
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * If this line is about an item from the drone bay
     *
     * @return boolean
     */
    public function isDrone()
    {
        return $this->drone;
    }
    
    /**
     * If this line is about an item in the cargo bay
     * 
     * @return boolean
     */
    public function isCargo()
    {
        return $this->cargo;
    }

    /**
     * If this line is about an attacker name,
     * it determines if this it has the final blow
     *
     * @return boolean
     */
    public function hasFinalBlow()
    {
        return $this->finalBlow;
    }

    /**
     * If the item was inside a container
     *
     * @return boolean
     */
    public function inContainer()
    {
        return $this->container;
    }
}
