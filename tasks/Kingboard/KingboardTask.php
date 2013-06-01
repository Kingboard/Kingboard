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
        "stomp_process_queue" => "process kills from a stomp queue",
        "stomp_register_dsub" => "register a dsub queue for stomp queing",
        "stomp_drop_dsub"     => "drop a dsub registration (Optional parameter: id to drop, if parameter not given it will drop the one from the config)"
    );
    /**
     * Name of the module
     */
    protected $name = "Kingboard";

    /**
     * Register a dsub for the stomp topic
     * @param array $options
     */
    public function stomp_register_dsub(array $options)
    {
        $reg = \King23\Core\Registry::getInstance();
        // create new stomp client
        $stomp = new \Stomp($reg->stomp['url'], $reg->stomp['user'], $reg->stomp['passwd']);

        // destination has the destination topic (for example /topic/kills)
        $destination = $reg->stomp['destination_read'];

        // we subscribe with additional parameters
        $stomp->subscribe($destination, array(
            "id" => $reg->stomp['dsub_id'], // dsub id, this one should be some unique identifier that identifies your board
                                            // multiple boards using the same dsub_id will consume each others subscription
            "persistent" => "true",         // this flag enables the dsub itself
            "ack" => "client"               // ensure we don't auto-ack (serverside) but have the client acknowledge his subscription
        ));
    }


    /**
     * utility function, allows to drop dsubs
     * @param array $options
     */
    public function stomp_drop_dsub(array $options)
    {
        $reg = \King23\Core\Registry::getInstance();
        // create new stomp client
        $stomp = new \Stomp($reg->stomp['url'], $reg->stomp['user'], $reg->stomp['passwd']);

        // destination has the destination topic (for example /topic/kills)
        $destination = $reg->stomp['destination_read'];

        if(isset($options[0]) && !empty($options[0]))
            $dsub_id = $options[0];
        else
            $dsub_id = $reg->stomp['dsub_id'];

        // we subscribe with additional parameters
        $stomp->unsubscribe($destination, array(
            "id" => $dsub_id,
            "persistent" => "true"
        ));
    }

    /**
     * Experimental task to enable kill processing from queue.
     * @param array $options
     */
    public function stomp_process_queue(array $options)
    {
        $this->cli->header("Starting Stomp Import");
        $reg = \King23\Core\Registry::getInstance();

        $stompcfg = $reg->stomp;
        if (is_null($stompcfg) || !is_array($stompcfg)) {
            $this->cli->error("stomp is not configured, see config.php for details");
            return;
        }

        $stomp = new \Stomp($stompcfg['url'], $stompcfg['user'], $stompcfg['passwd']);

        // subscribe to the dsub we created
        $stomp->subscribe('/dsub/' . $stompcfg['dsub_id']);

        while (true) {
            try {
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
                // we have a stomp exception here most likely that means that the server died.
                // so we are going to sleep for a bit and retry
                sleep(10);

                // replace stomp connection by new one
                // @todo: check if that might cause open connections not to close over time
                $stomp = new \Stomp($stompcfg['url'], $stompcfg['user'], $stompcfg['passwd']);
                $stomp->subscribe('/dsub/' . $stompcfg['dsub_id']);

            }

        }


    }

    public function test(array $options)
    {
    }

}
