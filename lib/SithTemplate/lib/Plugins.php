<?php
/** @file Plugins.php
 Contains plugin machinery.

 @since 1.1a0
 @author PiotrLegnica
 @license{New BSD License}
*/

/**
 Handles discovery, registration and utilization of plugins.
*/
class TemplatePlugins {
 /**
  Already loaded plugins.
 */
 private $plugins    = array();
 /**
  Elements registry.
 */
 private $elements   = array();
 /**
  Plugins' search paths. Reference to @c 'pluginsPaths' key of @ref TemplateEnviron::$settings.
 */
 public $searchPaths = array();
 
 /**
  Constructor. Also handles registration of built-ins.
  
  @param[in] $builtins Built-in elements to register
 */
 public function __construct(array $builtins) {
  $this->elements = $builtins;
 }
 
 /**
  Load single plugin.
  
  @param[in] $compiler @ref TemplateCompilerEx instance
  @param[in] $node Optional @ref TemplateNodeEx instance
  @param[in] $plugin Plugin name
  @param[in] $pluginFile Plugin's full filename (optional)
  @param[in] $noThrow If @c true, no exception will be thrown on error (boolean @c false will be returned)
  @retval true Plugin has been loaded
  @retval false Plugin is invalid/non-existant
 */
 public function load(TemplateCompilerEx $compiler, $node, $plugin, $pluginFile = null, $noThrow = false) {
  // if already loaded, then abort
  if (isset($this->plugins[$plugin])) return;
  
  // sanity check
  TemplateUtils::checkIfAllowed($compiler, 'plugin', $plugin, $node);
  if (!$pluginFile) $pluginFile = $this->findPlugin($plugin);
  $className = 'Template'.$plugin.'Plugin';
  
  if (
   $pluginFile === false     || (!include_once $pluginFile) ||
   !class_exists($className) || !TemplateUtils::doesImplement($className, 'ITemplatePlugin')
  ) {
   if ($noThrow) return false;
   
   throw new TemplateError(
    'Could not load plugin: "'.$plugin.'". Either file or main class does not exists, or is invalid',
    TemplateError::E_INVALID_PLUGIN
   );
  }
  
  // create instance and register handlers
  $pluginObj = new $className;
  $handlers  = $pluginObj->providedHandlers();
  if (!isset($handlers['tags']))    $handlers['tags']    = array();
  if (!isset($handlers['filters'])) $handlers['filters'] = array();
  if (!isset($handlers['hooks']))   $handlers['hooks']   = array();
  
  $this->register($plugin, $pluginObj, 'tag',    $handlers['tags']);
  $this->register($plugin, $pluginObj, 'filter', $handlers['filters']);
  // too special case after all
  $this->registerHooks($plugin, $pluginObj, $handlers['hooks']);
  
  $this->plugins[$plugin] = $pluginObj;
 }
 
 /**
  Load multiple plugins.
  
  @param[in] $compiler @ref TemplateCompilerEx instance
  @param[in] $node Optional @ref TemplateNodeEx instance
  @param[in] $plugins Array (plugins to look for) or boolean ('load all plugins' mode)
 */
 public function loadMultiple(TemplateCompilerEx $compiler, $node, $plugins) {
  foreach ($this->findPlugins($plugins) as $plugin => $pluginFile) {
   $this->load($compiler, $node, $plugin, $pluginFile);
  }
 }
 
 /**
  Check whether given element is registered.
  
  @param[in] $type Element type (@c 'tag', @c 'filter', @c 'hook' or @c 'plugin')
  @param[in] $name Element name
  @retval true Element is registered
  @retval false Element is not registered
 */
 public function known($type, $name) {
  if ($type == 'plugin') return isset($this->plugins[$name]);
  return isset($this->elements[$type.'s'][$name]);
 }
 
 /**
  Returns handler(s) information for given element.
  Doesn't check for element's existence.
  
  @param[in] $type Element type (@c 'tag', @c 'filter' or @c 'hook')
  @param[in] $name Element name
  @return Assoc. array
 */
 public function &get($type, $name) {
  return $this->elements[$type.'s'][$name];
 }
 
 /**
  Looks for plugin file on all search paths.
  
  @param[in] $plugin Plugin name (as string)
  @return Filename or @c false
  @sa TemplatePlugins::load
 */
 private function findPlugin($plugin) {
  $found = false;
  
  foreach ($this->searchPaths as &$path) {
   if (file_exists($path.$plugin.'.plugin.php')) {
    $found = $path.$plugin.'.plugin.php';
    break;
   }
  }
  
  return $found;
 }
 
 /**
  Looks for multiple plugins' files on all search paths.
  
  @param[in] $plugins Plugins (array or boolean)
  @return Assoc. array (plugin => file)
  @sa TemplatePlugins::loadMultiple
 */
 private function findPlugins($plugins) {
  $found = array();
 
  foreach ($this->searchPaths as &$path) {
   if (is_bool($plugins)) {
    foreach (glob($path.'*.plugin.php') as $file) {
     $plugin = mb_substr(pathinfo($file, PATHINFO_BASENAME), 0, -11);
     $found[$plugin] = $file;
    }
   } else {
    foreach ($plugins as &$plugin) {
     if (isset($found[$plugin])) continue;
    
     if (file_exists($path.$plugin.'.plugin.php')) {
      $found[$plugin] = $path.$plugin.'.plugin.php';
     }
    }
   }
  }
  
  
  if (is_array($plugins) && ($notFound = array_diff($plugins, array_keys($found)))) {
   throw new TemplateError(
    'Several of requested plugins have not been found: "'.implode(', ', $notFound).'"',
    TemplateError::E_UNKNOWN_PLUGIN
   );
  }
  return $found;
 }
 
 /**
  Registers given elements.
  
  @param[in] $plugin Plugin name
  @param[in] $pluginObj Plugin instance
  @param[in] $type Element type (@c 'tag' or @c 'filter')
  @param[in,out] $handlers Array of handlers
 */
 private function register($plugin, ITemplatePlugin $pluginObj, $type, array &$handlers) {
  foreach ($handlers as $element => &$elementInfo) {
   if (isset($this->elements[$type.'s'][$element])) {
    throw new TemplateError(
     'Element collision while loading plugin "'.$plugin.'" - '.$type.' "'.$element.'" already exists',
     TemplateError::E_INVALID_HANDLER
    );
   }
   
   $elementInfo['plugin'] = $plugin;
   if (!is_array($elementInfo['handler']) && !isset($elementInfo['standalone'])) {
    $elementInfo['handler'] = array($pluginObj, $elementInfo['handler']);
   }
   
   $this->elements[$type.'s'][$element] = $elementInfo;
  }
 }
 
 /**
  Register given hooks.
  
  @param[in] $plugin Plugin name
  @param[in] $pluginObj Plugin instance
  @param[in,out] $hooks Array of hooks
 */
 private function registerHooks($plugin, ITemplatePlugin $pluginObj, array &$hooks) {
  $allHooks = &$this->elements['hooks'];
  foreach ($hooks as $hook => &$handlers) {
   if (!isset($allHooks[$hook])) $allHooks[$hook] = array();
   
   foreach ($handlers as &$handler) {
    $handler['plugin'] = $plugin;
    
    if (!is_array($handler['handler']) && !isset($handler['standalone'])) {
     $handler['handler'] = array($pluginObj, $handler['handler']);
    }
    
    $allHooks[$hook][] = $handler;
   }
  }
 }
}
