<?php
/** @file plugins/StdLibEx.plugin.php
 Contains all of the standard tags, filters and hooks.

 @since 1.1a0
 @author PiotrLegnica
 @license{New BSD License}
*/
/** @page stdlib Standard library
 SithTemplate comes with one plugin - @c StdLibEx. It implements so-called
 <strong>standard library</strong>, that is set of tags and filters always
 available (by default, at least, as you can opt to not use it at all) to all
 templates. Standard library tries to be Django-compatbile, while providing
 several SithTemplate-specific extensions.
 
 @section stdlib-tags Standard tags
  @subsection stdlib-tag-autoescape {% autoescape %}
   <code>{% autoescape on|off %} ... {% endautoescape %}</code>
   
   Activates or deactivates variable auto-escaping inside the block.
   Also see @ref stdlib-filter-escape filters.
   
   @include stdlib/00_tag_autoescape.html
   
  @subsection stdlib-tag-block {% block %}
   <code>{% block \<name\> %} ... {% endblock %}</code>
   
   <code>{% block \<name\> store %} ... {% endblock %}</code> (<strong>non-Django extension</strong>)
   
   Creates a new overridable, named block (see @ref tut-inheritance).
   @c name argument must be non-quoted string. You can access @ref stdlib-var-block
   special variable from within the block.
   
   If @c store is used, then block contents will be remembered, but not displayed
   (see @ref stdlib-tag-putblock).
   
   @include stdlib/00_tag_block.html
   
  @subsection stdlib-tag-call {% call %}
   <strong>Non-Django tag.</strong>
   
   <code>{% call \<callback\> [\<argument\> [\<argument\> [...]]] %}</code>
   
   <code>{% call \<callback\> [\<argument\> [\<argument\> [...]]] as \<variable\> %}</code>
   
   Calls raw PHP function, specified by @c callback
   (checked against security lists - see @ref tut-security), and either displays it
   (first signature) or stores in a new context variable (second signature).
   
   @c callback can be a quoted string constant, or a variable expression (that
   must evaluate to @c call_user_func_array compatible callback value).
   You can pass as many arguments as you need; every @c argument can be either
   constant or variable. If second version is used, @c variable must be a
   simple variable name (i.e. it's not parsed as an expression, and cannot
   contain access operators, or filters).
   
   @include stdlib/00_tag_call.html
   
  @subsection stdlib-tag-cycle {% cycle %}
   <code>{% cycle \<value\> \<value\> [\<value\> [...]] %}</code>
   
   <code>{% cycle \<value\> \<value\> [\<value\> [...]] as \<name\> %}</code>
   
   <code>{% cycle \<name\> %}</code>
   
   Cycles through given list of values. It can be used in two ways:
   inside the loop (first signature), or outside, as named cycle
   (second signature creates named cycle, third calls already created one).
   
   @c value can be either constant or filtered variable expression. @c name
   must be non-quoted constant string.
   
   @include stdlib/00_tag_cycle.html
   
  @subsection stdlib-tag-debug {% debug %}
   <code>{% debug %}</code>
   
   As for now, it only @c var_dump's the context.
   Maybe will be extended in the future.
   
  @subsection stdlib-tag-extends {% extends %}
   <code>{% extends \<template\> %}</code>
   
   Extends given template. @c template must be a quoted constant string,
   and be a correct template ID (see @ref tut-io). Argument is tested
   against @c restrictExtendIO setting (see @ref tut-security).
   
   If you use <code>{% extends %}</code> more than once in one template,
   an error will be raised (see @ref tut-errors).
   
   For more complex example on inheritance, see @ref tut-inheritance.
   
   @include stdlib/00_tag_extends.html
   
  @subsection stdlib-tag-filter {% filter %}
   <code>{% filter \<filters\> %} ... {% endfilter %}</code>
   
   Filters the block contents. @c filters must be a correct filter chain
   (i.e. a variable expression, but without variable part; see @ref tut-context),
   and contain at least one filter.
   
   @include stdlib/00_tag_filter.html
   
  @subsection stdlib-tag-firstof {% firstof %}
   <code>{% firstof \<variable\> \<variable\> [\<variable\> [...]] [\<fallback\>] %}</code>
   
   Outputs first variable that exists and evaluates to @c true, or a fallback value
   (unless it's not specified, then nothing is outputted).
   
   @c variable must be a correct variable expression (see @ref tut-context), and
   @c fallback, if specified, must a quoted constant string.
   
   @include stdlib/00_tag_firstof.html
   
  @subsection stdlib-tag-for {% for %} and {% empty %}
   <code>{% for [\<key\>,] \<value\> in \<iterable\> %} ... [{% empty %} ...] {% endfor %}</code>
   
   Iterates through variable using @c foreach statement.
   @c key and @c value must be simple variable names (no filters, no operators),
   @c iterable must be a variable expression evaluating to an iterable value
   (i.e. an array, or an object that implements @c Traversable interface).
   
   <code>{% empty %}</code> can be used to specify alternate block, which will
   be used if @c iterable yields no results (i.e. an empty array).
   
   You can also access @ref stdlib-var-internal special variable from inside
   the for loop.
   
   @include stdlib/00_tag_for_empty.html
   
  @subsection stdlib-tag-if {% if %}, {% else %} and {% elseif %}
   <strong>Non-Django behaviour: Django has simpler <code>{% if %}</code>, without rich comparison
   operators or grouping, and also have no <code>{% elseif %}</code>.</strong>
   
   <code>{% if \<condition\> %} ... [{% elseif \<condition\> %} ...
   [{% elseif \<condition\> %} ... [...]]] [{% else %} ...] {% endif %}</code>
   
   Conditional block. You can specify alternate condition blocks using
   one or more <code>{% elseif %}</code> tags,
   and an <code>{% else %}</code> tag (only one else is allowed).
   
   @c condition is a conditional expression that supports statement
   grouping (like in PHP, using parentheses) and rich operators:
   <ul>
    <li>@c eq - PHP's @c ==</li>
    <li>@c neq - PHP's @c !=</li>
    <li>@c lt - PHP's @c \<</li>
    <li>@c lte - PHP's @c \<=</li>
    <li>@c gt - PHP's @c \></li>
    <li>@c gte - PHP's @c \>=</li>
    <li>@c and - PHP's @c &&</li>
    <li>@c or - PHP's @c ||</li>
    <li>@c not - PHP's @c !</li>
    <li>@c id - PHP's @c === (<strong>added in 1.1a2</strong>)</li>
    <li>@c nid - PHP's @c !== (<strong>added in 1.1a2</strong>)</li>
   </ul>
   
   @include stdlib/00_tag_if_else_elseif.html
   
  @subsection stdlib-tag-ifchanged {% ifchanged %} and {% else %}
   <strong>This tag may only appear inside the loop.</strong>
   
   <code>{% ifchanged %} ... [{% else %} ...] {% endifchanged %}</code>
   
   On every iteration, checks whether its contents has changed, and outputs
   correct block accordingly (or nothing, if it hasn't changed, and <code>{% else %}</code>
   block was not given).
   
   <code>{% ifchanged \<variable\> %} ... [{% else %} ...] {% endifchanged %}</code>
   
   Behaves like the first signature, but compares variable value instead of block
   content. @c variable must be a filtered variable expression.
   
   @include stdlib/00_tag_ifchanged.html
   
  @subsection stdlib-tag-ifequal {% ifequal %}, {% ifnotequal %} and {% else %}
   <code>{% ifequal \<variable\> \<variable\> %} ... [{% else %} ...] {% endif %}</code>
   
   <code>{% ifnotequal \<variable\> \<variable\> %} ... [{% else %} ...] {% endif %}</code>
   
   Simplified versions of <code>{% if %}</code> provided for Django compatibility.
   Equivalent to <code>{% if \<variable\> eq \<variable\> %}</code> and
   <code>{% if \<variable\> neq \<variable\> %}</code>, accordingly.
   
  @subsection stdlib-tag-include {% include %}
   <code>{% include \<template\> %}</code>
   
   Includes another template's contents. @c template must be either quoted constant string,
   or a variable expression evaluating to correct template ID (see @ref tut-io). Argument is tested
   against @c restrictIncludeIO setting (see @ref tut-security).
   
   @include stdlib/00_tag_include.html
   
  @subsection stdlib-tag-load {% load %}
   <strong>This tag is a built-in (e.g. a part of the core itself, not standard library)</strong>
   
   <code>{% load \<plugin\> %}</code>
   
   Loads new plugin for current template, making its tags, filter and hooks
   immediately accessible. @c plugin must be a non-quoted constant string.
   
   This tag respects security lists (see @ref tut-security).
   
   @include stdlib/00_tag_load.html
   
  @subsection stdlib-tag-meta {% meta %}
   <strong>Non-Django tag.</strong>
   
   <code>{% meta \<name\> \<value\> %}</code>
   
   Creates an entry in template's metadata. @c name must be a non-quoted constant
   string, @c value must be a quoted constant string.
   
   Custom metadata will be prefixed @c user: internally, and can be retrieved using
   @ref TemplateEnviron::getMeta API call.
   
   @include stdlib/00_tag_meta.html
   
  @subsection stdlib-tag-now {% now %}
   <code>{% now \<format\> %}</code>
   
   Outputs current timestamp, formatted using @c format argument, which must be either
   quoted constant string, or a variable expression that evaluates to string.
   
   For format codes see PHP's @c date function documentation.
   
   @include stdlib/00_tag_now.html
   
  @subsection stdlib-tag-putblock {% putblock %}
   <strong>Non-Django tag.</strong>
   
   <code>{% putblock \<name\> %}</code>
   
   Outputs previously defined block (see @ref stdlib-tag-block). You can
   use store blocks to maximize code reuse within single template (blocks
   are always evaluated with current context).
   
   Keep in mind that blocks are internal to template, and can only be
   inherited. You cannot e.g. <code>{% putblock %}</code> a block from
   a included template (see @ref stdlib-tag-include).
   
   @include stdlib/00_tag_putblock.html
   
  @subsection stdlib-tag-spaceless {% spaceless %}
   <code>{% spaceless %} ... {% endspaceless %}</code>
   
   Removes all whitespace between HTML tags (but not inside the tags).
   
   @include stdlib/00_tag_spaceless.html
   
  @subsection stdlib-tag-templatetag {% templatetag %}
   <code>{% templatetag \<tag\> %}</code>
   
   Inserts literal bit of template syntax, @c tag must be one of:
   <ul>
    <li>@c openblock - inserts @c {%</li>
    <li>@c closeblock - inserts @c %}</li>
    <li>@c openvariable - inserts @c {{</li>
    <li>@c closevariable - inserts @c }}</li>
    <li>@c opencomment - inserts @c {#</li>
    <li>@c closecomment - inserts @c #}</li>
    <li>@c openbrace - inserts @c { (<strong>added in 1.1a2</strong>)</li>
    <li>@c closebrace - inserts @c } (<strong>added in 1.1a2</strong>)</li>
   </ul>
   
   <strong>Non-Django behaviour follows.</strong>
   
   In addition to above, Django-compatible tags, SithTemplate also
   defines several aliases on its own (although you are encouraged to
   use full versions, these are kept mainly for backwards compatibility):
   <ul>
    <li>@c ob, @c ot and @c opentag - alias to @c openblock</li>
    <li>@c cb, @c ct and @c closetag - alias to @c closeblock</li>
    <li>@c ov, and @c openvar - alias to @c openvariable</li>
    <li>@c cv, and @c closevar - alias to @c closevariable</li>
    <li>@c oc - alias to @c opencomment</li>
    <li>@c cc - alias to @c closecomment</li>
   </ul>
   
  @subsection stdlib-tag-widthratio {% widthratio %}
   <code>{% widthratio \<value\> \<maxValue\> \<constant\> %}</code>
   
   Calculates width ratio using formula: <code>round((value/maxValue)*constant)</code>.
   
   Both @c value and @c maxValue must be either numeric constants, or a variable
   expression evaluating to integer or float. @c constant must be an integer constant.
   
   @include stdlib/00_tag_widthratio.html
   
  @subsection stdlib-tag-with {% with %}
   <code>{% with \<variable\> as \<name\> %} ... {% endwith %}</code>
   
   Creates a new variable, using value of filtered variable expression,
   visible only within the block.
   
   @c variable must be a filtered variable expression, and @c name must
   be a simple variable name.
   
   @include stdlib/00_tag_with.html
  
 @section stdlib-filters Standard filters
  @subsection stdlib-filter-add add
   <code>add:\<argument\></code>
   
   Adds the argument to the variable. @c argument must be either constant string
   (operator @c . (join strings) is used), or constant number/numeric variable (operator
   @c + (add numbers) is used).
   
   @include stdlib/01_filter_add.html
  
  @subsection stdlib-filter-addslashes addslashes
   <code>addslashes</code>
   
   See PHP's @c addslashes function.
   
   @include stdlib/01_filter_addslashes.html
  
  @subsection stdlib-filter-capfirst capfirst, lower, upper, title
   <code>capfirst</code>
   
   <code>lower</code>
   
   <code>upper</code>
   
   <code>title</code>
   
   Changes capitalization of the string variable - @c capfirst capitalizes
   first letter, @c lower converts entire string into lowercase, @c upper
   converts entire string into uppercase, and @c title converts entire string into
   titlecase (e.g. converts every first letter of a word into uppercase).
   
   Also see PHP's @c mb_convert_case function.
   
   @include stdlib/01_filter_capfirst_lower_upper_title.html
  
  @subsection stdlib-filter-cut cut
   <code>cut:\<argument\></code>
   
   Removes the argument from the string variable. @c argument
   can be a variable or a constant. Uses PHP's @c preg_replace.
   
   @include stdlib/01_filter_cut.html
  
  @subsection stdlib-filter-date date
   <code>date:\<format\></code>
   
   Formats the timestamp according to the @c format. See PHP's
   @c date function.
   
   @include stdlib/01_filter_date.html
  
  @subsection stdlib-filter-default default, default_if_none
   <code>default:\<value\></code>
   
   <code>default_if_none:\<value\></code>
   
   Uses default value if variable doesn't exist (or evaluates to false, @c default) or
   is NULL (@c default_if_none). @c value can be a constant, or a variable.
   
   @include stdlib/01_filter_default_default_if_none.html
  
  @subsection stdlib-filter-divisibleby divisibleby
   <code>divisibleby:\<value\></code>
   
   Returns @c true if variable is evenly divisible by @c value (which can be either
   variable or a constant number different than zero).
   
   @include stdlib/01_filter_divisibleby.html
  
  @subsection stdlib-filter-escape escape and safe
   <code>escape</code>
   
   <code>safe</code>
   
   @c escape applies @c htmlspecialchars function to the variable. @c safe is a
   pseudofilter (i.e. it's not actually defined and it's handled by hooks instead)
   that marks variable as safe (i.e. already escaped, or that it doesn't need escaping at all)
   causing autoescaping (see @ref tut-security) to skip it.
   
   @include stdlib/01_filter_escape.html
  
  @subsection stdlib-filter-filesizeformat filesizeformat
   <code>filesizeformat</code>
   
   Formats the integer variable as human-readable filesize
   (e.g. to bytes, kilobytes, megabytes, gigabytes).
   
   @include stdlib/01_filter_filesizeformat.html
  
  @subsection stdlib-filter-fixampersands fix_ampersands
   <code>fix_ampersands</code>
   
   Changes every @c & into @c &amp; HTML entity.
   
   @include stdlib/01_filter_fix_ampersands.html
  
  @subsection stdlib-filter-join join
   <code>join:\<separator\></code>
   
   @c implode's an array variable using @c separator (a variable, or a constant).
   
   @include stdlib/01_filter_join.html
  
  @subsection stdlib-filter-length length, length_is
   <code>length</code>
   
   <code>length_is:\<number\></code>
   
   @c length returns length of a string or count of an array elements.
   @c length_is compares that length with a @c number (a variable, or a constant number), and
   returns boolean.
   
   @include stdlib/01_filter_length_length_is.html
  
  @subsection stdlib-filter-linebreaks linebreaks, linebreaksbr
   <code>linebreaks</code>
   
   <code>linebreaksbr</code>
   
   @c linebreaks converts newlines in the variable into HTML paragraphs and linebreaks.
   @c linebreaksbr applies @c nl2br.
   
   @include stdlib/01_filter_linebreaks_linebreaksbr.html
  
  @subsection stdlib-filter-ljust ljust, rjust
   <code>ljust:\<width\></code>
   
   <code>rjust:\<width\></code>
   
   Aligns the text inside the field of given @c width.
   
   @include stdlib/01_filter_ljust_rjust.html
  
  @subsection stdlib-filter-makelist make_list
   <code>make_list</code>
   
   Splits the string/numeric variable into an array of characters/digits.
   
   @include stdlib/01_filter_make_list.html
  
  @subsection stdlib-filter-pluralize pluralize
   <code>pluralize</code>
   
   Returns plural suffix @c -s if the filtered variable evaluates to integer
   bigger than 1.
   
   <code>pluralize:\<suffix\></code>
   
   Returns user-specified plural suffix instead. @c suffix must be a quoted constant
   string.
   
   <code>pluralize:\<suffixes\></code>
   
   Returns either singular or plural suffix, both user-specified in @c suffixes, and
   delimited by a comma. @c suffixes must be a quoted constant string.
   
   @include stdlib/01_filter_pluralize.html
  
  @subsection stdlib-filter-random random
   <code>random</code>
   
   Returns random element of the filtered array.
   
   @include stdlib/01_filter_random.html
  
  @subsection stdlib-filter-removetags removetags
   <code>removetags</code>
   
   Applies PHP's @c strip_tags on filtered variable.
   
   @include stdlib/01_filter_removetags.html
  
  @subsection stdlib-filter-slugify slugify
   <code>slugify</code>
   
   Converts the string into a URL-friendly "slug", i.e. converts it to lowercase,
   strips HTML tags, converts all whitespace and underscores into dashes, and removes
   all remaining characters that are neither dash, nor alphanumeric.
   
   @include stdlib/01_filter_slugify.html
  
  @subsection stdlib-filter-urlencode urlencode, urldecode
   <code>urlencode</code>
   
   <code>urldecode</code>
   
   PHP's @c urlencode and @c urldecode, respectively.
   
   @include stdlib/01_filter_urlencode_urldecode.html
  
  @subsection stdlib-filter-wordcount wordcount
   <code>wordcount</code>
   
   Counts the words in the string, using PHP's @c str_word_count.
   
   @include stdlib/01_filter_wordcount.html
  
  @subsection stdlib-filter-wordwrap wordwrap
   <code>wordwrap:\<length\></code>
   
   Applies PHP's @c wordwrap, using given @c length (a variable or a constant number).
   
   @include stdlib/01_filter_wordwrap.html
  
 @section stdlib-variables Special variables
  @subsection stdlib-var-block {{ block }}
   This variable is accessible from within @ref stdlib-tag-block tag.
   
   It contains only one subkey - <code>{{ block.super }}</code> which
   evaluates to the contents of parent block.
   
   @include stdlib/02_var_block.html
  
  @subsection stdlib-var-forloop {{ forloop }}
   This variable is accessible from within the @c for loop
   (see @ref stdlib-tag-for).
   
   It contains several subkeys:
   <ul>
    <li><code>{{ forloop.counter }}</code> - current iteration, starting from 1</li>
    <li><code>{{ forloop.counter0 }}</code> - current iteration, starting from 0</li>
    <li><code>{{ forloop.revcounter }}</code> - number of iterations left, ending on 1</li>
    <li><code>{{ forloop.revcounter0 }}</code> - number of iterations left, ending on 0</li>
    <li><code>{{ forloop.first }}</code> - true if first iteration</li>
    <li><code>{{ forloop.last }}</code> - true if last iteration</li>
    <li><code>{{ forloop.parentloop }}</code> - @c forloop variable of the parent loop,
    available in nested loops</li>
   </ul>
   
   @include stdlib/02_var_forloop.html
  
  @subsection stdlib-var-internal {{ internal }}
   This variable is accessible in the entire template.
   
   It contains several subkeys:
   <ul>
    <li><code>{{ internal.request }}</code> - allows you to access PHP's superglobals (also see @ref tut-security)</li>
    <li><code>{{ internal.const }}</code> - allows you to access PHP's constants (also see @ref tut-security)</li>
    <li><code>{{ internal.version }}</code> - evaluates to the current engine's version (e.g. SITHTEMPLATE_VERSION)
    - keep in mind that this value is hardcoded into the template's code, so it won't change without recompilation
    </li>
   </ul>
   
   @include stdlib/02_var_internal.html
 
*/
/** @example stdlib/00_tag_autoescape.html
 Example on how to use @ref stdlib-tag-autoescape tag.
*/
/** @example stdlib/00_tag_block.html
 Example on how to use @ref stdlib-tag-block tag.
*/
/** @example stdlib/00_tag_call.html
 Example on how to use @ref stdlib-tag-call tag.
*/
/** @example stdlib/00_tag_cycle.html
 Example on how to use @ref stdlib-tag-cycle tag.
*/
/** @example stdlib/00_tag_extends.html
 Example on how to use @ref stdlib-tag-extends tag.
*/
/** @example stdlib/00_tag_filter.html
 Example on how to use @ref stdlib-tag-filter tag.
*/
/** @example stdlib/00_tag_firstof.html
 Example on how to use @ref stdlib-tag-firstof tag.
*/
/** @example stdlib/00_tag_for_empty.html
 Example on how to use @ref stdlib-tag-for tags.
*/
/** @example stdlib/00_tag_if_else_elseif.html
 Example on how to use @ref stdlib-tag-if tags.
*/
/** @example stdlib/00_tag_ifchanged.html
 Example on how to use @ref stdlib-tag-ifchanged tag.
*/
/** @example stdlib/00_tag_include.html
 Example on how to use @ref stdlib-tag-include tag.
*/
/** @example stdlib/00_tag_load.html
 Example on how to use @ref stdlib-tag-load tag.
*/
/** @example stdlib/00_tag_meta.html
 Example on how to use @ref stdlib-tag-meta tag.
*/
/** @example stdlib/00_tag_now.html
 Example on how to use @ref stdlib-tag-now tag.
*/
/** @example stdlib/00_tag_putblock.html
 Example on how to use @ref stdlib-tag-putblock tag.
*/
/** @example stdlib/00_tag_spaceless.html
 Example on how to use @ref stdlib-tag-spaceless tag.
*/
/** @example stdlib/00_tag_widthratio.html
 Example on how to use @ref stdlib-tag-widthratio tag.
*/
/** @example stdlib/00_tag_with.html
 Example on how to use @ref stdlib-tag-with tag.
*/
/** @example stdlib/01_filter_add.html
 Example on how to use @ref stdlib-filter-add filter.
*/
/** @example stdlib/01_filter_addslashes.html
 Example on how to use @ref stdlib-filter-addslashes filter.
*/
/** @example stdlib/01_filter_capfirst_lower_upper_title.html
 Example on how to use @ref stdlib-filter-capfirst filters.
*/
/** @example stdlib/01_filter_cut.html
 Example on how to use @ref stdlib-filter-cut filter.
*/
/** @example stdlib/01_filter_date.html
 Example on how to use @ref stdlib-filter-date filter.
*/
/** @example stdlib/01_filter_default_default_if_none.html
 Example on how to use @ref stdlib-filter-default filters.
*/
/** @example stdlib/01_filter_divisibleby.html
 Example on how to use @ref stdlib-filter-divisibleby filter.
*/
/** @example stdlib/01_filter_escape.html
 Example on how to use @ref stdlib-filter-escape filters.
*/
/** @example stdlib/01_filter_filesizeformat.html
 Example on how to use @ref stdlib-filter-filesizeformat filter.
*/
/** @example stdlib/01_filter_fix_ampersands.html
 Example on how to use @ref stdlib-filter-fixampersands filter.
*/
/** @example stdlib/01_filter_join.html
 Example on how to use @ref stdlib-filter-join filter.
*/
/** @example stdlib/01_filter_length_length_is.html
 Example on how to use @ref stdlib-filter-length filters.
*/
/** @example stdlib/01_filter_linebreaks_linebreaksbr.html
 Example on how to use @ref stdlib-filter-linebreaks filters.
*/
/** @example stdlib/01_filter_ljust_rjust.html
 Example on how to use @ref stdlib-filter-ljust filters.
*/
/** @example stdlib/01_filter_make_list.html
 Example on how to use @ref stdlib-filter-makelist filter.
*/
/** @example stdlib/01_filter_pluralize.html
 Example on how to use @ref stdlib-filter-pluralize filter.
*/
/** @example stdlib/01_filter_random.html
 Example on how to use @ref stdlib-filter-random filter.
*/
/** @example stdlib/01_filter_removetags.html
 Example on how to use @ref stdlib-filter-removetags filter.
*/
/** @example stdlib/01_filter_slugify.html
 Example on how to use @ref stdlib-filter-slugify filter.
*/
/** @example stdlib/01_filter_urlencode_urldecode.html
 Example on how to use @ref stdlib-filter-urlencode filters.
*/
/** @example stdlib/01_filter_wordcount.html
 Example on how to use @ref stdlib-filter-wordcount filter.
*/
/** @example stdlib/01_filter_wordwrap.html
 Example on how to use @ref stdlib-filter-wordwrap filter.
*/
/** @example stdlib/02_var_block.html
 Example on how to use @ref stdlib-var-block special variable.
*/
/** @example stdlib/02_var_forloop.html
 Example on how to use @ref stdlib-var-forloop special variable.
*/
/** @example stdlib/02_var_internal.html
 Example on how to use @ref stdlib-var-internal special variable.
*/

