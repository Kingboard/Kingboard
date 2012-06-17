<?php
class Kingboard_Homepage_View extends Kingboard_Base_View
{
    public function index($request)
    {
        $currentPage = 1;
        if (!empty($request['page']))
        {
            $currentPage = (int) $request['page'];

            if ($currentPage < 1)
            {
                $currentPage = 1;
            }
        }


        $info = array();
        $templateVars =array();
        $criteria = array();
        // differences for owned boards
        if($this->_context['ownerID'])
        {
            switch($this->_context['ownerType'])
            {
                case "alliance":
                    $killstats = Kingboard_Kill_MapReduce_KillsByShipByAlliance::getInstanceByAllianceId((int) $this->_context['ownerID']);
                    $lossstats = Kingboard_Kill_MapReduce_LossesByShipByAlliance::getInstanceByAllianceId((int) $this->_context['ownerID']);
                    $criteria = array('$or' => array(
                        array('attackers.allianceID' => (int)  $this->_context['ownerID']),
                        array('victim.allianceID' => (int)  $this->_context['ownerID'])
                    ));
                    break;
				case "faction":
                    $killstats = Kingboard_Kill_MapReduce_KillsByShipByFaction::getInstanceByFactionId((int) $this->_context['ownerID']);
                    $lossstats = Kingboard_Kill_MapReduce_LossesByShipByFaction::getInstanceByFactionId((int) $this->_context['ownerID']);
                    $criteria = array('$or' => array(
                        array('attackers.factionID' => (int)  $this->_context['ownerID']),
                        array('victim.factionID' => (int)  $this->_context['ownerID'])
                    ));
                    break;
				case "corporation":
                    $killstats = Kingboard_Kill_MapReduce_KillsByShipByCorporation::getInstanceByCorporationId((int) $this->_context['ownerID']);
                    $lossstats = Kingboard_Kill_MapReduce_LossesByShipByCorporation::getInstanceByCorporationId((int) $this->_context['ownerID']);
                    $criteria = array('$or' => array(
                        array('attackers.corporationID' => (int)  $this->_context['ownerID']),
                        array('victim.corporationID' => (int)  $this->_context['ownerID'])
                    ));
                    break;
                case "pilot":
                    $killstats = Kingboard_Kill_MapReduce_KillsByShipByPilot::getInstanceByPilotId((int) $this->_context['ownerID']);
                    $lossstats = Kingboard_Kill_MapReduce_LossesByShipByPilot::getInstanceByPilotId((int) $this->_context['ownerID']);
                    $criteria = array('$or' => array(
                        array('attackers.characterID' => (int)  $this->_context['ownerID']),
                        array('victim.characterID' => (int)  $this->_context['ownerID'])
                    ));
                    break;
                default:
                    die("missconfiguration, ownerID set, but no ownerType");
            }
            $totalstats = $this->calculateTotalStats($killstats, $lossstats);
            $templateVars['killstats'] = $killstats;
            $templateVars['lossstats'] = $lossstats;
            $templateVars['totalstats'] = $totalstats;
            $template = "index_owned_board.html";
        } else {
            $stats = Kingboard_Kill_MapReduce_KillsByShip::find();
            $stats = $stats->sort(array("value.value" => -1));
            $templateVars['stats'] = $stats;
            $template = "index.html";
        }


        $killsPerPage = 20;
        $skip = ($currentPage - 1) * $killsPerPage;
        $count = Kingboard_Kill::find($criteria, array("_id"=> true))->count();
        $lastPage = ceil($count / $killsPerPage);

        if ($currentPage > $lastPage && $lastPage != 0)
        {
            $this->sendErrorAndQuit('Page does not exist');
        }

        $data = Kingboard_Kill::find($criteria)
                ->sort(array('killTime' => -1))
                ->skip($skip)
                ->limit($killsPerPage);

        $templateVars['data'] = $data;
        $templateVars['next'] = ($skip + $killsPerPage < $count) ? $currentPage + 1 : false;
        $templateVars['prev'] = $currentPage > 1 ? $currentPage - 1 : false;
        $templateVars['currentPage'] = $currentPage;
        $templateVars['lastPage'] = $lastPage;
        $templateVars['action'] = '/home';
        $templateVars['count'] = $count;
        $templateVars['info'] = $info;
        $templateVars['reports'] = Kingboard_BattleSettings::find()->limit(20)->sort(array('enddate' => -1));

        return $this->render($template, $templateVars);
    }

