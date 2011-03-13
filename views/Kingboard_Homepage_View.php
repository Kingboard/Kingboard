<?php
class Kingboard_Homepage_View extends Kingboard_Base_View
{

    public function index($request)
    {
        $data = Kingboard_Kill::find()->sort(array('killTime' => -1))->limit(20);
	    $count = Kingboard_Kill::count();
        
        $stats = Kingboard_Kill_MapReduce_KillsByShip::find()->sort(array("value.value" => -1));

        return $this->render("index.html", array('data' => $data, 'count' => $count, 'stats' => $stats));
    }
}
