<?php
namespace Kingboard\Views;

use DateTime;
use Kingboard\Lib\Paginator;
use Kingboard\Model\Kill;
use Kingboard\Model\MapReduce\KillsByDay;
use MongoDate;


class Date extends Base
{
    public function index(array $params)
    {

        if (!isset($params['date'])) {
            $context["date"] = date("Y-m-d");
        } else {
            $context["date"] = $params['date'];
        }


        if (!isset($params['page']) || empty($params['page'])) {
            $page = 1;
        } else {
            $page = (int) $params['page'];
        }

        // reset date
        $dt = new DateTime($context['date'], new \DateTimeZone("UTC"));
        $mdt = new MongoDate($dt->getTimestamp());

        $stats = KillsByDay::findOne($mdt);
        $context['stats'] = $stats['value'];

        $paginator = new Paginator($page, $context['stats']['total']);
        $context['page']= $paginator->getNavArray();

        // reset date
        $dt = new DateTime($context['date']);

        $kills = Kill::find(array(
            '$and' => array(
                array("killTime" => array('$gt' => new MongoDate($dt->getTimestamp()))),
                array("killTime" => array('$lt' => new MongoDate($dt->add(new \DateInterval("P1D"))->getTimestamp())))
            )
        ))->hint(array("killTime" => 1 ))->sort(array("killTime" => -1))->skip($paginator->getSkip())->limit(10);

        $context['kills'] = $kills;

        // reset date
        $dt = new DateTime($context['date']);
        $context['topValue']= Kill::find(array(
            '$and' => array(
                array("killTime" => array('$gt' => new MongoDate($dt->getTimestamp()))),
                array("killTime" => array('$lt' => new MongoDate($dt->add(new \DateInterval("P1D"))->getTimestamp())))
            )
        ))->sort(array("totalISKValue" => -1))->limit(1);

        $context['topValue']->next();
        $context['topValue'] = $context['topValue']->current();

        $context['action'] = "/day/" . $context['date'];
        $this->render("date/daily.html", $context);
    }
}
