<?php
/** @file TemplateTestsEx.php
 Test suite for SithTemplate. "Official" method of running
 it is the run.cmd batch script - see run-config.in and env/README files
 for details on how to configure the runner.
 
 @since 1.1a0
 @license{New BSD License}
 @author PiotrLegnica
*/

ini_set('display_errors', 'on');
// PHPUnit
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
// SithTemplate
require_once '../lib/SithTemplate.php';

/**
 Main mega-suite, containing all test cases for the library.
 It sucks, but it works.
*/
class TemplateTestsEx extends PHPUnit_Framework_TestCase {
 /**
  @ref TemplateEnviron object.
 */
 protected $environ;
 /**
  @ref TemplateCompilerEx object.
 */
 protected $compiler;
 
 /**
  setUp is called before every test case, to setup a clean environment.
 */
 public function setUp() {
  @mkdir('./data/tplc');
  @mkdir(($outputPrefix = './data/tplc/'.phpversion().'/'));
  
  $this->environ                           = TemplateEnviron::createFromINI('./data/conf.ini');
  $this->environ->settings['outputPrefix'] = $outputPrefix;
  $this->compiler                          = new TemplateCompilerEx;
  $this->compiler->settings                = &$this->environ->settings;
  $this->environ->compiler                 = $this->compiler;
  
  $this->skipReflection = array('method' => array(), 'argument' => array());
  $this->skipSPL        = array();
  
  if (version_compare(PHP_VERSION, '5.2.0', '<')) {
   $this->skipReflection['argument'] = array('getPosition');
   $this->skipSPL                    = array('objectHash');
  }
  
  
  $this->compiler->reset();
 }
 
 /**
  tearDown is called after every test case, to cleanup the environment.
 */
 public function tearDown() {
  unset($this->compiler);
  unset($this->environ);
 }
 
