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

    public static function find()
    {
        return self::_find(__class__, array());
    }

    public static function count()
    {
        return self::_find(__class__, array())->count();
    }

    public static function mrShipsLost()
    {
        $map = new MongoCode("function () {
            emit(this.victim.shipType, 1);
        }");


        $reduce = new MongoCode("function (k, vals) {
            var sum = 0;
            for (var i in vals) {
                sum += vals[i];
            }
            return sum;
        }");

        
    }
}
