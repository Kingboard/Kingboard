<?php
namespace Kingboard\Model;

class Kill extends \King23\Mongo\MongoObject implements \ArrayAccess
{
    protected $_className = "Kingboard_Kill";

    public static function getByKillId($killid)
    {
        return self::_getInstanceByCriteria(__CLASS__, array("killID" => (int) $killid));
    }

    public function injectDataFromMail(array $data)
    {
        if(is_null($this->_data)) $this->_data = array();
        $this->_data = array_merge($data, $this->_data);
    }

    public static function find($criteria = array(), $fields = array())
    {
        return self::_find(__CLASS__, $criteria, $fields);
    }

    public static function findOne($criteria)
    {
        return self::_findOne(__CLASS__, $criteria);
    }

    public function save()
    {
        if(!$this->_data['killTime'] instanceof \MongoDate)
        {
            if(is_array($this->_data['killTime']))
                $this->_data['killTime'] = new \MongoDate($this->_data['killTime']['sec'], $this->_data['killTime']['usec']);
        }
        $this->_data['saved'] = new \MongoDate();
        parent::save();
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
        )))->sort(array('killTime' => -1))->limit(1);

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
                "factionName" => $kill['victim']['factionName']
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
                    "factionName" => $attacker['factionName']
                );
            }
        }
        return null;
    }

    /**
     * Fetches Information about the corporation identified by characterID
     * @static
     * @param String $id
     * @return array|null
     */
    public static function getCorporationInfoFromId($id)
    {
        // find latest kill with characterID $id
        $kill = self::_find(__CLASS__, array('$or' => array(
                          array('victim.corporationID' => (int) $id),
                          array('attackers.corporationID' => (int) $id)
        )))->sort(array('killTime' => -1))->limit(1);

        $kill->next();

        if(!($kill = $kill->current()))
            return null;

        if($kill['victim']['corporationID'] == $id)
        {
            return array(
                "corporationID" => $kill['victim']['corporationID'],
                "corporationName" => $kill['victim']['corporationName'],
                "allianceID" => $kill['victim']['allianceID'],
                "allianceName" => $kill['victim']['allianceName'],
                "factionID" => $kill['victim']['factionID'],
                "factionName" => $kill['victim']['factionName']
            );
        }
        foreach($kill['attackers'] as $attacker)
        {
            if($attacker['corporationID'] == $id)
            {
                return array(
                    "corporationID" => $attacker['corporationID'],
                    "corporationName" => $attacker['corporationName'],
                    "allianceID" => $attacker['allianceID'],
                    "allianceName" => $attacker['allianceName'],
                    "factionID" => $attacker['factionID'],
                    "factionName" => $attacker['factionName']
                );
            }
        }
        return null;
    }

    /**
     * Fetches Information about the faction identified by id
     * @static
     * @param String $id
     * @return array|null
     */
    public static function getFactionInfoFromId($id)
    {
        // find latest kill with characterID $id
        $kill = self::_find(__CLASS__, array('$or' => array(
                          array('victim.factionID' => (int) $id),
                          array('attackers.factionID' => (int) $id)
        )))->sort(array('killTime' => -1))->limit(1);

        $kill->next();

        if(!($kill = $kill->current()))
            return null;

        if($kill['victim']['factionID'] == $id)
        {
            return array(
                "factionID" => $kill['victim']['factionID'],
                "factionName" => $kill['victim']['factionName'],
            );
        }
        foreach($kill['attackers'] as $attacker)
        {
            if($attacker['factionID'] == $id)
            {
                return array(
                    "factionID" => $attacker['factionID'],
                    "factionName" => $attacker['factionName'],
                );
            }
        }
        return null;
    }

    /**
     * Fetches Information about the alliance identified by id
     * @static
     * @param String $id
     * @return array|null
     */
    public static function getAllianceInfoFromId($id)
    {
        // find latest kill with characterID $id
        $kill = self::_find(__CLASS__, array('$or' => array(
                          array('victim.allianceID' => (int) $id),
                          array('attackers.allianceID' => (int) $id)
        )))->sort(array('killTime' => -1))->limit(1);

        $kill->next();

        if(!($kill = $kill->current()))
            return null;

        if($kill['victim']['allianceID'] == $id)
        {
            return array(
                "allianceID" => $kill['victim']['allianceID'],
                "allianceName" => $kill['victim']['allianceName'],
                "factionID" => $kill['victim']['factionID'],
                "factionName" => $kill['victim']['factionName']
            );
        }
        foreach($kill['attackers'] as $attacker)
        {
            if($attacker['allianceID'] == $id)
            {
                return array(
                    "allianceID" => $attacker['allianceID'],
                    "allianceName" => $attacker['allianceName'],
                    "factionID" => $attacker['factionID'],
                    "factionName" => $attacker['factionName']
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

    public static function getPilotIdFromName($name)
    {
        foreach(\Kingboard\Model\MapReduce\NameSearch::search($name, 1) as $result)
        {
            $id = $result->value['id'];
        }
        if($id > 0)
        {
            return $id;
        }
        else
        {
            // find a random kill with characterID $id
            $kill = self::_findOne(__CLASS__, array('$or' => array(
                array('victim.characterName' =>  $name),
                array('attackers.characterName' => $name)
            )), array(
                'victim.characterName' => 1,
                'attackers.characterName' => 1,
                'victim.characterID' => 1,
                'attackers.characterID' => 1
            ));

            if($kill['victim']['characterName'] == $name)
                return $kill['victim']['characterID'];
            else
            {
                foreach($kill['attackers'] as $attacker)
                {
                    if($attacker['characterName'] == $name)
                        return $attacker['characterID'];
                }
            }
            return false;
        }
    }

    public static function getCorporationIdFromName($name)
    {
        foreach(\Kingboard\Model\MapReduce\NameSearch::search($name, 1) as $result)
        {
            $id = $result->value['id'];
        }
        if($id > 0)
        {
            return $id;
        }
        else
        {
            // find a random kill with characterID $id
            $kill = self::_findOne(__CLASS__, array('$or' => array(
                array('victim.corporationName' =>  $name),
                array('attackers.corporationName' => $name)
            )), array(
                'victim.corporationName' => 1,
                'attackers.corporationName' => 1,
                'victim.corporationID' => 1,
                'attackers.corporationID' => 1
            ));

            if($kill['victim']['corporationName'] == $name)
                return $kill['victim']['corporationID'];
            else
            {
                foreach($kill['attackers'] as $attacker)
                {
                    if($attacker['corporationName'] == $name)
                        return $attacker['corporationID'];
                }
            }
            return false;
        }
    }

    public static function getFactionIdFromName($name)
    {
        foreach(\Kingboard\Model\MapReduce\NameSearch::search($name, 1) as $result)
        {
            $id = $result->value['id'];
        }
        if($id > 0)
        {
            return $id;
        }
        else
        {
            // find a random kill with characterID $id
            $kill = self::_findOne(__CLASS__, array('$or' => array(
                array('victim.factionName' =>  $name),
                array('attackers.factionName' => $name)
            )), array(
                'victim.factionName' => 1,
                'attackers.factionName' => 1,
                'victim.factionID' => 1,
                'attackers.factionID' => 1
            ));

            if($kill['victim']['factionName'] == $name)
                return $kill['victim']['factionID'];
            else
            {
                foreach($kill['attackers'] as $attacker)
                {
                    if($attacker['factionName'] == $name)
                        return $attacker['factionID'];
                }
            }
            return false;
        }
    }
    public static function count()
    {
        return self::_find(__CLASS__, array())->count();
    }


    public function toArray()
    {
        return $this->_data;
    }
}
