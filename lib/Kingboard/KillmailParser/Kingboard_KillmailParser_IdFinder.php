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
 * Various functions to determine ids for names
 *
 * @author Georg Grossberger
 * @package Kingboard
 */
class Kingboard_KillmailParser_IdFinder
{
    /**
     * Find an ID in the kill collections for name of type key
     *
     * @throws UnexpectedValueException
     * @param string $name
     * @param string $key
     * @return integer
     */
    protected function queryKillCollections($name, $key)
    {
        $keyName = $key . 'Name';
        $keyId   = $key . 'ID';
        $result  = Kingboard_Kill::findOne(array('$or' => array(
            array(
                'victim.' . $keyName => $name,
            ),
            array(
                'attackers.' . $keyName => $name
            )
        )));
        
        if(!empty($kill['victim'][$keyId]) && $result['victim'][$keyName] == $name) {
            return (int) $kill['victim'][$keyId];
        }
        else {
            if (is_array($result['attackers'])) {
                foreach($result['attackers'] as $attacker) {
                    if($attacker[$keyName] == $name) {
                        return (int) $attacker[$keyName];
                    }
                }
            }
        }

        throw new UnexpectedValueException('No result for typeName ' . $name . ' in the kills collection');
    }


    /**
     * Find an ID in the API
     *
     * @throws UnexpectedValueException
     * @param string $name
     * @return integer
     */
    protected function queryNameToIdApi($name)
    {
        PhealConfig::getInstance()->http_post = FALSE;
        $request = new Pheal();
        $result = $request->eveScope->CharacterID(array('names' => $name))->characters->toArray();
        if ((int) $result[0]['characterID'] > 0) {
            return (int) $result[0]['characterID'];
        }
        throw new UnexpectedValueException('No API result for typeName ' . $name);
    }

   /**
    * Get the character id
    *
    * @throws UnexpectedValueException
    * @param string $name
    * @return integer
    */
   public function getCharacterId($name)
   {
      // Try to find in kills collection
      try {
          return (int) $this->queryKillCollections($name, 'character');
      }
      catch (UnexpectedValueException $e) {
            // Nothing found, ask the API
          try {
              return (int) $this->queryNameToIdApi($name);
          }
          catch (UnexpectedValueException $e) {}
      }
      throw new UnexpectedValueException('No character found with the name ' . $name);
   }

   /**
    * Find the ID of an alliance
    *
    * @throws UnexpectedValueException
    * @param string $name
    * @return integer
    */
   public function getAllianceId($name)
   {
      try {
          return (int) $this->queryKillCollections($name, 'alliance');
      }
      catch (UnexpectedValueException $e) {
          try {
              return (int) $this->queryNameToIdApi($name);
          } catch (UnexpectedValueException $e) {
          }
      }
      throw new UnexpectedValueException('No alliance found with the name ' . $name);
   }

   /**
    * Find the ID of a corporation
    *
    * @throws UnexpectedValueException
    * @param string $name
    * @return integer
    */
   public function getCorporationId($name)
   {
      try {
          return (int) $this->queryKillCollections($name, 'corporation');
      }
      catch (UnexpectedValueException $e) {
          try {
              return (int) $this->queryNameToIdApi($name);
          } catch (UnexpectedValueException $e) {
          }
      }
      throw new UnexpectedValueException('No corporation found with the name ' . $name);
   }

   /**
    * Find the ID of an item
    * Can be a ship, weapon, drone or any cargo item
    *
    * @throws UnexpectedValueException
    * @param string $name
    * @return integer
    */
   public function getItemId($name)
   {
       // Try in kills collection
        $result = Kingboard_EveItem::getInstanceByCriteria(array('typeName'  => $name));

        if ($result) {
            return (int) $result->typeID;
        }

        // Not found, try the api
        try {
            return (int) $this->queryNameToIdApi($name);
        } catch(UnexpectedValueException $e){
        }
        throw new UnexpectedValueException('No item found with the name ' . $name);
   }

   /**
    * Get the ID of a solar system
    *
    * @param string $name
    * @return integer
    */
   public function getSolarSystemId($name)
   {
       $result = Kingboard_EveSolarSystem::getInstanceByCriteria(array('itemName' => $name));
       if (is_object($result)) {
           return (int) $result->itemID;
       }
       
       try {
           return (int) $this->queryNameToIdApi($name);
       }
       catch(UnexpectedValueException $e) {
       }
       throw new UnexpectedValueException('No system found with the name ' . $name);
   }

   /**
    * Find the ID of a faction
    *
    * @throws UnexpectedValueException
    * @param string $name
    * @return integer
    */
   public function getFactionId($name)
   {
      // Try to find in kills collection
      try {
          return (int) $this->queryKillCollections($name, 'faction');
      }
      catch (UnexpectedValueException $e) {
            // Nothing found, ask the API
          return (int) $this->queryNameToIdApi($name);
      }

      // Nothing here yet, that cannot be right
      throw new UnexpectedValueException('No faction found with the name ' . $name);
   }
}
