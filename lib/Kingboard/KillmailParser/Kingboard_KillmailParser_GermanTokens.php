<?php
/**
 * German tokens
 *
 * @author Georg Grossberger
 * @package Kingboard
 */
class Kingboard_KillmailParser_GermanTokens implements Kingboard_KillmailParser_TokenInterface
{
    public function alliance()
	{
        return 'Allianz:';
    }

    public function cargo()
	{
        return '(Fracht)';
    }

    public function container()
	{
        return '(In Container)';
    }

    public function corp()
	{
        return 'Corporation:';
    }

    public function damageDone()
	{
        return 'Verursachter Schaden:';
    }

    public function damageTaken()
	{
        return 'Erlittener Schaden:';
    }

    public function destroyed()
	{
        return 'Zerstört:';
    }

    public function destroyedItems()
	{
        return 'Zerstörte Gegenstände:';
    }

    public function drone()
	{
        return '(Drohnenhangar)';
    }

    public function droppedItems()
	{
        return 'Hinterlassene Gegenstände:';
    }

    public function faction()
	{
        return 'Fraktion:';
    }

    public function finalBlow()
	{
        return '(gab den letzten Schuss ab)';
    }

    public function involvedParties()
	{
        return 'Beteiligte Parteien:';
    }

    public function moon()
	{
        return 'Mond:';
    }

    public function name()
	{
        return 'Name:';
    }

    public function qty()
	{
        return ', Anz.:';
    }

    public function security()
	{
        return 'Sicherheit:';
    }

    public function ship()
	{
        return 'Schiff:';
    }

    public function system()
	{
        return 'System:';
    }

    public function victim()
	{
        return 'Ziel:';
    }

    public function weapon()
	{
        return 'Waffe:';
    }
}
