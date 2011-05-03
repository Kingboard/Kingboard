<?php
class Kingboard_Kill extends King23_MongoObject implements ArrayAccess
{
    protected $_className = __class__;
    
    public static function getByKillId($killid)
    {
        return self::_getInstanceByCriteria(__class__, array("killID" => $killid));    
    }

    public static function getInstanceByIdHash($hash)
    {
        return self::_getInstanceByCriteria(__class__, array("idHash" => $hash));
    }

    public function injectDataFromMail(array $data)
    {
        if(is_null($this->_data)) $this->_data = array(); 
        $this->_data = array_merge($data, $this->_data);
    }

    public static function find($search = array())
    {
        return self::_find(__class__, $search);
    }

    public static function findOne($criteria)
    {
        return self::_findOne(__CLASS__, $criteria);
    }

    /**
     * Fetches Information about the pilot identified by characterID
     * @static
     * @param String $id
     * @return array|null
     */
    public static function getPilotInfoFromId($id)
    {
        // find latest kill with characterID $id
        $kill = self::_find(__CLASS__, array('$or' => array(
                          array('victim.characterID' => (int) $id),
                          array('attackers.characterID' => (int) $id)
        )))->sort(array('killTime' => -1));

        $kill->next();

        if(!($kill = $kill->current()))
            return null;

        if($kill['victim']['characterID'] == $id)
        {
            return array(
                "characterID" => $kill['victim']['characterID'],
                "characterName" => $kill['victim']['characterName'],
                "corporationID" => $kill['victim']['corporationID'],
                "corporationName" => $kill['victim']['corporationName'],
                "allianceID" => $kill['victim']['allianceID'],
                "allianceName" => $kill['victim']['allianceName'],
                "factionID" => $kill['victim']['factionID'],
                "factionName" => $kill['victim']['factionID']
            );
        }
        foreach($kill['attackers'] as $attacker)
        {
            if($attacker['characterID'] == $id)
            {
                return array(
                    "characterID" => $attacker['characterID'],
                    "characterName" => $attacker['characterName'],
                    "corporationID" => $attacker['corporationID'],
                    "corporationName" => $attacker['corporationName'],
                    "allianceID" => $attacker['allianceID'],
                    "allianceName" => $attacker['allianceName'],
                    "factionID" => $attacker['factionID'],
                    "factionName" => $attacker['factionID']
                );
            }
        }
        return null;
    }

    public static function getPilotNameFromId($id)
    {
        // find a random kill with characterID $id
        $kill = self::_findOne(__CLASS__, array('$or' => array(
                          array('victim.characterID' => (int) $id),
                          array('attackers.characterID' => (int) $id)
        )));

        if($kill['victim']['characterID'] == $id)
            return $kill['victim']['characterName'];
        else
        {
            foreach($kill['attackers'] as $attacker)
            {
                if($attacker['characterID'] == $id)
                    return $attacker['characterName'];
            }
        }
        return false;
    }

    public static function count()
    {
        return self::_find(__class__, array())->count();
    }
}