 /**
  Public API specification test provider.
 */
 public static function providerAPISpecification() {
  $commonGetArguments = array(
   array(
    'getName' => 'template', 'isOptional' => false, 'getPosition' => 0, 'isArray' => false,
    'isPassedByReference' => false, 'isDefaultValueAvailable' => false
   ),
   array(
    'getName' => 'mode', 'isOptional' => true, 'getPosition' => 1, 'isArray' => false,
    'isPassedByReference' => false, 'isDefaultValueAvailable' => true, 'getDefaultValue' => null
   )
  );
  $commonIOArguments = array(
   array(
    'getName' => 'settings', 'isOptional' => false, 'getPosition' => 0, 'isArray' => true,
    'isPassedByReference' => true, 'isDefaultValueAvailable' => false
   ),
   array(
    'getName' => 'template', 'isOptional' => false, 'getPosition' => 1, 'isArray' => false,
    'isPassedByReference' => true, 'isDefaultValueAvailable' => false
   )
  );
  $commonCompileArguments = array(
   array(
    'getName' => 'io', 'isOptional' => false, 'getPosition' => 0, 'isArray' => false,
    'isPassedByReference' => false, 'isDefaultValueAvailable' => false, 'getClass' => 'ITemplateIODriver'
   ),
   array(
    'getName' => 'template', 'isOptional' => false, 'getPosition' => 1, 'isArray' => false,
    'isPassedByReference' => false, 'isDefaultValueAvailable' => false,
   ),
  );
  return array(
   // <class>, <method>, <method's constraints>, <arguments' constraints>
   //
   // TemplateEnviron
   //
   array(
    'TemplateEnviron', '__construct',
    array('isConstructor' => true, 'getNumberOfParameters' => 1, 'getNumberOfRequiredParameters' => 0),
    array(
     array(
      'getName' => 'settings', 'isOptional' => true, 'getPosition' => 0, 'isArray' => true,
      'isDefaultValueAvailable' => true, 'getDefaultValue' => array(), 'isPassedByReference' => false,
     )
    )
   ),
   array(
    'TemplateEnviron', 'createFromINI',
    array('isStatic' => true, 'getNumberOfParameters' => 1, 'getNumberOfRequiredParameters' => 1),
    array(
     array(
      'getName' => 'settingsINI', 'isOptional' => false, 'getPosition' => 0, 'isArray' => false,
      'isDefaultValueAvailable' => false, 'isPassedByReference' => false,
     )
    )
   ),
   array(
    'TemplateEnviron', 'compile',
    array('isStatic' => false, 'getNumberOfParameters' => 2, 'getNumberOfRequiredParameters' => 2),
    $commonCompileArguments
   ),
   array(
    'TemplateEnviron', 'include_',
    array('isStatic' => false, 'getNumberOfParameters' => 3, 'getNumberOfRequiredParameters' => 1),
    array_merge(
     $commonGetArguments,
     array(
      array(
       'getName' => 'returnMeta', 'isOptional' => true, 'getPosition' => 2, 'isArray' => false,
       'isPassedByReference' => false, 'isDefaultValueAvailable' => true, 'getDefaultValue' => false
      )
     )
    )
   ),
   array(
    'TemplateEnviron', 'get',
    array('isStatic' => false, 'getNumberOfParameters' => 2, 'getNumberOfRequiredParameters' => 1),
    $commonGetArguments
   ),
   array(
    'TemplateEnviron', 'getMeta',
    array('isStatic' => false, 'getNumberOfParameters' => 2, 'getNumberOfRequiredParameters' => 1),
    $commonGetArguments
   ),
   array(
    'TemplateEnviron', 'cachedGet',
    array('isStatic' => false, 'getNumberOfParameters' => 2, 'getNumberOfRequiredParameters' => 1),
    $commonGetArguments
   ),
   array(
    'TemplateEnviron', 'render',
    array('isStatic' => false, 'getNumberOfParameters' => 3, 'getNumberOfRequiredParameters' => 2),
    array(
     array(
      'getName' => 'template', 'isOptional' => false, 'getPosition' => 0, 'isArray' => false,
      'isPassedByReference' => false, 'isDefaultValueAvailable' => false
     ),
     array(
      'getName' => 'context', 'isOptional' => false, 'getPosition' => 1, 'isArray' => true,
      'isPassedByReference' => false, 'isDefaultValueAvailable' => false
     ),
     array(
      'getName' => 'mode', 'isOptional' => true, 'getPosition' => 2, 'isArray' => false,
      'isPassedByReference' => false, 'isDefaultValueAvailable' => true, 'getDefaultValue' => null
     )
    )
   ),
   //
   // TemplateIO
   //
   array(
    'TemplateIO', 'register',
    array('isStatic' => true, 'getNumberOfParameters' => 2, 'getNumberOfRequiredParameters' => 2),
    array(
     array(
      'getName' => 'driver', 'isOptional' => false, 'getPosition' => 0, 'isArray' => false,
      'isPassedByReference' => false, 'isDefaultValueAvailable' => false
     ),
     array(
      'getName' => 'className', 'isOptional' => false, 'getPosition' => 1, 'isArray' => false,
      'isPassedByReference' => false, 'isDefaultValueAvailable' => false
     )
    )
   ),
   array(
    'TemplateIO', 'get',
    array('isStatic' => true, 'getNumberOfParameters' => 1, 'getNumberOfRequiredParameters' => 1),
    array(
     array(
      'getName' => 'driver', 'isOptional' => false, 'getPosition' => 0, 'isArray' => false,
      'isPassedByReference' => false, 'isDefaultValueAvailable' => false
     ),
    )
   ),
   //
   // Template
   //
   array(
    'Template', 'render',
    array('isStatic' => false, 'isFinal' => true, 'getNumberOfParameters' => 2, 'getNumberOfRequiredParameters' => 2),
    array(
     array(
      'getName' => 'ctx', 'isOptional' => false, 'getPosition' => 0, 'isArray' => true,
      'isPassedByReference' => false, 'isDefaultValueAvailable' => false
     ),
     array(
      'getName' => 'environ', 'isOptional' => false, 'getPosition' => 1, 'isArray' => false,
      'getClass' => 'TemplateEnviron', 'isPassedByReference' => false, 'isDefaultValueAvailable' => false
     )
    )
   ),
   //
   // ITemplateIODriver
   //
   array(
    'ITemplateIODriver', 'upToDate',
    array('isStatic' => false, 'isAbstract' => true, 'getNumberOfParameters' => 3, 'getNumberOfRequiredParameters' => 3),
    array_merge($commonIOArguments, array(
     array(
      'getName' => 'mode', 'isOptional' => false, 'getPosition' => 2, 'isArray' => false,
      'isPassedByReference' => false, 'isDefaultValueAvailable' => false
     ),
    ))
   ),
   array(
    'ITemplateIODriver', 'includeCode',
    array('isStatic' => false, 'isAbstract' => true, 'getNumberOfParameters' => 2, 'getNumberOfRequiredParameters' => 2),
    $commonIOArguments
   ),
   array(
    'ITemplateIODriver', 'className',
    array('isStatic' => false, 'isAbstract' => true, 'getNumberOfParameters' => 2, 'getNumberOfRequiredParameters' => 2),
    $commonIOArguments
   ),
   array(
    'ITemplateIODriver', 'loadTemplate',
    array('isStatic' => false, 'isAbstract' => true, 'getNumberOfParameters' => 2, 'getNumberOfRequiredParameters' => 2),
    $commonIOArguments
   ),
   array(
    'ITemplateIODriver', 'loadMetadata',
    array('isStatic' => false, 'isAbstract' => true, 'getNumberOfParameters' => 2, 'getNumberOfRequiredParameters' => 2),
    $commonIOArguments
   ),
   array(
    'ITemplateIODriver', 'saveTemplate',
    array('isStatic' => false, 'isAbstract' => true, 'getNumberOfParameters' => 3, 'getNumberOfRequiredParameters' => 3),
    array_merge($commonIOArguments, array(
     array(
      'getName' => 'code', 'isOptional' => false, 'getPosition' => 2, 'isArray' => false,
      'isPassedByReference' => true, 'isDefaultValueAvailable' => false
     )
    ))
   ),
   array(
    'ITemplateIODriver', 'saveMetadata',
    array('isStatic' => false, 'isAbstract' => true, 'getNumberOfParameters' => 3, 'getNumberOfRequiredParameters' => 3),
    array_merge($commonIOArguments, array(
     array(
      'getName' => 'metadata', 'isOptional' => false, 'getPosition' => 2, 'isArray' => true,
      'isPassedByReference' => true, 'isDefaultValueAvailable' => false
     )
    ))
   ),
   //
   // ITemplatePlugin
   //
   array(
    'ITemplatePlugin', 'providedHandlers',
    array('isStatic' => false, 'isAbstract' => true, 'getNumberOfParameters' => 0)
   ),
   //
   // TemplateCompilerEx
   //
   array(
    'TemplateCompilerEx', '__construct',
    array('isStatic' => false, 'isConstructor' => true, 'getNumberOfParameters' => 0)
   ),
   array(
    'TemplateCompilerEx', 'reset',
    array('isStatic' => false, 'getNumberOfParameters' => 0)
   ),
   array(
    'TemplateCompilerEx', 'compile',
    array('isStatic' => false, 'getNumberOfParameters' => 2, 'getNumberOfRequiredParameters' => 2),
    $commonCompileArguments
   ),
   array(
    'TemplateCompilerEx', 'handleChildren',
    array('isStatic' => false, 'getNumberOfParameters' => 1, 'getNumberOfRequiredParameters' => 1),
    array(
     array(
      'getName' => 'children', 'isOptional' => false, 'getPosition' => 0, 'isArray' => true,
      'isPassedByReference' => true, 'isDefaultValueAvailable' => false
     )
    )
   ),
   array(
    'TemplateCompilerEx', 'createBlock',
    array('isStatic' => false, 'getNumberOfParameters' => 2, 'getNumberOfRequiredParameters' => 2),
    array(
     array(
      'getName' => 'block', 'isOptional' => false, 'getPosition' => 0, 'isArray' => false,
      'isPassedByReference' => false, 'isDefaultValueAvailable' => false
     ),
     array(
      'getName' => 'node', 'isOptional' => false, 'getPosition' => 1, 'isArray' => false,
      'isPassedByReference' => false, 'getClass' => 'TemplateNodeEx', 'isDefaultValueAvailable' => false
     )
    )
   ),
   array(
    'TemplateCompilerEx', 'parseVariableExpression',
    array('isStatic' => false, 'getNumberOfParameters' => 2, 'getNumberOfRequiredParameters' => 2),
    array(
     array(
      'getName' => 'node', 'isOptional' => false, 'getPosition' => 0, 'isArray' => false,
      'isPassedByReference' => false, 'getClass' => 'TemplateNodeEx', 'isDefaultValueAvailable' => false
     ),
     array(
      'getName' => 'variable', 'isOptional' => false, 'getPosition' => 1, 'isArray' => false,
      'isPassedByReference' => false, 'isDefaultValueAvailable' => false
     ),
    )
   ),
   array(
    'TemplateCompilerEx', 'parseFilterChain',
    array('isStatic' => false, 'getNumberOfParameters' => 3, 'getNumberOfRequiredParameters' => 3),
    array(
     array(
      'getName' => 'node', 'isOptional' => false, 'getPosition' => 0, 'isArray' => false,
      'isPassedByReference' => false, 'getClass' => 'TemplateNodeEx', 'isDefaultValueAvailable' => false
     ),
     array(
      'getName' => 'filterExpr', 'isOptional' => false, 'getPosition' => 1, 'isArray' => false,
      'isPassedByReference' => false, 'isDefaultValueAvailable' => false
     ),
     array(
      'getName' => 'code', 'isOptional' => false, 'getPosition' => 2, 'isArray' => false,
      'isPassedByReference' => false, 'isDefaultValueAvailable' => false
     ),
    )
   ),
   array(
    'TemplateCompilerEx', 'raiseIf',
    array('isStatic' => false, 'getNumberOfParameters' => 4, 'getNumberOfRequiredParameters' => 4),
    array(
     array(
      'getName' => 'cond', 'isOptional' => false, 'getPosition' => 0, 'isArray' => false,
      'isPassedByReference' => false, 'isDefaultValueAvailable' => false
     ),
     array(
      'getName' => 'node', 'isOptional' => false, 'getPosition' => 1, 'isArray' => false,
      'isPassedByReference' => false, 'getClass' => 'TemplateNodeEx', 'isDefaultValueAvailable' => false
     ),
     array(
      'getName' => 'message', 'isOptional' => false, 'getPosition' => 2, 'isArray' => false,
      'isPassedByReference' => false, 'isDefaultValueAvailable' => false
     ),
     array(
      'getName' => 'code', 'isOptional' => false, 'getPosition' => 3, 'isArray' => false,
      'isPassedByReference' => false, 'isDefaultValueAvailable' => false
     ),
    )
   ),
   array(
    'TemplateCompilerEx', 'raise',
    array('isStatic' => false, 'getNumberOfParameters' => 3, 'getNumberOfRequiredParameters' => 3),
    array(
     array(
      'getName' => 'node', 'isOptional' => false, 'getPosition' => 0, 'isArray' => false,
      'isPassedByReference' => false, 'getClass' => 'TemplateNodeEx', 'isDefaultValueAvailable' => false
     ),
     array(
      'getName' => 'message', 'isOptional' => false, 'getPosition' => 1, 'isArray' => false,
      'isPassedByReference' => false, 'isDefaultValueAvailable' => false
     ),
     array(
      'getName' => 'code', 'isOptional' => false, 'getPosition' => 2, 'isArray' => false,
      'isPassedByReference' => false, 'isDefaultValueAvailable' => false
     ),
    )
   ),
   array(
    'TemplateCompilerEx', 'findAlternativeBranch',
    array('isStatic' => false, 'getNumberOfParameters' => 2, 'getNumberOfRequiredParameters' => 2),
    array(
     array(
      'getName' => 'node', 'isOptional' => false, 'getPosition' => 0, 'isArray' => false,
      'isPassedByReference' => false, 'getClass' => 'TemplateNodeEx', 'isDefaultValueAvailable' => false
     ),
     array(
      'getName' => 'tag', 'isOptional' => false, 'getPosition' => 1, 'isArray' => false,
      'isPassedByReference' => false, 'isDefaultValueAvailable' => false
     ),
    )
   ),
   array(
    'TemplateCompilerEx', 'generateUniqueBlock',
    array('isStatic' => false, 'getNumberOfParameters' => 3, 'getNumberOfRequiredParameters' => 1),
    array(
     array(
      'getName' => 'idPrefix', 'isOptional' => false, 'getPosition' => 0, 'isArray' => false,
      'isPassedByReference' => false, 'isDefaultValueAvailable' => false
     ),
     array(
      'getName' => 'blockPrefix', 'isOptional' => true, 'getPosition' => 1, 'isArray' => false,
      'isPassedByReference' => false, 'isDefaultValueAvailable' => true, 'getDefaultValue' => 'custom:'
     ),
     array(
      'getName' => 'keyLength', 'isOptional' => true, 'getPosition' => 2, 'isArray' => false,
      'isPassedByReference' => false, 'isDefaultValueAvailable' => true, 'getDefaultValue' => 5
     ),
    )
   ),
  );
 }
 
