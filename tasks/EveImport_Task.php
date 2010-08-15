<?php
require_once 'conf/config.php';
class EveImport_Task extends King23_CLI_Task
{
    /**
     * documentation for the single tasks
     * @var array
     */
    protected $tasks = array(
        "info" => "General Informative Task",
        "import" => "items",
    );

    /**
     * Name of the module
     */
    protected $name = "EveImport";

    public function import(array $options)
    {
        $this->cli->message('importing items from sqlite');

        if(count($options) < 1 || !file_exists($options[0]) || !($db = sqlite_open($options[0])))
        {
            $this->cli->error('you need to specify a sqlite filename as option');
            return -1;
        }

        $res = sqlite_query(" select * from invTypes a LEFT JOIN invGroups b ON a.groupID = b.groupID LEFT JOIN invCategories c ON b.categoryID = c.categoryID LEFT JOIN invMarketGroups d ON a.marketGroupID = d.marketGroupID LIMIT 1000",$db);

        while($row = sqlite_fetch_array($res, SQLITE_ASSOC))
        {
            print_r($row);
        }

    }
}
