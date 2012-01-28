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
                $results = Kingboard_Kill_MapReduce_NameSearch::mapReduce($_POST['searchbox'], 'character');
                $context['results'] = $results['results'];
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
            $id = Kingboard_Kill::getPilotIdFromName($params['pilotname']);
            $this->redirect("/pilot/$id/");
            return;
        }
        die('unknown character');
    }

    public function nameCorporation(array $params)
    {
        if(!empty($params['corpname']))
        {
            $id = Kingboard_Kill::getCorporationIdFromName($params['corpname']);
            $this->redirect("/corporation/$id/");
            return;
        }
        die('unknown character');
    }
}