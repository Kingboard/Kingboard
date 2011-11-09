<?php
class Kingboard_ApiKillParser
{
    public function parseKills($kills)
    {
        $oldkills =0;
        $newkills = 0;
        $errors = 0;
        $lastID = 0;
        $lastIntID = 0;
        foreach($kills as $kill)
        {
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
                        "solarSystem" => Kingboard_EveSolarSystem::getBySolarSystemId($kill->solarSystemID)->itemName,
                        "security" => Kingboard_EveSolarSystem::getBySolarSystemId($kill->solarSystemID)->security,
                        "region" => Kingboard_EveSolarSystem::getBySolarSystemId($kill->solarSystemID)->Region['itemName'],
                    ),
                    "killTime" => new MongoDate(strtotime($kill->killTime)),
                    "moonID" => $kill->moonID,
                    "victim" => array(
                        "characterID" => $this->ensureCharacterID($kill->victim->characterID, $kill->victim->characterName),
                        "characterName" => $kill->victim->characterName,
                        "corporationID" => $this->ensureCorporationID($kill->victim->corporationID, $kill->victim->corporationName),
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
                        "characterID" => $attacker->characterID,
                        "characterName" => $attacker->characterName,
                        "entityType" => Kingboard_Helper_EntityType::getEntityTypeByEntityId((int) $attacker->characterID),
                        "corporationID" => $this->ensureCorporationID($attacker->corporationID, $attacker->corporationName),
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

                if(@!is_null($kill->items))
                {
                    foreach($kill->items as $item)
                    {
                        $killdata['items'][] = $this->ParseItem($item);
                    }
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
            } catch (Kingboard_KillmailParser_KillmailErrorException $e)
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
            "typeName" => @Kingboard_EveItem::getByItemId($row->typeID)->typeName,
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

    public function ensureCharacterID($id, $charname)
    {
        $id = (int) $id;
        if($id == 0)
        {
            $idfinder = new Kingboard_KillmailParser_IdFinder();
            return $idfinder->getCharacterId($charname);
        }
        return $id;
    }

    public function ensureCorporationID($id, $corpname)
    {
        $id = (int) $id;
        if($id == 0)
        {
            $idfinder = new Kingboard_KillmailParser_IdFinder();
            return $idfinder->getCorporationId($corpname);
        }
        return $id;
    }
}