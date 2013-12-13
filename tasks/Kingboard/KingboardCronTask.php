<?php
namespace Kingboard;

use King23\Core\Registry;
use King23\Tasks\King23Task;
use Kingboard\Lib\Fetcher\EveApi;
use Kingboard\Model\EveItem;
use Kingboard\Model\MapReduce\KillsByDay;
use Kingboard\Model\MapReduce\KillsByDayByEntity;
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
use Kingboard\Model\User;

/**
 * This class contains all Kingboard Tasks that need to be
 * run on a regulary basis.
 */
class KingboardCronTask extends King23Task
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
        $log = Registry::getInstance()->getLogger();
        $log->info('start updating stats');

        $log->info('updating kills by Shiptype');
        // stats table of how often all ships have been killed
        KillsByShip::mapReduce();
        $log->info('update of KillsByShip stats completed');

        $log->info('updating pilot loss stats');
        LossesByShipByPilot::mapReduce();
        $log->info('update of pilot loss stats completed');

        $log->info('updating pilot kill stats');
        KillsByShipByPilot::mapReduce();
        $log->info('update of pilot kill stats completed');

        $log->info('updating corporation loss stats');
        LossesByShipByCorporation::mapReduce();
        $log->info('update of corporation loss stats completed');

        $log->info('updating corporation kill stats');
        KillsByShipByCorporation::mapReduce();
        $log->info('update of corporation kill stats completed');

        $log->info('updating alliance loss stats');
        LossesByShipByAlliance::mapReduce();
        $log->info('update of alliance loss stats completed');

        $log->info('updating alliance kill stats');
        KillsByShipByAlliance::mapReduce();
        $log->info('update of alliance kill stats completed');

        $log->info('updating faction loss stats');
        LossesByShipByFaction::mapReduce();
        $log->info('update of faction loss stats completed');

        $log->info('updating faction kill stats');
        KillsByShipByFaction::mapReduce();
        $log->info('update of faction kill stats completed');

        $log->info('updating name lists for search');
        NameSearch::mapReduce();
        $log->info("name list updated");

        $log->info('updating daily stats');
        KillsByDay::mapReduce();
        $log->info("daily stats updated");

        $log->info('updating daily stats by entity');
        KillsByDayByEntity::mapReduce();
        $log->info("daily stats by entity updated");


    }

    /**
     * import mails from ccp's API.
     * @param array $options
     */
    public function api_import(array $options)
    {
        $log = Registry::getInstance()->getLogger();
        $log->info("api import running");

        foreach (User::findWithApiKeys() as $user) {
            /** @var User $user */
            foreach ($user['keys'] as $keyid => $key) {
                // skip inactive keys
                if (!$key['active']) {
                    continue;
                }

                try {
                    $stats = EveApi::fetch($key);
                    $log->info(
                        "processed key $keyid for user "
                        . $user->name
                        . " : "
                        . $stats['old']
                        . ' known / '
                        . $stats['new']
                        . " new"
                    );
                } catch (\Exception $e) {
                    // we caught a pheal exception, we should implement proper handling of errors here
                    $log->warning("key $keyid caused pheal exception: " . $e->getMessage());
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
        $log = Registry::getInstance()->getLogger();
        $log->info("item value updating running");
        $result = EveItem::getMarketIDs();

        foreach ($result as $item) {
            $isk = \Kingboard\Lib\EveCentral\Api::getValue($item->typeID);
            if ($isk > 0) {

                $instance = EveItem::getByItemId($item->typeID);
                $instance->iskValue = $isk;
                $instance->save();

                $log->info("Successfully updated " . $item->typeID . " to " . $isk);
            } else {
                $log->info("Did not update " . $item->typeID . ", it had a value of 0");
            }
        }
    }
}
