<?php
class Kingboard_Base_View extends King23_TwigView
{
    protected function sendErrorAndQuit($message)
    {
        header('HTTP/1.1 200 Bad Request');
        header('Content-Type:text/plain');
        echo $message;
        exit;
    }
    
    public function __construct($loginrequired = false)
    {
        if($loginrequired && !Kingboard_Auth::isLoggedIn())
            $this->redirect("/login");
        parent::__construct();
        $reg = King23_Registry::getInstance();
        $this->_context['images'] = $reg->imagePaths;
        $this->_context['baseHost'] = $reg->baseHost;

        // ownerID, if this is an owned board, this should be filled, for public boards this needs to be false
        $this->_context['ownerID'] = $reg->ownerID;

        // ownerType, if this is an owned board, this should be filled, for public boards this doesn't matter
        $this->_context['ownerType'] = $reg->ownerType;

        // when user is logged in we provide user object to all pages, false otherwise
        $this->_context['user'] = Kingboard_Auth::getUser();

        // make sure all views have the XSRF Token available
        $this->_context['XSRF'] = Kingboard_Form::getXSRFToken();

        // Global Kingboard information
        $this->_context['Kingboard']['Version'] = Kingboard::Version;
    }
}