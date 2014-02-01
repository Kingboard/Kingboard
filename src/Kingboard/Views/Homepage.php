<?php
namespace Kingboard\Views;

use Kingboard\Lib\KillList;
use Kingboard\Lib\Paginator;
use Kingboard\Model\Kill as KillModel;
use Kingboard\Model\MapReduce\KillsByShipByPilot;
use Kingboard\Model\MapReduce\LossesByShipByPilot;
use Kingboard\Model\MapReduce\NameSearch;

class Homepage extends Base
{
    public function killlist($request)
    {

        if (empty($request['ownerType']) || empty($request['ownerID'])) {
            return $this->error("type / id not given");
        }

        $ownerType = $request['ownerType'];
        $ownerID = $request['ownerID'];

        $currentPage = 1;
        if (!empty($request['page'])) {
            $currentPage = ((int)$request['page'] < 1) ? 1 : (int)$request['page'];
        }

        $templateVars = array();

        // kill list
        $killList = new KillList($ownerType, $ownerID);
        $templateVars['killstats'] = $killList->getKillStats();
        $templateVars['lossstats'] = $killList->getLossStats();
        $templateVars['totalstats'] = $killList->getTotalStats();

        $paginator = new Paginator($currentPage, $killList->getCount());
        // fetch kill data
        $templateVars['data'] = $killList->getKills($paginator->getSkip(), $paginator->getKillsPerPage());

        // merge in pagination data
        $templateVars = array_merge($templateVars, $paginator->getNavArray());

        $templateVars['count'] = $killList->getCount();
        $info = null;
        $template = null;

        switch ($ownerType) {
            case "character":
            case "char":
            case "pilot":
                $template = "pilot/index.html";
                $info = KillModel::getPilotInfoFromId($ownerID);
                break;
            case "corp":
            case "corporation":
                $template = "corporation/index.html";
                $info = KillModel::getCorporationInfoFromId($ownerID);
                break;
            case "faction":
                $template = "faction/index.html";
                $info = KillModel::getFactionInfoFromId($ownerID);
                break;
            case "alliance":
                $template = "alliance/index.html";
                $info = KillModel::getAllianceInfoFromId($ownerID);
                break;
        }

        if (is_null($template) || is_null($info)) {
            return $this->error("unknown ownerType: " . $ownerType);
        }

        $templateVars['info'] = $info;

        // we replace the defaults with the ones of the current look
        $templateVars['ownerID'] = $ownerID;
        $templateVars['ownerType'] = $ownerType;
        $templateVars['action'] = "/details/$ownerType/$ownerID";

        return $this->render($template, $templateVars);
    }

    public function topValue(array $params)
    {
        $data = KillModel::find()->sort(array("totalISKValue" => -1))->limit(12);
        $items = array();
        foreach ($data as $item) {
            $items[] = $item;
        }
        return $this->render("top/iskvalue.html", array("data" => $items));
    }

    public function topKiller(array $params)
    {
        $result = KillsByShipByPilot::find(array("_id" => array('$gt' => 0)))->sort(array("value.total" => -1))->limit(
            12
        );
        $data = array();
        foreach ($result as $value) {
            $name = NameSearch::getNameByEveId($value->_id);
            $data[] = array(
                "name" => $name,
                "kills" => $value
            );
        }

        $this->render("top/killer.html", array("data" => $data));
    }

    public function topLoser(array $params)
    {
        $result = LossesByShipByPilot::find(array("_id" => array('$gt' => 0)))->sort(array("value.total" => -1))->limit(
            12
        );
        $data = array();
        foreach ($result as $value) {
            $name = NameSearch::getNameByEveId($value->_id);
            $data[] = array(
                "name" => $name,
                "kills" => $value
            );
        }

        $this->render("top/loser.html", array("data" => $data));
    }

    public function newIndex(array $params)
    {
        return $this->render("newindex.html", array());
    }
}
