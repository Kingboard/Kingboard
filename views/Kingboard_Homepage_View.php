<?php
class Kingboard_Homepage_View extends Kingboard_Base_View
{

    protected $killsPerPage = 20;
    
    public function index($request)
    {
        $page = 1;
        if (!empty($_GET['page']))
        {
            $page = (int) preg_replace('/[^0-9]+/', '', $_GET['page']);
            if ($page < 1)
            {
                $this->sendErrorAndQuit('There are no negative pages morron');
            }
        }
        
        $killsPerPage = 20;
        $skip = ($page - 1) * $killsPerPage;
        $data = Kingboard_Kill::find()
                    ->sort(array('killTime' => -1))
                    ->skip($skip)
                    ->limit($killsPerPage);
        
        $count = Kingboard_Kill::count();
        $context = array(
            'data' => $data,
            'next' => ($skip + $killsPerPage < $count) ? $page + 1 : false,
            'prev' => $page > 1 ? $page - 1 : false
        );
        
        if (!empty($_GET['ajax']))
        {
            return $this->render('home_killspage.html', $context);
        }
        $stats = Kingboard_Kill_MapReduce_KillsByShip::find();
        $info = array();
        $template = "index.html";
        $stats = $stats->sort(array("value.value" => -1));
        
        $context['count'] = $count;
        $context['stats'] = $stats;
        $context['info'] = $info;
        
        return $this->render($template, $context);
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
