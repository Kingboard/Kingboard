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
 * Parser for killmails
 *
 * @author Georg Grossberger
 * @package Kingboard
 */
class Kingboard_KillmailParser_Parser
{
    protected $victim = array(
        'characterName'   => '',
        'characterID'     => 0,
        'corporationName' => '',
        'corporationID'   => 0,
        'allianceName'    => '',
        'allianceID'      => 0,
        'shipType'        => '',
        'shipTypeID'      => 0,
        'factionName'     => '',
        'factionID'       => 0
    );

    protected $location = array(
        'solarSystemName' => '',
        'solarSystemID'   => 0,
        'security'        => 0
    );

    protected $attackers = array();

    protected $idHash = null;

    protected $items = array();

    protected $plainMail = '';

    /**
     * Unix Timestamp of the kill
     *
     * @var integer
     */
    protected $killTime = 0;

    /**
     * Errors that render the killmail useless
     *
     * @var array
     */
    protected $errors = array();

    /**
     * Parse the different parts of a killmail into
     * a multidimensional array which can be further processed
     *
     * @param string $mail
     */
    public function parse($mail)
    {
        $this->plainMail = $mail;
        
        $victimActive    = false;
        $attackerActive  = false;
        $involed         = false;
        $destroyed       = false;
        $dropped         = false;
        $tokens          = Kingboard_KillmailParser_Factory::findTokensForMail($mail);
        $currentAttacker = -1;
        
        // Clean the mail and explode by line
        $lines = explode(chr(10), $mail);
        $ids = new Kingboard_KillmailParser_IdFinder();

        foreach ($lines as $line)
        {
            try {
                $line = new Kingboard_KillmailParser_Line($line, $tokens);
                switch ($line->getType()) {
                    case Kingboard_KillmailParser_Line::TYPE_EMPTY:
                        $victimActive = false;
                        $attackerActive = false;
                        break;
                    case Kingboard_KillmailParser_Line::TYPE_TIME:
                        $this->killTime = $line->getValue();
                        break;

                    case Kingboard_KillmailParser_Line::TYPE_NAME:
                        if ($involed) {
                            $attackerActive = false;
                            if ($involed) {
                                $currentAttacker++;
                                $this->attackers[$currentAttacker] = array(
                                    'corporationName' => '',
                                    'corporationID'   => 0,
                                    'allianceName'    => '',
                                    'allianceID'      => 0,
                                    'shipType'        => '',
                                    'shipTypeID'      => 0,
                                    'factionName'     => '',
                                    'factionID'       => 0
                                 );
                            }
                            $this->attackers[$currentAttacker]['characterName'] = $line->getValue();
                            $this->attackers[$currentAttacker]['characterID']   = $ids->getCharacterId($line->getValue());
                            $this->attackers[$currentAttacker]['finalBlow']     = $line->hasFinalBlow();
                        } else {
                            $victimActive = TRUE;
                            $this->victim['characterName'] = $line->getValue();
                            $this->victim['characterID'] = $ids->getCharacterId($line->getValue());
                        }
                        break;

                    case Kingboard_KillmailParser_Line::TYPE_ALLIANCE:
                        if ($victimActive) {
                            $record =& $this->victim;
                        }
                        elseif ($involed) {
                            $record =& $this->attackers[$currentAttacker];
                        }
                        if (!$line->isEmpty()) {
                            $record['allianceName'] = $line->getValue();
                            $record['allianceID'] = $ids->getAllianceId($line->getValue());
                        }
                        unset($record);
                        break;

                    case Kingboard_KillmailParser_Line::TYPE_CORP:
                         if ($victimActive) {
                            $record =& $this->victim;
                        }
                        elseif ($involed) {
                            $record =& $this->attackers[$currentAttacker];
                        }
                        if (!$line->isEmpty()) {
                            $record['corporationID'] = $ids->getCorporationId($line->getValue());
                            $record['corporationName'] = $line->getValue();
                        }
                        unset($record);
                        break;

                    case Kingboard_KillmailParser_Line::TYPE_DAMAGE:
                         if ($victimActive) {
                            $this->victim['damageTaken'] = $line->getValue();
                        }
                        elseif ($involed) {
                            $this->attackers[$currentAttacker]['damageDone'] = $line->getValue();
                        }
                        break;

                    case Kingboard_KillmailParser_Line::TYPE_FACTION:
                         if ($victimActive) {
                            $record =& $this->victim;
                        }
                        elseif ($involed) {
                            $record =& $this->attackers[$currentAttacker];
                        }
                        if (!$line->isEmpty()) {
                            $record['factionID'] = $ids->getFactionId($line->getValue());
                            $record['factionName'] = $line->getValue();
                        }
                        unset($record);
                        break;

                     case Kingboard_KillmailParser_Line::TYPE_ITEM:
                         $itemIndex = null;
                         foreach ($this->items as $index => $item) {
                             if ($item['typeName'] == $line->getValue()) {
                                 $itemIndex = $index;
                                 break;
                             }
                         }
                         if ($itemIndex === null) {
                             $itemIndex = count($this->items);
                             $this->items[$itemIndex] = array(
                                 'typeName'     => $line->getValue(),
                                 'typeID'       => $ids->getItemId($line->getValue()),
                                 'droneBay'     => $line->isDrone(),
                                 'cargoBay'     => $line->isCargo(),
                                 'qtyDropped'   => 0,
                                 'qtyDestroyed' => 0
                             );
                         }
                         $this->items[$itemIndex][$dropped ? 'qtyDropped' : 'qtyDestroyed'] += $line->getQty();
                         unset($itemIndex, $index, $item);
                         break;

                     case Kingboard_KillmailParser_Line::TYPE_SECURITY:
                         $value = (float) $line->getValue();
                         if ($victimActive) {
                            $this->location['security'] = $value;
                        }
                        elseif ($involed) {
                            $this->attackers[$currentAttacker]['securityStatus'] = $value;
                        }
                        unset($value);
                        break;

                    case Kingboard_KillmailParser_Line::TYPE_SHIP:
                        if ($victimActive) {
                            $record =& $this->victim;
                        }
                        elseif ($involed) {
                            $record =& $this->attackers[$currentAttacker];
                        }
                        if (!$line->isEmpty()) {
                            $record['shipType'] = $line->getValue();
                            $record['shipTypeID'] = $ids->getItemId($line->getValue());
                        }
                        unset($record);
                        break;

                     case Kingboard_KillmailParser_Line::TYPE_SWITCH_ATTACKERS:
                         $victimActive = false;
                         $attackerActive = false;
                         $involed = true;
                         break;

                     case Kingboard_KillmailParser_Line::TYPE_SWITCH_DROPPED:
                         $dropped = true;
                         break;

                     case Kingboard_KillmailParser_Line::TYPE_SYSTEM:
                         $this->location['solarSystemName'] = $line->getValue();
                         $this->location['solarSystemID']   = $ids->getSolarSystemId($line->getValue());
                         break;

                     case Kingboard_KillmailParser_Line::TYPE_WEAPON:
                         $this->attackers[$currentAttacker]['weaponType'] = $line->getValue();
                         $this->attackers[$currentAttacker]['weaponTypeID'] = $ids->getItemId($line->getValue());
                         break;
                }
            }
            catch (Kingboard_KillmailParser_KillmailErrorException $e) {
                $this->errors[] = $e->getMessage();
                continue;
            }

            // Save errors for log / output
            if ($line->hasError()) {
                $this->errors[] = $line->getError();
            }
        }

        return $this;
    }

