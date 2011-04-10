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
class Kingboard_KillmailParser_Factory
{

    /**
     * Determine the kill mail tokens and return the right ones
     *
     * @param string $mail
     * @return Kingboard_KillmailParser_TokenInterface
     */
    public static function findTokensForMail($mail)
    {
        $tokens = new Kingboard_KillmailParser_GermanTokens();
        if (Kingboard_Helper_String::getInstance()->stripos($tokens->involvedParties(), $mail) !== FALSE) {
            return $tokens;
        }
        return new Kingboard_KillmailParser_EnglishTokens();
    }

    /**
     * Parse a plain text killmail and return
     * the according Kingboard_Kill object
     *
     * @param string $mail
     * @return Kingboard_KillmailParser_Parser
     */
    public static function parseTextMail($mail)
    {
        $parser = new Kingboard_KillmailParser_Parser();
        $parser->parse($mail);

        $validator = new Kingboard_KillmailParser_Validator();
        $validator->validateKillmailData($parser->getDataArray());

        return $parser->getModel();
    }
}