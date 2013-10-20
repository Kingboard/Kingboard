<?php
namespace Kingboard\Views;

use Kingboard\Model\Kill;


class Date extends Base
{
    public function index(array $params)
    {

        if (!isset($params['date'])) {
            $context["date"] = date("Y-m-d");
        } else {
            $context["date"] = $params['date'];
        }

        if (!isset($params['page'])) {
            $context['page'] = 1;
        } else {
            $context['page'] = (int) $params['page'];
        }

        $dt = new \DateTime($context['date'], new \DateTimeZone("UTC"));

        $kills = Kill::find(array(
            '$and' => array(
                array("killTime" => array('$gt' => new \MongoDate($dt->getTimestamp()))),
                array("killTime" => array('$lt' => new \MongoDate($dt->add(new \DateInterval("P1D"))->getTimestamp())))
            )
        ))->sort(array("killTime" => -1))->skip(0)->limit(10);

        $context['kills'] = $kills;

        // reset date
        $dt = new \DateTime($context['date'], new \DateTimeZone("UTC"));
        $context['topValue']= Kill::find(array(
            '$and' => array(
                array("killTime" => array('$gt' => new \MongoDate($dt->getTimestamp()))),
                array("killTime" => array('$lt' => new \MongoDate($dt->add(new \DateInterval("P1D"))->getTimestamp())))
            )
        ))->sort(array("totalISKValue" => -1))->limit(1);
        $context['topValue']->next();
        $context['topValue'] = $context['topValue']->current();

        $this->render("date/daily.html", $context);
    }
}