 /**
  Public API specification test.
  
  @dataProvider providerAPISpecification
  @param $class Class to test
  @param $method Method to test
  @param $methodConstraints Set of method constraints to test for (see ReflectionMethod methods)
  @param $argsConstraints Set of method constraints to test for (see ReflectionParameter methods)
 */
 public function testAPISpecification($class, $method, $methodConstraints, $argsConstraints = null) {
  $classObj = new ReflectionClass($class);
  if (!$classObj->hasMethod($method)) {
   $this->fail('Class '.$class.' does not have method '.$method);
  }
  
  $methodObj = $classObj->getMethod($method);
  if (!$methodObj->isPublic()) {
   $this->fail('Method '.$class.'::'.$method.' is not public');
  }
  
  foreach ($methodConstraints as $constraint => $expected) {
   if (in_array($constraint, $this->skipReflection['method'])) {
    continue;
   }
   
   if (($got = $methodObj->{$constraint}()) !== $expected) {
    $this->fail(
     'Method '.$class.'::'.$method.' fails constraint '.$constraint.
     ' (expected: '.var_export($expected, true).', got: '.var_export($got, true).')'
    );
   }
  }
  
  if (is_null($argsConstraints)) return;
  
  $args = $methodObj->getParameters();
  reset($argsConstraints);
  foreach ($args as $argument) {
   foreach (current($argsConstraints) as $constraint => $expected) {
    if (in_array($constraint, $this->skipReflection['argument'])) {
     continue;
    }
    
    $got = $argument->{$constraint}();
    if ($constraint == 'getClass') {
     $got = $got->getName();
    }
    if ($got !== $expected) {
     $this->fail(
      'Method '.$class.'::'.$method.', argument '.$argument->getName().' fails argument constraint '.$constraint.
      ' (expected: '.var_export($expected, true).', got: '.var_export($got, true).')'
     );
    }
   }
   next($argsConstraints);
  }
 }
 
 //
 // Standard I/O tests
 //
 /**
  Tests the file:// I/O driver.
 */
 public function testFileIO() {
  $this->assertEquals('fóó', $this->environ->get('file://foo_unicode.html')->render(array(), $this->environ));
 }
 /**
  Tests the string:// I/O driver.
 */
 public function testStringIO() {
  $this->assertEquals('fóó', $this->environ->get('string://fóó')->render(array(), $this->environ));
 }
 
 //
 // Public API behaviour tests
 //
 /**
  Common stub for @ref TemplateEnviron::get behaviour.
 */
 private function _commonEnvironGet($tpl, $tpl2) {
  $this->assertTrue($tpl instanceof Template);
  $this->assertEquals('foo', $tpl->render(array(), $this->environ));

  $this->assertTrue($tpl2 instanceof Template);
  $this->assertEquals('foo', $tpl2->render(array(), $this->environ));
 }
 /**
  Tests the behaviour of @ref TemplateEnviron::get.
 */
 public function testAPIEnvironGet() {
  if (in_array('objectHash', $this->skipSPL)) {
   $this->markTestSkipped('This PHP does not support spl_object_hash');
   return;
  }
  
  $tpl  = $this->environ->get('string://foo');
  $tpl2 = $this->environ->get('string://foo');
  
  $this->_commonEnvironGet($tpl, $tpl2);
  $this->assertNotEquals(spl_object_hash($tpl), spl_object_hash($tpl2));
 }
 /**
  Tests the behaviour of @ref TemplateEnviron::get, when source template does not exist.
 */
 public function testAPIEnvironGetInvalid() {
  $this->setExpectedException('TemplateError', 'does not exist', TemplateError::E_IO_LOAD_FAILURE);
  $this->environ->get('file://notexistant.html');
 }
 /**
  Tests the behaviour of @ref TemplateEnviron::cachedGet.
 */
 public function testAPIEnvironCachedGet() {
  if (in_array('objectHash', $this->skipSPL)) {
   $this->markTestSkipped('This PHP does not support spl_object_hash');
   return;
  }

  $tpl  = $this->environ->cachedGet('string://foo');
  $tpl2 = $this->environ->cachedGet('string://foo');

  $this->_commonEnvironGet($tpl, $tpl2);
  $this->assertEquals(spl_object_hash($tpl), spl_object_hash($tpl2));
 }
 /**
  Tests the behaviour of @ref TemplateEnviron::render.
 */
 public function testAPIEnvironRender() {
  $this->assertEquals('foo', $this->environ->render('string://{{ var }}', array('var' => 'foo')));
 }
 /**
  Tests the behaviour of @ref TemplateEnviron::getMeta.
 */
 public function testAPIEnvironGetMeta() {
  $expected = array('parentTemplate' => 'foobar', 'foo' => 'bar');
  $metadata = $this->environ->getMeta('string://{% meta parentTemplate "foobar" %}{% meta foo "bar" %}');
  $this->assertEquals($expected, $metadata);
 }
 /**
  Tests the behaviour of @ref TemplateIO::register when registering
  driver with illegal name.
 */
 public function testAPIIORegisterInvalid() {
  $this->setExpectedException('TemplateError', 'cannot contain', TemplateError::E_INVALID_ARGUMENT);
  TemplateIO::register('://', 'foo');
 }
 /**
  Tests the behaviour of @ref TemplateIO::get.
 */
 public function testAPIIOGet() {
  $this->assertTrue(TemplateIO::get('file') instanceof TemplateFileIO);
  $this->assertTrue(TemplateIO::get('string') instanceof TemplateStringIO);
 }
 /**
  Tests the behaviour of @ref TemplateIO::get, when I/O driver class
  does not exist.
 */
 public function testAPIIOGetInvalid() {
  $this->setExpectedException('TemplateError', 'does not exist', TemplateError::E_INVALID_PLUGIN);
  TemplateIO::register('t1_foo', 'bar');
  TemplateIO::get('t1_foo');
 }
 /**
  Tests the behaviour of @ref TemplateEnviron::get, when I/O driver class
  does not implement @ref ITemplateIODriver.
 */
 public function testAPIIOGetInvalid2() {
  $this->setExpectedException('TemplateError', 'does not implement', TemplateError::E_INVALID_PLUGIN);
  TemplateIO::register('t1_bar', 'stdClass');
  TemplateIO::get('t1_bar');
 }
 
 //
 // Internal API behaviour test providers
 //
 /**
  Test cases for @ref TemplateUtils::splitEscaped.
 */
 public static function providerUtilsSplitEscaped() {
  return array(
   array('|', 'a|b|c|d', array('a', 'b', 'c', 'd')),
   array('|', 'a:"||"|b:"|","|"|c|d:"|||"', array('a:"||"', 'b:"|","|"', 'c', 'd:"|||"')),
   array(',', 'a,b,"c,d,e",f', array('a', 'b', '"c,d,e"', 'f')),
   array(' ', 'a  b "c    d"  e', array('a', 'b', '"c    d"', 'e')),
   array(',', 'a,"b\"c\"d",e', array('a', '"b"c"d"', 'e')),
  );
 }
 
 //
 // Internal API behaviour tests
 //
 /**
  Tests the behaviour of @ref TemplateUtils::escape.
 */
 public function testUtilsEscape() {
  $this->assertEquals('\\\'', TemplateUtils::escape('\''));
  $this->assertEquals('\'."\n".\'', TemplateUtils::escape("\n"));
  $this->assertEquals('\'."\t".\'', TemplateUtils::escape("\t"));
  $this->assertEquals('\'."\r".\'', TemplateUtils::escape("\r"));
 }
 /**
  Tests the behaviour of @ref TemplateUtils::sanitize.
 */
 public function testUtilsSanitize() {
  $this->assertEquals('f__123b_r', TemplateUtils::sanitize('fó#123b+r'));
 }
 /**
  Tests the behaviour of @ref TemplateUtils::strip.
 */
 public function testUtilsStrip() {
  $this->assertEquals('abcdef', TemplateUtils::strip("abc\ndef"));
 }
 /**
  Tests the behaviour of @ref TemplateUtils::split.
 */
 public function testUtilsSplit() {
  $this->assertEquals(array('a', 'c'), TemplateUtils::split('b', 'abc'));
  $this->assertEquals(array('a c', ''), TemplateUtils::split('b', 'a c'));
 }
 /**
  Tests the behaviour of @ref TemplateUtils::splitEscaped.
  
  @dataProvider providerUtilsSplitEscaped
 */
 public function testUtilsSplitEscaped($delimiter, $string, $expected) {
  $this->assertEquals($expected, TemplateUtils::splitEscaped($delimiter, $string));
 }
 /**
  Tests the behaviour of @ref TemplateUtils::doesImplement.
 */
 public function testUtilsDoesImpl() {
  $this->assertTrue(TemplateUtils::doesImplement('ReflectionClass', 'Reflector'));
  $this->assertTrue(TemplateUtils::doesImplement(new ReflectionClass('stdClass'), 'Reflector'));
 }
 /**
  Tests the behaviour of @ref TemplateUtils::splitDSN.
 */
 public function testUtilsSplitDSN() {
  $settings  = &$this->environ->settings;
  
  $this->assertEquals(array('string', 'foo'), TemplateUtils::splitIODSN($settings, 'string://foo'));
  $this->assertEquals(array($settings['defaultIODriver'], 'foo'), TemplateUtils::splitIODSN($settings, 'foo'));
 }
 /**
  Tests the behaviour of @ref TemplateUtils::parseDSN.
 */
 public function testUtilsParseDSN() {
  $settings  = &$this->environ->settings;
  
  list($io, $id) = TemplateUtils::parseIODSN($settings, 'string://foo');
  $this->assertTrue($io instanceof TemplateStringIO);
  $this->assertEquals('foo', $id);
  
  list($io, $id) = TemplateUtils::parseIODSN($settings, 'foo');
  $this->assertTrue($io instanceof TemplateFileIO);
  $this->assertEquals('foo', $id);
 }
 /**
  Tests the behaviour of @ref TemplateUtils::panic.
 */
 public function testUtilsPanic() {
  $this->setExpectedException('TemplateError', 'PANIC', TemplateError::E_INTERNAL_CORE_FAILURE);
  TemplateUtils::panic(__FILE__, __LINE__);
 }
 
