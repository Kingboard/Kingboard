<?php
class Kingboard_Information_View extends Kingboard_Base_View
{
   public function index(array $params)
   {
       $context = array(
           "readme" => MarkdownWrapper::MarkdownFile(APP_PATH . "/README.md"),
           "license" => MarkdownWrapper::MarkdownFile(APP_PATH . "/LICENSE.md"),
           "contributions" => MarkdownWrapper::MarkdownFile(APP_PATH."/CONTRIBUTORS.md")
       );
       return $this->render('information.html', $context);
   }
}