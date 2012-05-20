<?php
class Kingboard_Auth_Jabber_View extends Kingboard_Base_View
{
    public function __construct()
    {
        parent::__construct();
    }

    public function login($request)
    {
        $context = array();
        if(isset($_POST["login"]) && isset($_POST["passwd"]))
        {
            if(Kingboard_Auth_Jabber::login($_POST["login"], $_POST["passwd"])) {
                $this->redirect("/account/");
            }
            $context['login_failed'] = true;
        }
        $this->render("user/login_jabber.html", $context);
    }

    public function logout($request)
    {
        Kingboard_Auth_Jabber::logout();
        $this->redirect("/");
    }
}