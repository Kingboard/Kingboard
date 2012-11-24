<?php
namespace Kingboard\Lib\Forms;

/**
 * validation class for the Battle Editors Form
 * @todo add validations
 */
class BattleCreateForm extends \Kingboard\Lib\Form
{
    public $apiKey = null;
    public $character = null;


    /**
     * validate if character is actually a character of the current user
     * @param $characterData
     * @return bool
     */
    protected function validateCharacter($characterData)
    {
        $characterData = explode("|",$characterData);

        // dont have a user, meaning not logged in..
        if(!($user = \Kingboard\Lib\Auth\Auth::getUser()))
            return false;

        // user does not have any api keys, so can't be his character
        if(is_null($user->keys) || !is_array($user->keys))
            return false;

        if(!isset($user->keys[$characterData[0]]) || !$user->keys[$characterData[0]]["active"])
            return false;

        // key was not found
        $this->apiKey = $user->keys[$characterData[0]];
        $this->character = (int) $characterData[1];
        return true;
    }

    /**
     * validate solar system entered by the user
     * @param $solarSystem
     * @return bool
     * @todo implement me
     */
    protected function validateSolarSystem($solarSystem)
    {
        return true;
    }

    /**
     * validate dates that the user entered, those shouldnt be too far
     * from eachother or this will be heavy on the database
     * @param $startDate
     * @param $endDate
     * @return bool
     * @todo implement me
     */
    protected function validateDates($startDate, $endDate)
    {
        return true;
    }

    /**
     * Method to be implemented by all forms,
     * should get the array containing the forms values passed
     * @param array $formData
     * @return boolean
     */
    public function validate(array $formData)
    {
        switch(true)
        {
            case !$this->validateCharacter($formData['character']):
            case !$this->validateDates($formData['startdate'], $formData['enddate']):
            case !$this->validateSolarSystem($formData['system']):
                return false;
            case !self::validateXSRF($formData['XSRF']):
                die('xsrf!');
        }
        return true;
    }
}