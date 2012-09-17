<?php
namespace Kingboard\Views\Auth;

class Auth extends \Kingboard\Views\Base
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
            if($user = \Kingboard\Lib\Auth\Auth::login($_POST["login"], $_POST["passwd"]))
            {
                if($user->status == \Kingboard\Model\User::STATUS_NEW)
                {
                    \Kingboard\Lib\Auth\Auth::logout();
                    return $this->render('user/activation_error.html', array('activation_failed' => 'this user has not been activated yet, please check your mailbox for the activation email and follow its instructions.'));
                }
                $this->redirect("/account/"); // login successfull, redirect to /
            }
            else
            {
                $context['login_failed'] = true;
            }
        }
        // not logged in yet, show login page
        $this->render("user/login.html", $context);
    }

    public function logout($request)
    {
        \Kingboard\Lib\Auth\Auth::logout();
        $this->redirect("/");
    }

    public function activateUser($request)
    {
        $key = $request['activationkey'];
        if($user = \Kingboard\Model\User::findOne(array('validationCode' => $key)))
        {
            if($user->status != \Kingboard\Model\User::STATUS_NEW)
                return $this->render('user/activation_error.html', array('activation_failed' => "no such activationkey"));
        } else {
            return $this->render('user/activation_error.html', array('activation_failed' => "no such activationkey"));
        }
        $user->status = \Kingboard\Model\User::STATUS_EMAIL_VALID;
        unset($user->validationCode);
        $user->save();
        $this->redirect("/login");
    }

    public function registerForm($request)
    {
        if(isset($_POST['XSRF']))
        {
            if(\Kingboard\Lib\Form::getXSRFToken() == $_POST['XSRF'])
            {
                if(!isset($_POST['passwd'])
                   || !isset($_POST['passwd2'])
                   || !isset($_POST['login']))
                {
                    $this->_context['registration_failed'] = 'Please fill in all fields';
                } elseif($_POST['passwd'] != $_POST['passwd2']) {
                    $this->_context['registration_failed'] = 'both Password fields need to have the same value';
                } elseif(!is_null(\Kingboard\Model\User::findOne(array('username' => $_POST['login'])))) {
                    $this->_context['registration_failed'] = 'email/login allready in use';
                } elseif(!\Kingboard\Lib\Form::isEmail($_POST['login'])) {
                    $this->_context['registration_failed'] = 'not a valid email adresse';
                } else {
                    $validationCode = sha1(time() . $_POST['login']);

                    $user = new \Kingboard\Model\User();
                    $user->username = $_POST['login'];
                    $user->password = hash('sha256', $_POST['passwd']);
                    $user->status = \Kingboard\Model\User::STATUS_NEW;
                    $user->validationCode = $validationCode;
                    $user->save();
                    $body = file_get_contents(APP_PATH . '/templates/mails/verify_email.html');
                    $body = strtr($body, array(
                        "{{username}}" => $_POST['login'],
                        "{{hostname}}" => \King23\Core\Registry::getInstance()->baseHost,
                        "{{activationkey}}" => $validationCode
                    ));
                    $headers = "From: ". \King23\Core\Registry::getInstance()->sendFromEmail ."\r\n";
                    $headers .= "Reply-To: " . \King23\Core\Registry::getInstance()->sendFromEmail . "\r\n";
                    $headers .= "X-Mailer: PHP/" . phpversion();
                    
					mail($_POST['login'], "Kingboard Activation", $body, $headers);
                    $this->redirect('/');
                }
            } else {
                $this->_context['registration_failed'] = 'XSRF Token Invalid.';
            }
        }
        $this->render('user/registration.html', $_POST);
    }
}