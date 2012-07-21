<?php
namespace Kingboard;
class Kingboard_Task extends \King23\Tasks\King23Task
{
    /**
     * documentation for the single tasks
     * @var array
     */
    protected $tasks = array(
        "info" => "General Informative Task",
        "idfeed_add" => "add a idfeed to the idfeeds to be pulled",
    );

    /**
     * Name of the module
     */
    protected $name = "Kingboard";

    public function idfeed_add($options)
    {
        $this->cli->header('adding new idfeed');
        if(count($options) < 2)
        {
            $this->cli->error('atleast two parameters (url, handle) should be given');
            return;
        }

        if(!is_null(\Kingboard\Model\IdFeed::findByUrl($options[0])))
        {
            $this->cli->error('a feed by this url allready exists!');
            return;
        }


        $feed = new \Kingboard\Model\IdFeed();
        $feed->url = $options[0];
        $feed->handle = $options[1];

        if(count($options) == 3)
            $feed->type = $options[2];

        $feed->save();
        $this->cli->positive('done');
    }

    public function test(array $options)
    {
    }

}