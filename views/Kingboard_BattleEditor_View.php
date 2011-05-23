<?php
class Kingboard_BattleEditor_View extends Kingboard_Base_View
{
    public function __construct()
    {
        // require user to be logged in for this view
        parent::__construct(true);
    }

    public function create(array $params)
    {
        print_r($_POST);
        if(!Kingboard_BattleCreate_Form::validate($_POST))
        {
            // @todo handle invalid
            die();
        }
        $user = Kingboard_Auth::getUser();
        list($key, $character) = explode('|', $_POST['character']);
        $key = $user["keys"][$key];
        $pheal = new Pheal($key['apiuserid'], $key['apikey'], 'corp');
        $contacts = $pheal->ContactList(array('characterID' => $character));
        $positives = array();
        foreach($contacts->corporateContactList as $contact)
        {
            // accumulate postive standings
            if($contact->standing > 0)
                $positives[$contact->contactID]= (int) $contact->contactID;
        }
        // alliance standings override corp standings
        foreach($contacts->allianceContactList as $contact)
        {
            if($contact->standing > 0)
            {
                $positives[$contact->contactID]= (int) $contact->contactID;
            } else {
                // negative standings, we only need those if corp has positive, but alliance negative
                if(isset($positives[$contact->contactID]))
                    unset($positives[$contact->contactID]);
            }
        }
        print_r($positives);
    }
}