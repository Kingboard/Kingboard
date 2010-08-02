<?php
/** @file SithTemplate.php
 Entry point of the SithTemplate library, containing global constants
 and SPL autoloader.
 
 Available constants:
  
  <ul>
   <li>SITHTEMPLATE_VERSION       - non-overridable, contains current version of the library</li>
   <li>SITHTEMPLATE_DIR           - overridable, contains path to library's files</li>
   <li>SITHTEMPLATE_NO_AUTOLOADER - if defined, autoloader won't be registered with SPL</li>
   <li>
    SITHTEMPLATE_MBSTRING_UTF8 - if defined, mbstring internal encoding won't be changed to UTF-8
    (note that no other setting is tested, and therefore library may fail to work properly)
   <li>
  </ul>
 
 @author PiotrLegnica
 @license{New BSD License}
*/
/** @mainpage SithTemplate - open-source template engine for PHP5
 @section license License
  Copyright (c) 2007-2009, PiotrLegnica
  
  All rights reserved.
  
  Redistribution and use in source and binary forms, with or without modification,
  are permitted provided that the following conditions are met:
  
  <ul>
   <li>
    Redistributions of source code must retain the above copyright notice,
    this list of conditions and the following disclaimer.
   </li>
   <li>
    Redistributions in binary form must reproduce the above copyright notice,
    this list of conditions and the following disclaimer in the documentation and/or
    other materials provided with the distribution.
   </li>
   <li>
    Neither the name of the author nor the names of its contributors may be used to endorse
    or promote products derived from this software without specific prior written permission.
   </li>
  </ul>
  
  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
  "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
  LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
  A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
  CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
  EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
  PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
  PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
  LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
  NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
  SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 
 @section main-intro Introduction
  SithTemplate is a flexible and extensible PHP templating engine,
  inspired primarily by Django.
  
  This is documentation for 1.1 line of the library, which obsoletes
  older 0.1-1.0 line.
  
  @subsection main-intro-why Why should I try?
   SithTemplate has number of useful features, so I think you should
   at least try it, if you're looking for a template engine for PHP.
   
   <ul>
    <li>
     SithTemplate is <strong>small</strong> -
     library itself is about ~80kB of code, ~1000 SLOC (without comments) in 12 classes (in 10 files), and
     standard library is single ~45kB plugin file, ~900 SLOC (without comments)
     [measure based on Mercurial head, as of 10-08-2009].
    </li>
    <li>
     SithTemplate has relatively <strong>small overhead</strong> - as library tries to shift the
     overhead from runtime part to the compiler, it has low memory usage. As for speed - it may not be
     the fastest one available (especially since it [ab]uses slowish PHP object system - no other engine
     I know generates PHP classes for templates) but it's not the slowest one either -
     speed overhead is (IMHO at least) reasonable and pretty stable
     (especially for repeated template rendering). And it's opcode-cache friendly.
    </li>
    <li>
     SithTemplate is <strong>free</strong> and <strong>open-source</strong>,
     with very permissive licensing - used <strong>New BSD License</strong> allows it to be included
     in both free and commercial, open- and closed-source projects.
    </li>
    <li>
     SithTemplate is <strong>generic</strong>, and can process any kind of plain text, not only
     (X)HTML, like some engines. Web layouts, e-mails, code, configuration files, anything is possible.
    </li>
    <li>
     SithTemplate is <strong>extensible</strong> - if included I/O drivers or tags/filters are not
     enough for your project, you can easily add your own.
    </li>
    <li>
     SithTemplate has an <strong>easy API</strong> - create one object, call one method
     and your template output is ready to be displayed. It also has a
     <strong>simple, plain-text syntax</strong>, based on Django. No XML writing required, ever!
    </li>
    <li>
     SithTemplate supports a <strong>multi-zoned template inheritance</strong> - feature ported from
     Django (Python web framework) that together with simple syntax makes templates more clear and maintainable,
     and hierarchical ones feel more natural (than using e.g. header/footer inclusion in every template).
    </li>
    <li>
     SithTemplate is <strong>Unicode-aware</strong>. It uses mbstring routines exclusively, both in
     the core and in the standard library, instead of the core PHP string manipulation functions.
     It introduces additional dependency, and maybe even slight performance degradation, but
     it also makes library safer when UTF-8-encoded data is manipulated. And standard filters are
     shorter and locale-independent (like @c lower).
    </li>
    <li>
     SithTemplate has been <strong>tested in production</strong>, and while internals and API has
     changed in 1.1, established concepts and template syntax are the same. Library is also tested
     by an automated unit test suite, with over 200 test cases, which keeps old bugs from reappearing.
     It is tested on three different PHP versions before releasing, ensuring that it will work without
     compatibility problems on all supported configurations.
    </li>
    <li>
     SithTemplate tries to stay <strong>compatible with Django</strong>, and while it introduces its
     own extensions to the standard library, there are just slight differences in the syntax
     (the most significant is in the variable access). So if you know Django, then you will have
     little or no problems using SithTemplate. Also most of the Django's standard tags and filters are
     implemented in the current version of the library.
    </li>
   </ul>
  
  @subsection main-intro-req Requirements
   SithTemplate requires PHP5 (at least 5.1, but 5.2 is recommended) with SPL and mbstring enabled.
   
   PHP versions 5.0 and 6.x are not supported. The library is tested
   with PHPUnit tests on PHP 5.1, 5.2 and 5.3 before releasing.
   
   Test suite also requires PHPUnit 3.2.
   
   Runtime part of the library requires at least ~200kB of free RAM in simplest cases, and the compiler
   requires at least 1MB.
 
 @section main-tutorial Tutorial
  If you want to try SithTemplate, follow tutorial on page @ref tutorial
  to get yourself familiar with library's concepts and usage.
 
 @section main-stdlib Standard library reference
  See @ref stdlib page for reference on standard tags and filters.
 
 @section main-extending Extending SithTemplate
  If you're interested in extending library's capabilities, see
  @ref extending-st page.
 
 @section main-bugs Reporting bugs, getting support
  You're encouraged to file all found bugs to the project's
  <a href="http://bitbucket.org/piotrlegnica/sithtemplate/issues/">bugtracker</a>,
  even if you're not sure whether it's library's bug, or just
  your mistake.

  To get support or discuss about SithTemplate, you can visit
  project's IRC channel, <a href="irc://chat.freenode.net/sithtemplate">\#sithtemplate</a>
  on Freenode network.

  Remember to use English in both cases.
*/