/**
 New @c StdLibEx plugin, which combines old
 @c CoreTags, @c CoreFilters and @c CoreHooks.
 
 Since there is no more distinction between compile-time and
 run-time plugins, everything is provided within single class,
 to save I/O and object allocations.
*/
class TemplateStdLibExPlugin implements ITemplatePlugin {
 // Handlers registration

 /**
  Provided tags. See @ref stdlib-tags.
  
  @return Array of handlers
 */
 public function providedTags() {
  return array(
   // Django
   'autoescape'  => array('handler' => 'handleTAutoEscape',  'type' => 'block',  'minArgs' => 1),
   'block'       => array('handler' => 'handleTBlock',       'type' => 'block',  'minArgs' => 1),
   'cycle'       => array('handler' => 'handleTCycle',       'type' => 'inline', 'minArgs' => 1),
   'debug'       => array('handler' => 'handleTDebug',       'type' => 'inline', 'minArgs' => 0),
   'extends'     => array('handler' => 'handleTExtends',     'type' => 'inline', 'minArgs' => 1),
   'filter'      => array('handler' => 'handleTFilter',      'type' => 'block',  'minArgs' => 1),
   'firstof'     => array('handler' => 'handleTFirstOf',     'type' => 'inline', 'minArgs' => 2),
   'for'         => array('handler' => 'handleTFor',         'type' => 'block',  'minArgs' => 3),
   'empty'       => array('handler' => 'handleTEmpty',       'type' => 'inline', 'minArgs' => 0, 'parent' => 'for'),
   'ifchanged'   => array('handler' => 'handleTIfChanged',   'type' => 'block',  'minArgs' => 0, 'parent' => 'for'),
   'if'          => array('handler' => 'handleTIf',          'type' => 'block',  'minArgs' => 1),
   'else'        => array('handler' => 'handleTElse',        'type' => 'inline', 'minArgs' => 0, 'parent' => 'if*'),
   'elseif'      => array('handler' => 'handleTElseIf',      'type' => 'inline', 'minArgs' => 1, 'parent' => 'if'),
   'ifequal'     => array('handler' => 'handleTIfEqual',     'type' => 'block',  'minArgs' => 2),
   'ifnotequal'  => array('handler' => 'handleTIfNotEqual',  'type' => 'block',  'minArgs' => 2),
   'include'     => array('handler' => 'handleTInclude',     'type' => 'inline', 'minArgs' => 1),
   'now'         => array('handler' => 'handleTNow',         'type' => 'inline', 'minArgs' => 1),
   'spaceless'   => array('handler' => 'handleTSpaceless',   'type' => 'block',  'minArgs' => 0),
   'templatetag' => array('handler' => 'handleTTemplateTag', 'type' => 'inline', 'minArgs' => 1),
   'widthratio'  => array('handler' => 'handleTWidthRatio',  'type' => 'inline', 'minArgs' => 3),
   'with'        => array('handler' => 'handleTWith',        'type' => 'block',  'minArgs' => 3),
   // SithTemplate extensions
   'putblock'    => array('handler' => 'handleTPutBlock',    'type' => 'inline', 'minArgs' => 1),
   'call'        => array('handler' => 'handleTCall',        'type' => 'inline', 'minArgs' => 1),
   'meta'        => array('handler' => 'handleTMeta',        'type' => 'inline', 'minArgs' => 2),
  );
 }
 /**
  Provided filters. See @ref stdlib-filters.
  
  @return Array of handlers
 */
 public function providedFilters() {
  return array(
   // Django
   'add'             => array('handler' => 'handleFAdd',            'minArgs' => 1),
   'addslashes'      => array('handler' => 'handleFAddSlashes',     'minArgs' => 0),
   'capfirst'        => array('handler' => 'handleFCapFirst',       'minArgs' => 0),
   'cut'             => array('handler' => 'handleFCut',            'minArgs' => 1),
   'date'            => array('handler' => 'handleFDate',           'minArgs' => 1),
   'default'         => array('handler' => 'handleFDefault',        'minArgs' => 1),
   'default_if_none' => array('handler' => 'handleFDefaultIfNone',  'minArgs' => 1),
   'divisibleby'     => array('handler' => 'handleFDivisibleBy',    'minArgs' => 1),
   'escape'          => array('handler' => 'handleFEscape',         'minArgs' => 0),
   'filesizeformat'  => array('handler' => 'handleFFileSizeFormat', 'minArgs' => 0),
   'fix_ampersands'  => array('handler' => 'handleFFixAmpersands',  'minArgs' => 0),
   'join'            => array('handler' => 'handleFJoin',           'minArgs' => 1),
   'length'          => array('handler' => 'handleFLength',         'minArgs' => 0),
   'length_is'       => array('handler' => 'handleFLengthIs',       'minArgs' => 1),
   'linebreaks'      => array('handler' => 'handleFLineBreaks',     'minArgs' => 0),
   'linebreaksbr'    => array('handler' => 'handleFLineBreaksBR',   'minArgs' => 0),
   'ljust'           => array('handler' => 'handleFLJust',          'minArgs' => 1),
   'lower'           => array('handler' => 'handleFLower',          'minArgs' => 0),
   'make_list'       => array('handler' => 'handleFMakeList',       'minArgs' => 0),
   'pluralize'       => array('handler' => 'handleFPluralize',      'minArgs' => 0),
   'random'          => array('handler' => 'handleFRandom',         'minArgs' => 0),
   'removetags'      => array('handler' => 'handleFRemoveTags',     'minArgs' => 0),
   'rjust'           => array('handler' => 'handleFRJust',          'minArgs' => 1),
   'slugify'         => array('handler' => 'handleFSlugify',        'minArgs' => 0),
   'title'           => array('handler' => 'handleFTitle',          'minArgs' => 0),
   'upper'           => array('handler' => 'handleFUpper',          'minArgs' => 0),
   'urlencode'       => array('handler' => 'handleFURLEncode',      'minArgs' => 0),
   'urldecode'       => array('handler' => 'handleFURLDecode',      'minArgs' => 0),
   'wordcount'       => array('handler' => 'handleFWordCount',      'minArgs' => 0),
   'wordwrap'        => array('handler' => 'handleFWordWrap',       'minArgs' => 1),
  );
 }
 /**
  Provided hooks.
  
  @return Array of handlers
 */
 public function providedHooks() {
  return array(
   'parseVariableExpression:postCodeGen' => array(
    array('handler' => 'handleHInternalVariable'),
    array('handler' => 'handleHForLoopVariable'),
    array('handler' => 'handleHBlockVariable'),
   ),
   'parseFilterChain:entry' => array(
    array('handler' => 'handleHAutoEscape'),
   )
  );
 }
 /**
  Provided handlers. See @ref stdlib.
  
  @return Array of handlers
  @sa ITemplatePlugin::providedHandlers
 */
 public function providedHandlers() {
  return array(
   'tags'    => $this->providedTags(),
   'filters' => $this->providedFilters(),
   'hooks'   => $this->providedHooks(),
  );
 }
 
