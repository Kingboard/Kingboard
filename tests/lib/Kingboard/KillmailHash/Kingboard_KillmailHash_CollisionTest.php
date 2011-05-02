<?php

/*
  MIT License
  Copyright (c) 2011 Peter Petermann

  Permission is hereby granted, free of charge, to any person
  obtaining a copy of this software and associated documentation
  files (the "Software"), to deal in the Software without
  restriction, including without limitation the rights to use,
  copy, modify, merge, publish, distribute, sublicense, and/or sell
  copies of the Software, and to permit persons to whom the
  Software is furnished to do so, subject to the following
  conditions:

  The above copyright notice and this permission notice shall be
  included in all copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
  EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
  OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
  NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
  HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
  WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
  FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
  OTHER DEALINGS IN THE SOFTWARE.

 */

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
                $id = (int) (int) (empty($attacker['characterID']) ?  $attacker['corporationID'] :  $attacker['characterID']);
                $hash->addAttackerId($id);
                
                if (!empty($attacker['finalBlow']))
                {
                    $hash->setFinalBlowAttackerId($id);
                }
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
