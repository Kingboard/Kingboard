<?php
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
     * @throws Kingboard_KillmailParser_KillmailErrorException
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
        
        if(!empty($kill['victim'][$keyId]) && $result['victim'][$keyName] == $name)
		{
            return (int) $kill['victim'][$keyId];
        }
        else
        {
            if (is_array($result['attackers']))
            {
                foreach($result['attackers'] as $attacker)
                {
                    if($attacker[$keyName] == $name)
                    {
                        return (int) $attacker[$keyId];
                    }
                }
            }
        }

        throw new Kingboard_KillmailParser_KillmailErrorException('No result for typeName ' . $name . ' in the kills collection');
    }


    /**
     * Find an ID in the API
     *
     * @throws Kingboard_KillmailParser_KillmailErrorException
     * @param string $name
     * @return integer
     */
    protected function queryNameToIdApi($name)
    {
        try
        {
            $request = new Pheal();
            $result = $request->eveScope->CharacterID(array('names' => $name))->characters->toArray();
            
            if ((int) $result[0]['characterID'] > 0)
            {
                return (int) $result[0]['characterID'];
            }
        }
        catch (PhealAPIException $e)
        {
        }
        throw new Kingboard_KillmailParser_KillmailErrorException('No API result for typeName ' . $name);
    }

   /**
    * Get the character id
    *
    * @throws Kingboard_KillmailParser_KillmailErrorException
    * @param string $name
    * @return integer
    */
   public function getCharacterId($name)
   {
      // Try to find in kills collection
      try
      {
          return (int) $this->queryKillCollections($name, 'character');
      }
      catch (Kingboard_KillmailParser_KillmailErrorException $e) {}
      
      // Nothing found here, we just presume it's an npc which are stored as types
      try
      {
          return $this->getItemId($name);
      }
      catch (Kingboard_KillmailParser_KillmailErrorException $e) {}
      
      // Nothing found, ask the API
      try
      {
          return (int) $this->queryNameToIdApi($name);
      }
      catch (Kingboard_KillmailParser_KillmailErrorException $e) {}

      throw new Kingboard_KillmailParser_KillmailErrorException('No character found with the name ' . $name);
   }

   /**
    * Find the ID of an alliance
    *
    * @throws Kingboard_KillmailParser_KillmailErrorException
    * @param string $name
    * @return integer
    */
   public function getAllianceId($name)
   {
      try
      {
          return (int) $this->queryKillCollections($name, 'alliance');
      }
      catch (Kingboard_KillmailParser_KillmailErrorException $e)
      {
          try
          {
              return (int) $this->queryNameToIdApi($name);
          }
          catch (Kingboard_KillmailParser_KillmailErrorException $e)
          {
          }
      }
      throw new Kingboard_KillmailParser_KillmailErrorException('No alliance found with the name ' . $name);
   }

   /**
    * Find the ID of a corporation
    *
    * @throws Kingboard_KillmailParser_KillmailErrorException
    * @param string $name
    * @return integer
    */
   public function getCorporationId($name)
   {
      try
      {
          return (int) $this->queryKillCollections($name, 'corporation');
      }
      catch (Kingboard_KillmailParser_KillmailErrorException $e)
      {
          try
          {
              return (int) $this->queryNameToIdApi($name);
          }
          catch (Kingboard_KillmailParser_KillmailErrorException $e)
          {
          }
      }
      throw new Kingboard_KillmailParser_KillmailErrorException('No corporation found with the name ' . $name);
   }

   /**
    * Find the ID of an item
    * Can be a ship, weapon, drone or any cargo item
    *
    * @throws Kingboard_KillmailParser_KillmailErrorException
    * @param string $name
    * @return integer
    */
   public function getItemId($name)
   {
       // Try in kills collection
        $result = Kingboard_EveItem::getInstanceByCriteria(array('typeName'  => $name));

        if ($result)
        {
            return (int) $result->typeID;
        }

        // Try to find in kills
       /*
        $result  = Kingboard_Kill::findOne(array(
            'items.typeName' => $name
        ));

        if(!empty($kill['items']))
        {
            foreach ($kill['items'] as $item)
            {
                if ($item['typeName'] == $name)
                {
                    return (int) $item['typeID'];
                }
            }
        }*/

        // Not found, try the api
        try
        {
            return (int) $this->queryNameToIdApi($name);
        }
        catch(Kingboard_KillmailParser_KillmailErrorException $e)
        {
        }
        throw new Kingboard_KillmailParser_KillmailErrorException('No item found with the name ' . $name);
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
       if (is_object($result))
       {
           return (int) $result->itemID;
       }
       
       try
       {
           return (int) $this->queryNameToIdApi($name);
       }
       catch(Kingboard_KillmailParser_KillmailErrorException $e)
       {
       }
       throw new Kingboard_KillmailParser_KillmailErrorException('No system found with the name ' . $name);
   }

   /**
    * Find the ID of a faction
    *
    * @throws Kingboard_KillmailParser_KillmailErrorException
    * @param string $name
    * @return integer
    */
   public function getFactionId($name)
   {
      // Try to find in kills collection
      try
      {
          return (int) $this->queryKillCollections($name, 'faction');
      }
      catch (Kingboard_KillmailParser_KillmailErrorException $e)
      {
            // Nothing found, ask the API
          return (int) $this->queryNameToIdApi($name);
      }

      // Nothing here yet, that cannot be right
      throw new Kingboard_KillmailParser_KillmailErrorException('No faction found with the name ' . $name);
   }
}
