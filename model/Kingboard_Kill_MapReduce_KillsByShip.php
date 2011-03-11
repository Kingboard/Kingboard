<?php
class Kingboard_Kill_MapReduce_KillsByShip extends King23_MongoObject implements ArrayAccess
{
    protected $_className = "Kingboard_Kill_MapReduce_KillsByShip";

    public static function find()
    {
        if(!($mongo = King23_Registry::getInstance()->mongo))
            throw new King23_MongoException('mongodb is not configured');

        // emit shipType and 1
        $map = new MongoCode("function () {
            emit(this.victim.shipType, 1);
        }");

        // reduce to shiptype, sum of losses
        $reduce = new MongoCode("function (k, vals) {
            var sum = 0;
            for (var i in vals) {
                sum += vals[i];
            }
            return sum;
        }");

        // out defines that it is stored in a collection named
        // like this class, this allows to access the m/r result
        // as if it was a regular collection (m/r could be asynchronous executed
        // by other tasks
        $mongo['db']->command(array(
            "mapreduce" => "Kingboard_Kill",
            "map" => $map,
            "reduce" => $reduce,
            "out" => __CLASS__
        ));

        return self::_find(__class__, array());
    }

    /**
     * counts the amount of m/r results
     * @static
     * @return int
     */
    public static function count()
    {
        return self::_find(__class__, array())->count();
    }

}
