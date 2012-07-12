<?php
namespace Kingboard\Views;
class Information extends \Kingboard\Views\Base
{
   public function index(array $params)
   {
       $context = array(
           "readme" => \Wrapper\PHPMarkdown\MarkdownWrapper::MarkdownFile(APP_PATH . "/README.md"),
           "license" => \Wrapper\PHPMarkdown\MarkdownWrapper::MarkdownFile(APP_PATH . "/LICENSE.md"),
           "contributions" => \Wrapper\PHPMarkdown\MarkdownWrapper::MarkdownFile(APP_PATH."/CONTRIBUTORS.md")
       );
       return $this->render('information.html', $context);
   }
}