<?php
namespace Kingboard\Lib\Parser;
class EveAPI
{
    public function parseKills($kills)
    {
        $oldkills = 0;
        $newkills = 0;
        $errors = 0;
        $lastID = 0;
        $lastIntID = 0;
        foreach($kills as $kill)
        {
            $qtyDropped = 0;
            $qtyDestroyed = 0;
            $valueDropped = 0;
            $valueDestroyed = 0;
            try {
                // this needs to be run before exit of loop, otherwise having all kills of this run
                // will cause the lastID not being updated
                if(!is_null($kill->killID) && $kill->killID > 0)
                    $lastID=$kill->killID;

                if(!is_null(@$kill->killInternalID) && @$kill->killInternalID > 0)
                    $lastIntID = $kill->killInternalID;

                /*if(!is_null(Kingboard_Kill::getByKillId($kill->killID)))
                {
                    $oldkills++;
                    continue;
                }*/
                $killdata = array(
                    "killID" => $kill->killID,
                    "solarSystemID" => $kill->solarSystemID,
                    "location" => array(
                        "solarSystem" => \Kingboard\Model\EveSolarSystem::getBySolarSystemId($kill->solarSystemID)->itemName,
                        "security" => \Kingboard\Model\EveSolarSystem::getBySolarSystemId($kill->solarSystemID)->security,
                        "region" => \Kingboard\Model\EveSolarSystem::getBySolarSystemId($kill->solarSystemID)->Region['itemName'],
                    ),
                    "killTime" => new \MongoDate(strtotime($kill->killTime)),
                    "moonID" => $kill->moonID,
                    "victim" => array(
                        "characterID" => (int) $this->ensureEveEntityID($kill->victim->characterID, $kill->victim->characterName),
                        "characterName" => $kill->victim->characterName,
                        "corporationID" => (int) $this->ensureEveEntityID($kill->victim->corporationID, $kill->victim->corporationName),
                        "corporationName" => $kill->victim->corporationName,
                        "allianceID" => (int) $kill->victim->allianceID,
                        "allianceName" => $kill->victim->allianceName,
                        "factionID" => (int) $kill->victim->factionID,
                        "factionName" => $kill->victim->factionName,
                        "damageTaken" => $kill->victim->damageTaken,
                        "shipTypeID"  => (int)$kill->victim->shipTypeID,
                        "shipType"  => \Kingboard\Model\EveItem::getByItemId($kill->victim->shipTypeID)->typeName,
                        "iskValue" => \Kingboard\Model\EveItem::getItemValue($kill->victim->shipTypeID)
                    )
                );
                $totalISKValue = \Kingboard\Model\EveItem::getItemValue($kill->victim->shipTypeID);
                $killdata['attackers'] = array();
                foreach($kill->attackers as $attacker)
                {
                    $killdata['attackers'][] = array(
                        "characterID" => (int)$attacker->characterID,
                        "characterName" => $attacker->characterName,
                        "corporationID" => (int) $this->ensureEveEntityID($attacker->corporationID, $attacker->corporationName),
                        "corporationName" => $attacker->corporationName,
                        "allianceID" => (int) $attacker->allianceID,
                        "allianceName" => $attacker->allianceName,
                        "factionID" => (int) $attacker->factionID,
                        "factionName" => $attacker->factionName,
                        "securityStatus" => $attacker->securityStatus,
                        "damageDone" => $attacker->damageDone,
                        "finalBlow"  => $attacker->finalBlow,
                        "weaponTypeID" => (int) $attacker->weaponTypeID,
                        "weaponType" => \Kingboard\Model\EveItem::getByItemId($attacker->weaponTypeID)->typeName,
                        "shipTypeID" => (int) $attacker->shipTypeID,
                        "shipType"  => \Kingboard\Model\EveItem::getByItemId($attacker->shipTypeID)->typeName
                    );
                }
                $killdata['items'] = array();

                if(@!is_null($kill->items))
                {
                    foreach($kill->items as $item)
                    {
                        $item = $this->ParseItem($item);
                        $killdata['items'][] = $item;
                        $totalISKValue += $item["iskValue"];
                        $qtyDropped += $item["qtyDropped"];
                        $valueDropped += $item["iskValue"];
                        $qtyDestroyed += $item["qtyDestroyed"];
                        $valueDestroyed += $item["iskValue"];
                    }
                }
                
                $hash = \Kingboard\Lib\IdHash::getByData($killdata);
                $killdata['idHash'] = $hash->generateHash();
                $killdata['totalISKValue'] = $totalISKValue;
                $killdata['totalDropped'] = $qtyDropped;
                $killdata['totalDestroyed'] = $qtyDestroyed;
                $killdata['totalISKDropped'] = $valueDropped;
                $killdata['totalISKDestroyed'] = $valueDestroyed;

                if(is_null(\Kingboard\Model\Kill::getInstanceByIdHash($killdata['idHash'])))
                {
                    // if stomp queue read is set we assume that all saves are done through queue and don't save here
                    if(is_null(\King23\Core\Registry::getInstance()->stomp) || !\King23\Core\Registry::getInstance()->stomp['read'])
                    {
                        $killObject = new \Kingboard\Model\Kill();
                        $killObject->injectDataFromMail($killdata);
                        $killObject->save();
                    }
                    $newkills++;
                } else {
                    $oldkills++;
                }
                // we stomp all kills, so boards that dont have it yet can get it from there
                if(!is_null(\King23\Core\Registry::getInstance()->stomp) && \King23\Core\Registry::getInstance()->stomp['post'])
                    \Kingboard\Lib\Stomp\KillPublisher::send($killdata);

            } catch (\Exception $e)
            {
                $errors++;
            }
        }
        return array('oldkills' => $oldkills, 'newkills' => $newkills, 'lastID' => $lastID, 'lastIntID' => $lastIntID, 'errors' => $errors);
    }


    private function ParseItem($row)
    {
        // Build the standard item
        $item = array(
            "typeID" => $row->typeID,
            "typeName" => \Kingboard\Model\EveItem::getByItemId($row->typeID)->typeName,
            "flag" => $row->flag,
            "qtyDropped" => $row->qtyDropped,
            "qtyDestroyed" => $row->qtyDestroyed,
            "iskValue" => \Kingboard\Model\EveItem::getItemValue($row->typeID)
        );

        // Check for nested items (container)
        if (isset($row['items']))
        {
            $item['items'] = array();
            foreach($row['items'] as $innerRow)
            {
                $item['items'][] = $this->ParseItem($innerRow);
            }
        }
        return $item;
    }

    public function ensureEveEntityID($id, $charname)
    {
        $id = (int) $id;
        if($id == 0)
        {
            if($id = \Kingboard\Model\MapReduce\NameSearch::getEveIdByName($charname))
                return $id;

            $pheal = new \Pheal();
            $result = $pheal->eveScope->typeName(array('names' => $charname))->toArray();
            if ((int) $result[0]['characterID'] > 0)
                return (int) $result[0]['characterID'];

            throw new \Exception("No such characterID");
        }
        return $id;
    }
}