<?php
namespace Wrapper\PHPMarkdown;
require_once 'lib/markdown.php';
/**
 * Wrapper class for Markdown Parser
 * @throws \King23\Core\Exceptions\Exception
 */
class MarkdownWrapper
{
    /**
     * return a parsed version of the Markdown file
     * @static
     * @throws \King23\Core\Exceptions\Exception
     * @param string $filename
     * @return string
     */
    public static function MarkdownFile($filename)
    {
        if(!file_exists($filename))
            throw new \King23\Core\Exceptions\Exception("could not find $filename for Markdown");

        return \Markdown(file_get_contents($filename));
    }
}
 
