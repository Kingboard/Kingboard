<?php
namespace Kingboard\Lib;
abstract class Form
{
    public static function getXSRFToken()
    {
        if(!isset($_SESSION['Kingboard_XSRF']))
        {
            $_SESSION['Kingboard_XSRF'] = sha1(uniqid('Kingboard_',true));
        }
        return $_SESSION['Kingboard_XSRF'];
    }

    public static function validateXSRF($xsrf)
    {
        return self::getXSRFToken() == $xsrf;
    }

    public static function isEmail($email)
    {
        return preg_match('|^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]{2,})+$|i', $email);
    }

    /**
     * Method to be implemented by all forms,
     * should get the array containing the forms values passed
     * @abstract
     * @param array $formData
     * @return boolean
     */
    abstract public function validate(array $formData);
}