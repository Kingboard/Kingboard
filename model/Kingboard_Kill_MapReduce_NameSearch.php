<?php
/**
 * creates / allows access to stats about which shiptype has been killed how often
 */
class Kingboard_Kill_MapReduce_NameSearch extends King23_MongoObject implements ArrayAccess
{
    protected $_className = "Kingboard_Kill_MapReduce_NameSearch";

    public static function mapReduce($search, $type)
    {
        $map = "function () {
            re = new RegExp(searchword);
            if(this.victim.".$type."Name.match(re))
                emit(this.victim.".$type."Name, {id: this.victim.".$type."ID, type: \"$type\"});
            else {
                this.attackers.forEach(function(attacker) {
                    if(attacker.".$type."Name.match(re))
                        emit(attacker.".$type."Name, {id: attacker.".$type."ID, type: \"$type\"});
                });
            }
        }";


        $reduce = "function(k, vals) {
            return vals[0]; // we only need the contents of the first hit for this key
        }";


        $filter = array('$or' => array(
            array("victim.".$type."Name" =>  new MongoRegex('/^' .$search . '.*/i')),
            array("attackers.".$type."Name" =>  new MongoRegex('/^' .$search . '.*/i'))
        ));
        $out = array("inline" => TRUE);
        return King23_Mongo::mapReduce("Kingboard_Kill", $out, $map, $reduce, $filter, array("scope" => array("searchword" => '^' .$search . '.*')));
    }

}
