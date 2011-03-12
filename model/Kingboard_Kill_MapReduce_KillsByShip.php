<?php
/**
 * creates / allows access to stats about which shiptype has been killed how often
 */
class Kingboard_Kill_MapReduce_KillsByShip extends King23_MongoObject implements ArrayAccess
{
    protected $_className = "Kingboard_Kill_MapReduce_KillsByShip";


    /**
     * run the map/reduce
     * @static
     * @return void
     */
    public static function mapReduce()
    {
        $map = "function () {
            emit(this.victim.shipType, 1);
        }";
        $reduce = "function (k, vals) {
            var sum = 0;
            for (var i in vals) {
                sum += vals[i];
            }
            return sum;
        }";
        King23_Mongo::mapReduce("Kingboard_Kill", __CLASS__, $map, $reduce);
    }


    /**
     * find all ships and their value
     * @static
     * @return King23_MongoResult
     */
    public static function find()
    {
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
