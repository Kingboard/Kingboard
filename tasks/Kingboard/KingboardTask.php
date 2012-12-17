<?php
namespace Kingboard;
use Kingboard\Lib\Parser\EveAPI;

class KingboardTask extends \King23\Tasks\King23Task
{
    /**
     * documentation for the single tasks
     * @var array
     */
    protected $tasks = array(
        "info" => "General Informative Task",
        "process_stomp_queue" => "process kills from a stomp queue"
    );

    /**
     * Name of the module
     */
    protected $name = "Kingboard";

    /**
     * Experimental task to enable kill processing from queue.
     * @param array $options
     */
    public function process_stomp_queue(array $options)
    {
        $this->cli->header("Experimental Stomp Kill Import");
        $reg = \King23\Core\Registry::getInstance();
        $stompcfg = $reg->stomp;
        if(is_null($stompcfg)|| !is_array($stompcfg))
        {
            $this->cli->error("stomp is not configured, see config.php for details");
            return;
        }
        $stomp = new \Stomp($reg->stomp['url'], $reg->stomp['user'], $reg->stomp['passwd']);
        $destination = $reg->stomp['destination_read'];
        $stomp->subscribe($destination);

        while(true) {
            $frame = $stomp->readFrame();
            if($frame) {
                $killdata = json_decode($frame->body, true);
                $existing = \Kingboard\Model\Kill::getByKillId($killdata["killID"]);
                if(!is_null($existing))
                {
                    $this->cli->message($frame->headers['message-id'] .'::' . $killdata["killID"] . " kill by killID exists");
                    $stomp->ack($frame);
                    continue;
                }
                try {
                    $apiParser = new EveAPI();
                    $apiParser->parseKill($killdata);

                    $this->cli->message($frame->headers['message-id'] .'::' . $killdata["killID"] . " saved");
                    $stomp->ack($frame);
                } catch(\Exception $e) { 
                    $this->cli->error($frame->headers['message-id'] . "could not be saved, exception: " . $e->getMessage());
                }
            }
        }


    }

    public function test(array $options)
    {
    }

}
