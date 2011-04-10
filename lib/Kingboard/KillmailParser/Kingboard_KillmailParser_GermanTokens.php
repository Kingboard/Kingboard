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
 * German tokens
 *
 * @author Georg Grossberger
 * @package Kingboard
 */
class Kingboard_KillmailParser_GermanTokens implements Kingboard_KillmailParser_TokenInterface
{
    public function alliance() {
        return 'Allianz:';
    }

    public function cargo() {
        return '(Cargo)';
    }

    public function container() {
        return '(In Container)';
    }

    public function corp() {
        return 'Corporation:';
    }

    public function damageDone() {
        return 'Verursachter Schaden:';
    }

    public function damageTaken() {
        return 'Erlittener Schaden:';
    }

    public function destroyed() {
        return 'Zerstört:';
    }

    public function destroyedItems() {
        return 'Zerstörte Gegenstände:';
    }

    public function drone() {
        return '(Drohnenhangar)';
    }

    public function droppedItems() {
        return 'Hinterlassene Gegenstände:';
    }

    public function faction() {
        return 'Fraktion:';
    }

    public function finalBlow() {
        return '(gab den letzten Schuss ab)';
    }

    public function involvedParties() {
        return 'Beteiligte Parteien:';
    }

    public function moon() {
        return 'Mond:';
    }

    public function name() {
        return 'Name:';
    }

    public function qty() {
        return ', Anz.:';
    }

    public function security() {
        return 'Sicherheit:';
    }

    public function ship() {
        return 'Schiff:';
    }

    public function system() {
        return 'System:';
    }

    public function victim() {
        return 'Ziel:';
    }

    public function weapon() {
        return 'Waffe:';
    }
}
