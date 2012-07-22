<?php
namespace Kingboard\Model;
class EveItem extends \King23\Mongo\MongoObject
{
    protected $_className = "Kingboard_EveItem";

    public static function getById($id)
    {
        return self::_getInstanceById(__CLASS__, $id);
    }

    public static function getInstanceByCriteria($criteria)
    {
        return self::_getInstanceByCriteria(__CLASS__, $criteria);
    }

    public static function find($criteria = array())
    {
        return self::_find(__CLASS__, $criteria);
    }

    public static function getByItemId($invItemID)
    {
        return self::_getInstanceByCriteria(__CLASS__, array('typeID' => (int)$invItemID));
    }
    public static function getShipIDs($typeName)
    {
        return self::_find(__CLASS__, array('$and' =>
            array(
                array('marketGroup.0.parentGroup.0.marketGroupName' => $typeName),
                array('$or' =>
                    array(
                        array('marketGroup.0.parentGroup.0.parentGroup.0.parentGroup.0.marketGroupName' => 'Ships'),
                        array('marketGroup.0.parentGroup.0.parentGroup.0.parentGroup.0.parentGroup.0.marketGroupName' => 'Ships'),
                        array('marketGroup.0.parentGroup.0.parentGroup.0.marketGroupName' => 'Starbase & Sovereignty Structures'),
                        array('marketGroup.0.parentGroup.0.parentGroup.0.parentGroup.0.marketGroupName' => 'Starbase & Sovereignty Structures')
                    )
                )
            )
        ), array(
            'typeName' => 1,
            'typeID' => 1
        ));
    }
    public static function getMarketIDs()
    {
        // Only needed when we update, all the market IDs here are stuff that are on the market ingame
        return self::_find(__CLASS__, array(
            "marketGroupID" =>
                array('$in' =>
                    array(204, 205, 261, 264, 272, 277, 206, 273, 274, 275, 276, 207, 278, 
                    279, 280, 281, 208, 282, 283, 284, 285, 1389, 408, 410, 411, 412, 413, 
                    414, 415, 416, 417, 418, 419, 425, 427, 428, 429, 442, 443, 444, 445, 
                    446, 453, 454, 455, 456, 457, 458, 459, 461, 462, 463, 496, 497, 582, 
                    583, 584, 585, 586, 588, 589, 590, 591, 592, 634, 635, 636, 637, 638, 
                    782, 783, 784, 785, 786, 787, 788, 789, 790, 791, 878, 884, 885, 886, 
                    887, 879, 888, 889, 890, 891, 880, 892, 893, 894, 895, 881, 896, 897, 
                    898, 899, 882, 900, 901, 902, 903, 883, 904, 1045, 1046, 209, 210, 286, 
                    289, 290, 291, 792, 287, 296, 297, 298, 793, 288, 292, 293, 295, 794, 
                    338, 340, 343, 363, 913, 937, 939, 1019, 1198, 211, 299, 309, 312, 313, 
                    597, 300, 306, 307, 308, 598, 301, 302, 303, 305, 599, 314, 315, 316, 
                    317, 318, 390, 617, 975, 1286, 320, 339, 753, 1016, 1105, 1358, 212, 
                    326, 327, 328, 329, 213, 322, 323, 324, 325, 430, 905, 214, 333, 334, 
                    335, 252, 331, 332, 341, 357, 358, 359, 1028, 1029, 1030, 1313, 406, 
                    407, 1008, 799, 800, 265, 796, 798, 1097, 1191, 1357, 941, 943, 944, 
                    1202, 1203, 1204, 945, 1240, 1241, 1242, 946, 1243, 1244, 1245, 947, 
                    1246, 1247, 1248, 948, 1249, 1250, 1251, 949, 1252, 1253, 1254, 950, 
                    1255, 1256, 1257, 951, 1258, 1259, 1260, 952, 1261, 1262, 1263, 953, 
                    1264, 1265, 1266, 954, 1267, 1268, 1269, 1041, 1042, 1043, 1412, 1338, 
                    1339, 1340, 1341, 1342, 1343, 1344, 1345, 1346, 1347, 1348, 1349, 1350, 
                    1351, 1352, 1353, 1354, 1355, 1356, 1359, 1411)
                )
        ), array(
            "typeID" => 1
        ));
    }
    public static function getItemValue($itemID)
    {
        $item = self::getByItemId($itemID);
        if(!is_null($item->isk))
            return $item->isk;
        else
            return self::updateItemValue($itemID);
    }
}
