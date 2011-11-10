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
        $info = array();
        // differences for owned boards
        if($this->_context['ownerID'])
        {
            switch($this->_context['ownerType'])
            {
                case "alliance":
                    $killstats = Kingboard_Kill_MapReduce_KillsByShipByAlliance::mapReduceKills((int) $this->_context['ownerID']);
                    $lossstats = Kingboard_Kill_MapReduce_KillsByShipByAlliance::mapReduceLosses((int) $this->_context['ownerID']);
                    break;
                case "corporation":
                    $killstats = Kingboard_Kill_MapReduce_KillsByShipByCorporation::mapReduceKills((int) $this->_context['ownerID']);
                    $lossstats = Kingboard_Kill_MapReduce_KillsByShipByCorporation::mapReduceLosses((int) $this->_context['ownerID']);
                    break;
                case "pilot":
                    $killstats = Kingboard_Kill_MapReduce_KillsByShipByPilot::mapReduceKills((int) $this->_context['ownerID']);
                    $lossstats = Kingboard_Kill_MapReduce_KillsByShipByPilot::mapReduceLosses((int) $this->_context['ownerID']);
                    break;
                default:
                    die("missconfiguration, ownerID set, but no ownerType");
            }
            $templateVars['killstats'] = $killstats;
            $templateVars['lossstats'] = $lossstats;
            $template = "index_owned_board.html";
        } else {
            $stats = Kingboard_Kill_MapReduce_KillsByShip::find();
            $stats = $stats->sort(array("value.value" => -1));
            $templateVars['stats'] = $stats;
            $template = "index.html";
        }

        $templateVars['count'] = $count;
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

    public function corporation($request)
    {
        if(!empty($request['hid']))
        {
            $count = Kingboard_Kill::find(
                array('attackers.corporationID' => (int) $request['hid'])
            )->count();

            $killdata = Kingboard_Kill::find(
                  array('attackers.corporationID' => (int)  $request['hid'])
            )->sort(array('killTime' => -1))->limit(20);

            $lossdata = Kingboard_Kill::find(
                array('victim.corporationID' => (int) $request['hid'])
            )->sort(array('killTime' => -1))->limit(20);

            $killstats = Kingboard_Kill_MapReduce_KillsByShipByCorporation::mapReduceKills((int) $request['hid']);
            $lossstats = Kingboard_Kill_MapReduce_KillsByShipByCorporation::mapReduceLosses((int) $request['hid']);
            $template = "corporation_home.html";
            $info = Kingboard_Kill::getCorporationInfoFromId($request['hid']);
            //$stats = $stats->sort(array("value.value" => -1));
            return $this->render($template, array('killdata' => $killdata, 'lossdata' =>$lossdata, 'count' => $count, 'killstats' => $killstats, 'lossstats' => $lossstats, 'info' => $info));
        } else {
            die('no corporation id specified');
        }
    }


    public function alliance($request)
    {
        if(!empty($request['hid']))
        {
            $count = Kingboard_Kill::find(
                array('attackers.allianceID' => (int) $request['hid'])
            )->count();

            $killdata = Kingboard_Kill::find(
                  array('attackers.allianceID' => (int)  $request['hid'])
            )->sort(array('killTime' => -1))->limit(20);

            $lossdata = Kingboard_Kill::find(
                array('victim.allianceID' => (int) $request['hid'])
            )->sort(array('killTime' => -1))->limit(20);

            $killstats = Kingboard_Kill_MapReduce_KillsByShipByAlliance::mapReduceKills((int) $request['hid']);
            $lossstats = Kingboard_Kill_MapReduce_KillsByShipByAlliance::mapReduceLosses((int) $request['hid']);
            $template = "alliance_home.html";
            $info = Kingboard_Kill::getAllianceInfoFromId($request['hid']);
            return $this->render($template, array('killdata' => $killdata, 'lossdata' =>$lossdata, 'count' => $count, 'killstats' => $killstats, 'lossstats' => $lossstats, 'info' => $info));
        } else {
            die('no alliance id specified');
        }
    }

}
