<?php
namespace Kingboard\Views;

use King23\Core\King23;
use Michelf\Markdown;
use Pheal\Pheal;

class Information extends \Kingboard\Views\Base
{
    public function index(array $params)
    {
        $context = array(
            "readme" => Markdown::defaultTransform(file_get_contents(APP_PATH . "/README.md")),
            "license" => Markdown::defaultTransform(file_get_contents(APP_PATH . "/LICENSE.md")),
            "contributions" => Markdown::defaultTransform(file_get_contents(APP_PATH . "/CONTRIBUTORS.md")),
            "ccp_copyright" => Markdown::defaultTransform(file_get_contents(APP_PATH . "/CCP.md")),
            "king23version" => King23::VERSION,
            "phealversion" => Pheal::$version
        );
        return $this->render('information.html', $context);
    }
}
