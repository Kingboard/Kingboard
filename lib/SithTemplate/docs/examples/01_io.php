<?php
require_once 'SithTemplate.php';

$environ = new TemplateEnviron;

// SithTemplate 1.1 comes with two I/O drivers bundled:
//
// - "file" I/O - a traditional template-from-file driver.
//   This driver uses "inputPrefix" as source directory with templates,
//   and "outputPrefix" as cache directory, to store metadata and
//   compiled templates' code.
echo $environ->get('template.html')->render(array(), $environ);
// - "string" I/O, which allows you to inline templates in code.
//   This driver uses only "outputPrefix" setting.
echo $environ->get('string://Hai')->render(array(), $environ);
//
// inputPrefix defaults to ./templates/
// outputPrefix defaults to ./templates_c/
