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
        $result = Kingboard_EveSolarSystem::find(array('itemName' => new MongoRegex('/^' .$_GET['term'] . '.*/i')))->limit(10);
        foreach($result as $system)
           $output[] = $system['itemName'];
        echo json_encode($output);
    }

    public function region(array $parameters)
    {
        $output = array();
        $result = Kingboard_EveRegion::find(array('itemName' => new MongoRegex('/^' .$_GET['term'] . '.*/i')))->limit(10);
        foreach($result as $system)
           $output[] = $system['itemName'];
        echo json_encode($output);
    }
}
