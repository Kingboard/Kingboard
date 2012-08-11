<?php
namespace Kingboard\Lib\Fetcher;

/**
 * Fetcher to get kills for a specific API key from CCP's EVE API
 */
class EveApi
{

    /**
     * fetch all kills for $key
     * @static
     * @param array $key
     * @return array
     */
    public static function fetch($key)
    {
        $newkills = 0;
        $oldkills = 0;
        $errors = 0;

        $pheal = new \Pheal($key['apiuserid'], $key['apikey']);
        $pheal->detectAccess();

        $characters = $pheal->accountScope->Characters()->characters;

        foreach($characters as $character)
        {
            switch($key['type'])
            {
                case "Corporation":
                    $pheal->scope = "corp";
                    break;
                case "Account":
                    // account keys are like character keys, just for the complete account
                case "Character":
                    $pheal->scope = "char";
                    break;
                default:
                    // not a key type we can use..
                    continue;
            }

            $kills = $pheal->Killlog(array('characterID' => $character->characterID))->kills;
            $kakp = new \Kingboard\Lib\Parser\EveAPI();
            $info = $kakp->parseKills($kills);

            $oldkills += $info['oldkills'];
            $newkills += $info['newkills'];
            $errors += $info['errors'];
        }
        return array("old" => $oldkills, "new"=>$newkills, "total" => $oldkills + $newkills, "errors" => $errors);
    }
}