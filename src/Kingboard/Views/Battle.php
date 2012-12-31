<?php
namespace Kingboard\Views;
class Battle extends \Kingboard\Views\Base
{
    /**
     * display a list of battles
     * @param array $parameters
     * @return void
     */
    public function index(array $parameters)
    {
        // battles
        $templateVars['reports'] = \Kingboard\Model\BattleSettings::find()->limit(5)->sort(array('enddate' => -1));
        return $this->render("battle/index.html", $templateVars);
    }

    /**
     * display a certain battle
     * @param array $parameters
     * @return void
     */
    public function show(array $parameters)
    {
        $battleSetting = \Kingboard\Model\BattleSettings::getById($parameters['id']);

        if(is_null($battleSetting))
            $this->sendErrorAndQuit("Battle with Id " . $parameters['id'] . " does not exist");

        $battle = \Kingboard\Model\Battle::getByBattleSettings($battleSetting);




        //var_dump($battleSetting);

        //print_r($battle->data);
        $this->_context['battleSetting'] = $battleSetting;
        $this->render("battle/details.html", $battle->data);
    }
}