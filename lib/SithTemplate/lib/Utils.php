<?php
/** @file Utils.php
 Utilities used throughout SithTemplate.
 
 @license{New BSD License}
 @author PiotrLegnica
*/

/**
 Namespace-acting all-static class.
*/
class TemplateUtils {
 /**
  Escape string to use in template class.
  
  @param[in] $str String
  @return Escaped string
 */
 public static function escape($str) {
  $str = str_replace('\'', '\\\'', $str);
  $str = str_replace(
   array("\n", "\r", "\t"),
   array('\'."\\n".\'', '\'."\\r".\'','\'."\\t".\''),
   $str
  );
  return $str;
 }
 
 /**
  Sanitize string, for use as function name.
 
  @param[in] $str String
  @return Sanitized string
 */
 public static function sanitize($str) {
  return preg_replace('/([^a-z0-9\_])/ui', '_', $str);
 }
 
 /**
  Strip newlines and spaces from string.
  
  @param[in] $str String
  @return Stripped string
 */
 public static function strip($str) {
  return str_replace(array("\n", ' '), '', $str);
 }
 
 /**
  Split string into two.
  
  @param[in] $separator Separator
  @param[in] $str String to split
  @param[in] $reverse Use reversed search
  @return Array
 */
 public static function split($separator, $str, $reverse = false) {
  $reverse = ($reverse ? 'mb_strrpos' : 'mb_strpos');
  $offset = mb_strlen($separator);
  $separator = $reverse($str, $separator);
  if ($separator === false) {
   return array($str, '');
  }
  return array(
   mb_substr($str, 0, $separator),
   mb_substr($str, $separator+$offset)
  );
 }
 
 /**
  Properly splits given expression using given delimiter.
  Supports string delimiter escaping (@c \").

  @param[in] $delimiter Delimiter to use
  @param[in] $expression Expression to split
  @return Split expression
  @todo Better way?
 */
 public static function splitEscaped($delimiter, $expression) {
  $splitChunks   = array();
  $currentChunk  = '';
  $insideString  = false;
  $expressionLen = mb_strlen($expression);

  if (mb_strlen($delimiter) > 1) {
   self::panic(__FILE__, __LINE__);
  }

  for ($currentIdx = 0; $currentIdx < $expressionLen; ++$currentIdx) {
   $currentChar = mb_substr($expression, $currentIdx, 1);
   $nextChar    = mb_substr($expression, $currentIdx + 1, 1);

   if ($currentChar == '"') {
    $currentChunk .= $currentChar;
    $insideString  = !$insideString;
   } elseif ($insideString && $currentChar == '\\' && $nextChar == '"') {
    // escaped "
    $currentChunk .= $nextChar;
    ++$currentIdx;
   } elseif (!$insideString && $currentChar == $delimiter) {
    // delimiter
    $splitChunks[] = $currentChunk;
    $currentChunk  = '';
   } else {
    $currentChunk .= $currentChar;
   }
  }
  
  $splitChunks[] = $currentChunk;
  
  // array_values used for keys to be reset
  return array_values(array_filter($splitChunks, array('TemplateUtils', 'filterEmpty')));
 }
 
 /** @internal
  Used as @c array_filter predicate, rejects empty chunks.
 */
 public static function filterEmpty($x) { return $x !== ''; }
 
 /**
  Check whether class implements given interface
  
  @param[in] $classOrObject Mixed
  @param[in] $interface String
  @return Boolean
 */
 public static function doesImplement($classOrObject, $interface) {
  return in_array($interface, class_implements($classOrObject));
 }
 
 /**
  Splits I/O DSN into driver name and template name.
  
  @param[in] $settings Settings array
  @param[in] $dsn DSN to split
  @return Array(driver name, template name)
 */
 public static function splitIODSN(array &$settings, $dsn) {
  if (mb_strpos($dsn, '://') !== false) {
   return self::split('://', $dsn);
  } else {
   return array($settings['defaultIODriver'], $dsn);
  }
 }
 
 /**
  Splits I/O DSN, and creates correct driver object.
  
  @param[in] $settings Settings array
  @param[in] $dsn DSN to parse
  @return Array(I/O driver, template name)
 */
 public static function parseIODSN(array &$settings, $dsn) {
  list($driver, $template) = self::splitIODSN($settings, $dsn);
  return array(TemplateIO::get($driver), $template);
 }
 
 /**
  Returns class name for given template or DSN. Note that
  only real template name should be used in class name.
  
  @param[in] $template %Template name
  @return Class name
 */
 public static function className($template) {
  if (mb_strpos($template, '://') !== false) self::panic(__FILE__, __LINE__);
  return 'Template_'.self::sanitize($template);
 }
 
