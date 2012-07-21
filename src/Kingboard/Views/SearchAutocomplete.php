<?php
namespace Kingboard\Views;

class SearchAutocomplete extends \Kingboard\Views\Base
{
    public function autocomplete(array $search)
    {
		if(empty($search))
			die();
		$find = $search["text"];
		$result = array();
		foreach(\Kingboard\Model\MapReduce\NameSearch::search($find, 6) as $result)
		{
			$results[] = $result->_id;
		}
		$json = json_encode($results);
		echo $json;
	}
}