 //
 // Compiler API behaviour test providers
 //
 /**
  Test cases for @ref TemplateCompilerEx::parseVariableExpression.
 */
 public static function providerCompilerParseVariable() {
  return array(
   array('foo', '$this->ctx[\'foo\']'),
   array('0', '$this->ctx[0]'),
   array('[foo]', '$this->ctx[@$this->ctx[\'foo\']]'),
   // arrays
   array('foo.bar', '$this->ctx[\'foo\'][\'bar\']'),
   array('foo.bar.0', '$this->ctx[\'foo\'][\'bar\'][0]'),
   array('foo.[bar]', '$this->ctx[\'foo\'][@$this->ctx[\'bar\']]'),
   array('foo.[bar].baz', '$this->ctx[\'foo\'][@$this->ctx[\'bar\']][\'baz\']'),
   array('foo.[bar]->baz', '$this->ctx[\'foo\'][@$this->ctx[\'bar\']]->baz'),
   array('foo.[bar.baz]', '$this->ctx[\'foo\'][@$this->ctx[\'bar\'][\'baz\']]'),
   array('foo.[bar->baz]', '$this->ctx[\'foo\'][@$this->ctx[\'bar\']->baz]'),
   array('foo->bar', '$this->ctx[\'foo\']->bar'),
   array('foo->bar.baz', '$this->ctx[\'foo\']->bar[\'baz\']'),
   array('foo->bar.0', '$this->ctx[\'foo\']->bar[0]'),
   array('foo->[bar]', '$this->ctx[\'foo\']->{@$this->ctx[\'bar\']}'),
   array('foo->[bar.baz]', '$this->ctx[\'foo\']->{@$this->ctx[\'bar\'][\'baz\']}'),
   array('foo->[bar->baz]', '$this->ctx[\'foo\']->{@$this->ctx[\'bar\']->baz}'),
   // checking code
   array(
    'foo', null,
    'if(!isset($this->ctx[\'foo\'])&&!is_null(@$this->ctx[\'foo\'])){$this->warnVar(\'foo\');}'
   ),
   array(
    'foo.bar', null,
    'if(!isset($this->ctx[\'foo\'][\'bar\'])&&!is_null(@$this->ctx[\'foo\'][\'bar\'])){$this->warnVar(\'foo.bar\');}'
   ),
   array(
    'foo->bar', null,
    'if(!isset($this->ctx[\'foo\']->bar)&&!is_null(@$this->ctx[\'foo\']->bar)){$this->warnVar(\'foo->bar\');}'
   ),
  );
 }
 
 //
 // Compiler API behaviour tests
 //
 /**
  Tests the behaviour of @ref TemplateCompilerEx::parseVariableExpression.
  
  @dataProvider providerCompilerParseVariable
  @param $variable Variable expression to test
  @param $expCode Expected code
  @param $expCheck Expected check code
 */
 public function testCompilerParseVariable($variable, $expCode = null, $expCheck = null) {
  $root = new TemplateNodeEx('root', null, null, null, -1);
  list($code, $check) = $this->compiler->parseVariableExpression($root, $variable);
  if ($expCode)  $this->assertEquals($expCode, $code);
  if ($expCheck) $this->assertEquals($expCheck, $check);
 }
 
