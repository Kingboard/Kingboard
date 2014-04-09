<?php
namespace Kingboard\Views;

use Assetic\Asset\AssetCache;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Cache\FilesystemCache;
use Assetic\Filter\Yui\CssCompressorFilter;
use Assetic\Filter\Yui\JsCompressorFilter;
use King23\Core\Registry;
use King23\View\View;

class Assetic extends View
{
    public function css(array $params)
    {
        $cssfiles = Registry::getInstance()->assets['css'];

        $collection = new AssetCollection();
        foreach ($cssfiles as $file) {
            $collection->add(
                new FileAsset(
                    $file,
                    array(
                        new CssCompressorFilter(APP_PATH . "/vendor/bin/yuicompressor.jar")
                    )
                )
            );
        }

        $cache = new AssetCache(
            $collection,
            new FilesystemCache(APP_PATH . "/cache/assetic/css")
        );

        header('Content-Type: text/css');
        echo $cache->dump();
    }

    public function js(array $params)
    {
        $jsfiles = Registry::getInstance()->assets['js'];

        $collection = new AssetCollection();
        foreach ($jsfiles as $file) {
            $collection->add(
                new FileAsset(
                    $file, array(
                        new JsCompressorFilter(APP_PATH . "/vendor/bin/yuicompressor.jar")
                    )
                )
            );
        }

        $cache = new AssetCache(
            $collection,
            new FilesystemCache(APP_PATH . "/cache/assetic/js")
        );

        header('Content-Type: text/javascript');
        echo $cache->dump();
    }

    public function fonts(array $params)
    {
        $font = $params['font'];
        if (strlen($font) < 1) {
            return false;
        }

        $fontfiles = Registry::getInstance()->assets['fonts'];
        if (isset($fontfiles[$font])) {

            $asset = new FileAsset($fontfiles[$font]);
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
            header('Content-Type: ' . $type);
            echo $asset->dump();
        }
        return true;
    }

    public function background(array $params)
    {
        $backgrounds = Registry::getInstance()->assets['backgrounds'];

        $dt = new \DateTime();
        $dt->setTime(date("H"), 0, 0);
        srand($dt->getTimestamp());

        $asset = new \Assetic\Asset\FileAsset($backgrounds[rand(0, count($backgrounds) - 1)]);
        header('Content-Type: image/jpeg');
        header("Cache-Control: public, max-age=3600, s-max-age=1800");
        echo $asset->dump();

        return true;
    }
}
