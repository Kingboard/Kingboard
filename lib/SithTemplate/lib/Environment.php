<?php
/** @file Environment.php
 Client API of the library
 
 @since 1.1a0
 @author PiotrLegnica
 @license{New BSD License}
*/
/** @page tutorial SithTemplate tutorial
 @section tut-overview Overview
  SithTemplate doesn't require any special installation or initialisation
  procedure. It uses an single entry point - @ref SithTemplate.php file.
  
  Library distinguishes between compilation time, and runtime - some parts
  may be used only in one of them (e.g. plugins - they run only during template compilation).
  
  Your application will typically interact with @ref TemplateEnviron class,
  and its members. This is "public API" or "client API" of the library, which is
  designed to be as simple as possible (I think nobody likes to dig through hundreds
  of pages of documentation, just to find right function to do something). Through this
  API you can create template instances, and retrieve template metadata. It also contains
  internal object cache, if you're lazy and repeatedly rendering same templates.
  
  To create (and compile, if neccessary) the template object, you need to call
  either @ref TemplateEnviron::get or @ref TemplateEnviron::cachedGet.
  It constructs and return an template instance, which is always a
  subclass of the @ref Template class.
  
  Template objects are independent of the @ref TemplateEnviron and context.
  Thus, you can create object once, and render it using different runtime
  environments and variables. To actually render the template, you need to
  call @ref Template::render method on previously constructed object, passing
  context array (containing all variables that template uses) and @ref
  TemplateEnviron object.
  
  You can also render the template using @ref TemplateEnviron::render, in case
  you don't use different environment objects.
  
  @include 00_hello.php
  
 @section tut-io I/O system
  SithTemplate has its own extensible I/O system built-in.
  
  All input and output is handled by so-called "I/O drivers".
  Library itself doesn't know, whether template is loaded/saved to
  filesystem, database or maybe across network. All I/O drivers
  follow @ref ITemplateIODriver interface.
  
  I/O system is transparent - to use non-default driver, all you have
  to do is to use URI-like DSN as template name, wherever it is supported
  (e.g. as @ref TemplateEnviron::get argument). Library may refer to the DSNs
  as "template IDs". If you don't specify the driver (e.g. the part before @c ://),
  a default one will be used (@c defaultIODriver setting, see @ref tut-settings).
  
  @include 01_io.php
  
  If you are interested in creating your own, see @ref extending-st.
  
 @section tut-settings Environment settings
  Every environment has a settings array associated with it. It determines
  library's behaviour during both runtime and compilation time.
  
  The settings array is stored as @ref TemplateEnviron::$settings (and, as
  reference, in @ref TemplateCompilerEx::$settings).
  
  @include 02_settings.php
  
 @section tut-context Variables and context
  A context is an associative array, that contains arbitarily nested scalars,
  objects, and other arrays. Context indexes (i.e. variable names) can be any
  Unicode string that doesn't contain any whitespace, except for "internal",
  "forloop" and "block" which are special variables used by the standard library
  (see @ref stdlib-variables).
  
  %Template variables are placeholders, that get replaced by appropriate values
  from the context array at runtime. They are the most basic template construct -
  without them templates would be quite useless.
  
  This section documents simplest use of template variables -
  in the standalone <code>{{ variable expression }}</code> construct.
  Final result of that expression <strong>MUST</strong> evaluate to a scalar value
  (i.e. can be neither array [PHP would convert it to "Array" string] nor object
  [unless it can be converted to string - see PHP documentation for that]).
  Since this includes filters, it's important that the <strong>last</strong> filter
  in the chain produces scalar.
  
  As mentioned above, variables can be filtered before displaying. This is done by
  appending <strong>filter chain</strong> to the entire expression. Filters can have
  their own arguments, and are separated in the chain by | (also known as pipe).
  Chain is executed in defined order (i.e. left to right), and have no length limit
  (but keep in mind that function calls in PHP are slowish, and filter chains are
  executed at runtime).
  
  @include 03_context.php
  
 @section tut-syntax Template syntax
  SithTemplate uses mostly Django-compatible syntax for templates, and follows its
  philosophy (but with PHP instead of Python as base language), both found in original docs at
  <a href="http://docs.djangoproject.com/en/dev/topics/templates/#topics-templates">
   http://docs.djangoproject.com/en/dev/topics/templates/#topics-templates</a>.
  
  Every template is just a plain text, with special commands for the compiler:
  <ul>
   <li>variables, already described in @ref tut-context</li>
   <li>tags, which control template logic (e.g. conditionals, loops)</li>
   <li>comments</li>
  </ul>
  
  SithTemplate includes a plugin called @c StdLibEx, which implements standard library
  of tags and filters - see @ref stdlib page for reference.
  
  @include 04_syntax.html
  
 @section tut-inheritance Template inheritance
  One of key concepts of SithTemplate is template inheritance, which allows you to
  build hierarchy of templates, e.g. the three-level approach Django docs mentions:
  <ol>
   <li>A base template, containing general layout of the site</li>
   <li>A section template, containing more specific layout bits for the site section</li>
   <li>A detail templates, containing the most specific bits for every page type</li>
  </ol>
  
  %Template inheritance increases maintainability and readability, and the hierarchy feels
  more natural than with e.g. header and footer included in every template (which is also error-prone).
  
  SithTemplate uses so-called "multi-zoned template inheritance", which means that parent template
  defines any number of blocks (just like parent class would define a number of methods), and children
  templates override them with their own content (again, like children classes would override methods),
  optionally including parent block's code within new block (using @ref stdlib-var-block special variable).
  Blocks are created using standard library's @ref stdlib-tag-block tag,
  and inheritance is done by @ref stdlib-tag-extends tag.
  
  An example inheritance (parent and then child):
  
  @include 05_inheritance_parent.html
  @include 06_inheritance_child.html
  
  The result of rendering child template would be
  (actually whitespace would be different, but it's irrelevant here):
  
  @include 07_inheritance_result.html
  
 @section tut-security Security settings in SithTemplate
  SithTemplate includes several settings that are referred to as
  "security settings", and implemented by the compiler and the standard library.
  These include variable autoescaping (using @ref stdlib-filter-escape filter), I/O restrictions
  (if used, templates will be bound to the originally used I/O driver), element
  whitelists and blacklists (you can sandbox templates by restricting access to plugins, tags,
  filters, and plain PHP functions), and @ref stdlib-var-internal access restrictions.
  See @ref TemplateEnviron::$settings for reference.
  
  @include 08_security.php
  
 @section tut-errors Error handling
  SithTemplate uses PHP exception mechanism to report errors
  (and standard @c trigger_error to report warnings). It uses single exception class -
  @ref TemplateError, which defines several class constants that indicates error groups.
  
  @include 09_errors.php
  
*/
/** @example 00_hello.php
 An "hello world" example, showing how to create template environment
 with default settings, template object using string I/O, and render it.
*/
/** @example 01_io.php
 An example showing different default I/O drivers.
*/
/** @example 02_settings.php
 An example showing how to change default library settings.
*/
/** @example 03_context.php
 An example showing how to create and use template context.
*/
/** @example 04_syntax.html
 Example template showing syntax rules.
*/
/** @example 05_inheritance_parent.html
 Inheritance example - parent template.
*/
/** @example 06_inheritance_child.html
 Inheritance example - child (inheriting) template.
*/
/** @example 07_inheritance_result.html
 Inheritance example - rendered child template.
*/
/** @example 08_security.php
 An example showing various security-related settings in
 SithTemplate.
*/
/** @example 09_errors.php
 An example showing error handling.
*/

