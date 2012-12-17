<?php
namespace Kingboard;
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
        \Kingboard\Model\MapReduce\KillsByShip::mapReduce();
        $this->cli->positive('update of KillsByShip stats completed');

        $this->cli->message('updating pilot loss stats');
        \Kingboard\Model\MapReduce\LossesByShipByPilot::mapReduce();
        $this->cli->positive('update of pilot loss stats completed');

        $this->cli->message('updating pilot kill stats');
        \Kingboard\Model\MapReduce\KillsByShipByPilot::mapReduce();
        $this->cli->positive('update of pilot kill stats completed');

        $this->cli->message('updating corporation loss stats');
        \Kingboard\Model\MapReduce\LossesByShipByCorporation::mapReduce();
        $this->cli->positive('update of corporation loss stats completed');

        $this->cli->message('updating corporation kill stats');
        \Kingboard\Model\MapReduce\KillsByShipByCorporation::mapReduce();
        $this->cli->positive('update of corporation kill stats completed');

        $this->cli->message('updating alliance loss stats');
        \Kingboard\Model\MapReduce\LossesByShipByAlliance::mapReduce();
        $this->cli->positive('update of alliance loss stats completed');

        $this->cli->message('updating alliance kill stats');
        \Kingboard\Model\MapReduce\KillsByShipByAlliance::mapReduce();
        $this->cli->positive('update of alliance kill stats completed');

        $this->cli->message('updating faction loss stats');
        \Kingboard\Model\MapReduce\LossesByShipByFaction::mapReduce();
        $this->cli->positive('update of faction loss stats completed');

        $this->cli->message('updating faction kill stats');
        \Kingboard\Model\MapReduce\KillsByShipByFaction::mapReduce();
        $this->cli->positive('update of faction kill stats completed');

        $this->cli->message('updating name lists for search');
        \Kingboard\Model\MapReduce\NameSearch::mapReduce();
        $this->cli->positive("name list updated");
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
