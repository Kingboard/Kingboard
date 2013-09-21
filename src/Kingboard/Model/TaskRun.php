<?php
namespace Kingboard\Model;

class TaskRun extends \King23\Mongo\MongoObject
{
    protected $_className = "Kingboard_Task_Run";

    public static function getById($id)
    {
        return parent::doGetInstanceById(__CLASS__, $id);
    }

    public static function find($criteria = array())
    {
        return parent::doFind(__CLASS__, $criteria);
    }

    public static function findOne($criteria = array())
    {
        return parent::doGetInstanceByCriteria(__CLASS__, $criteria);
    }

    public static function findByTaskType($name)
    {
        return parent::doGetInstanceByCriteria(__CLASS__, array('type' => $name));
    }

    public function save()
    {
        $this->_data['lastrun'] = new \MongoDate();
        parent::save();
    }
}
