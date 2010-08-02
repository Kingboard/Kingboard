<?php
/** @file Base.php
 File containing common abstract base class, used by compiled templates.
 
 @since 1.1a0
 @license{New BSD License}
 @author PiotrLegnica
*/

/**
 Abstract base class for templates.
*/
abstract class Template {
 /**
  Current context.
 */
 protected $ctx = array();
 
 /**
  Render template using given context.
  
  @param[in] $ctx Context (assoc. array with template variables)
  @param[in] $environ Environment to use (@ref TemplateEnviron instance)
  @return Result as string
 */
 public final function render(array $ctx, TemplateEnviron $environ) {
  $this->ctx = $ctx;
  return $this->_main($environ);
 }
 
 /**
  Warn about non-existant variable.
  
  @param[in] $variable Raw variable name, as encountered in template source
 */
 protected final function warnVar($variable) {
  trigger_error(
   'Non-existant variable "'.$variable.'" used in template. Maybe a typo, or you forgot to include it in context?',
   E_USER_WARNING
  );
 }
 
 /**
  Fail after encountering an invalid variable (e.g. non-iterable used as loop source).
  
  @param[in] $variable Raw variable name, as encountered in template source
  @param[in] $message Additional error details
 */
 protected final function invalidVar($variable, $message) {
  throw new TemplateError('Invalid variable "'.$variable.'" used: '.$message, TemplateError::E_INVALID_VAR);
 }
 
 /**
  Contents of template's main block.
  
  @param[in] $environ @ref TemplateEnviron instance
 */
 abstract function _main($environ);
}
