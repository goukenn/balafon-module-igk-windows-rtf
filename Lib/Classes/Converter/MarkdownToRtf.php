<?php
// @author: C.A.D. BONDJE DOUE
// @file: MarkdownToRtf.php
// @date: 20260128 18:51:29
namespace igk\Windows\Rtf\Converter;


use igk\Windows\Rtf\RtfMarkdown;

/**
* help convert mardown document to rtf 
* @package igk\Windows\Rtf\Converter
* @author C.A.D. BONDJE DOUE
*/
class MarkdownToRtf{
    
    /**
     * convert string markdown do rtf document 
     * @param string $source 
     * @return string 
     */
    public function convert(string $source): string{
      return RtfMarkdown::Convert($source);
    }
}