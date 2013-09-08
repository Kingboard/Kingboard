<?php
namespace Kingboard\Views;

use Michelf\Markdown;

class Information extends \Kingboard\Views\Base
{
    public function index(array $params)
    {
        $context = array(
            "readme" => Markdown::defaultTransform(file_get_contents(APP_PATH . "/README.md")),
            "license" => Markdown::defaultTransform(file_get_contents(APP_PATH . "/LICENSE.md")),
            "contributions" => Markdown::defaultTransform(file_get_contents(APP_PATH . "/CONTRIBUTORS.md")),
            "ccp_copyright" => Markdown::defaultTransform(file_get_contents(APP_PATH . "/CCP.md"))
        );
        return $this->render('information.html', $context);
    }
}
