<?php
class Kingboard_User_View extends Kingboard_Base_View
{
    public function __construct()
    {
        // ensure login
        parent::__construct(true);
    }

    public function myKingboard(array $parameters)
    {
        $this->render('user/index.html', array());
    }

}