<?php
namespace Kingboard;

use Kingboard\Model\MapReduce\KillsByShip;
use Kingboard\Model\MapReduce\KillsByShipByAlliance;
use Kingboard\Model\MapReduce\KillsByShipByCorporation;
use Kingboard\Model\MapReduce\KillsByShipByFaction;
use Kingboard\Model\MapReduce\KillsByShipByPilot;
use Kingboard\Model\MapReduce\LossesByShipByAlliance;
use Kingboard\Model\MapReduce\LossesByShipByCorporation;
use Kingboard\Model\MapReduce\LossesByShipByFaction;
use Kingboard\Model\MapReduce\LossesByShipByPilot;
use Kingboard\Model\MapReduce\NameSearch;
use Kingboard\Model\MapReduce\KillsByDay;

/**
 * This class contains all Kingboard Tasks that need to be
 * run on a regulary basis.
 */
class KingboardCronTask extends \King23\Tasks\King23Task
{
    /**
     * documentation for the single tasks
     * @var array
     */
    protected $tasks = array(
        "info" => "General Informative Task",
        "update_stats" => "task to update stats which will be map/reduced from the database",
        "api_import" => "import killmails from api",
        "item_values" => "updates item values for all items on the market",
    );

    /**
     * Name of the module
     */
    protected $name = "KingboardCron";

    /**
     * update statistics
     * @param array $options
     * @return void
     */
    public function update_stats(array $options)
    {
        $this->cli->header('updating stats');

        $this->cli->message('updating Kills by Shiptype');
        // stats table of how often all ships have been killed
        KillsByShip::mapReduce();
        $this->cli->positive('update of KillsByShip stats completed');

        $this->cli->message('updating pilot loss stats');
        LossesByShipByPilot::mapReduce();
        $this->cli->positive('update of pilot loss stats completed');

        $this->cli->message('updating pilot kill stats');
        KillsByShipByPilot::mapReduce();
        $this->cli->positive('update of pilot kill stats completed');

        $this->cli->message('updating corporation loss stats');
        LossesByShipByCorporation::mapReduce();
        $this->cli->positive('update of corporation loss stats completed');

        $this->cli->message('updating corporation kill stats');
        KillsByShipByCorporation::mapReduce();
        $this->cli->positive('update of corporation kill stats completed');

        $this->cli->message('updating alliance loss stats');
        LossesByShipByAlliance::mapReduce();
        $this->cli->positive('update of alliance loss stats completed');

        $this->cli->message('updating alliance kill stats');
        KillsByShipByAlliance::mapReduce();
        $this->cli->positive('update of alliance kill stats completed');

        $this->cli->message('updating faction loss stats');
        LossesByShipByFaction::mapReduce();
        $this->cli->positive('update of faction loss stats completed');

        $this->cli->message('updating faction kill stats');
        KillsByShipByFaction::mapReduce();
        $this->cli->positive('update of faction kill stats completed');

        $this->cli->message('updating name lists for search');
        NameSearch::mapReduce();
        $this->cli->positive("name list updated");

        $this->cli->message('updating daily stats');
        KillsByDay::mapReduce();
        $this->cli->positive("daily stats updated");

        $this->cli->message('updating daily stats by entity');
        KillsByDayByEntity::mapReduce();
        $this->cli->positive("daily stats by entity updated");


    }

    /**
     * import mails from ccp's API.
     * @param array $options
     */
    public function api_import(array $options)
    {
        $this->cli->message("api import running");

        foreach(\Kingboard\Model\User::findWithApiKeys() as $user)
        {
            /** @var \Kingboard\Model\User $user */
            foreach($user['keys'] as $keyid => $key)
            {
                // skip inactive keys
                if(!$key['active'])
                    continue;

                try {
                    $stats = \Kingboard\Lib\Fetcher\EveApi::fetch($key);
                    $this->cli->message("processed key $keyid for user " . $user->name . " : ". $stats['old'] . ' known / ' . $stats['new'] ." new");
                } catch(\Exception $e) {
                    // we caught a pheal exception, we should implement proper handling of errors here
                    $this->cli->error("key $keyid caused pheal exception: " . $e->getMessage());
                }

            }
        }
    }

    /**
     * update item values from eve central
     * @param array $options
     */
    public function item_values(array $options)
    {
        $this->cli->message("item value updating running");
        $result = \Kingboard\Model\EveItem::getMarketIDs();
        $total = $result->count();
        foreach($result as $item)
        {
            $isk = \Kingboard\Lib\EveCentral\Api::getValue($item->typeID);
            if($isk > 0)
            {

                $instance = \Kingboard\Model\EveItem::getByItemId($item->typeID);
                $instance->iskValue = $isk;
                $instance->save();

                $this->cli->message("Successfully updated ".$item->typeID." to ".$isk);
            }
            else
                $this->cli->message("Did not update ".$item->typeID.", it had a value of 0");
        }
    }
}
