<?php
class Kingboard_Kill extends King23_MongoObject implements ArrayAccess
{
    protected $_className = __class__;
    
    public static function getByKillId($killid)
    {
        return self::_getInstanceByCriteria(__class__, array("killID" => $killid));    
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

    public static function getPilotNameFromId($id)
    {
        // find a random kill with characterID $id
        $kill = self::_findOne(__CLASS__, array('$or' => array(
                          array('victim.characterID' => $id),
                          array('attackers.characterID' => $id)
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
