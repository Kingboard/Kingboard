<?php
namespace Kingboard\Views;

class Search extends \Kingboard\Views\Base
{
    public function index(array $params)
    {
        if (count($_POST) > 0) {
            $context = $_POST;
        } else {
            $context = array();
        }
        $results = null;

        if (isset($_POST['searchbox'])) {
            if (strlen($_POST['searchbox']) < 3) {
                $context['searchbox_length_to_short'] = true;
            } else {
                foreach (\Kingboard\Model\MapReduce\NameSearch::search($_POST['searchbox'], 50) as $result) {
                    if (is_null($results)) {
                        $results = array();
                    }

                    $results[] = array(
                        "name" => $result->_id,
                        "id" => $result->value['id'],
                        "type" => $result->value['type']
                    );
                }
                $context["results"] = $results;
            }
        }

        // if this was posted we actually searched for something,
        // so lets give a no results info if we did not find anything.
        if (count($_POST) > 0 && (is_null($results) || count($results) < 1)) {
            $context['no_results'] = true;
        }

        return $this->render("search/index.html", $context);
    }

    public function namePilot(array $params)
    {
        $id = 0;
        if (!empty($params['pilotname'])) {
            foreach (\Kingboard\Model\MapReduce\NameSearch::search($params['pilotname'], 1) as $result) {
                $id = $result->value['id'];
            }
            $this->redirect("/details/pilot/$id/");
            return;
        }
        return $this->error('unknown character');
    }

    public function nameCorporation(array $params)
    {
        $id = 0;
        if (!empty($params['corpname'])) {
            foreach (\Kingboard\Model\MapReduce\NameSearch::search($params['corpname'], 1) as $result) {
                $id = $result->value['id'];
            }
            $this->redirect("/details/corporation/$id/");
            return;
        }
        return $this->error('unknown corporation');
    }

    public function nameFaction(array $params)
    {
        $id = 0;
        if (!empty($params['factionname'])) {
            foreach (\Kingboard\Model\MapReduce\NameSearch::search($params['factionname'], 1) as $result) {
                $id = $result->value['id'];
            }
            $this->redirect("/details/faction/$id/");
            return;
        }
        return $this->error("unknown faction");
    }

    public function nameAlliance(array $params)
    {
        $id = 0;
        if (!empty($params['alliancename'])) {
            foreach (\Kingboard\Model\MapReduce\NameSearch::search($params['alliancename'], 1) as $result) {
                $id = $result->value['id'];
            }
            $this->redirect("/details/alliance/$id/");
            return;
        }
        return $this->error("unknown alliance");
    }
}
