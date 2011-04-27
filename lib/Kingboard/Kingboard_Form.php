<?php
class Kingboard_Form
{
    public static function getXSRFToken()
    {
        if(!isset($_SESSION['Kingboard_XSRF']))
        {
            $_SESSION['Kingboard_XSRF'] = sha1(uniqid('Kingboard_',true));
        }
        return $_SESSION['Kingboard_XSRF'];
    }

    public static function isEmail($email)
    {
        return preg_match('|^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]{2,})+$|i', $email);
    }
}