 //
 // Std tags
 //
 
 /** <code>{% autoescape %}</code> tag. */
 public function handleTAutoEscape(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$tag, array &$args) {
  $compiler->raiseIf(
   (!in_array($args[0], array('on', 'off'))),
   $node,
   'First argument of "autoescape" must be either "on" or "off"',
   TemplateError::E_INVALID_ARGUMENT
  );
  
  $oldAutoEscape = $compiler->settings['autoEscape'];
  $compiler->settings['autoEscape'] = ($args[0] == 'on' ? true : false);
  $code = $compiler->handleChildren($node->nodeChildren);
  $compiler->settings['autoEscape'] = $oldAutoEscape;
  
  return $code;
 }
 
 /** <code>{% block %}</code> tag. */
 public function handleTBlock(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$tag, array &$args) {
  $blockName = 'block:'.$args[0];
  $funcName  = '_'.TemplateUtils::sanitize($blockName);
  
  $compiler->raiseIf(
   (isset($compiler->blocks[$blockName])),
   $node,
   'Redefined block "'.$args[0].'"',
   TemplateError::E_INVALID_ARGUMENT
  );
  
  if (!isset($compiler->varInBlock)) {
   $compiler->varInBlock = array();
  }
  
  $compiler->varInBlock[] = $blockName;
  $compiler->createBlock($blockName, $node);
  array_pop($compiler->varInBlock);

  if (in_array('store', $args)) {
   return '';
  } else {
   return '$b.=$this->'.$funcName.'($e);';
  }
 }
 
