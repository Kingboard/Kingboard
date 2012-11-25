<?php
namespace Kingboard\Views;
class Base extends \King23\View\TwigView
{

    /**
     * displays $message while sending Bad Request header
     * @deprecated
     * @param string $message
     */
    protected function sendErrorAndQuit($message)
    {
        header('HTTP/1.1 400 Bad Request');
        header('Content-Type:text/plain');
        echo $message;
        exit;
    }

    /**
     * Check if this view was called from eves ingame browser
     * @return bool
     */
    protected function isIGB()
    {
        return isset($_SERVER['HTTP_EVE_TRUSTED']);
    }

    /**
     * constructor, should be called by all derived views
     * will cause redirect if $loginrequired and not logged in
     * @param bool $loginrequired
     */
    public function __construct($loginrequired = false)
    {
        if($loginrequired && !\Kingboard\Lib\Auth\Auth::isLoggedIn())
            $this->redirect("/login");
        parent::__construct();

        $reg = \King23\Core\Registry::getInstance();
        $this->_context['images'] = $reg->imagePaths;
        $this->_context['baseHost'] = $reg->baseHost;

        $this->_context['disqus'] = $reg->disqus;

        // ownerID, if this is an owned board, this should be filled, for public boards this needs to be false
        $this->_context['ownerID'] = $reg->ownerID;

        // ownerType, if this is an owned board, this should be filled, for public boards this doesn't matter
        $this->_context['ownerType'] = $reg->ownerType;

        // when user is logged in we provide user object to all pages, false otherwise
        $this->_context['user'] = \Kingboard\Lib\Auth\Auth::getUser();

        // make sure all views have the XSRF Token available
        $this->_context['XSRF'] = \Kingboard\Lib\Form::getXSRFToken();

        // Global Kingboard information

        // pass version information
        $this->_context['Kingboard']['Version'] = \Kingboard\Kingboard::Version;

        // ownerName, use Kingboard if not set
        if(!is_null($reg->ownerName) && $reg->ownerName)
            $this->_context['Kingboard']['Name'] = $reg->ownerName;
        else
            $this->_context['Kingboard']['Name'] = \Kingboard\Kingboard::Name;

        // release name
        $this->_context['Kingboard']['ReleaseName'] = \Kingboard\Kingboard::ReleaseName;

        // pick bootstrap theme path from public/css/themes folder
        $this->_context['theme']= !is_null($reg->theme) ? $reg->theme :"default";

        // set header image, fall back to default if non configured
        $this->_context['header_image'] = !is_null($reg->headerImage) ? $reg->headerImage : "/images/banner/kingboard.png";

        // ingame browser check
        $this->_context['igb'] = $this->isIGB();

   }
}