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