 /** <code>{% cycle %}</code> tag. */
 public function handleTCycle(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$tag, array &$args) {
  $inLoop = (isset($compiler->varInLoop) && $compiler->varInLoop);
  
  if ($inLoop) {
   $cycleBlockName = 'cycle:'.end($compiler->varInLoop);
   $cycleMustExist = false;
   $cycleArgs      = &$args;
  } else {
   $argCount = count($args);

   if ($argCount == 1) {
    $cycleBlockName = 'cycle:'.$args[0];
    $cycleMustExist = true;
   } else {
    $compiler->raiseIf(
     ($argCount < 4 || $args[$argCount - 2] != 'as'),
     $node,
     'Insufficient "cycle" arguments - correct signature is {% cycle value value [value [value [...]]] as name %}',
     TemplateError::E_INVALID_SYNTAX
    );
    
    $cycleBlockName = 'cycle:'.$args[$argCount - 1];
    $cycleMustExist = false;
    $cycleArgs      = array_slice($args, 0, -2);
   }
  }
  
  $cycleExists = isset($compiler->blocks[$cycleBlockName]);
  
  $compiler->raiseIf(
   ($cycleMustExist ? !$cycleExists : $cycleExists),
   $node,
   'Cycle "'.mb_substr($cycleBlockName, 6).'" '.
   ($cycleMustExist ? 'does not exist' : 'already exists'),
   TemplateError::E_INVALID_ARGUMENT
  );
  
  if (!$cycleExists) {
   // $v - array of cycled values
   // $i - current internal cycle index
   //      (array index is calculated by $i % count($v))
   if (!$cycleArgs) TemplateUtils::panic(__FILE__, __LINE__);
   
   $allCheck = '';
   
   foreach ($cycleArgs as &$arg) {
    if (mb_substr($arg, 0, 1) == '"') {
     $arg = '\''.TemplateUtils::escape(mb_substr($arg, 1, -1)).'\'';
    } else {
     list($arg, $check)  = $compiler->parseVariableExpression($node, $arg);
     $allCheck          .= $check;
    }
   }
   
   $compiler->blocks[$cycleBlockName] =
    'static $v=0,$i=0;if(!$v){'.$allCheck.'$v=array('.implode(',', $cycleArgs).');}'.
    'return $v[($i++)%'.count($cycleArgs).'];';
  }
  
  return '$b.=$this->_'.TemplateUtils::sanitize($cycleBlockName).'($e);';
 }
 
 /** <code>{% debug %}</code> tag. */
 public function handleTDebug(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$tag, array &$args) {
  return 'var_dump($this->ctx);';
 }
 
