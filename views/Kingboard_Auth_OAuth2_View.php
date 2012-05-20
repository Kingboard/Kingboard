<?php
class Kingboard_Auth_OAuth2_View extends Kingboard_Base_View
{
    /**
     * this method is called through King23s routing,
     * it is supposed to offer a list of OAuth2 Providers
     * which the user can use to authenticate himself
     * @param array $params
     */
    public function login(array $params)
    {
        $reg = King23_Registry::getInstance();
        $state = Kingboard_Form::getXSRFToken();
        $list = array();
        foreach($reg->oAuth2ProviderList as $provider => $config)
        {
            $class = $config['auth_class'];
            $list[$provider] = Kingboard_OAuth2_Consumer::getCodeRedirect(
                $class::getCodeUrl(),
                $config["client_id"],
                $config["redirect_url"],
                $state,
                $class::getScope()
            );
        }

        $this->render("user/oauth.html", array("providerlist" => $list));
    }

    /**
     * this method is the one that should be called when the
     * user returns from the OAuth2 Provider, and will use the auth class set
     * in config to process the data
     * @param array $params should contain one key named key, identifying which key from the config to use for this provider
     */
    public function callback(array $params)
    {
        if($_GET['state'] != Kingboard_Form::getXSRFToken())
            die("XSRF Token mismatch");

        try {
            $config = King23_Registry::getInstance()->oAuth2ProviderList[$params["key"]];
            $class = $config['auth_class'];
            $class::login($config);
            $this->redirect("/account/");
        } catch (Exception $e) {
            $this->_context['login_failed'] = $e->getMessage();
            return $this->login($params);
        }
    }

    /**
     * uses Kingboart_Auth to destroy the session, therefor logging the user out.
     * @param $params
     */
    public function logout(array $params)
    {
        Kingboard_Auth::logout();
        $this->redirect("/");
    }
}