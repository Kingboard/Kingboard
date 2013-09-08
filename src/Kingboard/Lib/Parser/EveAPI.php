<?php
namespace Kingboard\Lib\Parser;

/**
 * parse API style kills
 */
use Kingboard\Lib\Stomp\KillPublisher;
use Kingboard\Model\EveItem;
use Kingboard\Model\EveSolarSystem;
use Kingboard\Model\Kill;
use Pheal\Pheal;

class EveAPI
{
    public function parseKills($kills)
    {
        $oldkills = 0;
        $newkills = 0;
        $errors = 0;
        foreach ($kills as $kill) {
            if (
                !is_null(\King23\Core\Registry::getInstance()->stomp)
                && \King23\Core\Registry::getInstance()->stomp['post']
            ) {
                KillPublisher::send($kill->toArray());
            }

            /** @var $kill \Pheal\Core\Result */
            try {
                if ($this->parseKill($kill->toArray())) {
                    $newkills++;
                } else {
                    $oldkills++;
                }
            } catch (\Exception $e) {
                $errors++;
            }
        }
        return array('oldkills' => $oldkills, 'newkills' => $newkills, 'errors' => $errors);
    }

    /**
     * parse an kill array, add all necessary data and safe
     * @param array $kill
     * @throws \Exception
     * @return bool
     */
    public function parseKill($kill)
    {
        $qtyDropped = 0;
        $qtyDestroyed = 0;
        $valueDropped = 0;
        $valueDestroyed = 0;
        // this needs to be run before exit of loop, otherwise having all kills of this run
        // will cause the lastID not being updated
        if (!isset($kill['killID']) || is_null($kill['killID']) || $kill['killID'] < 1) {
            throw new \Exception("no valid killID set");
        }

        $killdata = $kill;

        // the kill needs some additional data

        // convert killID to int
        $killdata['killID'] = (int)$killdata['killID'];

        // location data
        $killdata['location'] = array(
            "solarSystem" => EveSolarSystem::getBySolarSystemId($kill['solarSystemID'])->itemName,
            "security" => EveSolarSystem::getBySolarSystemId($kill['solarSystemID'])->security,
            "region" => EveSolarSystem::getBySolarSystemId($kill['solarSystemID'])->Region['itemName'],
        );
        // killtime conversion to Mongo
        $killdata['killTime'] = new \MongoDate(strtotime($kill['killTime']));

        // victim conversions / additional data
        $killdata['victim']['characterID'] = (int)$this->ensureEveEntityID(
            $kill['victim']['characterID'],
            $kill['victim']['characterName']
        );
        $killdata['victim']['corporationID'] = (int)$this->ensureEveEntityID(
            $kill['victim']['corporationID'],
            $kill['victim']['corporationName']
        );
        $killdata['victim']['allianceID'] = (int)$kill['victim']['allianceID'];
        $killdata['victim']['factionID'] = (int)$kill['victim']['factionID'];
        $killdata['victim']['shipTpyeID'] = (int)$kill['victim']['shipTypeID'];
        $killdata['victim']['shipType'] = EveItem::getByItemId($kill['victim']['shipTypeID'])->typeName;
        $killdata['victim']['iskValue'] = EveITem::getItemValue($kill['victim']['shipTypeID']);

        //  add victim to involveds
        $involvedCharacters = array((int)$killdata["victim"]["characterID"]);
        $involvedCorporations = array((int)$killdata["victim"]["corporationID"]);

        if ($killdata['victim']['allianceID'] > 0) {
            $involvedAlliances = array((int)$killdata["victim"]["allianceID"]);
        } else {
            $involvedAlliances = array();
        }

        if ($killdata['victim']['factionID'] > 0) {
            $involvedFactions = array((int)$killdata["victim"]["factionID"]);
        } else {
            $involvedFactions = array();
        }

        // Karbos ISK Value
        $totalISKValue = EveItem::getItemValue($kill['victim']['shipTypeID']);

        foreach ($kill['attackers'] as $id => $attacker) {
            $killdata['attackers'][$id]['characterID'] = (int)$this->ensureEveEntityId(
                $attacker['characterID'],
                $attacker['characterName']
            );
            $killdata['attackers'][$id]['corporationID'] = (int)$this->ensureEveEntityID(
                $attacker['corporationID'],
                $attacker['corporationName']
            );
            $killdata['attackers'][$id]['allianceID'] = (int)$attacker['allianceID'];
            $killdata['attackers'][$id]['factionID'] = (int)$attacker['factionID'];
            $killdata['attackers'][$id]['weaponTypeID'] = (int)$attacker['weaponTypeID'];
            $killdata['attackers'][$id]['weaponType'] = EveItem::getByItemId($attacker['weaponTypeID'])->typeName;
            $killdata['attackers'][$id]['shipTypeID'] = (int)$attacker['shipTypeID'];
            $killdata['attackers'][$id]['shipType'] = EveItem::getByItemId($attacker['shipTypeID'])->typeName;

            // add involveds
            if (!in_array($attacker['characterID'], $involvedCharacters) && $attacker['characterID'] > 0) {
                $involvedCharacters[] = (int)$attacker['characterID'];
            }
            if (!in_array($attacker['corporationID'], $involvedCorporations) && $attacker['corporationID'] > 0) {
                $involvedCorporations[] = (int)$attacker['corporationID'];
            }
            if (!in_array($attacker['allianceID'], $involvedAlliances) && $attacker['allianceID'] > 0) {
                $involvedAlliances[] = (int)$attacker['allianceID'];
            }
            if (!in_array($attacker['factionID'], $involvedFactions) && $attacker['factionID'] > 0) {
                $involvedFactions[] = (int)$attacker['factionID'];
            }
        }

        if (@!is_null($kill['items'])) {
            foreach ($kill['items'] as $id => $item) {
                $item = $this->parseItem($item);
                $killdata['items'][$id] = $item;
                $totalISKValue += $item["iskValue"];
                $qtyDropped += $item["qtyDropped"];
                $valueDropped += $item["iskValue"];
                $qtyDestroyed += $item["qtyDestroyed"];
                $valueDestroyed += $item["iskValue"];
            }
        }

        $killdata["involvedCorporations"] = $involvedCorporations;
        $killdata["involvedAlliances"] = $involvedAlliances;
        $killdata["involvedCharacters"] = $involvedCharacters;
        $killdata["involvedFactions"] = $involvedFactions;

        $killdata['totalISKValue'] = $totalISKValue;
        $killdata['totalDropped'] = $qtyDropped;
        $killdata['totalDestroyed'] = $qtyDestroyed;
        $killdata['totalISKDropped'] = $valueDropped;
        $killdata['totalISKDestroyed'] = $valueDestroyed;

        if (is_null(Kill::getByKillId($killdata['killID']))) {
            $killObject = new Kill();
            $killObject->injectDataFromMail($killdata);
            $killObject->save();
            return true;
        } else {
            return false;
        }
    }

    /**
     * ensure there is an ID placed
     * @param string $id
     * @param string $charname
     * @return int
     * @throws \Exception
     */
    public function ensureEveEntityID($id, $charname)
    {
        $id = (int)$id;
        if ($id == 0) {
            if ($id = \Kingboard\Model\MapReduce\NameSearch::getEveIdByName($charname)) {
                return $id;
            }
            if (!empty($charname)) {
                $pheal = new Pheal();
                $result = $pheal->eveScope->typeName(array('names' => $charname))->toArray();
                if ((int)$result[0]['characterID'] > 0) {
                    return (int)$result[0]['characterID'];
                }
            }
            // with an empty charname we have to assume its 0
            return 0;
            //throw new \Exception("No such characterID");
        }
        return $id;
    }

    private function parseItem($row)
    {
        // Build the standard item
        $item = $row;
        $item['typeName'] = EveItem::getByItemId($row['typeID'])->typeName;
        $item['iskValue'] = EveItem::getItemValue($row['typeID']);

        // Check for nested items (container)
        if (isset($row['items']) && is_null($row['items'])) {
            foreach ($row['items'] as $id => $innerRow) {
                $item['items'][$id] = $this->parseItem($innerRow);
            }
        }
        return $item;
    }
}
