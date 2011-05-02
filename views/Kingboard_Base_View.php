<?php
class Kingboard_Base_View extends King23_TemplateView
{
    public function __construct($loginrequired = false)
    {
        if($loginrequired && !Kingboard_Auth::isLoggedIn())
            $this->redirect("/login");

        parent::__construct();
        $reg = King23_Registry::getInstance();
        $this->_context['images'] = $reg->imagePaths;
        $this->_context['baseHost'] = $reg->baseHost;
    }
}