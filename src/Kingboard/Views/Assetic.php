<?php
namespace Kingboard\Views;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use King23\Core\Registry;
use King23\View\View;

class Assetic extends View
{
    public function css(array $params)
    {
        $cssfiles = Registry::getInstance()->assets['css'];

        $collection = new AssetCollection();
        foreach ($cssfiles as $file) {
            $collection->add(new FileAsset($file));
        }

        header('Content-Type: text/css');
        echo $collection->dump();
    }

    public function js(array $params)
    {
        $jsfiles = Registry::getInstance()->assets['js'];

        $collection = new AssetCollection();
        foreach ($jsfiles as $file) {
            $collection->add(new FileAsset($file));
        }

        header('Content-Type: text/javascript');
        echo $collection->dump();
    }

    public function fonts(array $params)
    {
        $font = $params['font'];
        if (strlen($font) < 1) {
            return false;
        }

        $fontfiles = Registry::getInstance()->assets['fonts'];
        if (isset($fontfiles[$font])) {

            $asset= new FileAsset($fontfiles[$font]);
            switch (strtolower(substr($font, strpos($font, ".")))) {
                case ".eot":
                    $type = "application/vnd.ms-fontobject";
                    break;
                case "otf":
                    $type = "font/opentype";
                    break;
                case "ttf":
                    $type = "application/x-font-ttf";
                    break;
                case ".woff":
                    $type = "application/x-font-woff";
                    break;
                default:
                    $type = "application/x-font";
            }
            header('Content-Type: '. $type);
            echo $asset->dump();
        }
        return true;
    }
}