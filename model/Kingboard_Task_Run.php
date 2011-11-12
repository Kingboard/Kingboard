<?php
class Kingboard_Task_Run extends King23_MongoObject
{
    protected $_className = __class__;

    public static function getById($id)
    {
        return self::_getInstanceById(__class__, $id);
    }

    public static function find($criteria = array())
    {
        return self::_find(__class__, $criteria);
    }

    public static function findOne($criteria = array())
    {
        return self::_getInstanceByCriteria(__class__, $criteria);
    }

    public static function findByTaskType($name)
    {
        return self::_getInstanceByCriteria(__class__, array('type' => $name));
    }

    public function save()
    {
        $this->_data['lastrun'] = new MongoDate();
        parent::save();
    }
}
