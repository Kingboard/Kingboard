<?php
/** @file Error.php
 Exceptions used in the library.
 
 @since 1.1a0
 @author PiotrLegnica
 @license{New BSD License}
*/

/**
 Main and currently the only exception type thrown by SithTemplate internals.
 Since 1.1, preformatted messages are no longer used, full message is constructed
 wherever exception is thrown instead.
*/
class TemplateError extends Exception {
 /**
  An unknown error. Exception with this code indicates a mistake in
  code, and should be reported as bug.
 */
 const E_UNKNOWN_ERROR         = 0x00000000;
 /**
  An invalid variable error. Thrown when variable fails constraint
  test (e.g. non-iterable used as argument in <code>{% for %}</code>).
  Used in runtime only.
 */
 const E_INVALID_VAR           = 0x00000001;
 /**
  An I/O read error. Thrown if template DSN cannot be resolved (e.g.
  template doesn't exist, or cannot be read; or its metadata; or compiled code).
  Used in both compile time and runtime.
 */
 const E_IO_LOAD_FAILURE       = 0x00000002;
 /**
  An I/O save error. Thrown if template code or metadata cannot be
  saved. Used in compile time only.
 */
 const E_IO_SAVE_FAILURE       = 0x00000003;
 /**
  An unknown tag error. Thrown if unknown tag is encountered in template
  source. Used in compile time only.
 */
 const E_UNKNOWN_TAG           = 0x00000004;
 /**
  An unknown filter error. Thrown if unknown filter is encountered in template
  source. Used in compile time only.
 */
 const E_UNKNOWN_FILTER        = 0x00000005;
 /**
  An invalid handler error. This indicates a bug in the plugin you use. Don't report
  it, unless this plugin is @c StdLibEx, which implements SithTemplate's standard library.
  Used in compile time only.
 */
 const E_INVALID_HANDLER       = 0x00000006;
 /**
  An invalid syntax error. Thrown when compiler or tag/filter detects an syntax error,
  which doesn't have it's own error code. Used in compile time only.
 */
 const E_INVALID_SYNTAX        = 0x00000007;
 /**
  An unknown plugin error. In compile time: thrown when library tries to load non-existant plugin.
  In runtime: thrown when library tries to use non-existant I/O driver.
 */
 const E_UNKNOWN_PLUGIN        = 0x00000008;
 /**
  An invalid plugin error. It indicates a bug in the plugin or I/O driver you use.
  Don't report it, unless it's related to @c StdLibEx plugin, or @c file or @c string
  I/O drivers. Used in both runtime and compile time.
 */
 const E_INVALID_PLUGIN        = 0x00000009;
 /**
  An invalid argument error. Thrown when a function, or tag/filter gets called with
  invalid arguments. Used in both runtime and compile time.
 */
 const E_INVALID_ARGUMENT      = 0x0000000A;
 /**
  A security violation. Thrown when library encounters situation forbidden
  by the security settings.
 */
 const E_SECURITY_VIOLATION    = 0x0000000B;
 
 /**
  A core panic. If you get exception with this code, report it - it's a bug.
 */
 const E_INTERNAL_CORE_FAILURE = 0xFFFFFFFF;
}
