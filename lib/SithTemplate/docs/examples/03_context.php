<?php
require_once 'SithTemplate.php';

$environ = new TemplateEnviron;

// Context array is passed as first argument to Template::render, or as second
// argument to TemplateEnviron::render.
$tpl = $environ->get('string://{{ foo }} ');

echo $tpl->render(array('foo' => 'first'),  $environ);
echo $tpl->render(array('foo' => 'second'), $environ);
// Will produce: "first second "

// Above is the simplest variable expression. To access nested elements, slightly more
// complex syntax is required, presented below, with equivalent PHP code:
//
// - accessing a named array element
//   {{ foo.bar }} is equivalent to $context['foo']['bar']
// - accessing a numeric array index
//   {{ foo.42 }} is equivalent to $context['foo'][42]
// - accessing a named or numeric array index, using value of another variable as key
//   {{ foo.[bar] }} is equivalent to $context['foo'][$context['bar']]
//
// Same syntax rules applies to object properties - you just use -> operator instead of ., e.g.
// {{ foo->bar }}.
//
// This syntax allows you to create very complex constructs, like:
//  {{ [one->[two]].three->four }} which is equivalent to
//  $context[ $context['one']->{$context['two']} ]['three']->four
//
// SithTemplate by default generates code to check whether variable really exists in the context
// before it is used, which triggers E_USER_WARNING if it doesn't. This can interfere with "optional"
// variables (e.g. ones used with 'default' filter). You can tell compiler to omit this code, by prefixing
// entire expression with @ sign:
//  {{ @non-existant-variable }}

// Filter chains are built with pipe operator. Filter arguments are comma-separated, passed after colon.
//  {{ variable|filter1|filter2:variable2,"foo" }}
// is roughly equivalent (if filters were simply functions) to
//  filter2(filter1($context['variable']), $context['variable2'], 'foo')
