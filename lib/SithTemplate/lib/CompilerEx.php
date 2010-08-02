<?php
/** @file CompilerEx.php
 New and shiny AST-based template compiler.

 @since 1.1a0
 @author PiotrLegnica
 @license{New BSD License}
 @todo Better variable parser?
*/
/** @page extending-st Extending SithTemplate
 TODO: describe extending SithTemplate, compiler's API, etc.
 
 @section extending-st-ast Extending: AST nodes
  TODO: describe AST, and how tags may affect it
 
 @section extending-st-handlers Extending: handlers
  TODO: describe handlers for tags, filters and hooks
  
 @section extending-st-hooks Extending: hooks
  TODO: describe available hooks
*/

/**
 Primary compiler driver. It's responsible for creating the AST,
 and generating the code.
 
 Warning, large strings and heavy recursion ahead.
*/
class TemplateCompilerEx {
 /**
  Current settings (reference to @ref TemplateEnviron::$settings).
 */
 public $settings = null;
 
 // Plugins part
 /**
  Registry of available plugins. @ref TemplatePlugins instance.
 */
 public $plugins       = null;
 /**
  Registry of loaded plugins (per-template).
 */
 public $loadedPlugins = array();
 
 // Parser part
 /**
  Currently processed token.
  Assoc. array containing two keys - @c 'type' and @c 'content'.
 */
 private $parserCurrentToken = null;
 /**
  Currently processed line (approx).
 */
 private $parserCurrentLine  = 1;
 /**
  Currently processed template.
 */
 private $parserCurrentFile  = null;
 /**
  Regular expression used to split template into tokens.
 */
 private $parserTokenRegexp  = '~(\{\%.*?\%\})|(\{\{.*?\}\})|(\{\#.*?\#\})~u';

 // CodeGen part
 /**
  Already constructed code blocks.
 */
 public $blocks         = array();
 /**
  Template's metadata.
 */
 public $metadata       = array();
 /**
  Template's classname.
 */
 public $className      = null;

 // public API
 /**
  Constructor.
 */
 public function __construct() {
  // create plugins registry, and register built-ins
  $this->plugins = new TemplatePlugins(array(
   'tags' => array(
    'load'    => array(
     'plugin' => null, 'type' => 'inline', 'handler' => array($this, 'handleLoadBuiltin'), 'minArgs' => 1
    ),
    'comment' => array(
     'plugin' => null, 'type' => 'ignore', 'handler' => array($this, 'handleCommentBuiltin'), 'minArgs' => 0
    ),
   ),
   'filters' => array(),
   'hooks'   => array(),
  ));
 }
 
 /**
  Resets compiler to pristine state, and loads plugins specified in @c 'loadPlugins'
  setting.
 */
 public function reset() {
  $this->plugins->searchPaths = &$this->settings['pluginsPaths'];
  $this->blocks = $this->metadata = $this->loadedPlugins = array();
  $this->className = $this->parserCurrentToken = $this->parserCurrentFile = null;
  $this->parserCurrentLine = 1;
  
  $this->plugins->loadMultiple($this, null, $this->settings['loadPlugins']);
 }
 
 /**
  Compiles given template into output package.
  
  @param[in] $io Used I/O driver
  @param[in] $template %Template name
 */
 public function compile(ITemplateIODriver $io, $template) {
  $this->reset();
  
  if (($tpl = $io->loadTemplate($this->settings, $template)) === false) {
   throw new TemplateError('Could not load template "'.$in.'"', TemplateError::E_IO_LOAD_FAILURE);
  }
  
  $this->className          = $io->className($this->settings, $template);
  $this->parserCurrentFile  = $template;
  $this->metadata['usedIO'] = $io->driverID;
  
  $genAST  = $this->createAST($tpl);
  $genCode = $this->generateCode($genAST);
  
  // determine template's parent class
  $classCode = '<?php class '.$this->className.' extends ';
  if (isset($this->metadata['parentTemplate'])) {
   list($parentIO,$parent)  = TemplateUtils::parseIODSN($this->settings, $this->metadata['parentTemplate']);
   $classCode              .= $parentIO->className($this->settings, $parent);
   // main content is ignored, when template extends another
   unset($genCode['main']);
  } else {
   $classCode .= 'Template';
  }
  
  $classCode .= '{';
  foreach ($genCode as $blockName => &$blockCode) {
   $classCode .= 'function _'.TemplateUtils::sanitize($blockName).'($e){';
   // TODO: is constructor support really needed?
   if ($blockName == '_constructor') $classCode .= 'parent::__constructor();';
   $classCode .= $blockCode.'}';
  }
  $classCode .= '}';
  
  if ($io->saveMetadata($this->settings, $template, $this->metadata) === false) {
   throw new TemplateError(
    'Could not save template metadata (compiled from "'.$template.'")',
    TemplateError::E_IO_SAVE_FAILURE
   );
  }
  
  if ($io->saveTemplate($this->settings, $template, $classCode) === false) {
   throw new TemplateError(
    'Could not save template code (compiled from "'.$template.'").',
    TemplateError::E_IO_SAVE_FAILURE
   );
  }
 }
 
