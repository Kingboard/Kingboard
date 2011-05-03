<?php
class Kingboard_Homepage_View extends Kingboard_Base_View
{

    public function index($request)
    {
        $data = Kingboard_Kill::find()->sort(array('killTime' => -1))->limit(20);
        $count = Kingboard_Kill::count();
        $stats = Kingboard_Kill_MapReduce_KillsByShip::find();
        $info = array();
        $template = "index.html";
        $stats = $stats->sort(array("value.value" => -1));
        return $this->render($template, array('data' => $data, 'count' => $count, 'stats' => $stats, 'info' => $info));
    }

    public function pilot($request)
    {
        if(!empty($request['hid']))
        {
            $count = Kingboard_Kill::find(
                array('attackers.characterID' => (int) $request['hid'])
            )->count();

            $killdata = Kingboard_Kill::find(
                  array('attackers.characterID' => (int)  $request['hid'])
            )->sort(array('killTime' => -1))->limit(20);

            $lossdata = Kingboard_Kill::find(
                array('victim.characterID' => (int) $request['hid'])
            )->sort(array('killTime' => -1))->limit(20);

            $killstats = Kingboard_Kill_MapReduce_KillsByShipByPilot::mapReduceKills((int) $request['hid']);
            $lossstats = Kingboard_Kill_MapReduce_KillsByShipByPilot::mapReduceLosses((int) $request['hid']);
            $template = "pilot_home.html";
            $info = Kingboard_Kill::getPilotInfoFromId($request['hid']);
            //$stats = $stats->sort(array("value.value" => -1));
            return $this->render($template, array('killdata' => $killdata, 'lossdata' =>$lossdata, 'count' => $count, 'killstats' => $killstats, 'lossstats' => $lossstats, 'info' => $info));
        } else {
            die('no pilot id specified');
        }
        
    }

}
