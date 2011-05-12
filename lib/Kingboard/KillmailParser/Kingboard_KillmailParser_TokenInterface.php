<?php
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
