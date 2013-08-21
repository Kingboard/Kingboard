<?php
namespace Kingboard\Model\MapReduce;

/**
 * creates / allows access to stats about which shiptype has been killed how often
 */
class NameSearch extends \King23\Mongo\MongoObject implements \ArrayAccess
{
    protected $_className = "Kingboard_Kill_MapReduce_NameSearch";

    /**
     * @static
     * @param string $searchword
     * @param int $limit
     * @return \King23\Mongo\MongoResult
     */
    public static function search($searchword, $limit)
    {
        return parent::_find(
            __CLASS__,
            array(
                "_id" => new \MongoRegex('/^' . $searchword . '.*/i')
            )
        )->limit($limit);
    }

    /**
     * get a characterID by a characters name
     * @static
     * @param String $name
     * @return int
     */
    public static function getEveIdByName($name)
    {
        if ($data = parent::_findOne(__CLASS__, array("_id" => $name), array("value.id"))) {
            return $data['value']['id'];
        }
        return false;
    }

    public static function getNameByEveId($id)
    {
        if ($data = parent::_findOne(__CLASS__, array("value.id" => $id))) {
            return $data['_id'];
        }
        return false;
    }

    /**
     * @static
     */
    public static function mapReduce()
    {
        $map = "function () {
            emit(this.victim.characterName, {id: this.victim.characterID, type: \"character\"});
            emit(this.victim.factionName, {id: this.victim.factionID, type: \"faction\"});
            emit(this.victim.corporationName, {id: this.victim.corporationID, type: \"corporation\"});
            emit(this.victim.allianceName, {id: this.victim.allianceID, type: \"alliance\"});
            this.attackers.forEach(function(attacker) {
                emit(attacker.characterName, {id: attacker.characterID, type: \"character\"});
                emit(attacker.factionName, {id: attacker.factionID, type: \"faction\"});
                emit(attacker.corporationName, {id: attacker.corporationID, type: \"corporation\"});
                emit(attacker.allianceName, {id: attacker.allianceID, type: \"alliance\"});
            });
            }
        }";


        $reduce = "function(k, vals) {
            return vals[0]; // we only need the contents of the first hit for this key
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
