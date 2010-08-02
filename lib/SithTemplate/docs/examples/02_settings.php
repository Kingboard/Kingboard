<?php
require_once 'SithTemplate.php';

// You can change default settings during TemplateEnviron construction,
// by passing associative array to the constructor.
$environ = new TemplateEnviron(array(
 'inputPrefix'  => './templates/',
 'outputPrefix' => './templates_c/',
));

// You can also load settings from INI file, using static named constructor
// See sample-configuration.ini for syntax.
$environ = TemplateEnviron::createFromINI('settings.ini');

// Finally, you can change settings in runtime, by modifying settings
// array directly. Note that some settings won't take effect if changed
// in that way. Refer to documentation for more information.
$environ->settings['recompilationMode'] = TemplateEnviron::RECOMPILE_ALWAYS;
