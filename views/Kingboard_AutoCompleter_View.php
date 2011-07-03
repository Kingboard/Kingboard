<?php
class Kingboard_AutoCompleter_View extends Kingboard_Base_View
{
    /**
     * stuff in this view doesnt need to be authed
     */
    public function __construct()
    {
        parent::__construct(false);
    }

    /**
     * Fetch up to 10 Solar Systems starting with term
     */
    public function solarsystem(array $parameters)
    {
        $output = array();
        $result = Kingboard_EveSolarSystem::find(array('itemName' => new MongoRegex('/^' .$_GET['term'] . '.*/i')), array('itemName'=>1))->limit(10);
        foreach($result as $system)
           $output[] = $system['itemName'];
        echo json_encode($output);
    }

    public function region(array $parameters)
    {
        $output = array();
        $result = Kingboard_EveRegion::find(array('itemName' => new MongoRegex('/^' .$_GET['term'] . '.*/i')), array("itemName" => 1))->limit(10);
        foreach($result as $system)
           $output[] = $system['itemName'];
        echo json_encode($output);
    }

    public function victims(array $parameters)
    {
        $term = $_GET['term'];

        // at least 4 characters here or that will get quite slow
        if(strlen($term) < 4)
        {
            echo json_encode(array());
            return;
        }
        
        $result = Kingboard_Kill::find(
            array('victim.characterName' => new MongoRegex('/^' .$term . '.*/i')),
            array('victim.characterName' => 1))
            ->limit(50);
        $names = array();
        foreach($result as $res)
            $names[$res['victim']['characterName']] = true;
        $names = array_keys($names);
        echo json_encode($names);
    }
}
