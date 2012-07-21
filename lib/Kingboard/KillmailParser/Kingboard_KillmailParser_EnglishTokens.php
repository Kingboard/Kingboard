<?php
/**
 * Tokens for english killmails
 *
 * @author Georg Grossberger
 * @package Kingboard
 */
class Kingboard_KillmailParser_EnglishTokens implements Kingboard_KillmailParser_TokenInterface
{
    public function alliance()
	{
        return 'Alliance:';
    }

    public function corp()
	{
        return 'Corp:';
    }

    public function damageDone()
	{
        return 'Damage Done:';
    }

    public function damageTaken()
	{
        return 'Damage Taken:';
    }

    public function destroyed()
	{
        return 'Destroyed:';
    }

    public function destroyedItems()
	{
        return 'Destroyed items:';
    }

    public function droppedItems()
	{
        return 'Dropped items:';
    }

    public function faction()
	{
        return 'Faction:';
    }

    public function involvedParties()
	{
        return 'Involved parties:';
    }

    public function name()
	{
        return 'Name:';
    }

    public function qty()
	{
        return ', Qty:';
    }

    public function security()
	{
        return 'Security:';
    }

    public function ship()
	{
        return 'Ship:';
    }

    public function system()
	{
        return 'System:';
    }

    public function victim()
	{
        return 'Victim:';
    }

    public function weapon()
	{
        return 'Weapon:';
    }

    public function finalBlow()
	{
        return '(laid the final blow)';
    }

    public function cargo()
	{
        return '(Cargo)';
    }

    public function drone()
	{
        return '(Drone Bay)';
    }

    public function container()
	{
        return '(In Container)';
    }

    public function moon()
	{
        return 'Moon:';
    }
}
