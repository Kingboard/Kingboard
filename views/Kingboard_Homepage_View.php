<?php
class Kingboard_Homepage_View extends Kingboard_Base_View
{

    protected $killsPerPage = 20;
    
    public function index($request)
    {
        $currentPage = 1;
        if (!empty($request['page']))
        {
            $currentPage = (int) preg_replace('/[^0-9]+/', '', $request['page']);
            if ($currentPage < 1)
            {
                $this->sendErrorAndQuit('Page must be a positive value larger than one');
            }
        }
        
        $killsPerPage = 20;
        $skip = ($currentPage - 1) * $killsPerPage;        
        $count = Kingboard_Kill::count();
        $lastPage = ceil($count / $killsPerPage);
        
        if ($currentPage > $lastPage)
        {
            $this->sendErrorAndQuit('Page does not exist');
        }
        
        $data = Kingboard_Kill::find()
            ->sort(array('killTime' => -1))
            ->skip($skip)
            ->limit($killsPerPage);
        
        $templateVars = array(
            'data' => $data,
            'next' => ($skip + $killsPerPage < $count) ? $currentPage + 1 : false,
            'prev' => $currentPage > 1 ? $currentPage - 1 : false,
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
            'action' => '/home'
        );
        
        if (!empty($request['ajax']))
        {
            return $this->render('home_killspage.html', $templateVars);
        }
        $stats = Kingboard_Kill_MapReduce_KillsByShip::find();
        $info = array();
        $template = "index.html";
        $stats = $stats->sort(array("value.value" => -1));
        
        $templateVars['count'] = $count;
        $templateVars['stats'] = $stats;
        $templateVars['info'] = $info;

        $templateVars['reports'] = Kingboard_BattleSettings::find()->limit(20)->sort(array('enddate' => -1));


        return $this->render($template, $templateVars);
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
