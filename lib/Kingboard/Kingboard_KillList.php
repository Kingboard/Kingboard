<?php
class Kingboard_KillList
{
    private $ownerType;
    private $ownerID;

    private $killstats;
    private $lossstats;
    private $totalstats;
    private $criteria = array();
    private $count;


    public function __construct($ownerType, $ownerID)
    {
        $this->ownerType = $ownerType;
        $this->ownerID = $ownerID;

        switch($this->ownerType)
        {
            case "alliance":
                $killstats = Kingboard_Kill_MapReduce_KillsByShipByAlliance::getInstanceByAllianceId((int) $this->ownerID);
                $lossstats = Kingboard_Kill_MapReduce_LossesByShipByAlliance::getInstanceByAllianceId((int) $this->ownerID);
                $criteria = array('$or' => array(
                    array('attackers.allianceID' => (int) $this->ownerID),
                    array('victim.allianceID' => (int) $this->ownerID)
                ));
                break;
            case "faction":
                $killstats = Kingboard_Kill_MapReduce_KillsByShipByFaction::getInstanceByFactionId((int) $this->ownerID);
                $lossstats = Kingboard_Kill_MapReduce_LossesByShipByFaction::getInstanceByFactionId((int) $this->ownerID);
                $criteria = array('$or' => array(
                    array('attackers.factionID' => (int)  $this->ownerID),
                    array('victim.factionID' => (int)  $this->ownerID)
                ));
                break;
            case "corp":
            case "corporation":
                $killstats = Kingboard_Kill_MapReduce_KillsByShipByCorporation::getInstanceByCorporationId((int) $this->ownerID);
                $lossstats = Kingboard_Kill_MapReduce_LossesByShipByCorporation::getInstanceByCorporationId((int) $this->ownerID);
                $criteria = array('$or' => array(
                    array('attackers.corporationID' => (int) $this->ownerID),
                    array('victim.corporationID' => (int) $this->ownerID)
                ));
                break;
            case "char":
            case "character":
            case "pilot":
                $killstats = Kingboard_Kill_MapReduce_KillsByShipByPilot::getInstanceByPilotId((int) $this->ownerID);
                $lossstats = Kingboard_Kill_MapReduce_LossesByShipByPilot::getInstanceByPilotId((int) $this->ownerID);
                $criteria = array('$or' => array(
                    array('attackers.characterID' => (int) $this->ownerID),
                    array('victim.characterID' => (int) $this->ownerID)
                ));
                break;
            default:
                // its not a known type of anything, so we dont need to set anything here.
                // but can return
                return;
        }


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

        $this->killstats = $killstats;
        $this->lossstats = $lossstats;
        $this->totalstats = $totalstats;
        $this->criteria = $criteria;
    }

    public function getCount()
    {
        if(is_null($this->count))
            $this->count =  Kingboard_Kill::find($this->criteria, array("_id"=> true))->count();
        return $this->count;
    }

    public function getKillStats()
    {
        return $this->killstats;
    }

    public function getLossStats()
    {
        return $this->lossstats;
    }

    public function getTotalStats()
    {
        return $this->totalstats;
    }

    public function getKills($skip, $killsPerPage)
    {
        return Kingboard_Kill::find($this->criteria)
                ->sort(array('killTime' => -1))
                ->skip($skip)
                ->limit($killsPerPage);
    }


}