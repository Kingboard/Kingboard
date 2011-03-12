<?php
class Kingboard_Homepage extends Kingboard_BaseView
{

    public function index($request)
    {
        $data = Kingboard_Kill::find()->sort(array('killTime' => -1))->limit(20);
	    $count = Kingboard_Kill::count();
        
        $stats = Kingboard_Kill_MapReduce_KillsByShip::find();

        return $this->render("index.html", array('data' => $data, 'count' => $count, 'stats' => $stats));
    }
}
