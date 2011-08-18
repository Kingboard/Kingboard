<?php
class Kingboard_Battle extends King23_MongoObject
{
    protected $_className = __CLASS__;

    public static function getById($id)
    {
        return self::_getInstanceById(__CLASS__, $id);
    }

    public static function find($criteria = array())
    {
        return self::_find(__CLASS__, $criteria);
    }

    public static function getByBattleSettings(Kingboard_BattleSettings $battleSetting)
    {
        $battle = Kingboard_Battle::_getInstanceByCriteria(__CLASS__, array('settingsId' => $battleSetting->_id));
        if(is_null($battle) || time() > $battle->updated->sec + 600)
        {
            if(is_null($battle))
                $battle = new Kingboard_Battle();
            $battle->data = Kingboard_Battle::generateBattle($battleSetting);
            $battle->updated = new MongoDate(time());
            $battle->settingsId = $battleSetting->_id;
//            print_r($battle);
            $battle->save();
        }
        return $battle;
    }

    public static function generateBattle(Kingboard_BattleSettings $battleSetting)
    {
        $okills = array();
        $olosses = array();
        
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

            $timeline[$killTime]['kills'][] = $kill->toArray();
            $okills[] = $kill->toArray();
        }

        foreach($losses as $kill)
        {
            $killTime = date("Y-m-d H:i:s",$kill->killTime->sec);
            if(!isset($timeline[$killTime]))
                $timeline[$killTime] = array(
                    "kills" => array(),
                    "losses" => array()
                );

            $timeline[$killTime]['losses'][] = $kill->toArray();
            $olosses[] = $kill->toArray();
        }
        ksort($timeline);

        return array(
            "kills" => $okills,
            "losses" => $olosses,
            "timeline" => $timeline,
            //"battleSetting" => $battleSetting
        );

    }

}
