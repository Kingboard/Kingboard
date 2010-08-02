<?php
/** @file api/IIODriver.php
 Common interface for I/O drivers.

 @since 1.1a0
 @author PiotrLegnica
 @license{New BSD License}
*/

/**
 Interface required for all I/O drivers.
 
 I/O machinery is separated from template plugins.
*/
interface ITemplateIODriver {
 /**
  Should check whether given template is up-to-date. If driver
  uses @c recompilationMode setting, then it should use supplied
  @c $mode argument instead, to allow per-template mode override.
  Although parameters are supplied via reference, they should not
  be modified in any way.
  
  @param[in] $settings Settings array, see @ref TemplateEnviron::$settings
  @param[in] $template %Template name
  @param[in] $mode Recompilation mode
  @retval true %Template is up-to-date - no (re)compilation is needed
  @retval false %Template must be (re)compiled
 */
 public function upToDate(array &$settings, &$template, $mode);
 /**
  Should include template's code into global namespace. It must
  ensure that no code redefinition will happen.
  Although parameters are supplied via reference, they should not
  be modified in any way.
  
  @param[in] $settings Settings array, see @ref TemplateEnviron::$settings
  @param[in] $template %Template name
  @return Included class name
 */
 public function includeCode(array &$settings, &$template);
 /**
  Should return template's classname.
  
  @param[in] $settings Settings array, see @ref TemplateEnviron::$settings
  @param[in] $template %Template name
  @return Class name
 */
 public function className(array &$settings, &$template);
 
 /**
  Should read template source code as whole, and return it.
  SithTemplate ensures that this will be called only when
  compilation is needed, so no additional checks are needed.
  Although parameters are supplied via reference, they should not
  be modified in any way.

  @param[in] $settings Settings array, see @ref TemplateEnviron::$settings
  @param[in] $template %Template name
  @return Whole template source
 */
 public function loadTemplate(array &$settings, &$template);
 /**
  Should read template's metadata, and return it.
  Although parameters are supplied via reference, they should not
  be modified in any way.

  @param[in] $settings Settings array, see @ref TemplateEnviron::$settings
  @param[in] $template %Template name
  @return %Template metadata or @c false.
 */
 public function loadMetadata(array &$settings, &$template);
 /**
  Should save compiled template code.
  Although parameters are supplied via reference, they should not
  be modified in any way.

  @param[in] $settings Settings array, see @ref TemplateEnviron::$settings
  @param[in] $template %Template name
  @param[in] $code %Template code
  @retval true %Template has been saved
  @retval false An error occured
 */
 public function saveTemplate(array &$settings, &$template, &$code);
 /**
  Should save template metadata.
  Although parameters are supplied via reference, they should not
  be modified in any way.

  @param[in] $settings Settings array, see @ref TemplateEnviron::$settings
  @param[in] $template %Template name
  @param[in] $metadata Metadata
  @retval true Metadata has been saved
  @retval false An error occured
 */
 public function saveMetadata(array &$settings, &$template, array &$metadata);
}