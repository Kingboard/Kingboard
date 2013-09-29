<?php
namespace Kingboard;
use King23\Core\Registry;
use Kingboard\Lib\Parser\EveAPI;

class KingboardTask extends \King23\Tasks\King23Task
{
    /**
     * documentation for the single tasks
     * @var array
     */
    protected $tasks = array(
        "info" => "General Informative Task",
        "stomp_process_queue" => "process kills from a stomp queue",
    );
    /**
     * Name of the module
     */
    protected $name = "Kingboard";



    /**
     * Experimental task to enable kill processing from queue.
     * @param array $options
     */
    public function stomp_process_queue(array $options)
    {
        $this->cli->header("Starting Stomp Import");
        $reg = Registry::getInstance();

        $stompcfg = $reg->stomp;
        if (is_null($stompcfg) || !is_array($stompcfg)) {
            $this->cli->error("stomp is not configured, see config.php for details");
            return;
        }

        $stomp = new \Stomp($stompcfg['url'], $stompcfg['user'], $stompcfg['passwd']);

        // destination has the destination topic (for example /topic/kills)
        $destination = $reg->stomp['destination_read'];

        // we subscribe with additional parameters
        $stomp->subscribe($destination, array(
            "id" => $reg->stomp['dsub_id'], // dsub id, this one should be some unique identifier that identifies your board
                                            // multiple boards using the same dsub_id will consume each others subscription
            "persistent" => "true",         // this flag enables the dsub itself
            "ack" => "client",              // ensure we don't auto-ack (serverside) but have the client acknowledge his subscription,
            "prefetch-count" => 20
        ));

        while (true) {
            try {
                if(!$stomp->hasFrame())
                    continue;
                $frame = $stomp->readFrame();
                if ($frame) {
                    $killdata = json_decode($frame->body, true);
                    $existing = \Kingboard\Model\Kill::getByKillId($killdata["killID"]);

                    if (!is_null($existing)) {
                        $this->cli->message($frame->headers['message-id'] . '::' . $killdata["killID"] . " kill by killID exists");
                        $stomp->ack($frame);
                        continue;
                    }
                    try {
                        $apiParser = new EveAPI();
                        $apiParser->parseKill($killdata);

                        $this->cli->message($frame->headers['message-id'] . '::' . $killdata["killID"] . " saved");
                        $stomp->ack($frame);
                    } catch (\Exception $e) {
                        $this->cli->error($frame->headers['message-id'] . "could not be saved, exception: " . $e->getMessage());
                    }
                }
            } catch (\StompException $e) {
                $this->cli->error("there was some kind of error with stomp: " . $e->getMessage());
                $this->cli->message("going to sleep for 10, retrying then");
                // we have a stomp exception here most likely that means that the server died.
                // so we are going to sleep for a bit and retry
                sleep(10);

                // replace stomp connection by new one
                // @todo: check if that might cause open connections not to close over time
                unset($stomp);
                $stomp = new \Stomp($stompcfg['url'], $stompcfg['user'], $stompcfg['passwd']);
                $stomp->subscribe($destination, array(
                    "id" => $reg->stomp['dsub_id'], // dsub id, this one should be some unique identifier that identifies your board
                                                    // multiple boards using the same dsub_id will consume each others subscription
                    "persistent" => "true",         // this flag enables the dsub itself
                    "ack" => "client"               // ensure we don't auto-ack (serverside) but have the client acknowledge his subscription
                ));
                $this->cli->message("retrying");

            }

        }


    }

    public function test(array $options)
    {
    }

}
