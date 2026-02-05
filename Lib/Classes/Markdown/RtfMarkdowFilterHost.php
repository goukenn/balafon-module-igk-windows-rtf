<?php
// @author: C.A.D. BONDJE DOUE
// @file: RtfMarkdowFilterHost.php
// @date: 20260130 18:41:07
namespace igk\Windows\Rtf\Markdown;

use IGK\System\IO\Markdown\IMarkdownFilterHost;
use igk\Windows\Rtf\RtfDocument;

/**
* 
* @package igk\Windows\Rtf\Markdown
* @author C.A.D. BONDJE DOUE
*/
class RtfMarkdowFilterHost implements IMarkdownFilterHost
{
    private $m_host;
    private $m_list;
    public function __construct(RtfDocument $doc)
    {
        $this->m_host = $doc;
    }

    public function escape(string $text): string
    {
        return call_user_func_array([$this->m_host, __FUNCTION__], func_get_args());   
    }

    public function getTitleStyleId(int $level): ?string
    {
        return call_user_func_array([$this->m_host, __FUNCTION__], func_get_args());        
    }

    public function initMenuList($i)
    {
        return call_user_func_array([$this->m_host, __FUNCTION__], func_get_args());
    }

    public function listTableRefCount(): int
    {
        return call_user_func_array([$this->m_host, __FUNCTION__], func_get_args());
    }
    public function prepareFormat(string $text):string
    {
        return $this->m_host->prepareFormat($text);
    }
    public function __get(string $name)
    {
        return $this->m_host->{$name};
    }
    public function __call($name, $arguments)
    {
        if (method_exists($this->m_host, $name)) {
            return call_user_func_array([$this->m_host, $name], $arguments);
        }
    }
}