 /** <code>{% extends %}</code> tag. */
 public function handleTExtends(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$tag, array &$args) {
  $compiler->raiseIf(
   (isset($compiler->metadata['parentTemplate']) && !empty($compiler->metadata['parentTemplate'])),
   $node,
   'This template already has a parent specified',
   TemplateError::E_INVALID_SYNTAX
  );
  
  $dsn = mb_substr($args[0], 1, -1);
  
  TemplateUtils::checkIORestriction(
   $compiler, 'restrictExtendIO', $dsn, $compiler->metadata['usedIO'], $node
  );
  
  $compiler->metadata['parentTemplate'] = $dsn;
  // no block-level code generated
  return '';
 }
 
 /** <code>{% filter %}</code> tag. */
 public function handleTFilter(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$tag, array &$args) {
  $filters   = &$args[0];
  $blockName = 'block:filter:'.md5($filters);
  $call      = '$this->_'.TemplateUtils::sanitize($blockName).'($e)';
  
  $compiler->createBlock($blockName, $node);
  
  $code = $compiler->parseFilterChain($node, $filters, $call);
  return '$b.='.$code.';';
 }
 
 /** <code>{% firstof %}</code> tag. */
 public function handleTFirstOf(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$tag, array &$args) {
  $allCode  = '';
  $lastArg  = &$args[count($args) - 1];
  $fallback = null;
  
  foreach ($args as &$arg) {
   if (mb_substr($arg, 0, 1) == '"') {
    $compiler->raiseIf(
     ($arg !== $lastArg),
     $node,
     'Fallback value for "firstof" tag must be given as the last one',
     TemplateError::E_INVALID_ARGUMENT
    );
    
    $fallback = '\''.TemplateUtils::escape(mb_substr($arg, 1, -1)).'\'';
   } else {
    list($code,) = $compiler->parseVariableExpression($node, $arg);
    
    $allCode   = 'elseif(isset('.$code.')&&@'.$code.'){$b.='.$code.';}';
   }
  }
  
  $allCode = mb_substr($allCode, 4);
  
  if ($fallback) {
   $allCode .= 'else{$b.='.$fallback.';}';
  }
  
  return $allCode;
 }
 
