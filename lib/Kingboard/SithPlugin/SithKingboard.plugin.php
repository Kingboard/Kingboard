<?php
class TemplateSithKingboardPlugin implements ITemplatePlugin
{
    public function providedTags()
    {
        return array();
    }
    public function providedFilters()
    {
        return array(
            'iskm'   => array('handler' => 'handleFISKM', 'minArgs' => 0),
            'iskb'   => array('handler' => 'handleFISKB', 'minArgs' => 0),
            'isk'    => array('handler' => 'handleFISK', 'minArgs' => 0),
            'round'  => array('handler' => 'handleRound', 'minArgs' => 1)
        );
    }
    public function providedHooks()
    {
        return array();
    }

    public function providedHandlers()
    {
        return array(
            'tags'    => $this->providedTags(),
            'filters' => $this->providedFilters(),
            'hooks'   => $this->providedHooks(),
        );
    }
    public function handleFISKM(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args)
    {
        return 'number_format(%s / 1000000, 2, ".",",") ."M ISK"';
    }

    public function handleFISKB(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args)
    {
        return 'number_format(%s / 1000000000, 2, ".",",") ."B ISK"';
    }

    public function handleFISK(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args)
    {
        return 'number_format(%s, 2, ".",",") ." ISK"';
    }

    public function handleRound(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args)
    {
        var_dump($args);
        return 'number_format(%s, ' .$args[0]. ', ".",",")';
    }
}
