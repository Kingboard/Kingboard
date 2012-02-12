<?php
class Kingboard_Post_View extends Kingboard_Base_View
{
    public function post($request)
    {
		if(count($_POST) > 0)
			$context = $_POST;
		else
			$context = array();
		$results = null;
		
		if(isset($_POST['killmail']))
		{
			$killmail = $_POST['killmail'];
			try
			{
				$kill = Kingboard_KillmailParser_Factory::parseTextMail($killmail);
				$kill->save();
				$id = $kill['idHash'];
			} catch (Kingboard_KillmailParser_KillmailErrorException $e) {
				echo "You have an error in your killmail: {$e->getMessage()}\n";
			}
			if(!empty($kill))
			{
				foreach($kill['attackers'] as $attacker)
				{
					if(!isset($stats[$attacker['allianceName']]))
						$stats[$attacker['allianceName']] = array();
					if(!isset($stats[$attacker['allianceName']][$attacker['corporationName']]))
						$stats[$attacker['allianceName']][$attacker['corporationName']] = 0;
					$stats[$attacker['allianceName']][$attacker['corporationName']]++;  
				}
				ksort($stats);
				$kill['stats'] = $stats;
				if(empty($kill['stats'])) { echo "ERROR!"; die();}
				$this->redirect("/kill/$id/");
			}
		}
        return $this->render('post/index.html', $context);
    }
}