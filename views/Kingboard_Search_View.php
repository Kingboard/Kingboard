<?php
class Kingboard_Search_View extends Kingboard_Base_View
{
    public function index(array $params)
    {
        if(count($_POST) > 0)
            $context = $_POST;
        else
            $context = array();
        $results = null;

        if(isset($_POST['searchbox']))
        {
            if(strlen($_POST['searchbox']) <=3)
                $context['searchbox_length_to_short'] = true;
            else {
                foreach(Kingboard_Kill_MapReduce_NameSearch::search($_POST['searchbox'], 50) as $result)
                {
                    if(is_null($results)) $results = array();

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
        if(count($_POST) > 0 && (is_null($results) || count($results) < 1))
            $context['no_results'] = true;

        $this->render("search/index.html", $context);
    }

    public function namePilot(array $params)
    {
        if(!empty($params['pilotname']))
        {
            foreach(Kingboard_Kill_MapReduce_NameSearch::search($params['pilotname'], 1) as $result)
			{
				$id = $result->value['id'];
			}
            $this->redirect("/pilot/$id/");
            return;
        }
        die('unknown character');
    }

    public function nameCorporation(array $params)
    {
        if(!empty($params['corpname']))
        {
            foreach(Kingboard_Kill_MapReduce_NameSearch::search($params['corpname'], 1) as $result)
			{
				$id = $result->value['id'];
			}
            $this->redirect("/corporation/$id/");
            return;
        }
        die('unknown character');
    }
    public function nameFaction(array $params)
    {
        if(!empty($params['factionname']))
        {
            foreach(Kingboard_Kill_MapReduce_NameSearch::search($params['factionname'], 1) as $result)
			{
				$id = $result->value['id'];
			}
            $this->redirect("/faction/$id/");
            return;
        }
        die('unknown character');
    }
    public function nameAlliance(array $params)
    {
        if(!empty($params['corpname']))
        {
            foreach(Kingboard_Kill_MapReduce_NameSearch::search($params['alliancename'], 1) as $result)
			{
				$id = $result->value['id'];
			}
            $this->redirect("/alliance/$id/");
            return;
        }
        die('unknown character');
    }
}