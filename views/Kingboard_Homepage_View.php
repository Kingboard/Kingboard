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
            if ($currentPage < 1 )
            {
                $this->sendErrorAndQuit('Page must be a positive value larger than one');
            }
        }
        if (!empty($request['view']))
		{
			$type = $request['view'];
			$qry = Kingboard_EveItem::getShipIDs($type);
			$reqarray = array();
			foreach($qry as $result)
			{
				$reqarray[] = array('victim.shipTypeID' => $result->typeID);
			}
			var_dump($reqarray); die();
		}
        $killsPerPage = 20;
        $skip = ($currentPage - 1) * $killsPerPage;        
        $count = Kingboard_Kill::count();
        $lastPage = ceil($count / $killsPerPage);
        
        if ($currentPage > $lastPage && $lastPage != 0)
        {
            $this->sendErrorAndQuit('Page does not exist');
        }
        
		if(isset($reqarray))
		{
			$data = Kingboard_Kill::find(array('$or' => $reqarray))
				->sort(array('killTime' => -1))
				->skip($skip)
				->limit($killsPerPage);
		}
		else
		{
			$data = Kingboard_Kill::find()
				->sort(array('killTime' => -1))
				->skip($skip)
				->limit($killsPerPage);
		}	
        $templateVars = array(
            'data' => $data,
            'next' => ($skip + $killsPerPage < $count) ? $currentPage + 1 : false,
            'prev' => $currentPage > 1 ? $currentPage - 1 : false,
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
            'action' => '/home'
        );

        $info = array();
        // differences for owned boards
        if($this->_context['ownerID'])
        {
            switch($this->_context['ownerType'])
            {
                case "alliance":
                    $killstats = Kingboard_Kill_MapReduce_KillsByShipByAlliance::getInstanceByAllianceId((int) $this->_context['ownerID']);
                    $lossstats = Kingboard_Kill_MapReduce_LossesByShipByAlliance::getInstanceByAllianceId((int) $this->_context['ownerID']);
                    break;
				case "faction":
                    $killstats = Kingboard_Kill_MapReduce_KillsByShipByFaction::getInstanceByFactionId((int) $this->_context['ownerID']);
                    $lossstats = Kingboard_Kill_MapReduce_LossesByShipByFaction::getInstanceByFactionId((int) $this->_context['ownerID']);
                    break;
				case "corporation":
                    $killstats = Kingboard_Kill_MapReduce_KillsByShipByCorporation::getInstanceByCorporationId((int) $this->_context['ownerID']);
                    $lossstats = Kingboard_Kill_MapReduce_LossesByShipByCorporation::getInstanceByCorporationId((int) $this->_context['ownerID']);
                    break;
                case "pilot":
                    $killstats = Kingboard_Kill_MapReduce_KillsByShipByPilot::getInstanceByPilotId((int) $this->_context['ownerID']);
                    $lossstats = Kingboard_Kill_MapReduce_LossesByShipByPilot::getInstanceByPilotId((int) $this->_context['ownerID']);
                    break;
                default:
                    die("missconfiguration, ownerID set, but no ownerType");
            }
            $totalstats = array();

            if(isset($killstats['value']['group']))
                foreach($killstats['value']['group'] as $type => $value)
                {
                    if(!isset($totalstats[$type]))
                        $totalstats[$type] = array('kills'=> 0, 'losses' => 0);
                    $totalstats[$type]['kills'] = $value;
                }

            if(isset($losstats['value']['group']))
                foreach($lossstats['value']['group'] as $type => $value)
                {
                    if(!isset($totalstats[$type]))
                        $totalstats[$type] = array('kills' => 0, 'losses' => 0);
                    $totalstats[$type]['losses'] = $value;
                }

            ksort($totalstats);

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

            $template = "pilot/index.html";
            $info = Kingboard_Kill::getPilotInfoFromId($request['hid']);
            return $this->render($template, array('killdata' => $killdata, 'lossdata' =>$lossdata, 'count' => $count, 'killstats' => $killstats, 'lossstats' => $lossstats, 'info' => $info));
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

            $template = "corporation/index.html";
            $info = Kingboard_Kill::getCorporationInfoFromId($request['hid']);
            //$stats = $stats->sort(array("value.value" => -1));
            return $this->render($template, array('killdata' => $killdata, 'lossdata' =>$lossdata, 'count' => $count, 'killstats' => $killstats, 'lossstats' => $lossstats, 'info' => $info));
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

            $template = "faction/index.html";
            $info = Kingboard_Kill::getFactionInfoFromId($request['hid']);
            return $this->render($template, array('killdata' => $killdata, 'lossdata' =>$lossdata, 'count' => $count, 'killstats' => $killstats, 'lossstats' => $lossstats, 'info' => $info));
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

            $template = "alliance/index.html";
            $info = Kingboard_Kill::getAllianceInfoFromId($request['hid']);
            return $this->render($template, array('killdata' => $killdata, 'lossdata' =>$lossdata, 'count' => $count, 'killstats' => $killstats, 'lossstats' => $lossstats, 'info' => $info));
        } else {
            die('no alliance id specified');
        }
    }

}
