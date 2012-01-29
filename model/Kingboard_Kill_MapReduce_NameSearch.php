<?php
/**
 * creates / allows access to stats about which shiptype has been killed how often
 */
class Kingboard_Kill_MapReduce_NameSearch extends King23_MongoObject implements ArrayAccess
{
    protected $_className = "Kingboard_Kill_MapReduce_NameSearch";

    /**
     * @static
     * @param string $searchword
     * @return King23_MongoResult
     */
    public static function search($searchword) {
        return  parent::_find(__CLASS__, array(
            "_id" => new MongoRegex('/^' .$searchword . '.*/i')
        ))->limit(50);
    }

    /**
     * @static
     */
    public static function mapReduce()
    {
        $map = "function () {
            emit(this.victim.characterName, {id: this.victim.characterID, type: \"character\"});
            emit(this.victim.corporationName, {id: this.victim.corporationID, type: \"corporation\"});
            emit(this.victim.allianceName, {id: this.victim.allianceID, type: \"alliance\"});
            this.attackers.forEach(function(attacker) {
                emit(attacker.characterName, {id: attacker.characterID, type: \"character\"});
                emit(attacker.corporationName, {id: attacker.corporationID, type: \"corporation\"});
                emit(attacker.allianceName, {id: attacker.allianceID, type: \"alliance\"});
            });
            }
        }";


        $reduce = "function(k, vals) {
            return vals[0]; // we only need the contents of the first hit for this key
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
