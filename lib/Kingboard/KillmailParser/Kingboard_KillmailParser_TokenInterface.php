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
 * Describing the interface of a token class, which will be used to identify the different
 * tokens in a killmail
 *
 * @author Georg Grossberger
 * @package Kingboard
 */
interface Kingboard_KillmailParser_TokenInterface
{
    /**
     * Token for the victim name
     *
     * @return string
     */
    public function victim();

    /**
     * Token for a corp name
     *
     * @return string
     */
    public function corp();

    /**
     * Token for an alliance name
     *
     * @return string
     */
    public function alliance();

    /**
     * Token for a faction name
     *
     * @return string
     */
    public function faction();

    /**
     * Token for the destroyed ship name
     *
     * @return string
     */
    public function destroyed();

    /**
     * Token for a system name
     *
     * @return string
     */
    public function system();

    /**
     * Token for a security rating
     *
     * @return string
     */
    public function security();

    /**
     * Token for a damage taken value
     *
     * @return string
     */
    public function damageTaken();

    /**
     * Token starting the attackers list
     *
     * @return string
     */
    public function involvedParties();

    /**
     * token for an attacker character name
     *
     * @return string
     */
    public function name();

    /**
     * Token for an attackers ship name
     *
     * @return string
     */
    public function ship();

    /**
     * Token for an attackers main weapon name
     *
     * @return string
     */
    public function weapon();

    /**
     * Token for a damage done value
     *
     * @return string
     */
    public function damageDone();

    /**
     * Token starting the destroyed items list
     *
     * @return string
     */
    public function destroyedItems();

    /**
     * Token starting the dropped items list
     *
     * @return string
     */
    public function droppedItems();

    /**
     * Token for the quantity of an destroyed, dropped item
     *
     * @return string
     */
    public function qty();

    /**
     * Token that defines if a character did the final blow
     *
     * @return string;
     */
    public function finalBlow();

    /**
     * Token that shows an item in cargo bay
     *
     * @return string
     */
    public function cargo();

    /**
     * Token that shows an item in the drone bay
     *
     * @return string
     */
    public function drone();

    /**
     * Token that shows an item inside a container
     *
     * @return string
     */
    public function container();

    /**
     * Token that shows the moon, only for pos
     * 
     * @return string
     */
    public function moon();
}
