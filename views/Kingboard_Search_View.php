<?php
class Kingboard_Search_View extends Kingboard_Base_View
{
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