/**
 %Template environment - library's end-user API.
*/
class TemplateEnviron {
 /**
  One of recompilation modes - always recompile.
 */
 const RECOMPILE_ALWAYS     = 1;
 /**
  One of recompilation modes - recompile only when necessary (default).
 */
 const RECOMPILE_IF_CHANGED = 0;
 /**
  One of recompilation modes - never recompile (a.k.a. performance mode).
 */
 const RECOMPILE_NEVER      = -1;
 
 /**
  One of security modes - do not test against the lists.
 */
 const SECURITY_DISABLE     = 0;
 /**
  One of security modes - first allow all, then check 'disallowed' list.
 */
 const SECURITY_ALLOW_ALL   = 1;
 /**
  One of security modes - first check 'allowed' list, then 'disallowed'.
 */
 const SECURITY_ALLOW_DENY  = 2;
 /**
  One of security modes - first check 'disallowed' list, then 'allowed'.
 */
 const SECURITY_DENY_ALLOW  = 3;
 /**
  One of security modes - first disallow all, then check 'allowed' list.
 */
 const SECURITY_DENY_ALL    = 4;
 
 /**
  May be used instead of @c allowed or @c disallowed list, as a wildcard
  matching everything. Implemented for greater flexibility than only
  hardcoded modes specified above.
 */
 const SECURITY_MATCH_EVERYTHING = true;
 