 //
 // StdLibEx test providers
 //
 /**
  Test cases for the standard library tags.
 */
 public static function providerStdLibExTags() {
  return array(
   // template, expected results (result/resultRegex/exception)
   // {% autoescape %}
   array(
    '{% autoescape on %}{{ autoescape }}{% endautoescape %}',
    array('result' => '&lt;strong&gt;foo&lt;/strong&gt;'),
   ),
   // {% block %}
   array(
    '{% block foo %}foo{% endblock %}',
    array('result' => 'foo')
   ),
   array(
    '{% block foo %}{{ block.super }}errorish foo!{% endblock %}',
    array('exception' => array('TemplateError', 'no parent template', TemplateError::E_INVALID_SYNTAX))
   ),
   array(
    '{% block foo store %}stored{% endblock %}foo!',
    array('result' => 'foo!')
   ),
   array(
    '{% block foo %}foo{% endblock %}{% block foo %}bar{% endblock %}',
    array('exception' => array('TemplateError', 'Redefined block', TemplateError::E_INVALID_ARGUMENT))
   ),
   // {% comment %}
   array(
    '{# foo #}',
    array('result' => '')
   ),
   array(
    "{% comment %}\n\nfoo{% block %}\n{% endcomment %}",
    array('result' => ''),
   ),
   array(
    '{# {% block %} #}', 
    array('result' => ''),
   ),
   // {% cycle %}
   array(
    '{% cycle "foo" "bar" "baz" as foo %}{% cycle foo %}{% cycle foo %}{% cycle foo %}',
    array('result' => 'foobarbazfoo'),
   ),
   array(
    '{% cycle foo %}',
    array('exception' => array('TemplateError', 'does not exist', TemplateError::E_INVALID_ARGUMENT))
   ),
   array(
    '{% for v in for.range %}{% cycle "foo" cycle.foo.1 "baz" %}{% endfor %}',
    array('result' => 'foobarbazfoo')
   ),
   array(
    '{% cycle "foo" "bar" as foo %}{% cycle "baz" "quux" as foo %}',
    array('exception' => array('TemplateError', 'already exists', TemplateError::E_INVALID_ARGUMENT))
   ),
   // {% extends %}
   array(
    "{% extends \"parent1.html\" %}\nbar\n{% block foo %}baz{% endblock %}",
    array('result' => 'foo')
   ),
   array(
    "{% extends \"parent2.html\" %}\nbaz\n{% block bar %}bar2{% endblock %}",
    array('result' => "foo\nbar2")
   ),
   array(
    "{% extends \"parent3.html\" %}\n{% block foo %}{{ block.super }} bar{% endblock %}",
    array('result' => 'foo bar')
   ),
   array(
    "{% extends \"parent3.html\" %}\n{% block foo %}bar {{ block.super }}{% endblock %}",
    array('result' => 'bar foo')
   ),
   array(
    '{% extends "string://foo" %}',
    array('result' => 'foo')
   ),
   array(
    '{% extends "parent1.html" %}{% extends "parent2.html" %}',
    array('exception' => array('TemplateError', 'already has a parent', TemplateError::E_INVALID_SYNTAX))
   ),
   // {% empty %}
   array(
    '{% for v in for.empty %}foo{% empty %}bar{% endfor %}',
    array('result' => 'bar')
   ),
   // {% filter %}
   array(
    '{% filter capfirst %}foo{% endfilter %}',
    array('result' => 'Foo')
   ),
   array(
    '{% filter slugify|capfirst %}ąsdĄSDf aSDF as;dfk KO!  13 ADSFLA!!{% endfilter %}',
    array('result' => 'Sdsdf-asdf-asdfk-ko-13-adsfla')
   ),
   // {% firstof %}
   array(
    '{% firstof firstof.var1 firstof.var2 firstof.var3 %}',
    array('result' => 'foo')
   ),
   array(
    '{% firstof invalid1 invalid2 "fallback" %}',
    array('result' => 'fallback')
   ),
   // {% for %}
   array(
    '{% for v in for.range %}{{ v }}:{{ forloop.counter }}|{% endfor %}',
    array('result' => '0:1|1:2|2:3|3:4|')
   ),
   array(
    '{% for k, v in for.kv %}{{ k }}:{{ v }}|{% endfor %}',
    array('result' => 'a:42|b:42.42|c:foo|')
   ),
   /*array(
    '{% for k,v in for.kv %}{{ k }}:{{ v }}|{% endfor %}',
    array('exception' => array('TemplateError', 'unexpected ","', TemplateError::E_INVALID_ARGUMENT))
   ),*/
   array(
    '{% for v in for.range %}{% if forloop.first %}->{% endif %}'.
    '{{ forloop.counter }},{{ forloop.counter0 }},'.
    '{{ forloop.revcounter }},{{ forloop.revcounter0 }}'.
    '{% if forloop.last %}<-{% else %}||{% endif %}{% endfor %}',
    array('result' => '->1,0,4,3||2,1,3,2||3,2,2,1||4,3,1,0<-')
   ),
   array(
    '{% for v in for.range %}{% for vn in for.kv %}'.
    '{{ forloop.parentloop.counter }}:{{ forloop.counter }}|'.
    '{% endfor %}{% endfor %}',
    array('result' => '1:1|1:2|1:3|2:1|2:2|2:3|3:1|3:2|3:3|4:1|4:2|4:3|')
   ),
   array(
    '{% for v in for.range %}{{ forloop.counter }}{% endfor %}'.
    '{% for v in for.range %}{{ forloop.revcounter }}{% endfor %}',
    array('result' => '12344321')
   ),
   array(
    '{% for v in for.iterables.array %}{{ v }}{% endfor %}',
    array('result' => '1234')
   ),
   array(
    '{% for v in for.iterables.iterator %}{{ v }}{% endfor %}',
    array('result' => '1234')
   ),
   array(
    '{% for v in for.iterables.itaggr %}{{ v }}{% endfor %}',
    array('result' => '1234')
   ),
   array(
    '{% for v in for.iterables.noniterable %}{{ v }}{% endfor %}', 
    array('exception' => array('TemplateError', 'iterable expected', TemplateError::E_INVALID_VAR))
   ),
   array(
    '{% for v in for.single %}{{ forloop.last }}{% endfor %}',
    array('result' => '1')
   ),
   array(
    // block uniqueness test
    '{% for v in for.single %}a{% endfor %}{% for v in for.single %}b{% endfor %}',
    array('result' => 'ab')
   ),
   // {% if %}
   array(
    '{% if ((foo eq bar) and ("foo" eq blah)) %}foo{% endif %}bar',
    array('result' => 'bar')
   ),
   array(
    '{% if foo %}foo{% else %}bar{% endif %}',
    array('result' => 'bar')
   ),
   array(
    '{% if not foo %}foo{% endif %}',
    array('result' => 'foo')
   ),
   array(
    '{% else %}',
    array('exception' => array('TemplateError', 'Invalid tag nesting', TemplateError::E_INVALID_SYNTAX))
   ),
   array(
    '{% elseif foo %}',
    array('exception' => array('TemplateError', 'Invalid tag nesting', TemplateError::E_INVALID_SYNTAX))
   ),
   array(
    '{% if if.foo.bar %}foo.bar{% endif %}',
    array('result' => '')
   ),
   array(
    '{% if if.baf and if.baf.one %}{{ if.baf.one }}{% endif %}',
    array('result' => '1')
   ),
   array(
    '{% if if.foo.quux and if.foo.quux->one %}foo.quux->one{% endif %}',
    array('result' => '')
   ),
   array(
    '{% if not if.foo.quux->blah eq "blah" %}blah{% endif %}',
    array('result' => 'blah')
   ),
   array(
    '{% if ((if.foo)and(if.bar)) %}1{% else %}'.
    '{% if (not(if.bar)and((if.baz)eq("quux"))) %}2{% endif %}{% endif %}',
    array('result' => '2')
   ),
   array(
    '{% if (foo %}{% endif %}',
    array('exception' => array('TemplateError', 'Unbalanced parenthesis', TemplateError::E_INVALID_ARGUMENT))
   ),
   array(
    '{% if foo) %}{% endif %}',
    array('exception' => array('TemplateError', 'Unbalanced parenthesis', TemplateError::E_INVALID_ARGUMENT))
   ),
   array(
    '{% if if.baf.three and (if.baf.three|add:42 eq 45) %}foo{% endif %}',
    array('result' => 'foo')
   ),
   array(
    '{% if if.baf and (if.baf|length eq 3) %}foo{% endif %}',
    array('result' => 'foo')
   ),
   array(
    '{% if if.f00 %}f00{% elseif if.foo %}foo{% elseif if.bar %}bar{% else %}baz{% endif %}',
    array('result' => 'foo')
   ),
   // {% ifchanged %}
   array(
    '{% for v in for.ifchanged %}{% ifchanged %}{{ v }}+{% else %}-{% endifchanged %}{% endfor %}',
    array('result' => 'a+--b+-')
   ),
   array(
    '{% for v in for.ifchanged %}{% ifchanged v %}+{% else %}-{% endifchanged %}{% endfor %}',
    array('result' => '+--+-')
   ),
   // {% ifequal %}
   array(
    '{% ifequal if.foo if.bar %}yes{% endifequal %}',
    array('result' => '')
   ),
   array(
    '{% ifequal if.foo if.foo2 %}yes{% endifequal %}',
    array('result' => 'yes')
   ),
   // {% ifnotequal %}
   array(
    '{% ifnotequal if.foo if.bar %}yes{% endifnotequal %}',
    array('result' => 'yes')
   ),
   array(
    '{% ifnotequal if.foo if.foo2 %}yes{% endifnotequal %}',
    array('result' => '')
   ),
   // {% include %}
   array(
    '{% include "included.html" %}',
    array('result' => 'included file: foo')
   ),
   array(
    '{% include include.fileName %}',
    array('result' => 'included file: foo')
   ),
   // {% now %}
   array(
    '{% now "d-m-Y, H:i:s" %}',
    array('resultRegex' => '/^[0-9]{2}\-[0-9]{2}\-[0-9]{4}\,\s[0-9]{2}\:[0-9]{2}\:[0-9]{2}$/')
   ),
   // {% templatetag %}
   array(
    '{% templatetag openblock %},{% templatetag closeblock %},'.
    '{% templatetag openvariable %},{% templatetag closevariable %},'.
    '{% templatetag opencomment %},{% templatetag closecomment %},'.
    '{% templatetag ob %},{% templatetag cb %},'.
    '{% templatetag opentag %},{% templatetag closetag %},'.
    '{% templatetag ot %},{% templatetag ct %},'.
    '{% templatetag openvar %},{% templatetag closevar %},'.
    '{% templatetag ov %},{% templatetag cv %},'.
    '{% templatetag oc %},{% templatetag cc %},'.
    '{% templatetag openbrace %},{% templatetag closebrace %}',
    array('result' => '{%,%},{{,}},{#,#},{%,%},{%,%},{%,%},{{,}},{{,}},{#,#},{,}')
   ),
   // {% with %}
   array(
    '{% with with.blah.foo.bar.baz as v %}{{ v }}{% endwith %}',
    array('result' => 'quux')
   ),
   // {% widthratio %}
   array(
    '{% widthratio 175 200 100 %}',
    array('result' => '88')
   ),
   array(
    '{% widthratio widthratio.foo widthratio.bar|add:50 100 %}',
    array('result' => '88')
   ),
   // {% putblock %}
   array(
    '{% putblock foo %}bar{% block foo store %}foo{% endblock %}',
    array('result' => 'foobar')
   ),
   array(
    '{% putblock foo strict %}',
    array('exception' => array('TemplateError', 'does not exist', TemplateError::E_INVALID_ARGUMENT))
   ),
   array(
    '{% putblock foo %}',
    array('result' => '')
   ),
   array(
    '{% putblock foo strict %}{% block foo store %}foo{% endblock %}',
    array('exception' => array('TemplateError', 'does not exist', TemplateError::E_INVALID_ARGUMENT))
   ),
   array(
    '{% block foo store %}bar{% endblock %}{% putblock foo strict %}',
    array('result' => 'bar')
   ),
   // {% call %}
   array(
    '->{% call "md5" "foo" %}<-',
    array('result' => '->acbd18db4cc2f85cedef654fccc4a4d8<-')
   ),
   array(
    '->{% call "md5" "foo" as md5foo %}<-{{ md5foo }}',
    array('result' => '-><-acbd18db4cc2f85cedef654fccc4a4d8')
   ),
   array(
    '->{% call call.md5 "foo" %}<-',
    array('result' => '->acbd18db4cc2f85cedef654fccc4a4d8<-')
   ),
   array(
    '->{% call call.md5 "foo" as md5foo %}<-{{ md5foo }}',
    array('result' => '-><-acbd18db4cc2f85cedef654fccc4a4d8')
   ),
   array(
    '->{% call "md5" call.value %}<-',
    array('result' => '->acbd18db4cc2f85cedef654fccc4a4d8<-')
   ),
   array(
    '->{% call call.md5 call.value %}<-',
    array('result' => '->acbd18db4cc2f85cedef654fccc4a4d8<-')
   ),
   array(
    '{% call "foo" %}',
    array('exception' => array('TemplateError', 'callable expected', TemplateError::E_INVALID_VAR))
   ),
   array(
    '{% call call.invalid %}',
    array('exception' => array('TemplateError', 'callable expected', TemplateError::E_INVALID_VAR))
   ),
   array(
    '->{% call call.object "foo" %}<-',
    array('result' => '->acbd18db4cc2f85cedef654fccc4a4d8<-')
   ),
   // some errors
   array(
    '{% endblock %}',
    array('exception' => array('TemplateError', 'Unknown tag', TemplateError::E_UNKNOWN_TAG))
   ),
   array(
    '{% block foo %}{% filter cut:"foo" %}{% endblock %}{% endfilter %}',
    array('exception' => array('TemplateError', 'expected "endfilter"', TemplateError::E_INVALID_SYNTAX))
   ),
   array(
    '{% load %}',
    array('exception' => array('TemplateError', 'requires at least', TemplateError::E_INVALID_SYNTAX))
   ),
   array(
    '{% load foo %}',
    array('exception' => array('TemplateError', 'non-existant or invalid plugin', TemplateError::E_UNKNOWN_PLUGIN))
   ),
  );
 }
 /**
  Test cases for the standard library filters.
 */
 public static function providerStdLibExFilters() {
  return array(
   // template, expected results (result/resultRegex/exception)
   // add
   array(
    '{{ int.even|add:5 }} {{ int.even|add:-5 }}',
    array('result' => '47 37')
   ),
   // addslashes
   array(
    '{{ string.quoted|addslashes }}', 
    array('result' => '\\"foo\\"')
   ),
   // capfirst
   array(
    '{{ string.simple|capfirst }}',
    array('result' => 'Foo')
   ),
   array(
    '{{ string.simpleU|capfirst }}',
    array('result' => 'Ąćś')
   ),
   // cut
   array(
    '{{ array.space|cut:"bar" }}',
    array('result' => 'foo  baz')
   ),
   array(
    '{{ array.spaceU|cut:"bąr" }}',
    array('result' => 'fóó  bąż')
   ),
   array(
    '{{ array.spaceU|cut:cutU }}',
    array('result' => 'fóó  bąż')
   ),
   // date
   array(
    '{{ int.time|date:"d-m-Y, H:i:s" }}',
    array('result' => date('d-m-Y, H:i:s', 123456789))
   ),
   // default
   array(
    '{{ dummy|default:"foo" }}',
    array('result' => 'foo')
   ),
   array(
    '{{ @nonexistant|default:"foo" }}',
    array('result' => 'foo')
   ),
   // default_if_none
   array(
    '{{ dummy|default_if_none:"foo" }}',
    array('result' => '')
   ),
   array(
    '{{ dummyNull|default_if_none:"foo" }}',
    array('result' => 'foo')
   ),
   // divisibleby
   array(
    '{{ int.even|divisibleby:2 }} {{ int.odd|divisibleby:2 }}',
    array('result' => '1 ')
   ),
   array(
    '{{ int.even|divisibleby:"foo" }}',
    array('exception' => array('TemplateError', 'string argument', TemplateError::E_INVALID_ARGUMENT))
   ),
   // escape
   array(
    '{{ string.HTML|escape }}',
    array(
     'result' =>
      '&lt;a href=&quot;http://example.com/foo.php?a=b&amp;c=d&quot;'.
      '&gt;&lt;strong&gt;bar&lt;/strong&gt;&lt;/a&gt;'
    )
   ),
   // filesizeformat
   array(
    "{{ int.b|filesizeformat }}\n{{ int.kB|filesizeformat }}\n".
    "{{ int.MB|filesizeformat }}\n{{ int.GB|filesizeformat }}",
    array('result' => "512 b\n42 kB\n42 MB\n42 GB")
   ),
   // fix_ampersands
   array(
    '{{ string.HTML|fix_ampersands }}',
    array('result' => '<a href="http://example.com/foo.php?a=b&amp;c=d"><strong>bar</strong></a>')
   ),
   // join
   array(
    '{{ array.simple|join:"," }}',
    array('result' => 'foo,bar,baz')
   ),
   // length
   array(
    '{{ string.simple|length }}',
    array('result' => '3')
   ),
   // length_is
   array(
    '{{ string.simple|length_is:3 }}',
    array('result' => '1')
   ),
   // linebreaks
   array(
    '{{ array.newline|linebreaks }}',
    array('result' => "<p>foo<br />bar<br />baz</p>\n\n<p>quux</p>")
   ),
   // linebreaksbr
   array(
    '{{ array.newline|linebreaksbr }}',
    array('result' => "foo<br />\nbar<br />\nbaz<br />\n<br />\nquux")
   ),
   // ljust
   array(
    '{{ string.simple|ljust:20 }}',
    array('resultRegex' => '/^foo\s{17}$/')
   ),
   // lower
   array(
    '{{ string.mixed|lower }}',
    array('result' => 'foobarbaz'),
   ),
   array(
    '{{ string.mixedU|lower }}',
    array('result' => 'fóóbąrbąż'),
   ),
   // make_list
   array(
    '{% for v in string.simple|make_list %}{{ v }},{% endfor %}',
    array('result' => 'f,o,o,')
   ),
   // pluralize
   array(
    'foo{{ int.singular|pluralize }} foo{{ int.plural|pluralize }}',
    array('result' => 'foo foos')
   ),
   array(
    'foo{{ int.singular|pluralize:"es" }} foo{{ int.plural|pluralize:"es" }}',
    array('result' => 'foo fooes')
   ),
   array(
    'foo{{ int.singular|pluralize:"e,es" }} foo{{ int.plural|pluralize:"e,es" }}',
    array('result' => 'fooe fooes')
   ),
   // random
   array(
    '{{ array.simple|random }}',
    array('resultRegex' => '/foo|bar|baz/')
   ),
   // removetags
   array(
    '{{ string.HTML|removetags }}',
    array('result' => 'bar')
   ),
   // rjust
   array(
    '{{ string.simple|rjust:20 }}',
    array('resultRegex' => '/^\s{17}foo$/')
   ),
   // slugify
   array(
    '{{ string.slugify|slugify }}',
    array('result' => 'dsafasdff-dfpfaes-o-adsf-afdsalsk')
   ),
   // title
   array(
    '{{ string.title|title }}',
    array('result' => 'This Is A Title')
   ),
   array(
    '{{ string.titleU|title }}',
    array('result' => 'Żźąó Ąxć Foo')
   ),
   // upper
   array(
    '{{ string.mixed|upper }}',
    array('result' => 'FOOBARBAZ')
   ),
   array(
    '{{ string.mixedU|upper }}',
    array('result' => 'FÓÓBĄRBĄŻ')
   ),
   // urlencode
   array(
    '{{ string.URL|urlencode }}',
    array('result' => 'http%3A%2F%2Fexample.com')
   ),
   // urldecode
   array(
    '{{ string.URLencoded|urldecode }}',
    array('result' => 'http://example.com')
   ),
   // wordcount
   array(
    '{{ array.space|wordcount }}',
    array('result' => '3')
   ),
   // wordwrap
   array(
    '{{ array.space|wordwrap:3 }}',
    array('result' => "foo\nbar\nbaz")
   ),
  );
 }
 /**
  Test cases for the standard library hooks.
 */
 public static function providerStdLibExHooks() {
  return array(
   array(
    '{{ internal.version }}',
    array('result' => SITHTEMPLATE_VERSION)
   ),
   array(
    '{{ internal.request.POST.foo }}',
    array('result' => 'POSTfoo')
   ),
   array(
    '{{ internal.request.GET.[foo] }}',
    array('result' => 'GETfoo')
   ),
   array(
    '{{ internal.const.SITHTEMPLATE_CONST_TEST }}',
    array('result' => 'CONSTfoo')
   ),
  );
 }

