<?php
class Kingboard_BattleEditor_View extends Kingboard_Base_View
{
    public function __construct()
    {
        // require user to be logged in for this view
        parent::__construct(true);
    }
}