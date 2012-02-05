    <?php
/**
 * creates / allows access to stats about which shiptype has been killed how often
 */
class Kingboard_Kill_MapReduce_KillsByShipByAlliance extends King23_MongoObject implements ArrayAccess
{
    protected $_className = "Kingboard_Kill_MapReduce_KillsByShipByAlliance";

    public static function getInstanceByAllianceId($allianceid)
    {
        return self::_getInstanceByCriteria(__CLASS__, array("_id" => (int) $allianceid));
    }

    public static function mapReduce()
    {
        $map = "function () {
            var ship = db.Kingboard_EveItem.findOne({typeID: parseInt(this.victim.shipTypeID)},{'marketGroup.parentGroup.marketGroupName':1});
            if(ship != null && ship.marketGroup != null) {
                var info = {};
                info['group'] = {};
                info['ship'] = {}
                info['group'][ship.marketGroup[0].parentGroup[0].marketGroupName] = 1;
                info['ship'][this.victim.shipType] = 1;
                info['total'] = 1;
                var done = {};
                this.attackers.forEach(function(attacker) {
                    if(done[attacker.allianceID] === undefined)
                        emit(attacker.allianceID, info);
                    done[attacker.allianceID] = true;
                });
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

        $tr = Kingboard_Task_Run::findByTaskType(__CLASS__);
        if(is_null($tr))
        {
            $tr = new Kingboard_Task_Run();
            $tr->type = __CLASS__;
            $tr->lastrun = new MongoDate(0);
        }
        $last = $tr->lastrun;
        $tr->save();
        $new = $tr->lastrun;

        $filter = array("saved" => array('$gt' => $last, '$lte' => $new));
        $out = array("reduce" => __CLASS__);
        return King23_Mongo::mapReduce("Kingboard_Kill", $out, $map, $reduce, $filter);
    }

}
