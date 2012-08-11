<?php
namespace Kingboard\Lib;

/**
 *  KillList, utility class to get kill lists
 */
class KillList
{
    /**
     * @var string
     */
    private $ownerType;

    /**
     * @var string
     */
    private $ownerID;

    // instances that represent the mapreduce its comming from
    private $killstats;
    private $lossstats;
    private $totalstats;

    // criteria to be used to get this kill list
    private $criteria = array();

    /**
     * @var int total count of kills
     */
    private $count;


    /**
     * @param string $ownerType
     * @param string $ownerID
     */
    public function __construct($ownerType, $ownerID)
    {
        $this->ownerType = $ownerType;
        $this->ownerID = $ownerID;

        switch($this->ownerType)
        {
            case "alliance":
                $killstats = \Kingboard\Model\MapReduce\KillsByShipByAlliance::getInstanceByAllianceId((int) $this->ownerID);
                $lossstats = \Kingboard\Model\MapReduce\LossesByShipByAlliance::getInstanceByAllianceId((int) $this->ownerID);
                $criteria = array('$or' => array(
                    array('attackers.allianceID' => (int) $this->ownerID),
                    array('victim.allianceID' => (int) $this->ownerID)
                ));
                break;
            case "faction":
                $killstats = \Kingboard\Model\MapReduce\KillsByShipByFaction::getInstanceByFactionId((int) $this->ownerID);
                $lossstats = \Kingboard\Model\MapReduce\LossesByShipByFaction::getInstanceByFactionId((int) $this->ownerID);
                $criteria = array('$or' => array(
                    array('attackers.factionID' => (int)  $this->ownerID),
                    array('victim.factionID' => (int)  $this->ownerID)
                ));
                break;
            case "corp":
            case "corporation":
                $killstats = \Kingboard\Model\MapReduce\KillsByShipByCorporation::getInstanceByCorporationId((int) $this->ownerID);
                $lossstats = \Kingboard\Model\MapReduce\LossesByShipByCorporation::getInstanceByCorporationId((int) $this->ownerID);
                $criteria = array('$or' => array(
                    array('attackers.corporationID' => (int) $this->ownerID),
                    array('victim.corporationID' => (int) $this->ownerID)
                ));
                break;
            case "char":
            case "character":
            case "pilot":
                $killstats = \Kingboard\Model\MapReduce\KillsByShipByPilot::getInstanceByPilotId((int) $this->ownerID);
                $lossstats = \Kingboard\Model\MapReduce\LossesByShipByPilot::getInstanceByPilotId((int) $this->ownerID);
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
        $count = 0;

        if(isset($killstats['value']['group']))
            foreach($killstats['value']['group'] as $type => $value)
            {
                if(!isset($totalstats[$type]))
                    $totalstats[$type] = array('kills'=> 0, 'losses' => 0);
                $totalstats[$type]['kills'] = $value;
                $count += $value;
            }

        if(isset($lossstats['value']['group']))
            foreach($lossstats['value']['group'] as $type => $value)
            {
                if(!isset($totalstats[$type]))
                    $totalstats[$type] = array('kills' => 0, 'losses' => 0);
                $totalstats[$type]['losses'] = $value;
                $count += $value;
            }

        ksort($totalstats);

        $this->killstats = $killstats;
        $this->lossstats = $lossstats;
        $this->totalstats = $totalstats;
        $this->criteria = $criteria;
        $this->count = $count;

    }

    /**
     * if no count for this list exists yet, create a count
     * return count
     * @return int
     */
    public function getCount()
    {
        if(is_null($this->count))
            $this->count =  \Kingboard\Model\Kill::find($this->criteria, array("_id"=> true))->count();
        return $this->count;
    }

    /**
     * killstats
     * @return \King23\Mongo\MongoObject
     */
    public function getKillStats()
    {
        return $this->killstats;
    }

    /**
     * lossstats
     * @return \King23\Mongo\MongoObject
     */
    public function getLossStats()
    {
        return $this->lossstats;
    }

    /**
     * @return array
     */
    public function getTotalStats()
    {
        return $this->totalstats;
    }

    /**
     * get kills for the criteria this list obeys
     * @param int $skip
     * @param int $killsPerPage
     * @return mixed
     */
    public function getKills($skip, $killsPerPage)
    {
        return \Kingboard\Model\Kill::find($this->criteria)
                ->sort(array('killTime' => -1))
                ->skip($skip)
                ->limit($killsPerPage);
    }

}