 //
 // StdLibEx tests
 //
 /**
  Common stub for standard library tests.
  
  @param $template Template code to test
  @param $expected Expected result (assoc. array with one key: result, resultRegex or exception)
  @param $context Context array to use
 */
 private function _commonStdLibEx($template, $expected, array $context) {
  if (isset($expected['exception'])) {
   call_user_func_array(array($this, 'setExpectedException'), $expected['exception']);
  }

  $result = $this->environ->get('string://'.$template)->render($context, $this->environ);

  if (isset($expected['result'])) {
   $this->assertEquals($expected['result'], $result);
  } elseif (isset($expected['resultRegex'])) {
   $this->assertRegExp($expected['resultRegex'], $result);
  }
 }
 /**
  Tests the behaviour of standard library tags.
  
  @dataProvider providerStdLibExTags
  @param $template Template code to test
  @param $expected Expected result
  @sa TemplateTestsEx::_commonStdLibEx
 */
 public function testStdLibExTags($template, $expected) {
  $testData = array(
   'firstof'    => array('var1' => false, 'var3' => 'foo'),
   'cycle'      => array('foo'  => array('foo','bar','baz')),
   'with'       => array('blah' => array('foo' => array('bar' => array('baz' => 'quux')))),
   'widthratio' => array('foo' => 175, 'bar' => 150),
   'for'        => array(
    'empty'     => array(),
    'range'     => array(0,1,2,3), 'single' => array(0),
    'kv'        => array('a' => 42, 'b' => 42.42, 'c' => 'foo'),
    'iterables' => array(
     'array'  => array(1,2,3,4), 'iterator'    => _newIterator(),
     'itaggr' => _newItAggr(),   'noniterable' => 'something'
    ),
    'ifchanged' => array('a', 'a', 'a', 'b', 'b'),
   ),
   'if'         => array(
    'foo'  => true, 'bar' => false, 'baz' => 'quux',
    'baf'  => array('one' => 1, 'two' => 2, 'three' => 3),
    'quux' => _newStdClass(), 'foo2' => true,
   ),
   'include'    => array('fileName' => 'included.html', 'foo' => 'foo'),
   'call'       => array(
    'md5'    => 'md5', 'value' => 'foo', 'invalid' => false,
    'object' => array(_newTestObj(), 'md5'),
   ),
   'autoescape' => '<strong>foo</strong>',
  );
  
  $this->_commonStdLibEx($template, $expected, $testData);
 }
 /**
  Tests the behaviour of standard library filters.
  
  @dataProvider providerStdLibExFilters
  @param $template Template code to test
  @param $expected Expected result
  @sa TemplateTestsEx::_commonStdLibEx
 */
 public function testStdLibExFilters($template, $expected) {
  $testData = array(
   'int' => array(
    'even' => 42, 'odd' => 41, 'b' => 512, 'kB' => 43008, 'MB' => 44040192,
    'GB' => 45097156608, 'time' => 123456789, 'plural' => 2, 'singular' => 1,
   ),
   'string' => array(
    'simple' => 'foo', 'simpleU' => 'ąćś', 'quoted' => '"foo"',
    'URL' => 'http://example.com', 'URLencoded' => 'http%3A%2F%2Fexample.com',
    'HTML' => '<a href="http://example.com/foo.php?a=b&c=d"><strong>bar</strong></a>',
    'mixed' => 'fOoBaRbAZ', 'mixedU' => 'fÓóBąRbĄŻ',
    'slugify' => 'dsafŻASDfżf df#!@$Pfaes o =ADSf- -- AFDSALSK!',
    'title' => 'this is a title', 'titleU' => 'żźąó ąxć foo',
   ),
   'array' => array(
    'simple' => array('foo','bar','baz'), 'comma' => 'foo,bar,baz', 'space' => 'foo bar baz',
    'spaceU' => 'fóó bąr bąż', 'newline' => "foo\nbar\nbaz\n\nquux",
   ),
   'dummy' => false, 'dummyNull' => null,
   'cutU' => 'bąr'
  );
  
  $this->_commonStdLibEx($template, $expected, $testData);
 }
 /**
  Tests the behaviour of standard library hooks.
  
  @dataProvider providerStdLibExHooks
  @param $template Template code to test
  @param $expected Expected result
  @sa TemplateTestsEx::_commonStdLibEx
 */
 public function testStdLibExHooks($template, $expected) {
  $_POST['foo'] = 'POSTfoo';
  $_GET['bar']  = 'GETfoo';
  $this->_commonStdLibEx($template, $expected, array('foo' => 'bar'));
 }
 