 /** <code>{% for %}</code> tag. */
 public function handleTFor(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$tag, array &$args) {
  if (count($args) == 3) {
   // {% for value in iterable %}
   $valueVariable    = &$args[0];
   $keyVariable      = null;
   $inConstant       = &$args[1];
   $iterableVariable = &$args[2];
  } elseif (count($args) == 4) {
   // {% for value in iterable %}
   $valueVariable    = &$args[1];
   $keyVariable      = &$args[0];
   
   $compiler->raiseIf(
    (mb_substr($keyVariable, -1, 1) != ','),
    $node,
    'Key variable definition invalid - it should end with a comma',
    TemplateError::E_INVALID_ARGUMENT
   );
   
   $keyVariable      = mb_substr($keyVariable, 0, -1);
   
   $inConstant       = &$args[2];
   $iterableVariable = &$args[3];
  } else {
   $compiler->raise(
    $node,
    'Invalid argument count - either 3 or 4 are required',
    TemplateError::E_INVALID_ARGUMENT
   );
  }
  
  $compiler->raiseIf(
   ($inConstant != 'in'),
   $node,
   'Invalid argument given - expected "in", found "'.$inConstant.'"',
   TemplateError::E_INVALID_ARGUMENT
  );
  
  // to avoid loop's block name collisions, and long function names at the same time
  $loopBlockName = $compiler->generateUniqueBlock($keyVariable.$valueVariable.$iterableVariable, 'loop:');
  
  list($iterable, $filters)   = TemplateUtils::split('|', $iterableVariable);
  list($iterable, $iterCheck) = $compiler->parseVariableExpression($node, $iterable);
  
  if (!isset($compiler->varInLoop)) {
   $compiler->varInLoop = array();
  }
  
  $loopCall = sprintf(
   '$b.=$this->_'.TemplateUtils::sanitize($loopBlockName).'($e%s);',
   ($compiler->varInLoop ? ',$f' : '')
  );
  
  // body of the loop
  list($loopBodyNodes, $emptyNodes) = $compiler->findAlternativeBranch($node, 'empty');
  
  $compiler->varInLoop[] = $loopBlockName;
  $loopBody = $compiler->handleChildren($loopBodyNodes);
  array_pop($compiler->varInLoop);
  
  // and loop itself
  //
  // $k              = value of the key
  // $v              = value of the value
  // $kn             = name of context variable holding key
  // $vn             = name of context variable holding value
  // $ic             = item count in iterable
  // $iv             = iterable
  // $f              = name of the forloop variable
  // $this->ctx[$kn] = reference to $k
  // $this->ctx[$vn] = reference to $v
  // $cf             = reference to $this->ctx[$f]
  
  // sanity check
  $loopCheckCode =
   'if(!is_array($iv)&&!(is_object($iv)&&'.
   'TemplateUtils::doesImplement($iv,\'Traversable\')&&'.
   'TemplateUtils::doesImplement($iv,\'Countable\'))){'.
   '$this->invalidVar(\''.TemplateUtils::escape($iterableVariable).'\',\'iterable expected\');}';
  
  $loopCode =
   $iterCheck.
   '$iv='.$compiler->parseFilterChain($node, $filters, '@'.$iterable).';'.
   '$f=\'forloop:'.TemplateUtils::escape($loopBlockName).'\';'.
   $loopCheckCode;
  
  if ($keyVariable) {
   $loopCode .= TemplateUtils::strip('
    $k  = null;
    $kn = \''.TemplateUtils::escape($keyVariable).'\';
    $this->ctx[$kn] = &$k;
   ');
  }
  
  $loopCode .= sprintf(TemplateUtils::strip('
   $v  = null;
   $vn = \''.TemplateUtils::escape($valueVariable).'\';
   $this->ctx[$vn] = &$v;
   $ic = count($iv);
   
   $this->ctx[$f] = array(
    \'counter\'     => 1,
    \'counter0\'    => 0,
    \'revcounter\'  => $ic,
    \'revcounter0\' => $ic - 1,
    \'first\'       => true,
    \'last\'        => ($ic - 1) == 0
   );
   
   $cf = &$this->ctx[$f];
   
   if (func_num_args() == 2) {
    $cf[\'parentloop\'] = &$this->ctx[func_get_arg(1)];
   }
   
   %s
   
   %s {
    %s
    ++$cf[\'counter\'];
    ++$cf[\'counter0\'];
    --$cf[\'revcounter\'];
    --$cf[\'revcounter0\'];
    $cf[\'first\'] = false;
    $cf[\'last\']  = ($cf[\'revcounter0\'] == 0);
   }
   '),
   ($emptyNodes ? 'if($ic==0){$b=\'\';'.$compiler->handleChildren($emptyNodes).'return $b;}' : ''),
   'foreach($iv as '.($keyVariable ? '$k=>' : '').'$v)', $loopBody
  );
  
  $compiler->blocks[$loopBlockName] = '$b=\'\';'.$loopCode.'return $b;';
  
  return $loopCall;
 }
 
 /** <code>{% empty %}</code> tag. */
 public function handleTEmpty(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$tag, array &$args) {
  // handled by {% for %}, does not generate any code
  // in fact, this handler should never be called
  TemplateUtils::panic(__FILE__, __LINE__);
  return '';
 }
 
 /** <code>{% ifchanged %}</code> tag. */
 public function handleTIfChanged(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$tag, array &$args) {
  list($ifNodes, $elseNodes) = $compiler->findAlternativeBranch($node, 'else');
  $ifBlockName = 'ifchanged:'.end($compiler->varInLoop);
  
  $ifBlockBody = $compiler->handleChildren($ifNodes);
  
  $ifChangedCode  = '$__lv=$this->_%s($e%s);';
  $ifChangedCode .= 'if($__lv!==false){$b.=$__lv;}';
  $ifChangedCode .= ($elseNodes ? 'else{'.$compiler->handleChildren($elseNodes).'}' : '').'unset($__lv);';
  
  if (count($args) == 0) {
   // we compare rendered result of this block
   //
   // $lb is contents of the block after last call

   $ifBlockArgs  = '';
   $ifBlockCode  = 'static $lb=\'\';$b=\'\';'.$ifBlockBody;
   $ifBlockCode .= 'if($b!=$lb){$lb=$b;return $b;}else{return false;}';
  } else {
   // we compare values of given variables
   // this variant of {% ifchanged %} might be repeated, so
   // unique block name is required
   //
   // $vs is array of already captured variables
   
   $ifBlockName = $compiler->generateUniqueBlock(implode(';', $args), $ifBlockName.':');
   $ifBlockCode = 'static $vs=array('.implode(',', array_fill(0, count($args), 'null')).');if(';
   
   foreach ($args as $idx => &$arg) {
    list($arg, $check) = $compiler->parseVariableExpression($node, $arg);
    $ifChangedCode     = $check.$ifChangedCode;
    
    $ifBlockCode .= '($vs['.$idx.']!=func_get_arg('.($idx + 1).'))&&';
   }
   
   $ifBlockCode  = mb_substr($ifBlockCode, 0, -2).'){';
   $ifBlockCode .= '$nvs=func_get_args();$vs=array_slice($nvs,1);unset($nvs);';
   $ifBlockCode .= '$b=\'\';'.$ifBlockBody.'return $b;}else{return false;}';
   $ifBlockArgs  = ','.implode(',', $args);
  }

  $compiler->raiseIf(
   (isset($compiler->blocks[$ifBlockName])),
   $node,
   'Repeated "ifchanged" block within same loop',
   TemplateError::E_INVALID_ARGUMENT
  );
  

  $compiler->blocks[$ifBlockName] = $ifBlockCode;
  
  return sprintf($ifChangedCode, TemplateUtils::sanitize($ifBlockName), $ifBlockArgs);
 }
 
 /** @internal */
 private function parseIfExpressionNonEmpty($x) { return trim($x) != ''; }
 
 /** @internal */
 private function parseIfExpressionCheckParens(TemplateCompilerEx $compiler, TemplateNodeEx $node, $level, $final = false) {
  $compiler->raiseIf(
   (($final && ($level > 0)) || $level < 0),
   $node,
   'Unbalanced parenthesis - too much '.($final ? 'opening' : 'closing').' parens ('.
   strval(($final ? $level : -$level)).')',
   TemplateError::E_INVALID_ARGUMENT
  );
 }
 
 /** @internal
  Ported from 1.0.
  I just can't make a better one. :/
 */
 private function parseIfExpression(TemplateCompilerEx $compiler, TemplateNodeEx $node, $expression) {
  $tokens = array_filter(
   preg_split(
    '/(\".+?\")|([()])|([^\s()]+)/u', $expression, -1,
    PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
   ),
   array($this, 'parseIfExpressionNonEmpty')
  );
  
  $code  = '';
  $level = 0;
  
  // syntax checking
  $_operators    = array('eq','neq','lt','lte','gt','gte','and','or','id','nid');
  $_parentheses  = array('(',')');
  $_allowedCheck = array_merge($_parentheses, $_operators, array('not'));
  $nextAllowed   = array_merge($_parentheses, array('variable', 'not'));
  
  // process tokens
  foreach ($tokens as &$token) {
   $check = (
    (in_array($token, $_allowedCheck) && !in_array($token, $nextAllowed)) ||
    (!in_array($token, $_allowedCheck) && !in_array('variable', $nextAllowed))
   );
   
   $compiler->raiseIf(
    $check,
    $node,
    'Unexpected "'.$token.'", expected one of: '.implode(', ', $nextAllowed),
    TemplateError::E_INVALID_ARGUMENT
   );
   
   if (in_array($token, $_parentheses)) {

    // encountered parenthesis
    if ($token == '(') { // open sub-expr

     ++$level;
     $code .= '(';
     $nextAllowed = array('variable', 'not', '(');

    } else { // close sub-expr

     $this->parseIfExpressionCheckParens($compiler, $node, --$level);
     $code .= ')';
     $nextAllowed = array_merge($_operators, array(')'));

    }

   } elseif (in_array($token, $_operators)) {

    // encountered operator (except "not")
    switch ($token) {
     case 'eq':  $code .= '==';  break; // equals
     case 'neq': $code .= '!=';  break; // not equals
     case 'lt':  $code .= '<';   break; // less than
     case 'lte': $code .= '<=';  break; // less than or equals
     case 'gt':  $code .= '>';   break; // greater than
     case 'gte': $code .= '>=';  break; // greater than or equals
     case 'and': $code .= '&&';  break; // logical and
     case 'or':  $code .= '||';  break; // logical or
     case 'id':  $code .= '==='; break; // is identical
     case 'nid': $code .= '!=='; break; // is not identical
    }
    
    $nextAllowed = array('variable', 'not', '(');

   } elseif ($token == 'not') {

    // encountered "not"
    $code .= '!';
    $nextAllowed = array_merge($_operators, array('variable', '('));

   } else {

    // encountered variable or literal value
    if (mb_substr($token, 0, 1) == '"') {

     // literal string
     $code .= '\''.mb_substr($token, 1, -1).'\'';

    } elseif (preg_match('/^[0-9]+(.[0-9]+)?$/', $token)) {

     // integer/float
     $code .= $token;

    } else {

     // variable
     list($variable, $filters) = TemplateUtils::split('|', $token);
     list($variable,)          = $compiler->parseVariableExpression($node, $variable);
     $variable                 = $compiler->parseFilterChain($node, $filters, '@'.$variable);
     
     $code .= $variable;

    }

    $nextAllowed = array_merge($_operators, array(')'));

   }
  }
  
  $this->parseIfExpressionCheckParens($compiler, $node, $level, true);

  return $code;
 }
 
 /** <code>{% if %}</code> tag. */
 public function handleTIf(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$tag, array &$args) {
  return
   'if('.$this->parseIfExpression($compiler, $node, implode(' ', $args)).'){'.
   $compiler->handleChildren($node->nodeChildren).
   '}';
 }
 
 /** <code>{% else %}</code> tag. */
 public function handleTElse(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$tag, array &$args) {
  // if it's {% ifchanged %} ... {% else %} ... {% endif %}
  // then it should work like {% empty %}, and this handler should not be called
  if ($node->nodeParent->nodeContent[0] == 'ifchanged') TemplateUtils::panic(__FILE__, __LINE__);
  return '}else{';
 }
 
 /** <code>{% elseif %}</code> tag. */
 public function handleTElseIf(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$tag, array &$args) {
  return '}elseif('.$this->parseIfExpression($compiler, $node, implode(' ', $args)).'){';
 }
 
 /** @internal */
 private function commonIfEqual(TemplateCompilerEx $compiler, TemplateNodeEx $node, array $variables, $operator) {
  $compiler->raiseIf(
   (count($variables) != 2),
   $node,
   'Invalid argument count - both "ifequal" and "ifnotequal" require 2 exactly',
   TemplateError::E_INVALID_ARGUMENT
  );
  
  foreach ($variables as &$variable) {
   list($variable, $filters) = TemplateUtils::split('|', $variable);
   list($code, $check)       = $compiler->parseVariableExpression($node, $variable);
   $variable                 = array($compiler->parseFilterChain($node, $filters, '@'.$code), $check);
  }
  
  $check  = $variables[0][1].$variables[1][1];
  $code   = 'if('.$variables[0][0].$operator.$variables[1][0].'){';
  $code  .= $compiler->handleChildren($node->nodeChildren).'}';
  return $check.$code;
 }
 
 /** <code>{% ifequal %}</code> tag. */
 public function handleTIfEqual(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$tag, array &$args) {
  return $this->commonIfEqual($compiler, $node, $args, '==');
 }
 
 /** <code>{% ifnotequal %}</code> tag. */
 public function handleTIfNotEqual(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$tag, array &$args) {
  return $this->commonIfEqual($compiler, $node, $args, '!=');
 }
 
 /** <code>{% include %}</code> tag. */
 public function handleTInclude(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$tag, array &$args) {
  $code  = '$__io=%s;TemplateUtils::checkIORestriction($e,\'restrictIncludeIO\',$__io,%s);';
  $code .= '$b.=$e->cachedGet($__io)->render($this->ctx,$e);unset($__io);';
  
  if (mb_substr($args[0], 0, 1) == '"') {
   $varCode  = '\''.TemplateUtils::escape(mb_substr($args[0], 1, -1)).'\'';
   $varCheck = '';
  } else {
   list($varCode, $varCheck) = $compiler->parseVariableExpression($node, $args[0]);
  }
  
  $code = $varCheck.sprintf($code, $varCode, '\''.TemplateUtils::escape($compiler->metadata['usedIO']).'\'');
  
  return $code;
 }
 
 /** <code>{% now %}</code> tag. */
 public function handleTNow(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$tag, array &$args) {
  if (mb_substr($args[0], 0, 1) == '"') {
   $format = '\''.TemplateUtils::escape(mb_substr($args[0], 1, -1)).'\'';
   $check  = '';
  } else {
   list($variable, $filters) = TemplateUtils::split('|', $args[0]);
   list($variable, $check)   = $compiler->parseVariableExpression($node, $variable);
   $format                   = $compiler->parseFilterChain($node, $filters, '@'.$variable);
  }
  
  return $check.sprintf('$b.=date(%s);', $format);
 }
 
 /** <code>{% spaceless %}</code> tag. */
 public function handleTSpaceless(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$tag, array &$args) {
  foreach ($node->nodeChildren as &$childNode) {
   if ($childNode->nodeID == 'text') {
    $childNode->nodeContent = preg_replace('/>\s+</s', '><', $childNode->nodeContent);
   }
  }
  
  return $compiler->handleChildren($node->nodeChildren);
 }
 
 /** <code>{% templatetag %}</code> tag. */
 public function handleTTemplateTag(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$tag, array &$args) {
  static $openTag       = array('openblock', 'ob', 'opentag', 'ot');
  static $openVariable  = array('openvariable', 'openvar', 'ov');
  static $openComment   = array('opencomment', 'oc');
  static $closeTag      = array('closeblock', 'cb', 'closetag', 'ct');
  static $closeVariable = array('closevariable', 'closevar', 'cv');
  static $closeComment  = array('closecomment', 'cc');
  static $map           = null;
  
  if (!$map) {
   $map = array_merge(
    // replaced array_fill_keys with array_combine/array_fill combination
    // for compatibility with PHP 5.1
    // http://pl.php.net/manual/pl/function.array-fill-keys.php#83962
    array_combine($openTag,       array_fill(0, count($openTag),       '{%')),
    array_combine($openVariable,  array_fill(0, count($openVariable),  '{{')),
    array_combine($openComment,   array_fill(0, count($openComment),   '{#')),
    array_combine($closeTag,      array_fill(0, count($closeTag),      '%}')),
    array_combine($closeVariable, array_fill(0, count($closeVariable), '}}')),
    array_combine($closeComment,  array_fill(0, count($closeComment),  '#}')),
    array('openbrace' => '{', 'closebrace' => '}') // added in 1.1a2
   );
  }
  
  $tag = &$args[0];
  
  $compiler->raiseIf(
   (!isset($map[$tag])),
   $node,
   'Invalid "templatetag" argument - expected one of: '.implode(', ', array_keys($map)),
   TemplateError::E_INVALID_ARGUMENT
  );
  
  return '$b.=\''.$map[$tag].'\';';
 }
 
 /** <code>{% widthratio %}</code> tag. */
 public function handleTWidthRatio(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$tag, array &$args) {
  $allCheck  = '';
  $widthCode = '$b.=strval(round((';
  
  for ($i = 0; $i < 2; ++$i) {
   if (preg_match('/^[0-9]+(\.[0-9]+)?$/', $args[$i])) {
    $widthCode .= $args[$i];
   } else {
    list($variable, $filters) = TemplateUtils::split('|', $args[$i]);
    list($variable, $check)   = $compiler->parseVariableExpression($node, $variable);
    $allCheck  .= $check;
    $widthCode .= $compiler->parseFilterChain($node, $filters, '@'.$variable);
   }
   $widthCode .= '/';
  }
  
  $compiler->raiseIf(
   (!preg_match('/^[0-9]+$/', $args[2])),
   $node,
   'Last argument of "widthratio" must be integer constant',
   TemplateError::E_INVALID_ARGUMENT
  );
  
  return mb_substr($widthCode, 0, -1).')*'.$args[2].'));';
 }
 
 /** <code>{% with %}</code> tag. */
 public function handleTWith(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$tag, array &$args) {
  $withBlockName = $compiler->generateUniqueBlock($args[0].$args[2], 'with:');
  
  $compiler->raiseIf(
   ($args[1] != 'as'),
   $node,
   'Second "with" parameter must be literal "as"',
   TemplateError::E_INVALID_ARGUMENT
  );
  
  $compiler->blocks[$withBlockName] = '$b=\'\';';
  list($variable, $filters) = TemplateUtils::split('|', $args[0]);
  list($variable, $check)   = $compiler->parseVariableExpression($node, $variable);
  $variable                 = $compiler->parseFilterChain($node, $filters, '@'.$variable);
  
  $compiler->blocks[$withBlockName] .= $check.'$this->ctx[\''.$args[2].'\']=';
  $compiler->blocks[$withBlockName] .= $variable.';'.$compiler->handleChildren($node->nodeChildren);
  $compiler->blocks[$withBlockName] .= 'unset($this->ctx[\''.$args[2].'\']);return $b;';
  
  return '$b.=$this->_'.TemplateUtils::sanitize($withBlockName).'($e);';
 }
 
 /** <code>{% putblock %}</code> tag. */
 public function handleTPutBlock(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$tag, array &$args) {
  $blockName = 'block:'.$args[0];
  $blockCall = '$b.=$this->_'.TemplateUtils::sanitize($blockName).'($e);';
  
  if (isset($args[1]) && $args[1] == 'strict') {
   $compiler->raiseIf(
    (!isset($compiler->blocks[$blockName])),
    $node,
    'Block "'.$args[0].'" does not exist (strict mode used in "putblock")',
    TemplateError::E_INVALID_ARGUMENT
   );
   
   return $blockCall;
  } else {
   $code  = 'if(is_callable(array($this,\'_'.TemplateUtils::sanitize($blockName).'\'))){';
   $code .= $blockCall;
   $code .= '}';
   
   return $code;
  }
 }
 
 /** <code>{% call %}</code> tag. */
 public function handleTCall(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$tag, array &$args) {
  $argCount = count($args);
  
  if ($argCount >= 3 && $args[$argCount - 2] == 'as') {
   // {% call <function> [<arg> [<arg> [...]]] as <variable> %}
   $destCode  = '$this->ctx[\''.TemplateUtils::escape($args[$argCount - 1]).'\']=';
   $argCount -= 2;
   $args      = array_slice($args, 0, -2);
  } else {
   // {% call <function> [<arg> [<arg> [...]]] %}
   $destCode  = '$b.=';
  }
  
  $checkCode = '';
  $function  = $args[0];
  
  foreach ($args as &$arg) {
   if (mb_substr($arg, 0, 1) == '"') {
    $arg = '\''.TemplateUtils::escape(mb_substr($arg, 1, -1)).'\'';
   } else {
    list($arg, $filters) = TemplateUtils::split('|', $arg);
    list($arg, $check)   = $compiler->parseVariableExpression($node, $arg);
    $arg                 = $compiler->parseFilterChain($node, $filters, '@'.$arg);
    $checkCode          .= $check;
   }
  }
  
  return
   $checkCode.
   '$_fn='.$args[0].';'.
   'TemplateUtils::checkIfAllowed($e,\'function\',$_fn);'.
   'if(!is_callable($_fn)){$this->invalidVar(\''.TemplateUtils::escape($function).'\','.
   '\'callable expected\');}'.
   $destCode.'call_user_func($_fn'.
   (count($args) > 1 ? ','.implode(',', array_slice($args, 1)) : '').
   ');';
 }
 
 /** <code>{% meta %}</code> tag. */
 public function handleTMeta(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$tag, array &$args) {
  $args[0] = 'user:'.$args[0];
  $args[1] = mb_substr($args[1], 1, -1);
  
  $compiler->metadata[$args[0]] = $args[1];
  
  return '';
 }
 
 //
 // Std filters
 //
 
 /** @c add filter. */
 public function handleFAdd(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  list($argType, $argValue) = $args[0];
  $operator                 = ($argType == 'string' ? '.' : '+');
  
  return '(%s'.$operator.$argValue.')';
 }
 
 /** @c addslashes filter. */
 public function handleFAddSlashes(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  return 'addslashes(%s)';
 }
 
 /** @c capfirst filter. */
 public function handleFCapFirst(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  return '(mb_strtoupper(mb_substr(($__v=%s),0,1)).mb_substr($__v,1))';
 }
 
 /** @c cut filter. */
 public function handleFCut(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  list($cutType, $cutValue) = $args[0];
  if ($cutType == 'variable') {
   $cut = 'preg_quote('.$cutValue.',\'~\')';
  } elseif ($cutType == 'string') {
   $cut = preg_quote($cutValue, '~');
  }
  
  return 'preg_replace(\'~\'.'.$cut.'.\'~u\',\'\',%s)';
 }
 
 /** @c date filter. */
 public function handleFDate(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  return 'date('.$args[0][1].',%s)';
 }
 
 /** @c default filter. */
 public function handleFDefault(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  return '(($__v=%s)?$__v:'.$args[0][1].')';
 }
 
 /** @c default_if_none filter. */
 public function handleFDefaultIfNone(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  return '(($__v=%s)!==null?$__v:'.$args[0][1].')';
 }
 
 /** @c divisibleby filter. */
 public function handleFDivisibleBy(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  list($argType, $argValue) = $args[0];
  
  if ($argType == 'string') {
   $compiler->raise(
    $node,
    'Filter "divisibleby" does not support string argument',
    TemplateError::E_INVALID_ARGUMENT
   );
  } elseif ($argType == 'number' && $argValue == 0) {
   $compiler->raise(
    $node,
    'Filter "divisibleby" does not support "0" numeric argument',
    TemplateError::E_INVALID_ARGUMENT
   );
  }
  
  return '((%s%%((int)'.$args[0][1].'))==0)';
 }
 
 /** @c escape filter. */
 public function handleFEscape(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  return 'htmlspecialchars(%s)';
 }
 
 /** @c filesizeformat filter. */
 public function handleFFileSizeFormat(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  if (!isset($compiler->blocks['filter:filesizeformat'])) {
   $_b  = (string)(1024);
   $_kB = (string)(1024*1024);
   $_MB = (string)(1024*1024*1024);
   
   $compiler->blocks['filter:filesizeformat'] =
    '$v=func_get_arg(1);if($v<'.$_b.'){return $v.\' b\';}'.
    'elseif($v<'.$_kB.'){return round($v/'.$_b.',2).\' kB\';}'.
    'elseif($v<'.$_MB.'){return round($v/'.$_kB.',2).\' MB\';}'.
    'else{return round($v/'.$_MB.',2).\' GB\';}';
  }
  
  return '$this->_filter_filesizeformat($e,%s)';
 }
 
 /** @c fix_ampersands filter. */
 public function handleFFixAmpersands(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  return 'str_replace(\'&\',\'&amp;\',%s)';
 }
 
 /** @c join filter. */
 public function handleFJoin(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  return 'implode('.$args[0][1].',%s)';
 }
 
 /** @c length filter. */
 public function handleFLength(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  return '(is_string(($__v=%s))?mb_strlen($__v):count($__v))';
 }
 
 /** @c length_is filter. */
 public function handleFLengthIs(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  return '('.$this->handleFLength($compiler, $node, $filter, $args).'=='.$args[0][1].')';
 }
 
 /** @c linebreaks filter. */
 public function handleFLineBreaks(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  if (!isset($compiler->blocks['filter:linebreaks'])) {
   // FIXME: handle \r linebreaks?
   $compiler->blocks['filter:linebreaks'] =
    '$v=func_get_arg(1);$v=str_replace("\r\n","\n",$v);$ps=preg_split("/\n{2,}/",$v);'.
    'foreach($ps as &$p){$p=\'<p>\'.str_replace("\n",\'<br />\',trim($p)).\'</p>\';}'.
    'return implode("\n\n",$ps);';
  }
  
  return '$this->_filter_linebreaks($e,%s)';
 }
 
 /** @c linebreaksbr filter. */
 public function handleFLineBreaksBR(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  return 'nl2br(%s)';
 }
 
 /** @internal */
 private function commonFJust($sign, TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, &$width) {
  $compiler->raiseIf(
   ($width[0] == 'string'),
   $node,
   'Filter "'.$filter.'" does not support string argument ',
   TemplateError::E_INVALID_ARGUMENT
  );
  
  switch ($width[0]) {
   case 'number':   $width = $width[1]; break;
   case 'variable': $width = '\'.(int)'.$width[1].'.\''; break;
  }
  
  return 'sprintf(\'%%'.$sign.$width.'s\',%s)';
 }
 
 /** @c ljust filter. */
 public function handleFLJust(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  return $this->commonFJust('-', $compiler, $node, $filter, $args[0]);
 }
 
 /** @c lower filter. */
 public function handleFLower(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  return 'mb_strtolower(%s)';
 }
 
 /** @c make_list filter. */
 public function handleFMakeList(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  return 'str_split(%s)';
 }
 
 /** @c pluralize filter. */
 public function handleFPluralize(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  $singularSuffix = '';
  $pluralSuffix   = 's';
  
  if (isset($args[0])) {
   $compiler->raiseIf(
    ($args[0][0] != 'string'),
    $node,
    'Filter "pluralize" does not support variable/numeric suffix argument',
    TemplateError::E_INVALID_ARGUMENT
   );
   
   $suffixes = TemplateUtils::split(',', mb_substr($args[0][1], 1, -1));
   if ($suffixes[1] == '') {
    $pluralSuffix = $suffixes[0];
   } else {
    list($singularSuffix, $pluralSuffix) = $suffixes;
   }
   
   $singularSuffix = TemplateUtils::escape($singularSuffix);
   $pluralSuffix   = TemplateUtils::escape($pluralSuffix);
  }
  
  return '((%s)>1?\''.$pluralSuffix.'\':\''.$singularSuffix.'\')';
 }
 
 /** @c random filter. */
 public function handleFRandom(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  if (!isset($compiler->blocks['filter:random'])) {
   $compiler->blocks['filter:random'] = '$v=func_get_arg(1);return $v[array_rand($v)];';
  }
  
  return '$this->_filter_random($e,%s)';
 }
 
 /** @c removetags filter. */
 public function handleFRemoveTags(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  return 'strip_tags(%s)';
 }
 
 /** @c rjust filter. */
 public function handleFRJust(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  return $this->commonFJust('', $compiler, $node, $filter, $args[0]);
 }
 
 /** @c slugify filter. */
 public function handleFSlugify(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  if (!isset($compiler->blocks['filter:slugify'])) {
   $compiler->blocks['filter:slugify'] =
    '$v=func_get_arg(1);$v=mb_strtolower($v);$v=strip_tags($v);'.
    '$v=preg_replace(\'~\s+|\_~\', \'-\',$v);'.
    '$v=preg_replace(\'~\-+~\',\'-\',$v);'.
    '$v=preg_replace(\'~(^\-+)|(\-+$)|[^a-z0-9\-]~ui\',\'\',$v);'.
    'return $v;';
  }
  
  return '$this->_filter_slugify($e,%s)';
 }
 
 /** @c title filter. */
 public function handleFTitle(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  return 'mb_convert_case(%s,MB_CASE_TITLE)';
 }
 
 /** @c upper filter. */
 public function handleFUpper(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  return 'mb_strtoupper(%s)';
 }
 
 /** @c urlencode filter. */
 public function handleFURLEncode(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  return 'urlencode(%s)';
 }
 
 /** @c urldecode filter. */
 public function handleFURLDecode(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  return 'urldecode(%s)';
 }
 
 /** @c wordcount filter. */
 public function handleFWordCount(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  return 'str_word_count(%s)';
 }
 
 /** @c wordwrap filter. */
 public function handleFWordWrap(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$filter, array &$args) {
  return 'wordwrap(%s,'.$args[0][1].',"\n",true)';
 }
 
 //
 // Std hooks
 //
 
 /**
  Auto-escaping hook.
  Hooked into @c parseFilterChain:entry.
 */
 public function handleHAutoEscape(TemplateCompilerEx $compiler, array &$filterChain) {
  $safe = false;
  
  if (in_array('safe', $filterChain)) {
   // we always remove pseudofilter "safe" - even if autoescaping is disabled
   // otherwise syntax error may occur
   $filterChain = array_values(array_diff($filterChain, array('safe')));
   $safe        = true;
  }
  
  if (!$compiler->settings['autoEscape']) return false;
  
  // check whether we're called from within handleVariable
  // backtrace index is hardcoded, and should always be the same
  // (handleVariable -> parseFilterChain -> runHooks -> call_user_func_array -> handleHAutoEscape)
  //  ^ 4               ^ 3                 ^ 2         ^ 1                     ^ 0
  // if you ever run into a problem with this, report it, and loop-based checking
  // will be used instead
  $trace = debug_backtrace();
  if (!isset($trace[4]) || $trace[4]['function'] != 'handleVariable') return false;
  
  if (!$safe) {
   $filterChain[] = 'escape';
  }
  
  return false;
 }

 /**
  <code>{{ internal }}</code> variable handler.
  Hooked into @c parseVariableExpression:postCodeGen.
 */
 public function handleHInternalVariable(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$variableCode, &$variableCheck) {
  static $subVariables = null;
  static $internalProlog = '/^\$this->ctx\[\'internal\'\]/u';
  
  if (!$subVariables) {
   $subVariables = array(
    // variable => array(code regex, replacement code, setting to allow/disallow)
    'request' => array(
     '^\[\'request\'\]\[\'(POST|GET|COOKIE|SERVER|REQUEST|SESSION|ENV)\'\]',
     '\\$_${1}', 'allowInternalRequest'
    ),
    'version' => array('^\[\'version\'\]$', '\''.SITHTEMPLATE_VERSION.'\'', null),
    'const'   => array(
     '^\[\'const\'\]\[\'(.*?)\'\]$', 'constant(\'${1}\')', 'allowInternalConstants'
    ),
   );
  }
  
  if (preg_match($internalProlog, $variableCode)) {
   $variableCode  = preg_replace($internalProlog, '', $variableCode);
   $variableCheck = '';
   
   $match = false;
   foreach ($subVariables as $pattern) {
    if (preg_match('/'.$pattern[0].'/u', $variableCode)) {
     $compiler->raiseIf(
      (!is_null($pattern[2]) && !$compiler->settings[$pattern[2]]),
      $node,
      '"internal" restricted by "'.$pattern[2].'" setting',
      TemplateError::E_SECURITY_VIOLATION
     );
     
     $variableCode = preg_replace('/'.$pattern[0].'/u', $pattern[1], $variableCode);
     $match = true;
     break;
    }
   }
   
   $compiler->raiseIf(
    (!$match),
    $node,
    'Invalid "internal" variable syntax - no matching subvariable found, tried: '.
    implode(', ', array_keys($subVariables)),
    TemplateError::E_INVALID_SYNTAX
   );
  }
 }
 
 /**
  <code>{{ forloop }}</code> variable handler.
  Hooked into @c parseVariableExpression:postCodeGen.
 */
 public function handleHForLoopVariable(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$variableCode, &$variableCheck) {
  if (isset($compiler->varInLoop) && $compiler->varInLoop && mb_strpos($variableCode, '[\'forloop\']') !== false) {
   $variableCode  = str_replace('[\'forloop\']', '[$f]', $variableCode);
   $variableCheck = '';
  }
  
  return false;
 }

 /**
  <code>{{ block }}</code> variable handler.
  Hooked into @c parseVariableExpression:postCodeGen.
 */
 public function handleHBlockVariable(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$variableCode, &$variableCheck) {
  if (isset($compiler->varInBlock) && $compiler->varInBlock && mb_strpos($variableCode, '[\'block\'][\'super\']') !== false) {
   $compiler->raiseIf(
    (!isset($compiler->metadata['parentTemplate'])),
    $node,
    'Invalid use of "block.super" - no parent template',
    TemplateError::E_INVALID_SYNTAX
   );
   
   $variableCode  = 'parent::_'.TemplateUtils::sanitize(end($compiler->varInBlock)).'($e)';
   $variableCheck = '';
  }
  
  return false;
 }
}
