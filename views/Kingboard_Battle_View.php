<?php
class Kingboard_Battle_View extends Kingboard_Base_View
{
    /**
     * display a list of battles
     * @param array $parameters
     * @return void
     */
    public function index(array $parameters)
    {

    }

    /**
     * display a certain battle
     * @param array $parameters
     * @return void
     */
    public function show(array $parameters)
    {
        $battleSetting = Kingboard_BattleSettings::getById($parameters['id']);

        if(is_null($battleSetting))
            $this->sendErrorAndQuit("Battle with Id " . $parameters['id'] . " does not exist");

        $battle = Kingboard_Battle::getByBattleSettings($battleSetting);

        //print_r($battle->data);
        $this->_context['battleSetting'] = $battleSetting;
        $this->render("battle/details.html", $battle->data);
    }
}