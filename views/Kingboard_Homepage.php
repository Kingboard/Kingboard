<?php
class Kingboard_Homepage extends Kingboard_BaseView
{

    public function index($request)
    {
        $data = Kingboard_Kill::find()->sort(array('killTime' => -1));
        $this->render("index.html", array('data' => $data));
    }
}