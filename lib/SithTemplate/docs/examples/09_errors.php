<?php
require_once 'SithTemplate.php';

$environ = new TemplateEnviron;

// You should always remember about error handling
// If error occurs during template compilation, exception message
// may contain template file and approx. line of the mistake.

// Errors are grouped - every group has it's own errorcode, specified
// as class constants in TemplateError.
try {
 $environ->render('string://{% bkock foo %}Typos are evil.{% endblock %}', array());
} catch (TemplateError $e) {
 echo $e->getMessage(); // Unknown tag ...
 echo $e->getCode();    // TemplateError::E_UNKNOWN_TAG
}
