<?php
require_once 'SithTemplate.php';

$environ = new TemplateEnviron;

// All security settings are set using environment's setting array.
// Some of them may be enforced at runtime, and some at compile time,
// see TemplateEnviron::$settings documentation for reference.

// The most common is variable autoescaping, which applies "escape" filter
// to all stand-alone variables (i.e. {{ vars }}), unless they are marked
// with "safe" pseudofilter.
// Autoescaping is turned on with "autoEscape" boolean setting.
$environ->settings['autoEscape'] = true;
$environ->render('string://{{ var }}', array('var' => '<b>'));      // will return "&lt;b&gt;"
$environ->render('string://{{ var|safe }}', array('var' => '<b>')); // will return "<b>"

// Next, there are I/O restriction settings. They allow you to enforce specific I/O driver,
// e.g. when you load template using your own db:// driver, and you don't want loaded template
// to use any other I/O driver, like file:// or string://.
// Note that this is a bit primitive, and may be replaced sometime in the future.
// I/O restrictions are turned on by "restrictIncludeIO" and "restrictExtendIO" boolean settings.
$environ->settings['restrictIncludeIO'] = true;
$environ->render('string://{% include "string://test" %}', array());    // will return "test"
$environ->render('string://{% include "file://test.html" %}', array()); // will raise TemplateError

// Next, there are {{ internal }} access restrictions (again, a bit primitive and boolean only).
// Since {{ internal }} allows template to access global constants and superglobal arrays
// (like $_SERVER or $_ENV), it may introduce security risk in sandboxed environment
// (e.g. when templates are loaded from DB, and users can edit them).
// {{ internal }} restrictions can be set by turning off "allowInternalRequest"
// and/or "allowInternalConstants" boolean settings.
// Since this is boolean-only and a bit inconsistent, it may get replaced.
$environ->render('string://{{ internal.request.ENV.PATH.0 }}', array()); // will return $_ENV['PATH'][0]
$environ->settings['allowInternalRequest'] = false;
$environ->render('string://{{ internal.request.ENV.PATH.0 }}', array()); // will raise TemplateError

// Finally, there are security lists, that allows you to handpick plugins, tags, filters and
// plain PHP functions that templates are allowed to use. Lists are the most complex of security
// settings, as they support multiple modes of evaluation (allow all, deny; allow, deny; deny, allow; deny all, allow),
// and wildcards (TemplateEnviron::SECURITY_MATCH_EVERYTHING).
// Evaluation mode is controlled by "securityEvalMode" enumerative setting, and lists themselves
// are stored in several array settings: "allowedPlugins", "allowedTags", "allowedFilters", "allowedFunctions"
// and their "disallowed*" counterparts.
$environ->settings['securityEvalMode'] = TemplateEnviron::SECURITY_DENY_ALL; // most restrictive setting
$environ->settings['allowedTags']      = array('block'); // you don't have to specify ending tags
$environ->render('string://{% block foo %}foo{% endblock %}', array()); // will return "foo"
$environ->render('string://{% comment %}foo{% endcomment %}', array()); // will raise TemplateError
