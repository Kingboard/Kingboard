<?php
namespace Kingboard\Views;

class Kill extends \Kingboard\Views\Base
{
    public function index($request)
    {
        $currentPage = 1;
        if (!empty($request['page'])) {
            $currentPage = ((int)$request['page'] < 1) ? 1 : (int)$request['page'];
        }

        $info = array();
        $templateVars = array();
        // differences for owned boards
        if ($this->_context['ownerID']) {
            $killList = new \Kingboard\Lib\KillList($this->_context["ownerType"], $this->_context["ownerID"]);
            $templateVars['killstats'] = $killList->getKillStats();
            $templateVars['lossstats'] = $killList->getLossStats();
            $templateVars['totalstats'] = $killList->getTotalStats();
            $template = "index_owned_board.html";
        } else {
            // this is the only case of a list without owner/type, which is open list.
            $killList = new \Kingboard\Lib\KillList(null, null);
            $stats = \Kingboard\Model\MapReduce\KillsByShip::find();
            $stats = $stats->sort(array("value.value" => -1));
            $templateVars['stats'] = $stats;
            $template = "index.html";
        }

        $paginator = new \Kingboard\Lib\Paginator($currentPage, $killList->getCount());
        // fetch kill data
        $templateVars['data'] = $killList->getKills($paginator->getSkip(), $paginator->getKillsPerPage());

        // merge in pagination data
        $templateVars = array_merge($templateVars, $paginator->getNavArray());

        $templateVars['action'] = '/kills';
        $templateVars['count'] = $killList->getCount();
        $templateVars['info'] = $info;

        return $this->render($template, $templateVars);
    }


    public function details($request)
    {
        $context = array();
        $context['killdata'] = \Kingboard\Model\Kill::getByKillId($request['killID']);
        foreach ($context['killdata']['attackers'] as $attacker) {
            if (!isset($stats[$attacker['allianceName']])) {
                $stats[$attacker['allianceName']] = array();
            }
            if (!isset($stats[$attacker['allianceName']][$attacker['corporationName']])) {
                $stats[$attacker['allianceName']][$attacker['corporationName']] = 0;
            }
            $stats[$attacker['allianceName']][$attacker['corporationName']]++;
        }
        ksort($stats);
        $context['stats'] = $stats;
        return $this->render('kill.html', $context);
    }

    public function json($request)
    {
        $kill = \Kingboard\Model\Kill::getByKillId($request['killID']);
        echo json_encode($kill->toArray());
    }
}
