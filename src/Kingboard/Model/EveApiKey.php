<?php
namespace Kingboard\Model;

class EveApiKey extends \King23\Mongo\MongoObject
{
    protected $_className = "Kingboard_EveApiKey";

    public static function getById($id)
    {
        return parent::doGetInstanceById(__CLASS__, $id);
    }

    public static function find($criteria = array())
    {
        return parent::doFind(__CLASS__, $criteria);
    }

    public static function getByUserId($userID)
    {
        return parent::doGetInstanceByCriteria(__CLASS__, array('userid' => $userID));
    }
}