 /**
  May be used as @c loadPlugins setting to always load all available plugins on
  all search paths.
 */
 const LOAD_ALL_PLUGINS = true;
 
 /**
  Default environment settings. Available settings are:
  
  <ul>
   <li>
    @c inputPrefix (string) - will be prefixed to all input filenames.
    Interpretation is up to the I/O driver. In bundled @c 'file' I/O:
    source directory name. In bundled @c 'string' I/O: not used.
    By default it's @c './templates/'.
   </li>
   <li>
    @c outputPrefix (string) - will be prefixed to all output filenames.
    Interpretation is up to the I/O driver. In both bundled I/O drivers
    (@c 'file' and @c 'string'): output directory name.
    By default it's @c './templates_c/'.
   </li>
   <li>
    @c loadPlugins (array) - if it's an array: list of plugins to load when compilation starts.
    Plugins are not loaded until compilation is required.
   </li>
   <li>
    @c loadPlugins (bool) - if it's a boolean (value is not checked, but
    you should use @ref TemplateEnviron::LOAD_ALL_PLUGINS for better self-documentation):
    library will gather and load all plugins, on every path given in @c pluginsPaths.
    It's also a default behaviour.
   </li>
   <li>
    @c pluginsPaths (array) - plugins' search paths. When plugin is loaded, all paths given
    in this array are searched for plugin's file. See @ref extending-st for more information about
    plugins.
   </li>
   <li>
    @c useDefaultPluginsPath (bool) - determines whether default plugins' search path
    (i.e. @c SITHTEMPLATE_DIR/plugins/) should be used. Note that it works only on
    construction (by appending to @c pluginsPaths) - if you override @c pluginsPaths later,
    this setting won't have any effect.
    By default it's @c true.
   </li>
   <li>
    @c recompilationMode (int) - controls how recompilation is handled.
    One of @ref TemplateEnviron::RECOMPILE_ALWAYS (templates are recompiled on each request),
    @ref TemplateEnviron::RECOMPILE_IF_CHANGED (templates are recompiled when modified), or
    @ref TemplateEnviron::RECOMPILE_NEVER (templates are compiled once and never recompiled).
    By default it's @c RECOMPILE_IF_CHANGED.
   </li>
   <li>
    @c defaultIODriver (string) - default I/O driver to use. Note that you must register
    it using @ref TemplateIO::register before you request any template.
   </li>
   <li>
    @c autoEscape (bool) - should variables not marked with pseudofilter @c safe be automatically
    escaped, using @c StdLibEx filter @c escape? Do not enable, if you do not use @c StdLibEx.
    Disabled by default.
   </li>
   <li>
    @c allowInternalRequest (bool) - should access to super-globals be allowed through
    <code>{{ internal.request }}</code>? Enabled by default.
   </li>
   <li>
    @c allowInternalConstants (bool) - should access to global constants be allowed through
    <code>{{ internal.const }}</code>? Enabled by default.
   </li>
   <li>
    @c restrictIncludeIO (bool) - should all {% include %} calls be restricted to the same
    I/O driver used in @ref TemplateEnviron::get or @ref TemplateEnviron::cachedGet?
    Disabled by default. Note that it is only enforced at runtime.
   </li>
   <li>
    @c restrictExtendIO (bool) - should all {% extend %} calls be restricted to the same
    I/O driver used in @ref TemplateEnviron::get or @ref TemplateEnviron::cachedGet?
    Disabled by default. Note that it is only enforced at compile time.
   </li>
   <li>
    @c securityEvalMode (int) - specifies whether and how all plugins, tags, filters and
    function calls should be tested against security (allow/disallow) lists. One of
    @ref TemplateEnviron::SECURITY_DISABLE, @ref TemplateEnviron::SECURITY_ALLOW_ALL,
    @ref TemplateEnviron::SECURITY_ALLOW_DENY, @ref TemplateEnviron::SECURITY_DENY_ALLOW,
    @ref TemplateEnviron::SECURITY_DENY_ALL. Default is @c SECURITY_DISABLE.
   </li>
   <li>
    @c allowedPlugins (array) - whitelist of entire plugins.
    You can also use @ref TemplateEnviron::SECURITY_MATCH_EVERYTHING.
   </li>
   <li>
    @c disallowedPlugins (array) - blacklist of entire plugins.
    You can also use @ref TemplateEnviron::SECURITY_MATCH_EVERYTHING.
   </li>
   <li>
    @c allowedTags (array) - whitelist of single tags.
    You can also use @ref TemplateEnviron::SECURITY_MATCH_EVERYTHING.
   </li>
   <li>
    @c disallowedTags (array) - blacklist of single tags.
    You can also use @ref TemplateEnviron::SECURITY_MATCH_EVERYTHING.
   </li>
   <li>
    @c allowedFilters (array) - whitelist of single filters.
    You can also use @ref TemplateEnviron::SECURITY_MATCH_EVERYTHING.
   </li>
   <li>
    @c disallowedFilters (array) - blacklist of single filters.
    You can also use @ref TemplateEnviron::SECURITY_MATCH_EVERYTHING.
   </li>
   <li>
    @c allowedFunctions (array) - whitelist of single functions (used in <code>{% call %}</code>).
    You can also use @ref TemplateEnviron::SECURITY_MATCH_EVERYTHING.
    Note that these are the only lists that have effect not only during compilation,
    but also on runtime.
   </li>
   <li>
    @c disallowedFunctions (array) - blacklist of single functions (used in <code>{% call %}</code>).
    You can also use @ref TemplateEnviron::SECURITY_MATCH_EVERYTHING.
    Note that these are the only lists that have effect not only during compilation,
    but also on runtime.
   </li>
  </ul>
 */
 public $settings = array(
  // [Prefixes]
  'inputPrefix'            => './templates/',
  'outputPrefix'           => './templates_c/',
  
  // [Plugins]
  'loadPlugins'            => self::LOAD_ALL_PLUGINS,
  'pluginsPaths'           => array(),
  'useDefaultPluginsPath'  => true,
  
  // [Compilation]
  'recompilationMode'      => self::RECOMPILE_IF_CHANGED,
  'defaultIODriver'        => 'file',
  
  // [Security]
  'autoEscape'             => false,
  'allowInternalRequest'   => true,
  'allowInternalConstants' => true,
  'restrictIncludeIO'      => false,
  'restrictExtendIO'       => false,
  
  // [Security Lists]
  'securityEvalMode'       => self::SECURITY_DISABLE,
  'allowedPlugins'         => self::SECURITY_MATCH_EVERYTHING,
  'disallowedPlugins'      => array(),
  'allowedTags'            => self::SECURITY_MATCH_EVERYTHING,
  'disallowedTags'         => array(),
  'allowedFilters'         => self::SECURITY_MATCH_EVERYTHING,
  'disallowedFilters'      => array(),
  'allowedFunctions'       => self::SECURITY_MATCH_EVERYTHING,
  'disallowedFunctions'    => array(),
 );
 
