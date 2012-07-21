<?php
namespace Kingboard\Views;
class EveInfo extends \Kingboard\Views\Base
{
    public function eveItem(array $data)
    {
        $context = array();
        $context['item'] = \Kingboard\Model\EveItem::getByItemId($data['itemid']);

        // sort attributes by categoryName so we can split 'em up in templates
        $attributes = array();
        foreach($context['item']['Attributes'] as $attribute) {
            // hack to ignore weird NULL category by ccp
            if($attribute['categoryName'] == "NULL") continue;

            // initialize if empty category
            if(!isset($attributes[$attribute['categoryName']]))
                $attributes[$attribute['categoryName']] = array();

            $attributes[$attribute['categoryName']][] = $attribute;
        }
        $context['item']['Attributes'] = $attributes;

        return $this->render('eve_item.html', $context);
    }
}