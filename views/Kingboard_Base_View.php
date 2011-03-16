<?php
class Kingboard_Base_View extends King23_TemplateView
{
    public function __construct()
    {
        parent::__construct();
        $reg = King23_Registry::getInstance();
        $this->_context['images'] = $reg->imagePaths;
    }
}