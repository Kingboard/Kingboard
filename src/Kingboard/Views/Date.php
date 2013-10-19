<?php
namespace Kingboard\Views;

class Date extends Base
{
    /*public function __construct()
    {
        parent::__construct(false);
    } */
    public function index(array $params)
    {
        $context = array();
        $context["date"] = date("Y-m-d");

        $this->render("date/daily.html", $context);
    }
}