<?php
/** @file api/IPlugin.php
 Common interface for plugins.
 
 @since 1.1a0
 @author PiotrLegnica
 @license{New BSD License}
*/

/**
 Interface required for all plugins.
 
 Since 1.1 plugins are used during compilation phase exclusively,
 there are no more 'runtime/compile-time libraries'.
 
 Both tags and filters now use code inlining (e.g. they embed code directly
 in template's code).
*/
interface ITemplatePlugin {
 /**
  Plugin's entry point, must return array of provided handlers
  (see @ref extending-st).
  Handler array structure:
  
  <ul>
   <li>
    @c tags - array of tag handlers
    <ul>
     <li>
      @c handler (array/string, required) - whatever @c call_user_func_array can handle.
      If you want to use stand-alone function, you must also
      set @c standalone key to @c true, otherwise library will
      assume that you meant @c array($this,$handler).
     </li>
     <li>
      @c standalone (boolean, optional) - set it, if you want to use non-member function
      as @c handler.
     </li>
     <li>
      @c type (string, required) - tag type. Either @c 'block' or @c 'inline'.
     </li>
     <li>
      @c minArgs (integer, optional) - minimum arguments this tag needs.
      Defaults to 0.
     </li>
     <li>
      @c parent (string, optional) - enforcement of specific immediate parent.
      Used in e.g. @c else/elseif. Compiler will raise error if immediate parent
      of this tag isn't one specified here. Defaults to nothing. May contain wildcard
      @c * (e.g. @c if* matches @c if, @c ifchanged, @c ifequals, etc.).
     </li>
    </ul>
   </li>
   <li>
    @c filters - array of filter handlers
    <ul>
     <li>@c handler (array/string, required) - see above.</li>
     <li>@c standalone (boolean, optional) - see above.</li>
     <li>@c minArgs (integer, optional) - see above.</li>
    </ul>
   </li>
   <li>
    @c hooks - array of hook handlers (for available hookpoints see @ref extending-st-hooks)
    <ul>
     <li>@c handler (array/string, required) - see above.</li>
     <li>@c standalone (boolean, optional) - see above.</li>
    </ul>
   </li>
  </ul>
  
  @return Assoc. array of handlers
 */
 public function providedHandlers();
}
