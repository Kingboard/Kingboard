<?php
namespace Kingboard\Views\Auth;

class Jabber extends \Kingboard\Views\Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function login($request)
    {
        $context = array();
        if (isset($_POST["login"]) && isset($_POST["passwd"])) {
            if (\Kingboard\Lib\Auth\Jabber::login($_POST["login"], $_POST["passwd"])) {
                $this->redirect("/account/");
            }
            $context['login_failed'] = true;
        }
        return $this->render("user/login_jabber.html", $context);
    }

    public function logout($request)
    {
        \Kingboard\Lib\Auth\Jabber::logout();
        $this->redirect("/");
    }
}