    public function pilot($request)
    {
	    if (!empty($request['view']))
		{
			$type = $request['view'];
			$qry = Kingboard_EveItem::getShipIDs($type);
			$reqarray = array();
			foreach($qry as $result)
			{
				$reqarray[] = array('victim.shipTypeID' => $result->typeID);
			}
		}
		
        if(!empty($request['hid']))
        {
            $count = Kingboard_Kill::find(
                array('attackers.characterID' => (int) $request['hid'])
            )->count();

			if(!empty($reqarray))
			{
				$killdata = Kingboard_Kill::find(
					  array('attackers.characterID' => (int)  $request['hid'], '$or' => $reqarray)
				)->sort(array('killTime' => -1))->limit(20);
			}
			else
			{
				$killdata = Kingboard_Kill::find(
					  array('attackers.characterID' => (int)  $request['hid'])
				)->sort(array('killTime' => -1))->limit(20);
			}
			if(!empty($reqarray))
			{
				$lossdata = Kingboard_Kill::find(
					array('victim.characterID' => (int) $request['hid'], '$or' => $reqarray)
				)->sort(array('killTime' => -1))->limit(20);
			}
			else
			{
				$lossdata = Kingboard_Kill::find(
					array('victim.characterID' => (int) $request['hid'])
				)->sort(array('killTime' => -1))->limit(20);
			}
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
	    if (!empty($request['view']))
		{
			$type = $request['view'];
			$qry = Kingboard_EveItem::getShipIDs($type);
			$reqarray = array();
			foreach($qry as $result)
			{
				$reqarray[] = array('victim.shipTypeID' => $result->typeID);
			}
		}
		
        if(!empty($request['hid']))
        {
            $count = Kingboard_Kill::find(
                array('attackers.corporationID' => (int) $request['hid'])
            )->count();

			if(!empty($reqarray))
			{
				$killdata = Kingboard_Kill::find(
					  array('attackers.corporationID' => (int)  $request['hid'], '$or' => $reqarray)
				)->sort(array('killTime' => -1))->limit(20);
			}
			else
			{
				$killdata = Kingboard_Kill::find(
					  array('attackers.corporationID' => (int)  $request['hid'])
				)->sort(array('killTime' => -1))->limit(20);
			}
			
			if(!empty($reqarray))
			{
				$lossdata = Kingboard_Kill::find(
					array('victim.corporationID' => (int) $request['hid'], '$or' => $reqarray)
				)->sort(array('killTime' => -1))->limit(20);
			}
			else
			{
				$lossdata = Kingboard_Kill::find(
					array('victim.corporationID' => (int) $request['hid'])
				)->sort(array('killTime' => -1))->limit(20);
			}
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
	    if (!empty($request['view']))
		{
			$type = $request['view'];
			$qry = Kingboard_EveItem::getShipIDs($type);
			$reqarray = array();
			foreach($qry as $result)
			{
				$reqarray[] = array('victim.shipTypeID' => $result->typeID);
			}
		}

        if(!empty($request['hid']))
        {
            $count = Kingboard_Kill::find(
                array('attackers.factionID' => (int) $request['hid'])
            )->count();
			
			if(!empty($reqarray))
			{
				$killdata = Kingboard_Kill::find(
					  array('attackers.factionID' => (int)  $request['hid'], '$or' => $reqarray)
				)->sort(array('killTime' => -1))->limit(20);
			}
			else
			{
				$killdata = Kingboard_Kill::find(
					  array('attackers.factionID' => (int)  $request['hid'])
				)->sort(array('killTime' => -1))->limit(20);
			}

			if(!empty($reqarray))
			{
				$lossdata = Kingboard_Kill::find(
					array('victim.factionID' => (int) $request['hid'], '$or' => $reqarray)
				)->sort(array('killTime' => -1))->limit(20);
			}
			else
			{
				$lossdata = Kingboard_Kill::find(
					array('victim.factionID' => (int) $request['hid'])
				)->sort(array('killTime' => -1))->limit(20);
			}
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
		
	    if (!empty($request['view']))
		{
			$type = $request['view'];
			$qry = Kingboard_EveItem::getShipIDs($type);
			$reqarray = array();
			foreach($qry as $result)
			{
				$reqarray[] = array('victim.shipTypeID' => $result->typeID);
			}
		}
		
        if(!empty($request['hid']))
        {
            $count = Kingboard_Kill::find(
                array('attackers.allianceID' => (int) $request['hid'])
            )->count();
			if(!empty($reqarray))
			{
				$killdata = Kingboard_Kill::find(
					array('attackers.allianceID' => (int) $request['hid'], '$or' => $reqarray)
				)->sort(array('killTime' => -1))->limit(20);
			}
			else
			{
				$killdata = Kingboard_Kill::find(
					array('attackers.allianceID' => (int) $request['hid'])
				)->sort(array('killTime' => -1))->limit(20);

			}
			if(!empty($reqarray))
			{
				$lossdata = Kingboard_Kill::find(
					array('victim.allianceID' => (int) $request['hid'], '$or' => $reqarray)
				)->sort(array('killTime' => -1))->limit(20);
			}
			else
			{
				$lossdata = Kingboard_Kill::find(
					array('victim.allianceID' => (int) $request['hid'])
				)->sort(array('killTime' => -1))->limit(20);
			}
			
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
