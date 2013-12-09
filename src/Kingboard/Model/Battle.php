<?php
namespace Kingboard\Model;

class Battle extends \King23\Mongo\MongoObject
{
    protected $_className = "Kingboard_Battle";

    public static function getById($id)
    {
        return parent::doGetInstanceById(__CLASS__, $id);
    }

    public static function find($criteria = array())
    {
        return parent::doFind(__CLASS__, $criteria);
    }

    public static function getByBattleSettings(\Kingboard\Model\BattleSettings $battleSetting)
    {
        $battle = Battle::doGetInstanceByCriteria(__CLASS__, array('settingsId' => $battleSetting->_id));
        if (is_null($battle) || time() > $battle->updated->sec + 600) {
            if (is_null($battle)) {
                $battle = new Battle();
            }
            $battle->data = Battle::generateBattle($battleSetting);
            $battle->updated = new \MongoDate(time());
            $battle->settingsId = $battleSetting->_id;
            $battle->save();
        }
        return $battle;
    }

    public static function generateBattle(\Kingboard\Model\BattleSettings $battleSetting)
    {
        $okills = array();
        $olosses = array();

        $kills = \Kingboard\Model\Kill::find(
            array(
                "killTime" => array(
                    '$gt' => $battleSetting->startdate,
                    '$lt' => $battleSetting->enddate
                ),
                "location.solarSystem" => $battleSetting->system,
                '$or' => array(
                    array(
                        "involvedCharacters" => array(
                            '$in' => array_merge(
                                array_keys($battleSetting->positives),
                                array((int)$battleSetting->ownerCorporation)
                            )
                        )
                    ),
                    array(
                        "involvedCorporations" => array(
                            '$in' => array_merge(
                                array_keys($battleSetting->positives),
                                array((int)$battleSetting->ownerCorporation)
                            )
                        )
                    ),
                    array(
                        "involvedAlliances" => array(
                            '$in' => array_merge(
                                array_keys($battleSetting->positives),
                                array((int)$battleSetting->ownerCorporation)
                            )
                        )
                    ),
                    array(
                        "involvedFactions" => array(
                            '$in' => array_merge(
                                array_keys($battleSetting->positives),
                                array((int)$battleSetting->ownerCorporation)
                            )
                        )
                    ),
                ),
            )
        );

        $timeline = array();
        foreach ($kills as $kill) {
            $killTime = date("Y-m-d H:i:s", $kill->killTime->sec);
            if (!isset($timeline[$killTime])) {
                $timeline[$killTime] = array(
                    "kills" => array(),
                    "losses" => array()
                );
            }

            if(
                (isset($battleSetting->positives[$kill['victim']['factionID']]) && !empty($battleSetting->positives[$kill['victim']['factionID']]))
                || (isset($battleSetting->positives[$kill['victim']['characterID']]) && !empty($battleSetting->positives[$kill['victim']['characterID']]))
                || (isset($battleSetting->positives[$kill['victim']['corporationID']]) && !empty($battleSetting->positives[$kill['victim']['corporationID']]))
                || (isset($battleSetting->positives[$kill['victim']['allianceID']]) && !empty($battleSetting->positives[$kill['victim']['allianceID']]))
                || ($battleSetting->ownerCorporation == $kill['victim']['corporationID'])
            ) {
                if($kill['victim']['characterID'] == 1540862716) {
                print_r($kill['victim']);
                print_r($battleSetting->positives);
                die();
                }
                $timeline[$killTime]['losses'][] = $kill->toArray();
                $olosses[] = $kill->toArray();

            } else {
                $timeline[$killTime]['kills'][] = $kill->toArray();
                $okills[] = $kill->toArray();
            }
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
