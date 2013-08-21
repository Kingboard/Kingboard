<?php
namespace Kingboard\Lib;

/**
 * abstract form class that offers basic form validations for kingboard
 */
abstract class Form
{
    /**
     * return the XSRF Token, create one if not available yet
     * @static
     * @return string
     */
    public static function getXSRFToken()
    {
        if (!isset($_SESSION['Kingboard_XSRF'])) {
            $_SESSION['Kingboard_XSRF'] = sha1(uniqid('Kingboard_', true));
        }
        return $_SESSION['Kingboard_XSRF'];
    }

    /**
     * check if $xsrf is the same as the xsrf token from the session
     * @static
     * @param string $xsrf
     * @return bool
     */
    public static function validateXSRF($xsrf)
    {
        return self::getXSRFToken() == $xsrf;
    }

    /**
     * check if $email could be an email address
     * @static
     * @param string $email
     * @return boolean
     */
    public static function isEmail($email)
    {
        return (boolean)preg_match('|^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]{2,})+$|i', $email);
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