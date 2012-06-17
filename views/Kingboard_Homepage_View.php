<?php
class Kingboard_Homepage_View extends Kingboard_Base_View
{
    public function index($request)
    {
        $currentPage = 1;
        if (!empty($request['page']))
        {
            $currentPage = ((int) $request['page'] <1) ?  1 : (int) $request['page'];
        }


        $info = array();
        $templateVars =array();
        // differences for owned boards
        if($this->_context['ownerID'])
        {
            $killList = new Kingboard_KillList($this->_context["ownerType"], $this->_context["ownerID"]);
            $templateVars['killstats'] = $killList->getKillStats();
            $templateVars['lossstats'] = $killList->getLossStats();
            $templateVars['totalstats'] = $killList->getTotalStats();
            $template = "index_owned_board.html";
        } else {
            // this is the only case of a list without owner/type, which is open list.
            $killList = new Kingboard_KillList(null, null);
            $stats = Kingboard_Kill_MapReduce_KillsByShip::find();
            $stats = $stats->sort(array("value.value" => -1));
            $templateVars['stats'] = $stats;
            $template = "index.html";
        }

        $paginator = new Kingboard_Paginator($currentPage, $killList->getCount());

        $skip = $paginator ->getSkip();
        $data = $killList->getKills($skip, $paginator->getKillsPerPage());

        $templateVars['data'] = $data;

        // merge in pagination data
        $templateVars= array_merge($templateVars, $paginator->getNavArray());

        $templateVars['action'] = '/home';
        $templateVars['count'] = $killList->getCount();
        $templateVars['info'] = $info;

        // battles
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

            $killstats = Kingboard_Kill_MapReduce_KillsByShipByPilot::getInstanceByPilotId($request['hid']);
            $lossstats = Kingboard_Kill_MapReduce_LossesByShipByPilot::getInstanceByPilotId($request['hid']);
            $totalstats = $this->calculateTotalStats($killstats, $lossstats);
            $template = "pilot/index.html";
            $info = Kingboard_Kill::getPilotInfoFromId($request['hid']);
            return $this->render($template, array('killdata' => $killdata, 'lossdata' =>$lossdata, 'count' => $count, 'killstats' => $killstats, 'lossstats' => $lossstats, 'totalstats' => $totalstats, 'info' => $info));
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

            $killstats = Kingboard_Kill_MapReduce_KillsByShipByCorporation::getInstanceByCorporationId($request['hid']);
            $lossstats = Kingboard_Kill_MapReduce_LossesByShipByCorporation::getInstanceByCorporationId($request['hid']);
            $totalstats = $this->calculateTotalStats($killstats, $lossstats);
            $template = "corporation/index.html";
            $info = Kingboard_Kill::getCorporationInfoFromId($request['hid']);
            //$stats = $stats->sort(array("value.value" => -1));
            return $this->render($template, array('killdata' => $killdata, 'lossdata' =>$lossdata, 'count' => $count, 'killstats' => $killstats, 'lossstats' => $lossstats, 'totalstats' => $totalstats, 'info' => $info));
        } else {
            die('no corporation id specified');
        }
    }

    public function faction($request)
    {

        if(!empty($request['hid']))
        {
            $count = Kingboard_Kill::find(
                array('attackers.factionID' => (int) $request['hid'])
            )->count();
			
            $killdata = Kingboard_Kill::find(
                  array('attackers.factionID' => (int)  $request['hid'])
            )->sort(array('killTime' => -1))->limit(20);

            $lossdata = Kingboard_Kill::find(
                array('victim.factionID' => (int) $request['hid'])
            )->sort(array('killTime' => -1))->limit(20);

            $killstats = Kingboard_Kill_MapReduce_KillsByShipByFaction::getInstanceByFactionId($request['hid']);
            $lossstats = Kingboard_Kill_MapReduce_LossesByShipByFaction::getInstanceByFactionId($request['hid']);
            $totalstats = $this->calculateTotalStats($killstats, $lossstats);
            $template = "faction/index.html";
            $info = Kingboard_Kill::getFactionInfoFromId($request['hid']);
            return $this->render($template, array('killdata' => $killdata, 'lossdata' =>$lossdata, 'count' => $count, 'totalstats' => $totalstats, 'killstats' => $killstats, 'lossstats' => $lossstats, 'info' => $info));
        } else {
            die('no alliance id specified');
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
                array('attackers.allianceID' => (int) $request['hid'])
            )->sort(array('killTime' => -1))->limit(20);

            $lossdata = Kingboard_Kill::find(
                array('victim.allianceID' => (int) $request['hid'])
            )->sort(array('killTime' => -1))->limit(20);

            $killstats = Kingboard_Kill_MapReduce_KillsByShipByAlliance::getInstanceByAllianceId($request['hid']);
            $lossstats = Kingboard_Kill_MapReduce_LossesByShipByAlliance::getInstanceByAllianceId($request['hid']);
            $totalstats = $this->calculateTotalStats($killstats, $lossstats);
            $template = "alliance/index.html";
            $info = Kingboard_Kill::getAllianceInfoFromId($request['hid']);
            return $this->render($template, array('killdata' => $killdata, 'lossdata' =>$lossdata, 'count' => $count, 'killstats' => $killstats, 'lossstats' => $lossstats, 'totalstats' => $totalstats, 'info' => $info));
        } else {
            die('no alliance id specified');
        }
    }

    /**
     * return the totalsstats array with the combined losses/kills
     *
     * @deprecated
     * @param array $killstats
     * @param array $lossstats
     * @return array
     */
    private function calculateTotalStats($killstats, $lossstats)
    {
        $totalstats = array();

        if(isset($killstats['value']['group']))
            foreach($killstats['value']['group'] as $type => $value)
            {
                if(!isset($totalstats[$type]))
                    $totalstats[$type] = array('kills'=> 0, 'losses' => 0);
                $totalstats[$type]['kills'] = $value;
            }

        if(isset($lossstats['value']['group']))
            foreach($lossstats['value']['group'] as $type => $value)
            {
                if(!isset($totalstats[$type]))
                    $totalstats[$type] = array('kills' => 0, 'losses' => 0);
                $totalstats[$type]['losses'] = $value;
            }

        ksort($totalstats);
        return $totalstats;
    }

}
