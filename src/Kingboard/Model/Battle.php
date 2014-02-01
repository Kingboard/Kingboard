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

    public static function getByBattleSettings(BattleSettings $battleSetting)
    {
        $battle = Battle::doGetInstanceByCriteria(__CLASS__, array('settingsId' => $battleSetting->_id));

        // only if it is null we are supposed to generate
        if (is_null($battle)) {
            $battle = self::generateForSettings($battleSetting);
        }

        return $battle;
    }

    public static function generateForSettings(BattleSettings $battleSetting)
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

    private static function findKills(BattleSettings $battleSetting)
    {
        return \Kingboard\Model\Kill::find(
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
                                array((int) $battleSetting->ownerCorporation)
                            )
                        )
                    ),
                    array(
                        "involvedCorporations" => array(
                            '$in' => array_merge(
                                array_keys($battleSetting->positives),
                                array((int) $battleSetting->ownerCorporation)
                            )
                        )
                    ),
                    array(
                        "involvedAlliances" => array(
                            '$in' => array_merge(
                                array_keys($battleSetting->positives),
                                array((int) $battleSetting->ownerCorporation)
                            )
                        )
                    ),
                    array(
                        "involvedFactions" => array(
                            '$in' => array_merge(
                                array_keys($battleSetting->positives),
                                array((int) $battleSetting->ownerCorporation)
                            )
                        )
                    ),
                ),
            ),
            array(
                "killID" => 1,
                "solarSystemID" => 1,
                "killTime" => 1,
                "moonID" => 1,
                "victim" => 1,
                "location" => 1,
            )
        );
    }


    /**
     * @param \King23\Mongo\MongoObject $kill
     * @param BattleSettings $battleSetting
     */
    private static function isLoss($kill, $battleSetting)
    {
        return (
                isset($battleSetting->positives[$kill['victim']['factionID']])
                && !empty($battleSetting->positives[$kill['victim']['factionID']])
            ) || (
                isset($battleSetting->positives[$kill['victim']['characterID']])
                && !empty($battleSetting->positives[$kill['victim']['characterID']])
            ) || (
                isset($battleSetting->positives[$kill['victim']['corporationID']])
                && !empty($battleSetting->positives[$kill['victim']['corporationID']])
            ) || (
                isset($battleSetting->positives[$kill['victim']['allianceID']])
                && !empty($battleSetting->positives[$kill['victim']['allianceID']])
            ) || ($battleSetting->ownerCorporation == $kill['victim']['corporationID']);
    }

    public static function generateBattle(BattleSettings $battleSetting)
    {
        $okills = array();
        $olosses = array();

        $stats = array();

        $kills = self::findKills($battleSetting);

        $timeline = array();
        foreach ($kills as $kill) {
            $killTime = date("Y-m-d H:i:s", $kill->killTime->sec);

            if (!isset($timeline[$killTime])) {
                $timeline[$killTime] = array(
                    "kills" => array(),
                    "losses" => array()
                );
            }

            if (!isset($stats[$kill['victim']['shipType']])) {
                $stats[$kill['victim']['shipType']] = array(
                    "losses" => 0,
                    "kills" => 0
                );
            }

            if (self::isLoss($kill, $battleSetting)) {
                $timeline[$killTime]['losses'][] = $kill->toArray();

                $stats[$kill['victim']['shipType']]['losses']++;
                $olosses[] = $kill->toArray();
            } else {
                $timeline[$killTime]['kills'][] = $kill->toArray();

                $stats[$kill['victim']['shipType']]['kills']++;

                $okills[] = $kill->toArray();
            }
        }

        ksort($timeline);
        ksort($stats);

        return array(
            "kills" => $okills,
            "losses" => $olosses,
            "timeline" => $timeline,
            "stats" => $stats,
            //"battleSetting" => $battleSetting
        );

    }

    public function toArray()
    {
        return $this->_data;
    }
}