/**
 SPL autoloader for SithTemplate
 
 @param $cls Class to load
 @since 0.4.0
*/
function sithtemplate_spl_autoload($cls) {
 // autoloader map
 static $_autoload_map = array(
  'templateenviron'    => 'Environment.php',
  'template'           => 'Base.php',
  'templateerror'      => 'Error.php',
  'templateplugins'    => 'Plugins.php',
  'templatecompilerex' => 'CompilerEx.php',
  'templatenodeex'     => 'CompilerEx.php',
  'itemplateplugin'    => 'api/IPlugin.php',
  'itemplateiodriver'  => 'api/IIODriver.php',
  'templateutils'      => 'Utils.php',
  'templatefileio'     => 'IO.php',
  'templatestringio'   => 'IO.php',
  'templateio'         => 'IO.php',
 );
 
 $cls = strtolower($cls);
 
 if (!isset($_autoload_map[$cls]) || !is_readable(SITHTEMPLATE_DIR.$_autoload_map[$cls])) {
  return false;
 }
 
 include_once $_autoload_map[$cls];
 return true;
}

/**
 Current version of the library.
*/
define('SITHTEMPLATE_VERSION', '1.1a2');

if (!defined('SITHTEMPLATE_DIR')) {
 define('SITHTEMPLATE_DIR', pathinfo(__FILE__, PATHINFO_DIRNAME).'/');
}

if (!defined('SITHTEMPLATE_NO_AUTOLOADER')) {
 spl_autoload_register('sithtemplate_spl_autoload');
}

if (!defined('SITHTEMPLATE_MBSTRING_UTF8')) {
 mb_internal_encoding('UTF-8');
}
