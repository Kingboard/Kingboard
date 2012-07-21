<?php
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
        if (Kingboard_Helper_String::getInstance()->stripos($tokens->involvedParties(), $mail) !== FALSE)
		{
            return $tokens;
        }
        return new Kingboard_KillmailParser_EnglishTokens();
    }

    /**
     * Parse a plain text killmail and return
     * the according Kingboard_Kill object
     *
     * @param string $mail
     * @return Kingboard_Kill
     */
    public static function parseTextMail($mail)
    {
        $parser = new Kingboard_KillmailParser_Parser();
        $parser->parse($mail);

        try 
        {
            $validator = new Kingboard_KillmailParser_Validator();
            $validator->validateKillmailData($parser->getDataArray());
        }
        catch (Kingboard_KillmailHash_ErrorException $e)
        {
            throw new Kingboard_KillmailParser_KillmailErrorException($e->getMessage());
        }

        return $parser->getModel();
    }
}