 // Parser part
 /**
  Creates and returns an Abstract Syntax Tree of given template.
  
  @param[in] $tpl Source template to parse
  @return Root node of the constructed AST
  @sa TemplateNodeEx
 */
 private function createAST(&$tpl) {
  $root = new TemplateNodeEx('root', null, null, $this->parserCurrentFile, -1);
  
  $tokens = preg_split($this->parserTokenRegexp, $tpl, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
  $this->parseTokenStream($root, $tokens);
  
  return $root;
 }
 
 /**
  Fetches next token from given stream, preprocesses it, and
  stores it in @ref TemplateCompilerEx::$parserCurrentToken.
  
  @param[in,out] $tokens Token stream (array of tokens)
  @retval false When there is no more tokens in the stream
  @retval true Otherwise
 */
 private function parserGetNextToken(array &$tokens) {
  $this->parserCurrentToken = array_shift($tokens);
  
  if ($this->parserCurrentToken === null) {
   return false;
  } elseif (mb_substr($this->parserCurrentToken, 0, 2) == '{%') {
   $this->parserCurrentToken = array(
    'type'    => 'tag',
    'content' => TemplateUtils::splitEscaped(
     ' ', trim(mb_substr($this->parserCurrentToken, 2, -2))
    )
   );
  } elseif (mb_substr($this->parserCurrentToken, 0, 2) == '{{') {
   $this->parserCurrentToken = array(
    'type'    => 'var',
    'content' => TemplateUtils::split(
     '|', trim(mb_substr($this->parserCurrentToken, 2, -2))
    ),
   );
  } elseif (mb_substr($this->parserCurrentToken, 0, 2) == '{#') {
   $this->parserCurrentToken = array(
    'type'    => 'comment',
    'content' => null,
   );
  } else {
   $this->parserCurrentToken = array(
    'type'    => 'text',
    'content' => $this->parserCurrentToken,
   );
  }
  
  return true;
 }
 
 /**
  Checks whether the parser has encountered ending tag with given name.
  
  @param[in] $node Currently processed node
  @param[in] $tag Tag name to look for ('end' is automatically prepended)
  @param[in] $type Tag type - if @c ignore, then invalid ending tags will be ignored
  @retval false When @c $tag is @c null, current token's type is not @c 'tag' or
                parser has not encountered the tag yet
  @retval true When the tag has been found
 */
 private function parserEncounteredEndTag(TemplateNodeEx $node, $tag, $type) {
  if ($tag === null || $this->parserCurrentToken['type'] != 'tag') return false;
  
  $gotTag = &$this->parserCurrentToken['content'][0];
  $tag    = 'end'.$tag;
  
  if (mb_substr($gotTag, 0, 3) != 'end') {
   return false;
  } elseif ($gotTag != $tag && $type == 'block') {
   $this->raise(
    $node,
    'Invalid ending tag encountered - expected "'.$tag.'", got "'.$gotTag.'"',
    TemplateError::E_INVALID_SYNTAX
   );
  } elseif ($gotTag == $tag) {
   return true;
  }
  return true;
 }
 
 /**
  Converts current token's array into the node, and adds it to given
  node's children.
  
  @param[in,out] $node Node to append child to
 */
 private function createNodeFromToken(TemplateNodeEx $node) {
  $token = &$this->parserCurrentToken;
  $node->addChild($token['type'], $token['content'], $this->parserCurrentFile, $this->parserCurrentLine);
 }
 
 /**
  Parses token stream until it runs out of tokens, or when it encounters given tag name.
  
  @param[in,out] $node Currently processed node, newly constructed children will be appended to it
  @param[in,out] $tokens Token stream
  @param[in] $parseUntil If not null, then parser will halt when it encounters tag with that name
  @param[in] $blockTagType Type of the block tag (either @c block or @c ignore)
 */
 private function parseTokenStream(TemplateNodeEx $node, array &$tokens, $parseUntil = null, $blockTagType = null) {
  while ($this->parserGetNextToken($tokens) && !$this->parserEncounteredEndTag($node, $parseUntil, $blockTagType)) {
   switch ($this->parserCurrentToken['type']) {
    case 'tag':
     $tag  = array_shift($this->parserCurrentToken['content']);
     $args = &$this->parserCurrentToken['content'];
     
     if (!$this->plugins->known('tag', $tag)) {
      // if doesn't exist, assume inline
      $tagInfo = array('type' => 'inline');
     } else {
      $tagInfo = $this->plugins->get('tag', $tag);
     }
     
     $newNode = new TemplateNodeEx('inlineTag', $node, array($tag, $args), $this->parserCurrentFile, $this->parserCurrentLine);
     if (in_array($tagInfo['type'], array('block', 'ignore'))) {
      $newNode->nodeID = 'blockTag';
      // capture block content
      $this->parseTokenStream($newNode, $tokens, $tag, ($blockTagType == 'ignore' ? 'ignore' : $tagInfo['type']));
     }
     
     $node->nodeChildren[] = $newNode;
    break;
    case 'var':
    case 'text':
     $this->createNodeFromToken($node);
     if ($this->parserCurrentToken['type'] == 'text') {
      // update current line counter
      $this->parserCurrentLine += mb_substr_count($this->parserCurrentToken['content'], "\n");
     }
    break;
   }
  }
 }
 
 // CodeGen part
 /**
  Constructs blocks and generates code for the entire AST.
  
  @param[in] $root Root node of the AST
  @return Array of the constructed code blocks
  @todo Is it needed, or maybe @ref TemplateCompilerEx::compile should do it?
 */
 private function generateCode(TemplateNodeEx $root) {
  $this->createBlock('main', $root);
  return $this->blocks;
 }
 
 /**
  Generates code from given node's children.
  Accepts array instead of @ref TemplateNodeEx for greater
  flexibility.
  
  @param[in] $children Array to process
  @return Code as string
 */
 public function handleChildren(array &$children) {
  $code = '';
  foreach ($children as $child) {
   $code .= $this->handleNode($child);
  }
  return $code;
 }
 
 /**
  Creates code block from raw node. Part of exposed compiler API.
  
  @param[in] $block Name of the block - in form of loop:XXX or block:XXX.
  @param[in] $node Node to process
  @sa TemplateCompilerEx::handleChildren
 */
 public function createBlock($block, TemplateNodeEx $node) {
  $this->blocks[$block] = '$b=\'\';'.$this->handleChildren($node->nodeChildren).'return $b;';
 }
 
 /**
  Creates code from given node.
  
  @param[in] $node Node to process
  @return Code as string
  @todo Maybe it should be merged with @ref TemplateCompilerEx::handleChildren?
 */
 public function handleNode(TemplateNodeEx $node) {
  switch ($node->nodeID) {
   case 'text'     : return '$b.=\''.TemplateUtils::escape($node->nodeContent).'\';';
   case 'var'      : return $this->handleVariable($node);
   case 'blockTag' :
   case 'inlineTag': return $this->handleTag($node);
  }
 }
 
 /**
  Handles @c 'blockTag' and 'inlineTag' nodes.
  Calls proper tag handler (see @ref extending-st-handlers).
  
  @param[in] $node Tag node to process
  @return Code as string
 */
 private function handleTag(TemplateNodeEx $node) {
  $tag  = &$node->nodeContent[0];
  $args = &$node->nodeContent[1];
  
  $tagInfo = $this->commonVerifyElement($node, 'tag', $tag, $args);
  
  if (isset($tagInfo['parent'])) {
   $pattern  = '~^'.str_replace('\\*', '.*?', preg_quote($tagInfo['parent'], '~')).'$~su';
   
   $this->raiseIf(
    (!($node->nodeParent instanceof TemplateNodeEx) || !preg_match($pattern, $node->nodeParent->nodeContent[0])),
    $node,
    'Invalid tag nesting - tag "'.$tag.'" requires "'.$tagInfo['parent'].'" tag as immediate parent, '.
    '"'.$node->nodeParent->nodeContent[0].'" found',
    TemplateError::E_INVALID_SYNTAX
   );
   
   unset($pattern);
  }
  
  return call_user_func_array($tagInfo['handler'], array($this, $node, &$tag, &$args));
 }
 
 /**
  Handles @c 'var' node.
  
  @param[in] $node Variable node to process
  @return Code as string
 */
 private function handleVariable(TemplateNodeEx $node) {
  list($variable, $filters) = $node->nodeContent;
  $noCheck                  = false;
  
  if (mb_substr($variable, 0, 1) == '@') {
   $noCheck  = true;
   $variable = mb_substr($variable, 1);
  }
  
  list($variableCode, $variableCheck) = $this->parseVariableExpression($node, $variable);
  $variableCode                       = $this->parseFilterChain($node, $filters, '@'.$variableCode);
  
  return ($noCheck ? '' : $variableCheck).'$b.='.$variableCode.';';
 }
 
 /**
  Parses variable expression, and creates runtime PHP access code.
  
  @param[in] $node Source node
  @param[in] $variable Variable expression to parse
  @return Array(access code, existence checking code)
 */
 public function parseVariableExpression(TemplateNodeEx $node, $variable) {
  $ast   = $this->generateVariableAST($variable);
  $code  = $this->generateVariableCode($node, $ast);
  $check = 'if(!isset('.$code.')&&!is_null(@'.$code.')){$this->warnVar(\''.TemplateUtils::escape($variable).'\');}';
  
  // Hook-point: parseVariableExpression:postCodeGen
  $this->runHooks('parseVariableExpression:postCodeGen', array($node, &$code, &$check));
  //
  
  return array($code, $check);
 }
 
 /**
  Generates syntax tree (using nested arrays ATM) from
  given variable expression.
  
  @param[in] $variable Variable expression to parse
  @return Arrayized AST
 */
 private function generateVariableAST($variable) {
  $chunks = preg_split(
   '/(\[.*?\]|\.|\-\>)/s', $variable, -1,
   PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
  );
  
  foreach ($chunks as &$chunk) {
   if (mb_substr($chunk, 0, 1) == '[') {
    $chunk = $this->generateVariableAST(mb_substr($chunk, 1, -1));
   }
  }
  
  return array_filter($chunks, array('TemplateUtils', 'filterEmpty'));
 }
 
 /**
  Recursively parses variable AST, and generates PHP access code.
  
  @param[in] $node Source node
  @param[in] $ast AST to process
  @return Access code
 */
 private function generateVariableCode(TemplateNodeEx $node, &$ast) {
  $code      = '$this->ctx';
  $nextChunk = 'array'; // array, object or operator
  $operators = array('.' => 'array', '->' => 'object');
  
  while (($currentChunk = array_shift($ast)) !== null) {
   if ($nextChunk == 'operator') {
    $this->raiseIf(
     (!isset($operators[$currentChunk])),
     $node,
     'Variable access operator expected ("." or "->"), but "'.$currentChunk.'" found',
     TemplateError::E_INVALID_SYNTAX
    );
    
    $nextChunk = $operators[$currentChunk];
   } else {
    switch ($nextChunk) {
     case 'array':
      $chunkTemplate         = '[%s]';
      $chunkVariableTemplate = '@%s';
      $chunkNumberTemplate   = '%d';
      $chunkStringTemplate   = '\'%s\'';
     break;
     case 'object':
      $chunkTemplate         = '->%s';
      $chunkVariableTemplate = '{@%s}';
      $chunkNumberTemplate   = '{%d}';
      $chunkStringTemplate   = null;
     break;
     default:
      TemplateUtils::panic(__FILE__, __LINE__);
     break;
    }
    
    if (is_array($currentChunk)) {
     // variable index
     $chunkCode = sprintf($chunkVariableTemplate, $this->generateVariableCode($node, $currentChunk));
    } elseif (ctype_digit($currentChunk)) {
     // integer index
     $chunkCode = sprintf($chunkNumberTemplate, $currentChunk);
    } else {
     if ($chunkStringTemplate) {
      $currentChunk = sprintf($chunkStringTemplate, TemplateUtils::escape($currentChunk));
     }
     $chunkCode = $currentChunk;
    }
    
    $code .= sprintf($chunkTemplate, $chunkCode);
    
    $nextChunk = 'operator';
   }
  }
  
  return $code;
 }
 
 /**
  Handles filter chains. Wraps given code, and returns new one.
  
  @param[in] $node Filter chain source node
  @param[in] $filterExpr Filter chain expression (e.g. a|b|c:d)
  @param[in] $code Code to wrap in filters
  @return New code that uses filters
 */
 public function parseFilterChain(TemplateNodeEx $node, $filterExpr, $code) {
  $filterChain = TemplateUtils::splitEscaped('|', $filterExpr);
  $resultCode  = $code;
  
  // Hook-point: parseFilterChain:entry
  $this->runHooks('parseFilterChain:entry', array(&$filterChain));
  //
  
  foreach ($filterChain as &$filter) {
   list($name, $args) = TemplateUtils::split(':', $filter);
   if ($args) {
    $args = TemplateUtils::splitEscaped(',', $args);
   } else {
    $args = array();
   }
   
   foreach ($args as &$arg) {
    if (mb_substr($arg, 0, 1) == '"') {
     $arg = array('string', '\''.TemplateUtils::escape(mb_substr($arg, 1, -1)).'\'');
    } elseif (preg_match('~^\-?[0-9]+(?:\.[0-9]+)?$~', $arg)) {
     $arg = array('number', $arg);
    } else {
     list($code,) = $this->parseVariableExpression($node, $arg);
     $arg = array('variable', '@'.$code);
    }
   }
   
   $filterInfo = $this->commonVerifyElement($node, 'filter', $name, $args);
   $filterCode = call_user_func_array(
    $filterInfo['handler'], array($this, $node, &$name, &$args)
   );
   
   $resultCode = sprintf($filterCode, $resultCode);
  }
  
  return $resultCode;
 }
 
 /**
  Verifies correctness of given element. Checks whether:
  <ul>
   <li>Element exists</li>
   <li>Element's handler is callable</li>
   <li>Enough arguments have been provided</li>
  </ul>
  
  @param[in] $node %Template node representing element
  @param[in] $element Element type - @c 'tag' or @c 'filter'
  @param[in] $name Element's name
  @param[in] $args Element's arguments
  @return Element's info array
 */
 private function commonVerifyElement(TemplateNodeEx $node, $element, &$name, array &$args) {
  if (!in_array($element, array('tag', 'filter'))) {
   TemplateUtils::panic(__FILE__, __LINE__);
  }
  
  $this->raiseIf(
   (!$this->plugins->known($element, $name)),
   $node,
   'Unknown '.$element.' "'.$name.'" encountered',
   ($element == 'tag' ? TemplateError::E_UNKNOWN_TAG : TemplateError::E_UNKNOWN_FILTER)
  );
  
  $elementInfo = $this->plugins->get($element, $name);
  
  TemplateUtils::checkIfAllowed($this, $element, $name, $node);
  
  if (!is_callable($elementInfo['handler'])) {
   throw new TemplateError(
    'Invalid handler ['.TemplateUtils::strip(var_export($elementInfo['handler'], true)).
    '] supplied for '.$element.' "'.$name.'" by plugin "'.$elementInfo['plugin'].'"',
    TemplateError::E_INVALID_HANDLER
   );
  }
  
  if (isset($elementInfo['minArgs'])) {
   $this->raiseIf(
    (count($args) < $elementInfo['minArgs']),
    $node,
    'Invalid '.$element.' call - '.$element.' "'.$name.'" requires at least '.
    '"'.$elementInfo['minArgs'].'" arguments ',
    TemplateError::E_INVALID_SYNTAX
   );
  }
  
  return $elementInfo;
 }
 
 // Bulitins part
 
 /**
  Handler for @c load built-in tag. For handlers reference, see @ref extending-st-handlers.
  For standard and built-in tags reference, see @ref stdlib-tags.
  
  @param[in] $compiler Compiler handle (@ref TemplateCompilerEx instance)
  @param[in] $node Node handle (@ref TemplateNodeEx instance, see @ref extending-st-ast)
  @param[in] $tag Tag name (as string)
  @param[in] $args Tag arguments (as array)
 */
 private function handleLoadBuiltin(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$tag, array &$args) {
  $this->raiseIf(
   ($this->plugins->load($this, $node, $args[0], null, true) === false),
   $node,
   'Tried to load non-existant or invalid plugin "'.$args[0].'"',
   TemplateError::E_UNKNOWN_PLUGIN
  );
  
  // {% load %} generates no code
  return '';
 }
 
 /**
  Handler for @c comment built-in tag. For handlers reference, see @ref extending-st-handlers.
  For standard and built-in tags reference, see @ref stdlib-tags.

  @param[in] $compiler Compiler handle (@ref TemplateCompilerEx instance)
  @param[in] $node Node handle (@ref TemplateNodeEx instance, see @ref extending-st-ast)
  @param[in] $tag Tag name (as string)
  @param[in] $args Tag arguments (as array)
 */
 private function handleCommentBuiltin(TemplateCompilerEx $compiler, TemplateNodeEx $node, &$tag, array &$args) {
  // no block-level code generated
  return '';
 }
 
 // Compiler-specific utils
 
 /**
  Raises an error, appending "(in template <file> somewhere around line <line>)"
  using given node.
  
  @param[in] $node Node producing an error
  @param[in] $message Error message
  @param[in] $code Error code
  @sa TemplateError
 */
 public function raise(TemplateNodeEx $node, $message, $code) {
  throw new TemplateError(
   $message.' (in template "'.$node->nodeFile.'" somewhere around line '.$node->nodeLine.')',
   $code
  );
 }
 
 /**
  Shorthand for conditional call to @ref TemplateCompilerEx::raise.
  Won't raise if @c $cond is @c false.
  
  @param[in] $cond Condition value as boolean
  @param[in] $node Node producing an error
  @param[in] $message Error message
  @param[in] $code Error code
  @sa TemplateError
 */
 public function raiseIf($cond, TemplateNodeEx $node, $message, $code) {
  if ($cond === true) $this->raise($node, $message, $code);
 }
 
 /**
  Used to find and isolate alternative branch of given node, starting
  with given inline tag. Can be used wherever alternative (e.g. <code>{% else %}</code>)
  branch cannot be implemented by simply inlining some code (e.g. <code>} else {</code>).
  
  @param[in] $node Current node
  @param[in] $tag Tag starting alternative branch
  @return Array(main nodes, alternative branch nodes)
 */
 public function findAlternativeBranch(TemplateNodeEx $node, $tag) {
  $alternativeIdx = null;

  foreach ($node->nodeChildren as $idx => $childNode) {
   if ($childNode->nodeID == 'inlineTag' && $childNode->nodeContent[0] == $tag) {
    $alternativeIdx = $idx;
    break;
   }
  }

  if ($alternativeIdx) {
   $mainNodes        = array_slice($node->nodeChildren, 0, $alternativeIdx);
   $alternativeNodes = array_slice($node->nodeChildren, $alternativeIdx + 1);
  } else {
   $mainNodes        = &$node->nodeChildren;
   $alternativeNodes = array();
  }

  return array($mainNodes, $alternativeNodes);
 }
 
 /**
  Generates prefixed block name that is guaranteed to be unique in
  current template. By default generates 5-character unique key using
  combination of @c uniqid, @c mt_rand and @c md5.
  Beware: potential infinite loop - if key length is too small, then
  key space might be exhausted, which will lead to infinite loop in
  this function. Increase @c $keyLength if your usage could lead to
  this condition.
  
  @param[in] $idPrefix @c uniqid prefix
  @param[in] $blockPrefix Unique key will be prefixed with this. Default: @c custom:.
  @param[in] $keyLength Length of generated key. Must be lower than 32. Default: 5.
 */
 public function generateUniqueBlock($idPrefix, $blockPrefix = 'custom:', $keyLength = 5) {
  if (32 - $keyLength <= 0) {
   TemplateUtils::panic(__FILE__, __LINE__);
  }
  
  do {
   $generatedID  = md5(uniqid($idPrefix, true));
   $randomOffset = mt_rand(0, 32 - $keyLength);

   $blockName = $blockPrefix.mb_substr($generatedID, $randomOffset, $keyLength);
  } while (isset($this->blocks[$blockName]));

  return $blockName;
 }
 
 // Hooks
 
 /**
  Runs handlers associated with given hook-point.
  Every handler might return @c true boolean value,
  to break the chain and finish hook execution.
  
  @param[in] $hookPoint Hook-point to execute
  @param[in] $args Array of hook arguments
  @retval true Some handler has broken the chain
  @retval false All handlers have been executed, and none has broken the chain
 */
 private function runHooks($hookPoint, array $args) {
  array_unshift($args, $this);
  $handlers = $this->plugins->get('hook', $hookPoint);
  
  foreach ($handlers as &$hookInfo) {
   $handler = &$hookInfo['handler'];
   
   if (!is_callable($handler)) {
    throw new TemplateError(
     'Invalid handler ['.TemplateUtils::strip(var_export($hookInfo['handler'], true)).
     '] hooked into "'.$hookPoint.'" point by plugin "'.$hookInfo['plugin'].'"',
     TemplateError::E_INVALID_HANDLER
    );
   }
   
   if (call_user_func_array($handler, $args) === true) return true;
  }
  
  return false;
 }
}

/**
 Class-container for AST nodes.
 Contains node ID (it's type), references to its parent and children,
 preprocessed content, and source template line where it has been found
 (although it may not be very accurate).
*/
class TemplateNodeEx {
 /**
  Type of this node.
  It may be @c 'text' (for plaintext nodes), @c 'var' (for variable nodes),
  @c 'inlineTag' or @c 'blockTag' (for inline and block tag nodes).
 */
 public $nodeID       = '';
 /**
  Parent of this node.
 */
 public $nodeParent   = null;
 /**
  Children of this node.
 */
 public $nodeChildren = array();
 /**
  Content of this node. It may be plain text (for text and var nodes), or
  an array (for tag nodes - @c [0] will be the tag's name and @c [1] the array of its arguments).
 */
 public $nodeContent  = null;
 /**
  Aid for template debugging, source line where the parser constructed this node.
 */
 public $nodeLine     = 0;
 /**
  Aid for template debugging, source file where the parser constructed this node.
 */
 public $nodeFile     = null;
 
