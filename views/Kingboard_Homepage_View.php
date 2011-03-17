<?php
class Kingboard_Homepage_View extends Kingboard_Base_View
{

    public function index($request)
    {
        switch($request['htype'])
        {
            case "pilot":
                if(!empty($request['hid']))
                {
                    $count = Kingboard_Kill::find(
                        array('attackers.characterID' => $request['hid'])
                    )->count();

                    $data = Kingboard_Kill::find(
                      array('$or' => array(
                          array('victim.characterID' => $request['hid']),
                          array('attackers.characterID' => $request['hid'])
                      ))
                    )->sort(array('killTime' => -1));
                    $data = $data->limit(20);
                    Kingboard_Kill_MapReduce_KillsByShipByPilot::mapReduce($request['hid']);
                    $stats = Kingboard_Kill_MapReduce_KillsByShipByPilot::find();
                } else {
                    die('no pilot id specified');
                }
                break;
            // no type or unknown type, we basically assume its home for all here
            default:
                $data = Kingboard_Kill::find()->sort(array('killTime' => -1))->limit(20);
                $count = Kingboard_Kill::count();
                $stats = Kingboard_Kill_MapReduce_KillsByShip::find();
        }

        $stats = $stats->sort(array("value.value" => -1));

        return $this->render("index.html", array('data' => $data, 'count' => $count, 'stats' => $stats));
    }
}
