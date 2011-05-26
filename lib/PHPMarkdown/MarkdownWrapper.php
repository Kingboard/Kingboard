<?php
require_once 'lib/markdown.php';
/**
 * Wrapper class for Markdown Parser
 * @throws King23_Exception
 */
class MarkdownWrapper
{
    /**
     * return a parsed version of the Markdown file
     * @static
     * @throws King23_Exception
     * @param string $filename
     * @return string
     */
    public static function MarkdownFile($filename)
    {
        if(!file_exists($filename))
            throw new King23_Exception("could not find $filename for Markdown");

        return Markdown(file_get_contents($filename));
    }
}
 
