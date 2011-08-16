<?php
class Kingboard_Battle_View extends Kingboard_Base_View
{
    /**
     * display a list of battles
     * @param array $parameters
     * @return void
     */
    public function index(array $parameters)
    {

    }

    /**
     * display a certain battle
     * @param array $parameters
     * @return void
     */
    public function show(array $parameters)
    {
        $battleSetting = Kingboard_BattleSettings::getById($parameters['id']);

        if(is_null($battleSetting))
            $this->sendErrorAndQuit("Battle with Id " . $parameters['id'] . " does not exist");
        $kills = Kingboard_Kill::find(array(
            "killTime" => array(
                '$gt' => $battleSetting->startdate,
                '$lt' => $battleSetting->enddate
            ),
            "location.solarSystem" => $battleSetting->system,
            '$or' => array(
                array("attackers.corporationID" => array('$in' => array_keys($battleSetting->positives))),
                array("attackers.allianceID" => array('$in' => array_keys($battleSetting->positives)))
            ),
            'victim.corporationID' => array('$nin' => array_keys($battleSetting->positives)),
            'victim.allianceID' => array('$nin' => array_keys($battleSetting->positives))
        ));
        $losses = Kingboard_Kill::find(array(
            "killTime" => array(
                '$gt' => $battleSetting->startdate,
                '$lt' => $battleSetting->enddate
            ),
            "location.solarSystem" => $battleSetting->system,
            '$or' => array(
                array("victim.corporationID" => array('$in' => array_keys($battleSetting->positives))),
                array("victim.allianceID" => array('$in' => array_keys($battleSetting->positives)))
            )
        ));

        $timeline = array();
        foreach($kills as $kill)
        {
            $killTime = date("Y-m-d H:i:s",$kill->killTime->sec);
            if(!isset($timeline[$killTime]))
                $timeline[$killTime] = array(
                    "kills" => array(),
                    "losses" => array()
                );

            $timeline[$killTime]['kills'][] = $kill;
        }

        foreach($losses as $kill)
        {
            $killTime = date("Y-m-d H:i:s",$kill->killTime->sec);
            if(!isset($timeline[$killTime]))
                $timeline[$killTime] = array(
                    "kills" => array(),
                    "losses" => array()
                );

            $timeline[$killTime]['losses'][] = $kill;
        }
        ksort($timeline);
     
        $this->render("battle.html", array(
            "kills" => $kills,
            "losses" => $losses,
            "timeline" => $timeline,
            "battleSetting" => $battleSetting
        ));
    }
}