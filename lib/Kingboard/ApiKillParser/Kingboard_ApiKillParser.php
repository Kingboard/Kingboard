<?php
class Kingboard_ApiKillParser
{
    public function parseKills($kills)
    {
        $oldkills =0;
        $newkills = 0;
        $lastID = 0;
        foreach($kills as $kill)
        {
            // this needs to be run before exit of loop, otherwise having all kills of this run
            // will cause the lastID not being updated
            if(!is_null($kill->killID) && $kill->killID > 0)
                $lastID=$kill->killID;

            if(!is_null(Kingboard_Kill::getByKillId($kill->killID)))
            {
                $oldkills++;
                continue;
            }

            $killdata = array(
                "killID" => $kill->killID,
                "solarSystemID" => $kill->solarSystemID,
                "location" => array(
                    "solarSystem" => Kingboard_EveSolarSystem::getBySolarSystemId($kill->solarSystemID)->itemName,
                    "security" => Kingboard_EveSolarSystem::getBySolarSystemId($kill->solarSystemID)->security,
                    "region" => Kingboard_EveSolarSystem::getBySolarSystemId($kill->solarSystemID)->Region['itemName'],
                ),
                "killTime" => new MongoDate(strtotime($kill->killTime)),
                "moonID" => $kill->moonID,
                "victim" => array(
                    "characterID" => (int) $kill->victim->characterID,
                    "characterName" => $kill->victim->characterName,
                    "corporationID" => (int) $kill->victim->corporationID,
                    "corporationName" => $kill->victim->corporationName,
                    "allianceID" => (int) $kill->victim->allianceID,
                    "allianceName" => $kill->victim->allianceName,
                    "factionID" => (int) $kill->victim->factionID,
                    "factionName" => $kill->victim->factionName,
                    "damageTaken" => $kill->victim->damageTaken,
                    "shipTypeID"  => (int)$kill->victim->shipTypeID,
                    "shipType"  => Kingboard_EveItem::getByItemId($kill->victim->shipTypeID)->typeName
                )
            );
            $killdata['attackers'] = array();
            foreach($kill->attackers as $attacker)
            {
                $killdata['attackers'][] = array(
                    "characterID" => (int) $attacker->characterID,
                    "characterName" => $attacker->characterName,
                    "entityType" => Kingboard_Helper_EntityType::getEntityTypeByEntityId((int) $attacker->characterID),
                    "corporationID" => (int) $attacker->corporationID,
                    "corporationName" => $attacker->corporationName,
                    "allianceID" => (int) $attacker->allianceID,
                    "allianceName" => $attacker->allianceName,
                    "factionID" => (int) $attacker->factionID,
                    "factionName" => $attacker->factionName,
                    "securityStatus" => $attacker->securityStatus,
                    "damageDone" => $attacker->damageDone,
                    "finalBlow"  => $attacker->finalBlow,
                    "weaponTypeID" => (int) $attacker->weaponTypeID,
                    "weaponType" => Kingboard_EveItem::getByItemId($attacker->weaponTypeID)->typeName,
                    "shipTypeID" => (int) $attacker->shipTypeID,
                    "shipType"  => Kingboard_EveItem::getByItemId($attacker->shipTypeID)->typeName
                );
            }
            $killdata['items'] = array();

            foreach($kill->items as $item)
            {
                $killdata['items'][] = $this->ParseItem($item);
            }

            $hash = Kingboard_KillmailHash_IdHash::getByData($killdata);
            $killdata['idHash'] = (String) $hash;

            if(is_null(Kingboard_Kill::getInstanceByIdHash($killdata['idHash'])))
            {
                $killObject = new Kingboard_Kill();
                $killObject->injectDataFromMail($killdata);
                $killObject->save();
                $newkills++;
            } else {
                $oldkills++;
            }
        }
        return array('oldkills' => $oldkills, 'newkills' => $newkills, 'lastID' => $lastID);
    }


    private function ParseItem($row)
    {
        // Build the standard item
        $item = array(
            "typeID" => $row->typeID,
            "typeName" => Kingboard_EveItem::getByItemId($row->typeID)->typeName,
            "flag" => $row->flag,
            "qtyDropped" => $row->qtyDropped,
            "qtyDestroyed" => $row->qtyDestroyed
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
}