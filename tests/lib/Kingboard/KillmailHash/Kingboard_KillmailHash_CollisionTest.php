<?php
/**
 * Test if the ID hash generator does not mix up mails
 *
 * @author Georg Grossberger
 * @package Kingboard
 */
class Kingboard_KillmailHash_CollisionTest extends PHPUnit_Framework_TestCase
{
    public function testIfCollisionCanOccurWithTheCurrentIdHash()
    {
        // Comment out the next linge to enable the test
        $this->markTestSkipped('Not part of the build process');
  
        $found = array();
        $errors = array();
        $allKills = Kingboard_Kill::find();

        foreach ($allKills as $kill)
        {
            $hash = new Kingboard_KillmailHash_IdHash();
            $hash->setVictimId((int) (empty($kill['victim']['characterID']) ?  $kill['victim']['corporationID'] :  $kill['victim']['characterID']));
            $hash->setTime(new MongoDate(strtotime(str_replace('.', '-', $kill['killTime']))));
            $hash->setVictimShip($kill['victim']['shipTypeID']);
            
            foreach ($kill['attackers'] as $attacker)
            {
                $hash->pushAttackerData($attacker);
            }
            
            foreach ($kill['items'] as $item)
            {
                $hash->addItem($item);
            }
            
            try 
            {
                $hash = $hash->generateHash();
                if (!isset($found[$hash])) {
                    $found[$hash] = array();
                }
                $found[$hash][] = $kill['killID'];
            } catch (Kingboard_KillmailHash_ErrorException $e) {
                $errors[] = "Error {$e->getMessage()}: at kill {$kill['killID']}\n";
            }
        }
        
        if (!empty($errors))
        {
            echo implode('', $errors) . "\n";
        }
        
        $collisions = false;
        foreach ($found as $hash => $foundOnes) 
        {
            if (count($foundOnes) > 1) {
                echo "Hash '$hash' generated for this kills: " . implode(', ', $foundOnes) . "\n";
                $collisions = true;
            }
        }
        if ($collisions)
        {
            $this->fail('Collisions detected');
        }
    }
}
