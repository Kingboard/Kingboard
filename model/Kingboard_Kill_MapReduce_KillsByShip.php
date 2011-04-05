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
            var ship = db.Kingboard_EveItem.findOne({typeID: parseInt(this.victim.shipTypeID)});
            var info = {}
            info[this.victim.shipType] = 1;
            if(ship != null && ship.marketGroup != null)
                emit(ship.marketGroup.parentGroup.marketGroupName, info);
        }";
        $reduce = "function (k, vals) {
            var sums = {}
            var total = 0;
            vals.forEach(function(info) {
                info[\"total\"] = 0;
                for (var key in info)
                {
                    if(sums[key] === undefined)
                        sums[key] = 0;
                    sums[key] += info[key];
                    total += info[key];
                }
            });
            sums[\"total\"] = total;
            return sums;
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
