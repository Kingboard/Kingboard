<?php
namespace Kingboard\Model\MapReduce;

use ArrayAccess;
use King23\Mongo\Mongo;
use King23\Mongo\MongoObject;
use King23\Mongo\MongoResult;
use Kingboard\Model\TaskRun;
use MongoDate;

/**
 * creates / allows access to stats about which shiptype has been killed how often
 */
class KillsByDayByEntity extends MongoObject implements ArrayAccess
{
    protected $_className = "Kingboard_Kill_MapReduce_KillsByDayByEntitys";


    /**
     * run the map/reduce
     * @static
     * @return void
     */
    public static function mapReduce()
    {
        $map = "function () {
            var info = {}

            // emitted is 1 kill each run
            info[\"total\"] = 1;

            // create daily id
            date = this.killTime;
            date.setUTCHours(0);
            date.setUTCMinutes(0);
            date.setUTCSeconds(0);
            datestring = Date.parse(date) / 1000;

            // collect the date needed for the most valuable each day
            info['topValue'] = {
                victim: this.victim,
                totalISKValue: this.totalISKValue
            }

            // emit for each possible type
            this.involvedCorporations.forEach(function (entity) {
                if (entity != 0) {
                    emit(datestring + \"-\" + entity, info);
                }
            });
            this.involvedAlliances.forEach(function (entity) {
                if (entity != 0) {
                    emit(datestring + \"-\" + entity, info);
                }
            });
            this.involvedFactions.forEach(function (entity) {
                if (entity != 0) {
                    emit(datestring + \"-\" + entity, info);
                }
            });
            this.involvedCharacters.forEach(function (entity) {
                if (entity != 0) {
                    emit(datestring + \"-\" + entity, info);
                }
            });

        }";

        $reduce = "function (k, vals) {
            // initialize empty
            var sums = {
                total: 0,
                topValue: {
                    totalISKValue: 0
                }
            };


            vals.forEach(function (info) {
                sums[\"total\"]+= info[\"total\"];

                // for each reduced value check if the kills totalISKValue is the higher one,
                // if so, thats the one we want in the end result.
                if (sums[\"topValue\"][\"totalISKValue\"] < info[\"topValue\"][\"totalISKValue\"]) {
                    sums[\"topValue\"] = info[\"topValue\"];
                }
            });

            return sums;
        }";

        $tr = TaskRun::findByTaskType(__CLASS__);
        if (is_null($tr)) {
            $tr = new TaskRun();
            $tr->type = __CLASS__;
            $tr->lastrun = new MongoDate(0);
        }
        $last = $tr->lastrun;
        $tr->save();
        $new = $tr->lastrun;

        $filter = array("saved" => array('$gt' => $last, '$lte' => $new));
        $obj = new self();
        $out = array("reduce" => $obj->_className);
        Mongo::mapReduce("Kingboard_Kill", $out, $map, $reduce, $filter);
    }

    /**
     * find stats about the day
     * @static
     * @param int $date day to get
     * @param String $entity
     * @return MongoResult
     */
    public static function findOne($date, $entity)
    {
        return parent::doFindOne(__CLASS__, array("_id" => $date . "-" . $entity));
    }
}
