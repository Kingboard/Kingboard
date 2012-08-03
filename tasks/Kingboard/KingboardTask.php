<?php
namespace Kingboard;
class KingboardTask extends \King23\Tasks\King23Task
{
    /**
     * documentation for the single tasks
     * @var array
     */
    protected $tasks = array(
        "info" => "General Informative Task",
        "idfeed_add" => "add a idfeed to the idfeeds to be pulled",
        "process_stomp_queue" => "process kills from a stomp queue"
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

    /**
     * Experimental task to enable kill processing from queue.
     * @param array $options
     */
    public function process_stomp_queue(array $options)
    {
        $this->cli->header("Experimental Stomp Kill Import");
        $reg = \King23\Core\Registry::getInstance();
        $destination = "/topic/kills";
        $stompcfg = $reg->stomp;
        if(is_null($stompcfg)|| !is_array($stompcfg))
        {
            $this->cli->error("stomp is not configured, see config.php for details");
            return;
        }
        $stomp = new \Stomp($reg->stomp['url'], $reg->stomp['user'], $reg->stomp['passwd']);
        $stomp->subscribe($destination);

        while(true) {
            $frame = $stomp->readFrame();
            if($frame) {
                $killdata = json_decode($frame->body, true);
                $existing = \Kingboard\Model\Kill::getInstanceByIdHash($killdata["idHash"]);
                if(!is_null($existing))
                {
                    $this->cli->message($frame->headers['message-id'] .'::' . $killdata["idHash"] . " kill by hash exists");
                    $stomp->ack($frame);
                    continue;
                }
                $kill = new \Kingboard\Model\Kill();
                $kill->injectDataFromMail($killdata);
                $kill->save();

                $this->cli->message($frame->headers['message-id'] .'::' . $killdata["idHash"] . " saved");
                $stomp->ack($frame);
            }
        }


    }

    public function test(array $options)
    {
    }

}