 /**
  Internal template objects cache.
 */
 public $templateCache = array();
 
 /**
  Compiler instance. Created when compilation is required.
 */
 public $compiler = null;
 
 /**
  Constructor. Optionally sets up initial settings.
  
  @param[in] $settings Settings array
  @sa TemplateEnviron::$settings
 */
 public function __construct(array $settings = array()) {
  $this->settings = array_merge($this->settings, $settings);
  if ($this->settings['useDefaultPluginsPath']) {
   $this->settings['pluginsPaths'][] = SITHTEMPLATE_DIR.'plugins/';
  }
 }
 
 /**
  Named constructor. Shorthand for INI parsing.
  
  @param[in] $settingsINI Settings INI filename
  @return @ref TemplateEnviron instance
  @sa TemplateEnviron::__construct
 */
 public static function createFromINI($settingsINI) {
  return new TemplateEnviron(parse_ini_file($settingsINI));
 }
 
 /**
  Compile given template. This doesn't check @c recompilationMode setting,
  so it may be used to forcibly recompile template.
  It doesn't use DSN - you must provide correct I/O driver object.
  
  @param[in] $io I/O driver
  @param[in] $template %Template name
 */
 public function compile(ITemplateIODriver $io, $template) {
  if (!$this->compiler) {
   $this->compiler           = new TemplateCompilerEx;
   $this->compiler->settings = &$this->settings;
  }
  
  $this->compiler->compile($io, $template);
 }
 