 /**
  Constructor.
  
  @param $id Type of this node
  @param $parent Parent of this node
  @param $content Content of this node
  @param $file Source template file
  @param $line Source template line
 */
 public function __construct($id, $parent = null, $content = null, $file = null, $line = 0) {
  $this->nodeID      = $id;
  $this->nodeParent  = $parent;
  $this->nodeContent = $content;
  $this->nodeLine    = $line;
  $this->nodeFile    = $file;
 }

 /**
  Creates a new @c TemplateNodeEx instance and adds it to this node children.

  @param $id Type of new node
  @param $content Content of new node
  @param $file Source template file
  @param $line Source template line
 */
 public function addChild($id, $content = null, $file = null, $line = 0) {
  $this->nodeChildren[] = new TemplateNodeEx($id, $this, $content, $file, $line);
 }
 
 /**
  Debugging aid. Dumps the AST and its children.
  
  @param $level Current indentation level
  @return The plaintext dump of current level and levels below
 */
 public function dump($level = 0) {
  $out = '';
  for ($i = 0; $i < $level; ++$i) { $out .= ' '; }
  $out .= 'ast<'.$this->nodeID.'>';
  if (is_array($this->nodeContent)) {
   $out .= '('.$this->nodeContent[0];
   if (count($this->nodeContent[1]) > 0) {
    $out .= ', '.implode(', ', $this->nodeContent[1]);
   }
   $out .= ')';
  } else {
   $s = str_replace("\n", '\n', $this->nodeContent);
   if (mb_strlen($s) > 30) $s = mb_substr($s, 0, 30).'[...]';
   $out .= '("'.$s.'")';
  }
  $out .= ' @ '.$this->nodeFile.':'.$this->nodeLine." => [\n";
  foreach ($this->nodeChildren as $child) { $out .= $child->dump($level + 1); }
  for ($i = 0; $i < $level; ++$i) { $out .= ' '; }
  return $out . "]\n";
 }
}
