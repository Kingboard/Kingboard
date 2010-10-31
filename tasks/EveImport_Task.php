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

    public function items(array $options)
    {
        $this->cli->message('importing items from sqlite');

        if(count($options) < 1 || !file_exists($options[0]) || !($pdo = new PDO("sqlite:" . $options[0], null,null)))
        {
            $this->cli->error('you need to specify a sqlite filename as option');
            return -1;
        }

        $query = "select * from invTypes a LEFT JOIN invGroups b ON a.groupID = b.groupID LEFT JOIN invCategories c ON b.categoryID = c.categoryID LEFT JOIN invMarketGroups d ON a.marketGroupID = d.marketGroupID LIMIT 1000";
        $stmt = $pdo->query($query);

        while($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            print_r($row);
        }

    }
}
