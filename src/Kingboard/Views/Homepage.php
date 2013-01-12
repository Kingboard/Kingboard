<?php
namespace Kingboard\Views;
use Kingboard\Lib\Paginator;

class Homepage extends \Kingboard\Views\Base
{
    public function killlist($request)
    {

        if(empty($request['ownerType']) || empty($request['ownerID']))
            return $this->error("type / id not given");

        $ownerType = $request['ownerType'];
        $ownerID = $request['ownerID'];

        $currentPage = 1;
        if (!empty($request['page']))
        {
            $currentPage = ((int) $request['page'] <1) ?  1 : (int) $request['page'];
        }

        $templateVars =array();

        // kill list
        $killList = new \Kingboard\Lib\KillList($ownerType, $ownerID);
        $templateVars['killstats'] = $killList->getKillStats();
        $templateVars['lossstats'] = $killList->getLossStats();
        $templateVars['totalstats'] = $killList->getTotalStats();

        $paginator = new Paginator($currentPage, $killList->getCount());
        // fetch kill data
        $templateVars['data'] = $killList->getKills($paginator ->getSkip(), $paginator->getKillsPerPage());

        // merge in pagination data
        $templateVars= array_merge($templateVars, $paginator->getNavArray());

        $templateVars['count'] = $killList->getCount();
        $info = null;
        $template = null;

        switch($ownerType)
        {
            case "character":
            case "char":
            case "pilot":
                $template = "pilot/index.html";
                $info = \Kingboard\Model\Kill::getPilotInfoFromId($ownerID);
                break;
            case "corp":
            case "corporation":
                $template = "corporation/index.html";
                $info = \Kingboard\Model\Kill::getCorporationInfoFromId($ownerID);
                break;
            case "faction":
                $template = "faction/index.html";
                $info = \Kingboard\Model\Kill::getFactionInfoFromId($ownerID);
                break;
            case "alliance":
                $template = "alliance/index.html";
                $info = \Kingboard\Model\Kill::getAllianceInfoFromId($ownerID);
                break;
        }

        if(is_null($template) || is_null($info))
        {
            return $this->error("unknown ownerType: ". $ownerType);
        }

        $templateVars['info'] = $info;

        // we replace the defaults with the ones of the current look
        $templateVars['ownerID'] = $ownerID;
        $templateVars['ownerType'] = $ownerType;
        $templateVars['action'] = "/details/$ownerType/$ownerID";

        return $this->render($template, $templateVars);
    }

    public function top(array $params)
    {
        $data = \Kingboard\Model\Kill::find()->sort(array("totalISKValue" => -1))->limit(12);
        return $this->render("top.html", array("data" => $data));
    }

    public function newIndex(array $params)
    {
        return $this->render("newindex.html", array());
    }
}