 /**
  Includes template's code into global namespace via I/O driver given in DSN.
  I/O system checks @c recompilationMode (which you may override per-template using @c $mode parameter) and
  acts accordingly, recompiling only when it's required either by this setting or template change.
  Also recursively handles inclusion of template parent.
  
  @param[in] $template %Template name
  @param[in] $mode Per-template recompilation mode override (optional)
  @param[in] $returnMeta If @c true, then returns metadata instead of including the code
  @return Template's class name, as string; or metadata, as array
 */
 public function include_($template, $mode = null, $returnMeta = false) {
  if (!$mode) $mode = $this->settings['recompilationMode'];
  list($io, $template) = TemplateUtils::parseIODSN($this->settings, $template);

  if (!$io->upToDate($this->settings, $template, $mode)) {
   $this->compile($io, $template);
  }
  
  $metadata = $io->loadMetadata($this->settings, $template);
  if ($returnMeta) return $metadata;
  
  if (is_array($metadata) && isset($metadata['parentTemplate'])) {
   $this->include_($metadata['parentTemplate'], $mode);
  }
  
  return $io->includeCode($this->settings, $template);
 }
 
 /**
  Returns template instance. Keep in mind that it doesn't cache template objects - every call will
  result in object construction, which may lead to performance loss. If you want to use internal
  object cache, use @ref TemplateEnviron::cachedGet.
  
  @param[in] $template %Template ID
  @param[in] $mode Per-template recompilation mode override (optional)
  @return @ref Template subclass instance - an template object
  @sa TemplateEnviron::include_
  @sa TemplateEnviron::cachedGet
 */
 public function get($template, $mode = null) {
  $className = $this->include_($template, $mode);
  return new $className;
 }
 
 /**
  Returns user-defined template metadata. It will trigger the compilation, if neccessary.
  Keep in mind, that the core doesn't cache the metadata. Every call will result in I/O,
  array unserialization and array filtering.
  
  @param[in] $template %Template ID
  @param[in] $mode Per-template recompilation mode override (optional)
  @return Metadata array
 */
 public function getMeta($template, $mode = null) {
  $metadata = $this->include_($template, $mode, true);
  $userMeta = array();
  
  foreach ($metadata as $var => &$value) {
   if (mb_substr($var, 0, 5) == 'user:') {
    $userMeta[mb_substr($var, 5)] = $value;
   }
  }
  
  return $userMeta;
 }
 
 /**
  Cached version of @ref TemplateEnviron::get.
  
  @param[in] $template %Template ID
  @param[in] $mode Per-template recompilation mode override (optional)
  @return @ref Template subclass instance - an template object
  @sa TemplateEnviron::$templateCache
 */
 public function cachedGet($template, $mode = null) {
  if (!isset($this->templateCache[$template])) {
   $this->templateCache[$template] = $this->get($template, $mode);
  }
  return $this->templateCache[$template];
 }
 
 /**
  Render the template directly. Uses internal cache.
  
  @param[in] $template %Template ID
  @param[in] $context Context array
  @param[in] $mode Per-template recompilation mode override (optional)
  @sa Template::render
  @sa TemplateEnviron::cachedGet
 */
 public function render($template, array $context, $mode = null) {
  return $this->cachedGet($template, $mode)->render($context, $this);
 }
}