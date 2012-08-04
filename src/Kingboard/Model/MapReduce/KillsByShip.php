<?php
namespace Kingboard\Model\MapReduce;
/**
 * creates / allows access to stats about which shiptype has been killed how often
 */
class KillsByShip extends \King23\Mongo\MongoObject implements \ArrayAccess
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
            if(arguments.callee.shipcache === undefined)
            {
                arguments.callee.shipcache = {}
            }
            if(arguments.callee.shipcache[this.victim.shipTypeID] === undefined)
            {
                arguments.callee.shipcache[this.victim.shipTypeID] = db.Kingboard_EveItem.findOne({typeID: parseInt(this.victim.shipTypeID)},{'marketGroup.parentGroup.marketGroupName':1});
            }
            var ship = arguments.callee.shipcache[this.victim.shipTypeID];
            var info = {}
            info[this.victim.shipType] = 1;
            info[\"total\"] = 1;
            if(ship != null && ship.marketGroup != null)
                emit(ship.marketGroup[0].parentGroup[0].marketGroupName, info);
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

        $tr = \Kingboard\Model\TaskRun::findByTaskType(__CLASS__);
        if(is_null($tr))
        {
            $tr = new \Kingboard\Model\TaskRun();
            $tr->type = __CLASS__;
            $tr->lastrun = new \MongoDate(0);
        }
        $last = $tr->lastrun;
        $tr->save();
        $new = $tr->lastrun;

        $filter = array("saved" => array('$gt' => $last, '$lte' => $new));
        $obj = new self();
        $out = array("reduce" => $obj->_className);
        \King23\Mongo\Mongo::mapReduce("Kingboard_Kill", $out, $map, $reduce, $filter);
    }


    /**
     * find all ships and their value
     * @static
     * @return \King23\Mongo\MongoResult
     */
    public static function find()
    {
        return self::_find(__CLASS__, array());
    }

    /**
     * counts the amount of m/r results
     * @static
     * @return int
     */
    public static function count()
    {
        return self::_find(__CLASS__, array())->count();
    }

}