    /**
     * Get the ID hash, generate one if not available
     *
     * @return string
     */
    protected function getIdHash()
    {
        if (!$this->idHash) {
            $this->idHash = new Kingboard_KillmailParser_IdHash();
            $this->idHash->setVictimId($this->victim['characterID'])
                         ->setTime($this->killTime);

            foreach ($this->attackers as $attacker) {
                $this->idHash->addAttackerId($attacker['characterID']);
                if ($attacker['finalBlow']) {
                    $this->idHash->setFinalBlowAttackerId($attacker['characterID']);
                }
            }
        }
        return $this->idHash->generateHash();
    }

    /**
     * Get all data as an array, in the same
     * layout it has in the database
     *
     * @return array
     */
    public function getDataArray()
    {
        return array(
            'idHash'    => $this->getIdHash(),
            'killtime'  => $this->killTime,
            'errors'    => array(),
            'victim'    => $this->victim,
            'attackers' => $this->attackers,
            'items'     => $this->items,
            'location'  => $this->location,
            'errors'    => $this->errors,
            'plainMail' => $this->plainMail
        );
    }

    /**
     * Get the data model, create one if none set
     * It does not save a new one or any changes!
     *
     * @return Kingboard_Kill
     */
    public function getModel()
    {
        $model = Kingboard_Kill::getInstanceByIdHash(array('idHash' => $this->getIdHash()));
        if (!$model) {
            $model = new Kingboard_Kill();
        }
        $model->injectDataFromMail($this->getDataArray());
        return $model;
    }

    /**
     * Get the vicitm array
     * Empty if no mail has been parsed
     *
     * @return array
     */
    public function getVictim() {
        return $this->victim;
    }

    /**
     * Get the system information
     *
     * @return array
     */
    public function getLocation() {
        return $this->location;
    }

    /**
     * Get the attackers
     *
     * @return array
     */
    public function getAttackers() {
        return $this->attackers;
    }

    /**
     * Get all items
     *
     * @return array
     */
    public function getItems() {
        return $this->items;
    }

    /**
     * Get the plain mail string
     *
     * @return string
     */
    public function getPlainMail() {
        return $this->plainMail;
    }

    /**
     * Get the kill time as timestamp
     *
     * @return integer
     */
    public function getTime() {
        return $this->killTime;
    }

    /**
     * Get an array of error messages
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
