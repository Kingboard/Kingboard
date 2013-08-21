<?php
namespace Kingboard\Model;

class TaskRun extends \King23\Mongo\MongoObject
{
    protected $_className = "Kingboard_Task_Run";

    public static function getById($id)
    {
        return self::_getInstanceById(__CLASS__, $id);
    }

    public static function find($criteria = array())
    {
        return self::_find(__CLASS__, $criteria);
    }

    public static function findOne($criteria = array())
    {
        return self::_getInstanceByCriteria(__CLASS__, $criteria);
    }

    public static function findByTaskType($name)
    {
        return self::_getInstanceByCriteria(__CLASS__, array('type' => $name));
    }

    public function save()
    {
        $this->_data['lastrun'] = new \MongoDate();
        parent::save();
    }
}
