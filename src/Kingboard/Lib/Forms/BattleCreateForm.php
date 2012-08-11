<?php
namespace Kingboard\Lib\Forms;

/**
 * validation class for the Battle Editors Form
 * @todo add validations
 */
class BattleCreateForm extends \Kingboard\Lib\Form
{
    /**
     * validate if character is actually a character of the current user
     * @static
     * @param $characterData
     * @return bool
     * @todo implement me!
     */
    protected static function validateCharacter($characterData)
    {
        return true;
    }

    /**
     * validate solar system entered by the user
     * @static
     * @param $solarSystem
     * @return bool
     * @todo implement me
     */
    protected static function validateSolarSystem($solarSystem)
    {
        return true;
    }

    /**
     * validate dates that the user entered, those shouldnt be too far
     * from eachother or this will be heavy on the database
     * @static
     * @param $startDate
     * @param $endDate
     * @return bool
     * @todo implement me
     */
    protected static function validateDates($startDate, $endDate)
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
            case !self::validateCharacter($formData['character']):
            case !self::validateDates($formData['startdate'], $formData['enddate']):
            case !self::validateSolarSystem($formData['system']):
                return false;
            case !self::validateXSRF($formData['XSRF']):
                die('xsrf!');
        }
        return true;
    }
}