 //
 // Security test providers
 //
 /**
  Test cases for allowedPlugins/disallowedPlugins lists.
 */
 public static function providerSecurityPlugins() {
  return array(
   array('{% load AllowedPlugin %}',    false),
   array('{% load DisallowedPlugin %}', true)
  );
 }
 /**
  Test cases for allowedTags/disallowedTags lists.
 */
 public static function providerSecurityTags() {
  return array(
   array('{% block foo %}{% endblock %}', false),
   array('{% call "md5" "foo" %}',        true),
  );
 }
 /**
  Test cases for allowedFilters/disallowedFilters lists.
 */
 public static function providerSecurityFilters() {
  return array(
   array('{{ foo|escape }}',    false),
   array('{{ foo|make_list }}', true),
  );
 }
 /**
  Test cases for allowedFunctions/disallowedFunctions lists.
 */
 public static function providerSecurityFunctions() {
  return array(
   array('{% call "md5" "foo" %}',                  false),
   array('{% call "call_user_func" "md5" "foo" %}', true),
  );
 }
 
 //
 // Security tests
 //
 /**
  Tests the behaviour of auto-escaping hook.
 */
 public function testAutoEscaping() {
  $this->environ->settings['autoEscape'] = true;
  $this->assertEquals(
   '&lt;b&gt;hai&lt;/b&gt;',
   $this->environ->get('string://{{ test }}')->render(
    array('test' => '<b>hai</b>'), $this->environ
   )
  );
 }
 /**
  Common stub for security lists' tests.
 */
 private function _commonSecurity($list, $allowed, $disallowed, $tpl, $exc) {
  $this->environ->settings['allowed'.$list]    = array($allowed);
  $this->environ->settings['disallowed'.$list] = array($disallowed);

  if ($exc) {
   $this->setExpectedException('TemplateError', '', TemplateError::E_SECURITY_VIOLATION);
  }
  
  $this->environ->get('string://'.$tpl)->render(array('foo' => 'foo'), $this->environ);
 }
 /**
  Common stub for allowedPlugins/disallowedPlugins lists' tests.
  
  @param $tpl Template code to test
  @param $exc Is exception expected?
 */
 private function _commonSecPlugins($tpl, $exc) {
  $this->_commonSecurity('Plugins', 'AllowedPlugin', 'DisallowedPlugin', $tpl, $exc);
 }
 /**
  Common stub for allowedTags/disallowedTags lists' tests.
  
  @param $tpl Template code to test
  @param $exc Is exception expected?
 */
 private function _commonSecTags($tpl, $exc) {
  $this->_commonSecurity('Tags', 'block', 'call', $tpl, $exc);
 }
 /**
  Common stub for allowedFilters/disallowedFilters lists' tests.
  
  @param $tpl Template code to test
  @param $exc Is exception expected?
 */
 private function _commonSecFilters($tpl, $exc) {
  $this->_commonSecurity('Filters', 'escape', 'make_list', $tpl, $exc);
 }
 /**
  Common stub for allowedFunctions/disallowedFunctions lists' tests.
  
  @param $tpl Template code to test
  @param $exc Is exception expected?
 */
 private function _commonSecFunctions($tpl, $exc) {
  $this->_commonSecurity('Functions', 'md5', 'call_user_func', $tpl, $exc);
 }
 /**
  Common stub for security evaluation mode tests.
  
  @param $mode Security evaluation mode to use
  @param $what What stub to call
  @param $tpl Template code to test
  @param $exc Is exception expected?
 */
 private function _commonSecAll($mode, $what, $tpl, $exc) {
  $this->environ->settings['securityEvalMode'] = $mode;
  $this->{'_commonSec'.$what}($tpl, $exc);
 }
 
 // ALLOW ALL
 
 /**
  Tests the behaviour of allowedPlugins/disallowedPlugins lists, under
  ALLOW_ALL evaluation mode.
  
  @dataProvider providerSecurityPlugins
  @param $tpl Template code to test
  @param $exc Is exception expected?
 */
 public function testSecurityPluginsAllowAll($tpl, $exc) {
  $this->_commonSecAll(TemplateEnviron::SECURITY_ALLOW_ALL, 'Plugins', $tpl, $exc);
 }
 /**
  Tests the behaviour of allowedTags/disallowedTags lists, under
  ALLOW_ALL evaluation mode.
  
  @dataProvider providerSecurityTags
  @param $tpl Template code to test
  @param $exc Is exception expected
 */
 public function testSecurityTagsAllowAll($tpl, $exc) {
  $this->_commonSecAll(TemplateEnviron::SECURITY_ALLOW_ALL, 'Tags', $tpl, $exc);
 }
 /**
  Tests the behaviour of allowedFilters/disallowedFilters lists, under
  ALLOW_ALL evaluation mode.

  @dataProvider providerSecurityFilters
  @param $tpl Template code to test
  @param $exc Is exception expected
 */
 public function testSecurityFiltersAllowAll($tpl, $exc) {
  $this->_commonSecAll(TemplateEnviron::SECURITY_ALLOW_ALL, 'Filters', $tpl, $exc);
 }
 /**
  Tests the behaviour of allowedFunctions/disallowedFunctions lists, under
  ALLOW_ALL evaluation mode.

  @dataProvider providerSecurityFunctions
  @param $tpl Template code to test
  @param $exc Is exception expected
 */
 public function testSecurityFunctionsAllowAll($tpl, $exc) {
  $this->_commonSecAll(TemplateEnviron::SECURITY_ALLOW_ALL, 'Functions', $tpl, $exc);
 }
 
 // ALLOW, DENY
 
