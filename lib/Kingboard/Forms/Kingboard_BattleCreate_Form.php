<?php
class Kingboard_BattleCreate_Form extends Kingboard_Form
{
    protected static function validateCharacter($characterData)
    {
        return true;
    }

    protected static function validateSolarSystem($solarSystem)
    {
        return true;
    }

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