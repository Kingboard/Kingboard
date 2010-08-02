<?php
/** @file IO.php
 I/O management, and default I/O drivers.
 
 @since 1.1a0
 @author PiotrLegnica
 @license{New BSD License}
*/

/**
 Global I/O driver storage.
*/
class TemplateIO {
 /**
  I/O driver registry.
 */
 private static $ioDrivers = array(
  // name => array(class, instance)
  'file'     => array('TemplateFileIO',     null),
  'string'   => array('TemplateStringIO',   null),
 );
 
 /**
  Returns (creates if necessary) an I/O driver instance.
  
  @param[in] $driver I/O driver name
  @return @c ITemplateIODriver implementation
 */
 public static function get($driver) {
  if (!isset(self::$ioDrivers[$driver])) {
   throw new TemplateError(
    'Unknown I/O driver: "'.$driver.'"',
    TemplateError::E_UNKNOWN_PLUGIN
   );
  }
  
  if (!self::$ioDrivers[$driver][1]) {
   $klass = self::$ioDrivers[$driver][0];
   
   if (!class_exists($klass)) {
    throw new TemplateError(
     'Class "'.$klass.'" registered for I/O driver "'.$driver.'" does not exist',
     TemplateError::E_INVALID_PLUGIN
    );
   }
   
   self::$ioDrivers[$driver][1] = new $klass;
   self::$ioDrivers[$driver][1]->driverID = $driver;
   
   if (!(self::$ioDrivers[$driver][1] instanceof ITemplateIODriver)) {
    throw new TemplateError(
     'I/O driver "'.$driver.'" does not implement ITemplateIODriver',
     TemplateError::E_INVALID_PLUGIN
    );
   }
  }
  
  return self::$ioDrivers[$driver][1];
 }
 
 /**
  Registers new I/O driver. New driver may override existing one.
  
  @param[in] $driver I/O driver name
  @param[in] $className I/O driver class
 */
 public static function register($driver, $className) {
  if (mb_strpos($driver, '://') !== false) {
   throw new TemplateError('I/O driver name cannot contain "://"', TemplateError::E_INVALID_ARGUMENT);
  }
  self::$ioDrivers[$driver] = array($className, null);
 }
}

/**
 File I/O implementation.
*/
class TemplateFileIO implements ITemplateIODriver {
 /** @internal
  Returns template-related pathnames.
  
  @return Array(input file, code output file, metadata output file)
 */
 protected function pfn(array &$settings, &$template) {
  $out = $settings['outputPrefix'].'tpl.'.$template;
  return array(
   $settings['inputPrefix'].$template,
   $out.'.code.php', $out.'.metadata'
  );
 }
 
 /** @copydoc ITemplateIODriver::upToDate */
 public function upToDate(array &$settings, &$template, $mode) {
  list($input, $codeOutput, $metaOutput) = $this->pfn($settings, $template);
  
  if (!file_exists($input)) {
   throw new TemplateError(
    'Template "'.$input.'" does not exist. Check for prefix correctness and filename misspellings',
    TemplateError::E_IO_LOAD_FAILURE
   );
  }
  
  if ($mode == TemplateEnviron::RECOMPILE_ALWAYS || !file_exists($codeOutput) || !file_exists($metaOutput)) {
   // if either metadata or compiled code is missing, then recompilation is necessary
   // if mode is RECOMPILE_ALWAYS then no checks are necessary
   return false;
  } elseif ($mode == TemplateEnviron::RECOMPILE_NEVER) {
   // if both output file exist then compilation is not required
   return true;
  } elseif ($mode == TemplateEnviron::RECOMPILE_IF_CHANGED) {
   // we know that file exists, but we don't know whether source has changed since last compilation
   return (filemtime($codeOutput) >= filemtime($input));
  } else {
   throw new TemplateError(
    'Invalid recompilation mode set ('.$mode.') for template "'.$template.'"',
    TemplateError::E_INVALID_ARGUMENT
   );
  }
 }
 
 /** @copydoc ITemplateIODriver::includeCode */
 public function includeCode(array &$settings, &$template) {
  $className = $this->className($settings, $template);
  if (class_exists($className)) return $className;
  
  list($input, $codeOutput,) = $this->pfn($settings, $template);
  include_once $codeOutput;
  
  if (!class_exists($className)) {
   throw new TemplateError(
    'Compiled template "'.$codeOutput.'" is damaged (class "'.$className.'" does not exist) - recompile.',
    TemplateError::E_IO_LOAD_FAILURE
   );
  }
  
  return $className;
 }
 
 /** @copydoc ITemplateIODriver::className */
 public function className(array &$settings, &$template) {
  return TemplateUtils::className($template);
 }
 
 /** @copydoc ITemplateIODriver::loadTemplate */
 public function loadTemplate(array &$settings, &$template) {
  list($input,,) = $this->pfn($settings, $template);
  return file_get_contents($input);
 }
 
 /** @copydoc ITemplateIODriver::loadMetadata */
 public function loadMetadata(array &$settings, &$template) {
  list($input,,$metaOutput) = $this->pfn($settings, $template);
  return unserialize(file_get_contents($metaOutput));
 }
 
 /** @copydoc ITemplateIODriver::saveTemplate */
 public function saveTemplate(array &$settings, &$template, &$code) {
  list($input,$codeOutput,) = $this->pfn($settings, $template);
  return file_put_contents($codeOutput, $code, LOCK_EX);
 }
 
 /** @copydoc ITemplateIODriver::saveMetadata */
 public function saveMetadata(array &$settings, &$template, array &$metadata) {
  list($input,,$metaOutput) = $this->pfn($settings, $template);
  return file_put_contents($metaOutput, serialize($metadata), LOCK_EX);
 }
}

/**
 String I/O implementation.
*/
class TemplateStringIO extends TemplateFileIO {
 protected function pfn(array &$settings, &$template) {
  $templateHash = crc32($template);
  $result       = parent::pfn($settings, $templateHash);
  $result[0]    = $settings['outputPrefix'].'tpl.'.$templateHash.'.source';
  
  if (!file_exists($result[0])) {
   file_put_contents($result[0], $template, LOCK_EX);
  }
  
  return $result;
 }
 
 public function upToDate(array &$settings, &$template, $mode) {
  list($input, $codeOutput, $metaOutput) = $this->pfn($settings, $template);
  return (
   $mode != TemplateEnviron::RECOMPILE_ALWAYS &&
   file_exists($codeOutput) && file_exists($metaOutput)
  );
 }
 
 public function includeCode(array &$settings, &$template) {
  $className = TemplateUtils::className(crc32($template));
  if (class_exists($className)) return $className;

  list($input, $codeOutput,) = $this->pfn($settings, $template);
  include_once $codeOutput;
  
  if (!class_exists($className)) {
   throw new TemplateError(
    'Compiled template "'.$codeOutput.'" is damaged (class "'.$className.'" does not exist) - recompile.',
    TemplateError::E_IO_LOAD_FAILURE
   );
  }
  
  return $className;
 }
 
 public function className(array &$settings, &$template) {
  return TemplateUtils::className(crc32($template));
 }
}
