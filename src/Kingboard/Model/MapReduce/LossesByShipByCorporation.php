<?php
namespace Kingboard\Model\MapReduce;

/**
 * creates / allows access to stats about which shiptype has been killed how often
 */
class LossesByShipByCorporation extends \King23\Mongo\MongoObject implements \ArrayAccess
{
    protected $_className = "Kingboard_Kill_MapReduce_LossesByShipByCorporation";

    public static function getInstanceByCorporationId($corpid)
    {
        return self::_getInstanceByCriteria(__CLASS__, array("_id" => (int)$corpid));
    }

    public static function mapReduce()
    {
        $map = "function () {
            var ship = db.Kingboard_EveItem.findOne({typeID: parseInt(this.victim.shipTypeID)},{'marketGroup.parentGroup.marketGroupName':1});
            if(ship != null && ship.marketGroup != null) {
                var info = {};
                info['group'] = {};
                info['ship'] = {}
                info['group'][ship.marketGroup.parentGroup.marketGroupName] = 1;
                info['ship'][this.victim.shipType] = 1;
                info['total'] = 1;
                emit(this.victim.corporationID, info);
            }
        }";
        $reduce = "function (k, vals) {
            var sums = {}
            sums['group'] = {}
            sums['ship'] = {}
            sums['total'] = 0;
            var total = 0;
            vals.forEach(function(info) {
                sums['total'] += info['total'];

                for (var key in info['group'])
                {
                    if(sums['group'][key] === undefined)
                        sums['group'][key] = 0;
                    sums['group'][key] += info['group'][key];
                }

                for (var key in info['ship'])
                {
                    if(sums['ship'][key] === undefined)
                        sums['ship'][key] = 0;
                    sums['ship'][key] += info['ship'][key];
                }

            });
            return sums;
        }";

        $tr = \Kingboard\Model\TaskRun::findByTaskType(__CLASS__);
        if (is_null($tr)) {
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
        return \King23\Mongo\Mongo::mapReduce("Kingboard_Kill", $out, $map, $reduce, $filter);
    }

}
