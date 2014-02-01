<?php
namespace Kingboard\Views;

use DateTime;
use Kingboard\Lib\Paginator;
use Kingboard\Model\BattleSettings;
use Kingboard\Model\Kill as KillModel;
use Kingboard\Model\MapReduce\KillsByDay;
use Kingboard\Model\MapReduce\KillsByDayByEntity;
use MongoDate;


class Date extends Base
{
    public function index(array $params)
    {

        if (!isset($params['date'])) {
            $context["date"] = date("Y-m-d");
        } else {
            $context["date"] = $params['date'];
        }

        // get previous day
        $dt = new DateTime($context['date']);
        $context["previousDate"] = date("Y-m-d", $dt->sub(new \DateInterval("P1D"))->getTimestamp());

        $dt = new DateTime($context['date']);
        $ts = $dt->add(new \DateInterval("P1D"))->getTimestamp();
        if ($ts < time()) {
            $context["nextDate"] = date("Y-m-d", $ts);
        } else {
            $context["nextDate"] = date("Y-m-d");
        }


        if (!isset($params['page']) || empty($params['page'])) {
            $page = 1;
        } else {
            $page = (int) $params['page'];
        }

        // reset date
        $dt = new DateTime($context['date']);
        $mdt = new MongoDate($dt->getTimestamp());

        if ($this->_context['ownerID']) {
            $stats = KillsByDayByEntity::findOne($dt->getTimestamp(), $this->_context['ownerID']);
        } else {
            $stats = KillsByDay::findOne($mdt);
        }
        $context['stats'] = $stats['value'];

        $paginator = new Paginator($page, $context['stats']['total']);
        $context['page']= $paginator->getNavArray();

        // reset date
        $dt = new DateTime($context['date']);

        if ($this->_context['ownerID']) {
            switch ($this->_context['ownerType']) {
                case "alliance":
                    $involvedType = "involvedAlliances";
                    break;
                case "faction":
                    $involvedType = "involvedFactions";
                    break;
                case "corp":
                case "corporation":
                    $involvedType = "involvedCorporations";
                    break;
                case "char":
                case "character":
                case "pilot":
                    $involvedType = "involvedCharacters";
                    break;
                default:
                    throw new \Exception("Configuration has set unknown ownerType!");
                    return;
            }
            $kills = KillModel::find(array(
                '$and' => array(
                    array("killTime" => array('$gt' => new MongoDate($dt->getTimestamp()))),
                    array("killTime" => array('$lt' => new MongoDate($dt->add(new \DateInterval("P1D"))->getTimestamp()))),
                    array($involvedType => $this->_context['ownerID'])
                )
            ))->hint(array("killTime" => 1 ))->sort(array("killTime" => -1))->skip($paginator->getSkip())->limit(10);
        } else {
            $kills = KillModel::find(array(
                '$and' => array(
                    array("killTime" => array('$gt' => new MongoDate($dt->getTimestamp()))),
                    array("killTime" => array('$lt' => new MongoDate($dt->add(new \DateInterval("P1D"))->getTimestamp())))
                )
            ))->hint(array("killTime" => 1 ))->sort(array("killTime" => -1))->skip($paginator->getSkip())->limit(10);
        }

        $context['kills'] = $kills;
        $context['action'] = "/day/" . $context['date'];

        $dt = new DateTime($context['date']);

        $criteria = array(
            '$and' => array(
                array("startdate" => array('$gt' => new MongoDate($dt->getTimestamp() -1))),
                array("startdate" => array('$lt' => new MongoDate($dt->add(new \DateInterval("P1D"))->getTimestamp())))
            )
        );

        if ($this->_context['ownerID']) {
            $criteria['$or'] = array(
                array("ownerCharacter" => $this->_context['ownerID']),
                array("ownerCorporation" => $this->_context['ownerID']),
                array("ownerAlliance" => $this->_context['ownerID']),
                array("ownerFaction" => $this->_context['ownerID'])
            );
        }


        $battles = BattleSettings::find($criteria);
        // resolve collection to avoid count() of doom
        $context["battles"] = array();
        foreach ($battles as $battle) {
            $context["battles"][] = $battle->toArray();
        }

        $this->render("date/daily.html", $context);

    }
}