 /**
  Tests the behaviour of allowedPlugins/disallowedPlugins lists, under
  ALLOW_DENY evaluation mode.
  
  @dataProvider providerSecurityPlugins
  @param $tpl Template code to test
  @param $exc Is exception expected
 */
 public function testSecurityPluginsAllowDeny($tpl, $exc) {
  $this->_commonSecAll(TemplateEnviron::SECURITY_ALLOW_DENY, 'Plugins', $tpl, $exc);
 }
 /**
  Tests the behaviour of allowedTags/disallowedTags lists, under
  ALLOW_DENY evaluation mode.
  
  @dataProvider providerSecurityTags
  @param $tpl Template code to test
  @param $exc Is exception expected
 */
 public function testSecurityTagsAllowDeny($tpl, $exc) {
  $this->_commonSecAll(TemplateEnviron::SECURITY_ALLOW_DENY, 'Tags', $tpl, $exc);
 }
 /**
  Tests the behaviour of allowedFilters/disallowedFilters lists, under
  ALLOW_DENY evaluation mode.
  
  @dataProvider providerSecurityFilters
  @param $tpl Template code to test
  @param $exc Is exception expected
 */
 public function testSecurityFiltersAllowDeny($tpl, $exc) {
  $this->_commonSecAll(TemplateEnviron::SECURITY_ALLOW_DENY, 'Filters', $tpl, $exc);
 }
 /**
  Tests the behaviour of allowedFunctions/disallowedFunctions lists, under
  ALLOW_DENY evaluation mode.

  @dataProvider providerSecurityFunctions
  @param $tpl Template code to test
  @param $exc Is exception expected
 */
 public function testSecurityFunctionsAllowDeny($tpl, $exc) {
  $this->_commonSecAll(TemplateEnviron::SECURITY_ALLOW_DENY, 'Functions', $tpl, $exc);
 }
 
 // DENY, ALLOW
 
 /**
  Tests the behaviour of allowedPlugins/disallowedPlugins lists, under
  DENY_ALLOW evaluation mode.
  
  @dataProvider providerSecurityPlugins
  @param $tpl Template code to test
  @param $exc Is exception expected
 */
 public function testSecurityPluginsDenyAllow($tpl, $exc) {
  $this->_commonSecAll(TemplateEnviron::SECURITY_DENY_ALLOW, 'Plugins', $tpl, $exc);
 }
 /**
  Tests the behaviour of allowedTags/disallowedTags lists, under
  DENY_ALLOW evaluation mode.

  @dataProvider providerSecurityTags
  @param $tpl Template code to test
  @param $exc Is exception expected
 */
 public function testSecurityTagsDenyAllow($tpl, $exc) {
  $this->_commonSecAll(TemplateEnviron::SECURITY_DENY_ALLOW, 'Tags', $tpl, $exc);
 }
 /**
  Tests the behaviour of allowedFilters/disallowedFilters lists, under
  DENY_ALLOW evaluation mode.
  
  @dataProvider providerSecurityFilters
  @param $tpl Template code to test
  @param $exc Is exception expected
 */
 public function testSecurityFiltersDenyAllow($tpl, $exc) {
  $this->_commonSecAll(TemplateEnviron::SECURITY_DENY_ALLOW, 'Filters', $tpl, $exc);
 }
 /**
  Tests the behaviour of allowedFunctions/disallowedFunctions lists, under
  DENY_ALLOW evaluation mode.
  
  @dataProvider providerSecurityFunctions
  @param $tpl Template code to test
  @param $exc Is exception expected
 */
 public function testSecurityFunctionsDenyAllow($tpl, $exc) {
  $this->_commonSecAll(TemplateEnviron::SECURITY_DENY_ALLOW, 'Functions', $tpl, $exc);
 }
 
 // DENY ALL
 
 /**
  Tests the behaviour of allowedPlugins/disallowedPlugins lists, under
  DENY_ALL evaluation mode.
  
  @dataProvider providerSecurityPlugins
  @param $tpl Template code to test
  @param $exc Is exception expected
 */
 public function testSecurityPluginsDenyAll($tpl, $exc) {
  $this->_commonSecAll(TemplateEnviron::SECURITY_DENY_ALL, 'Plugins', $tpl, $exc);
 }
 /**
  Tests the behaviour of allowedTags/disallowedTags lists, under
  DENY_ALL evaluation mode.
  
  @dataProvider providerSecurityTags
  @param $tpl Template code to test
  @param $exc Is exception expected
 */
 public function testSecurityTagsDenyAll($tpl, $exc) {
  $this->_commonSecAll(TemplateEnviron::SECURITY_DENY_ALL, 'Tags', $tpl, $exc);
 }
 /**
  Tests the behaviour of allowedFilters/disallowedFilters lists, under
  DENY_ALL evaluation mode.
  
  @dataProvider providerSecurityFilters
  @param $tpl Template code to test
  @param $exc Is exception expected
 */
 public function testSecurityFiltersDenyAll($tpl, $exc) {
  $this->_commonSecAll(TemplateEnviron::SECURITY_DENY_ALL, 'Filters', $tpl, $exc);
 }
 /**
  Tests the behaviour of allowedFunctions/disallowedFunctions lists, under
  DENY_ALL evaluation mode.
  
  @dataProvider providerSecurityFunctions
  @param $tpl Template code to test
  @param $exc Is exception expected
 */
 public function testSecurityFunctionsDenyAll($tpl, $exc) {
  $this->_commonSecAll(TemplateEnviron::SECURITY_DENY_ALL, 'Functions', $tpl, $exc);
 }
 
 /**
  Common stub for I/O restrictions' tests.
  
  @param $tpl Template to test
  @param $fail Is exception expected?
 */
 private function _commonIORestr($tpl, $fail) {
  if ($fail) $this->setExpectedException('TemplateError', 'I/O restriction', TemplateError::E_SECURITY_VIOLATION);
  $this->environ->settings['restrictIncludeIO'] = true;
  $this->environ->settings['restrictExtendIO']  = true;
  $this->environ->get($tpl)->render(array(), $this->environ);
 }
 /**
  Tests the behaviour of <code>{% include %}</code> I/O restriction,
  when different I/O driver is used within the restricted template.
 */
 public function testIncludeIORestrictionsFail() {
  $this->_commonIORestr('string://{% include "foo_unicode.html" %}', true);
 }
 /**
  Tests the behaviour of <code>{% include %}</code> I/O restriction,
  when the same I/O driver is used within the restricted template.
 */
 public function testIncludeIORestrictionsPass() {
  $this->_commonIORestr('string://{% include "string://included" %}', false);
 }
 /**
  Tests the behaviour of <code>{% extends %}</code> I/O restriction,
  when different I/O driver is used within the restricted template.
 */
 public function testExtendsIORestrictionsFail() {
  $this->_commonIORestr('string://{% extends "parent1.html" %}', true);
 }
 /**
  Tests the behaviour of <code>{% extends %}</code> I/O restriction,
  when the same I/O driver is used within the restricted template.
 */
 public function testExtendsIORestrictionsPass() {
  $this->_commonIORestr('string://{% extends "string://parent" %}', false);
 }
 
 /**
  Common stub for <code>{{ internal }}</code> restrictions' tests.
  
  @param $setting What setting to set to false
  @param $tpl Template code to test
 */
 private function _commonInternalRestr($setting, $tpl) {
  $this->setExpectedException(
   'TemplateError', 'restricted by "'.$setting.'"', TemplateError::E_SECURITY_VIOLATION
  );
  $this->environ->settings[$setting] = false;
  $this->environ->get($tpl)->render(array(), $this->environ);
 }
 /**
  Tests whether <code>{{ internal.request }}</code> honours the @c allowInternalRequest
  restriction setting.
 */
 public function testInternalRestrictRequest() {
  $this->_commonInternalRestr('allowInternalRequest', 'string://{{ internal.request.ENV.PATH }}');
 }
 /**
  Tests whether <code>{{ internal.const }}</code> honours the @c allowInternalConstants
  restriction setting.
 */
 public function testInternalRestrictConstants() {
  $this->_commonInternalRestr('allowInternalConstants', 'string://{{ internal.const.PHP_VERSION }}');
 }
 
 //
 // Misc tests
 //
 /**
  Tests the behaviour of variable silencing.
 */
 public function testNonExistantVariableSilencing() {
  $this->environ->get('string://{{ @nonexistant }}')->render(array(), $this->environ);
 }
}

//
// Additional data
//
/**
 Creates a new stdClass with couple of properties.
 
 @return stdClass instance
*/
function _newStdClass() {
 $c = new stdClass;
 $c->foo = 'foo';
 $c->bar = 'bar';
 return $c;
}

/**
 Creates a new ArrayIterator with test data.
 
 @return ArrayIterator instance
*/
function _newIterator() {
 return new ArrayIterator(array(1, 2, 3, 4));
}

/**
 Creates a new @ref TestItAggr instance.
 
 @return @ref TestItAggr instance
*/
function _newItAggr() {
 return new TestItAggr;
}

/**
 Creates a new @ref TestObj instance.
 
 @return @ref TestObj instance
*/
function _newTestObj() {
 return new TestObj;
}

/**
 A test IteratorAggregate implementation, used in <code>{% for %}</code> test
 cases.
*/
class TestItAggr implements IteratorAggregate, Countable {
 public function __construct() { $this->iterator = new ArrayIterator(array(1, 2, 3, 4)); }
 public function count()       { return 4;                                               }
 public function getIterator() { return $this->iterator;                                 }
}

/**
 A test object used in <code>{% call %}</code> test cases.
*/
class TestObj {
 public function md5($md5) { return md5($md5); }
}

/**
 A test constant used in <code>{{ internal }}</code> test cases.
*/
define('SITHTEMPLATE_CONST_TEST', 'CONSTfoo');
