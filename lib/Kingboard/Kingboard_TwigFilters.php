<?php
class Kingboard_TwigFilters
{
    public static function register()
    {
                
    }
}

function handleMongoDate($date)
{
    
}
/*
    public function handleMongoDate(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args)
    {
        return 'date("Y-m-d H:i:s", %s->sec)';
    }

    public function handleTruncate(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args)
    {
        if(isset($args[0][1]))
            $len = $args[0][1];
        else $len = 10;
        return 'substr(%s, 0, ' . $len .')';
    }

    public function handleJsonify(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args)
    {
        return 'json_encode(%s)';
    }

    public function handleSpace2Under(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args)
    {
        return 'str_replace(" ", "_", %s)';
    }
}