 /**
  Panics. Used internally when sanity checks are failing.
  
  @param[in] $file Source filename
  @param[in] $line Source line
 */
 public static function panic($file, $line) {
  throw new TemplateError(
   '!!! PANIC !!! Internal SithTemplate error at '.$file.' on line '.$line,
   TemplateError::E_INTERNAL_CORE_FAILURE
  );
 }
 
 /**
  Checks whether element is allowed. Raises @ref TemplateError if it's not.
  
  @param[in] $obj Instance of @ref TemplateEnviron or @ref TemplateCompilerEx
  @param[in] $type Element type (@c 'plugin', @c 'tag', @c 'filter', @c 'function')
  @param[in] $name Element name
  @param[in] $node Optional instance of @ref TemplateNodeEx
 */
 public static function checkIfAllowed($obj, $type, $name, $node = null) {
  if (!in_array($type, array('plugin', 'tag', 'filter', 'function'))) {
   self::panic(__FILE__, __LINE__);
  }
  
  $isCompiler = ($obj instanceof TemplateCompilerEx);
  $isEnviron  = ($obj instanceof TemplateEnviron);
  
  if (!$isCompiler && !$isEnviron) {
   self::panic(__FILE__, __LINE__);
  }
  
  $settings       = &$obj->settings;
  $mode           = &$settings['securityEvalMode'];
  $whitelist      = array('allowed'.ucwords($type).'s', true);
  $blacklist      = array('dis'.$whitelist[0],          false);
  $allowed        = null;
  $defaultAllowed = null;
  
  switch ($mode) {
   case TemplateEnviron::SECURITY_DISABLE: return true;
   case TemplateEnviron::SECURITY_ALLOW_ALL:
    $lists          = array($blacklist);
    $allowed        = true;
   break;
   case TemplateEnviron::SECURITY_ALLOW_DENY:
    $lists          = array($whitelist, $blacklist);
    $defaultAllowed = true;
   break;
   case TemplateEnviron::SECURITY_DENY_ALLOW:
    $lists          = array($blacklist, $whitelist);
    $defaultAllowed = false;
   break;
   case TemplateEnviron::SECURITY_DENY_ALL:
    $lists          = array($whitelist);
    $allowed        = false;
   break;
   default: self::panic(__FILE__, __LINE__);
  }
  
  foreach ($lists as $list) {
   list($listName, $isWhitelist) = $list;
   $list                         = &$settings[$listName];
   
   if (is_bool($list) || (is_array($list) && in_array($name, $list))) {
    $allowed = $isWhitelist;
    continue;
   }
  }
  
  if (is_null($allowed)) {
   trigger_error(
    'Warning: security eval mode is '.($defaultAllowed ? 'ALLOW_DENY' : 'DENY_ALLOW').
    ' and element "'.$name.'" appeared on neither "'.$whitelist[0].'" nor "'.
    $blacklist[0].'" list. Defaulting to "'.(!$defaultAllowed ? 'dis' : '').'allow".',
    E_USER_WARNING
   );
   $allowed = $defaultAllowed;
  }
  
  if ($allowed) return;
  
  $message = 'Element "'.$name.'" of type "'.$type.'" is not allowed by current security settings';
  $code    = TemplateError::E_SECURITY_VIOLATION;
  
  if ($isCompiler && ($node instanceof TemplateNodeEx)) {
   $obj->raise($node, $message, $code);
  } else {
   throw new TemplateError($message, $code);
  }
 }
 
 /**
  Checks whether I/O restriction is in effect.
  Raises @ref TemplateError if setting is active and
  driver names mismatch.
  
  @param[in] $obj Instance of @ref TemplateEnviron or @ref TemplateCompilerEx
  @param[in] $setting Setting to check (either @c restrictExtendIO or @c restrictIncludeIO)
  @param[in] $dsn DSN to check
  @param[in] $expectedDriver Expected driver name
  @param[in] $node Optional instance of @ref TemplateNodeEx
 */
 public static function checkIORestriction($obj, $setting, $dsn, $expectedDriver, $node = null) {
  if (!in_array($setting, array('restrictIncludeIO', 'restrictExtendIO'))) {
   self::panic(__FILE__, __LINE__);
  }

  $isCompiler = ($obj instanceof TemplateCompilerEx);
  $isEnviron  = ($obj instanceof TemplateEnviron);
  
  if (!$isCompiler && !$isEnviron) {
   self::panic(__FILE__, __LINE__);
  }
  
  $settings = &$obj->settings;
  list($driver,) = self::splitIODSN($settings, $dsn);
  
  if (!$settings[$setting] || $driver == $expectedDriver) return;
  
  $message = sprintf(
   'I/O restriction in effect - you can only use "%s" I/O driver, "%s" used',
   $expectedDriver, $driver
  );
  $code    = TemplateError::E_SECURITY_VIOLATION;
  
  if ($isCompiler && ($node instanceof TemplateNodeEx)) {
   $obj->raise($node, $message, $code);
  } else {
   throw new TemplateError($message, $code);
  }
 }
}
