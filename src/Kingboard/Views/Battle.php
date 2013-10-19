<?php
namespace Kingboard\Views;

class Battle extends \Kingboard\Views\Base
{
    /**
     * display a list of battles
     * @param array $request
     * @return string
     */
    public function index(array $request)
    {
        $templateVars = array();
        $currentPage = 1;
        if (!empty($request['page'])) {
            $currentPage = ((int)$request['page'] < 1) ? 1 : (int)$request['page'];
        }

        $count = \Kingboard\Model\BattleSettings::find()->count();

        $paginator = new \Kingboard\Lib\Paginator($currentPage, $count);

        // merge in pagination data
        $templateVars = array_merge($templateVars, $paginator->getNavArray());

        // battles
        $templateVars['reports'] = \Kingboard\Model\BattleSettings::find()
            ->skip($paginator->getSkip())
            ->limit($paginator->getKillsPerPage())
            ->sort(array('enddate' => -1));

        $templateVars['action'] = "/battles";

        return $this->render("battle/index.html", $templateVars);
    }

    /**
     * display a certain battle
     * @param array $parameters
     * @return string
     */
    public function show(array $parameters)
    {
        $battleSetting = \Kingboard\Model\BattleSettings::getById($parameters['id']);

        if (is_null($battleSetting)) {
            return $this->error("Battle with Id " . $parameters['id'] . " does not exist");
        }

        $battle = \Kingboard\Model\Battle::getByBattleSettings($battleSetting);

        $this->_context['battleSetting'] = $battleSetting;
        return $this->render("battle/details.html", $battle->data);
    }
}
