<?php
require_once 'SithTemplate.php';

// 1. We create environment
$environ  = new TemplateEnviron;
// 2. Next, we create template object
// Library will take care of the (re)compilation.
// SithTemplate 1.1 introduced unified I/O system,
// which allows you to easily inline small templates in your PHP code.
$template = $environ->get('string://Hello world');
// 3. Finally, we render and display previously created template
// You may notice that display/fetch APIs are gone, replaced by
// generic ones - you need to display template output by yourself.
//
// You can also see that environment object is passed back to the template -
// it is used in several places, like {% include %}-generated code, but passing
// it here, and not during construction, keeps template object more lightweight
// and independent, as it doesn't carry reference to original environment.
// It also eliminates possibility of circular reference, when template object
// is stored in environ's internal cache.
echo $template->render(array(), $environ);

// If you don't want to cache the template object on your own, you can use
// chained calls to cachedGet and render:
$environ->cachedGet('string://Other')->render(array(), $environ);

// If you don't need the object at all, you can call TemplateEnviron::render instead.
// This call is the same as the chained call above, just shorter and less explicit.
$environ->render('string://Other', array());
