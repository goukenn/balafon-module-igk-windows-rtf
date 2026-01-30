<?php
// @author: C.A.D. BONDJE DOUE
// @file: RtfFromMarkdownListener.php
// @date: 20260130 18:41:45
namespace igk\Windows\Rtf\Markdown;

use IGK\Helper\StringUtility;
use IGK\System\Console\Logger;
use IGK\System\IO\Markdown\IMarkdownElementListener;
use IGK\System\IO\Markdown\IMarkdownFilterHost;
use IGK\System\IO\Markdown\MarkdownConverter;  
use igk\Windows\Rtf\RtfBulletNFCTypes;
use igk\Windows\Rtf\RtfConstants;
use igk\Windows\Rtf\RtfTable;
use igk\Windows\Rtf\RtfUtility;


/**
 * 
 * @package igk\Windows\Rtf\Markdown
 * @author C.A.D. BONDJE DOUE
 */
class RtfFromMarkdownListener implements IMarkdownElementListener
{
    const TableSize = 165;
    private $m_host;
    var $titleColorIndexes = [];
    private $m_citem; // current item 
    private $m_buffer;
    private $m_styles;
    /**
     * quote font index
     * @var mixed
     */
    var $quoteFontIndex = 0;
    var $quoteColorIndex = 0;
    var $quoteFontSize = 8;
    /**
     * table size in mm 
     * @var ?int
     */
    var $tableSize;
    /**
     * to listen if document content is empty
     * @var mixed
     */
    var $emptyOutputListener;
    /**
     * 
     * @var ?callable
     */
    var $appendOutputListener;
    var $lf = "\n";

    /**
     * 
     * @param string $key 
     * @return mixed 
     */
    public function getStyle(string $key)
    {
        return igk_getv($this->m_styles, $key);
    }
    /**
     * 
     * @param string|'inline-code'|'fence-code'|'quote' $key 
     * @param null|IRtfFontStyleDefinition $definition 
     * @return void 
     */
    public function setStyle(string $key, ?IRtfFontStyleDefinition $definition)
    {
        if (is_null($definition)) {
            unset($this->m_styles[$key]);
        } else
            $this->m_styles[$key] = $definition;
    }
    /**
     * list id refererence
     * @var bool|string
     */
    private $m_ls_id = false;

    public function didHandleOutput(&$isSingleDefinition, &$output)
    {
        $isSingleDefinition = true;
    }
    protected function appendToOutut($s)
    {
        Logger::warn('append: ' . $s);
        if ($r = $this->appendOutputListener) {
            $r($s);
        }
    }

    public function didStateChanged()
    {
        if ($this->m_citem) {
            $this->m_citem = null;
        }
    }
    private function _preset_buffer(?string $buffer)
    {
        $this->m_buffer = $buffer;
    }
    /**
     * 
     * @param mixed $token_id 
     * @param mixed $value 
     * @param bool|mixed $root 
     * @param ?bool $buffer 
     * @return bool 
     */
    public function filter($token_id, $value, bool $root, $callback = null, $capture = null)
    {
        $_prefix = $root ? '_filter_' : '_willtreat_';

        if (method_exists($this, $fc = $_prefix . StringUtility::FuncName($token_id))) {

            $g = call_user_func_array([$this, $fc], [$value, $token_id, $callback, $capture]);
            if (is_string($g)) {
                return (object)['output' => $g];
            }
            if (is_object($g)) {
                return $g;
            }
            return is_null($g);
        }
        igk_is_debug() && Logger::info(__FILE__ . ":" . __LINE__  . ' : missing : ' . $fc);
        return false;
    }
    /**
     * create item 
     * @param mixed $type 
     * @return object 
     */
    private function _create_new_item($type)
    {
        return (object)[
            'type' => $type,
            'value' => [],
            'buffer' => $this->m_buffer,
        ];
    }
    /**
     * 
     * @return string 
     */
    public function endState(): string
    {
        $i = $this->m_citem;
        $this->m_citem = [];
        if ($i) {
            $v = $i->value;
            if (isset($i->render)) {
                $fc = $i->render;
                $v = [$fc()];
            }
            return implode('', array_filter([ // $i->buffer, 
                sprintf($i->format ?? '%s', implode('', $v))
            ]));
        }
        return '';
    }
    public function isEmpty()
    {
        if ($this->emptyOutputListener) {
            $fc = $this->emptyOutputListener;
            return $fc();
        }
        return true;
    }
    private function _prepareFormat($v)
    {
        return $this->m_host->prepareFormat($v);
    }
    private function _filter_table_segment($value){
        if ($this->m_citem && ($this->m_citem->type == 'table')){
            $this->m_citem->tab->firstHeader = true;
        }
        return null;
    }
    private function _filter_table_entry($value)
    {
        /**
         * @var mixed
         */
        $tab = null;
        $new = is_null($this->m_citem) || ($this->m_citem->type != 'table');
        if ($new) {
            $this->m_citem = $this->_create_new_item('table');
            $tab = new RtfTable;
            $this->m_citem->tab = $tab;
            $this->m_citem->render = function () use ($tab): ?string {
                $i = $tab->colCount;
                $cells = [];
                $tableW = $this->tableSize ?? self::TableSize;
                if ($i > 0) {
                    $W = round($tableW / $i);
                    $l = 1;
                    while ($i > 0) {
                        $cells[] = [$W * $l, [['solid', 15]]];
                        $i--;
                        $l++;
                    }
                    $tab->setCell(0, $cells);
                    return $tab->render();
                }
                return null;
            };
        }
        $tab = $this->m_citem->tab;
        $v = igk_str_rm_start(igk_str_rm_last($value, '|', 1), '|', 1);
        $p = explode('|', $v);
        $tab->rows[] = $p;
        $tab->colCount = max($tab->colCount, count($p));
    }
    private function _filter_text_quote($value, $token_id, $callback = null, $buffer = null)
    {
        $quote_index = $this->quoteFontIndex;
        $is_empty_output = empty($buffer);
        $new = is_null($this->m_citem) || ($this->m_citem->type != 'quote');
        if ($new) {
            $this->m_citem = $this->_create_new_item('quote');
            $this->m_citem->format = '{%s}' . "\n";
            $quote_size = ceil($this->quoteFontSize * 2);
            $this->m_citem->value[] = "\\pard\\f" . $quote_index . "\\cf" . $this->quoteColorIndex . "\\fs" . $quote_size . " ";
        }
        $v = substr($value, 2);
        $this->m_citem->value[] = $this->_prepareFormat($v) . RtfUtility::LF;
        return null;
    }
    private function _filter_text_italic(string $value)
    {
        $value = substr($value, 1, -1);
        return RtfUtility::Italic($value);
    }
    private function _filter_text_bold(string $value)
    {
        $value = substr($value, 2, -2);
        return RtfUtility::Bold($value);
    }
    private function _res_style($key, &$pardef = null)
    {
        $obj = $this->getStyle($key);
        list($cFs, $fcl, $bcl, $i, $b, $u, $paragrahBgColor, $fontSize) = igk_extract($obj, 'fontFamily|foreColor|bgColor|i|b|u|paragrahBgColor|fontSize');

        if (is_null($cFs)) {
            $cFs = 0;
        }
        $style = ["\\f" . $cFs];
        if ($fcl) $style[] = sprintf(RtfUtility::SET_COLOR_FMT, $fcl);
        if ($bcl) $style[] = sprintf(RtfUtility::SET_BGCOLOR_FMT, $bcl);
        if ($b) $style[] = "\\b";
        if ($i) $style[] = "\\i";
        if ($u) $style[] = "\\ul";
        if (!is_null($paragrahBgColor)) $pardef[] = sprintf(RtfUtility::SET_PAR_BGCOLOR_FMT, $paragrahBgColor);
        if ($fontSize) $style[] = "\\fs" . ($fontSize * 2);
        $style = implode('', $style);
        if ($pardef) {
            $pardef = implode('', $pardef);
        }
        return $style;
    }
    private function _filter_code_block(string $value)
    {
        $value = substr($value, 1, -1);
        $style = $this->_res_style('inline-code');
        $value = $this->_prepareFormat($value);
        return sprintf("{%s}", implode(" ", [$style, $value]));
    }
    private function _filter_text_uri_block(string $value, $d, $n, $g): string
    {
        $style = $this->_res_style('link');
        $uri = $g->captures['uri'][0];
        $text = $g->captures['text'][0];

        if (preg_match("/^#/", $uri)) {
            $value = sprintf(RtfUtility::LINK_TO_MARK_FMT, substr($uri, 1), $text);
        } else {
            $value = sprintf(RtfUtility::LINK_FMT, $uri, $text);
        }
        return sprintf("{%s}", implode(" ", array_filter([$style, $value])));
    }
    private function _filter_fence_code(string $value, $d, $n, $captures): string
    {
        $style = $this->_res_style('fence-code', $pardef);
        $name = isset($captures->beginCaptures['name']) ? igk_getv($captures->beginCaptures['name'], 0) : null;
        $value = substr($value, strlen($name) + 4, -4);
        $value = $this->_prepareFormat($value);
        return sprintf("\\pard" . $pardef . "\n{%s}\\\n", implode(" ", array_filter([$style, $value])));
    }
    /**
     * 
     * @param mixed $value 
     * @param mixed $token_id 
     * @return string 
     */
    private function _filter_list_item($value, $token_id, $c, $buffer)
    {
        $_LS = 'list';
        $cond2 = false;
        $v_new = is_null($this->m_citem) || ($cond2 = ($this->m_citem->type != $_LS));
        if ($v_new) {
            if ($cond2) {
                $rbuffer = $this->endState();
                $this->appendToOutut($rbuffer);
            }
            $this->m_citem = $this->_create_new_item($_LS);
            $this->m_citem->depth = 0;
            $this->m_citem->format = '{%s}' . "\n";
            if (!$this->m_ls_id) {
                $this->m_host->listOverride('\\ls1', "\\listid1", RtfConstants::PUCE_CIRCLE, RtfBulletNFCTypes::Puce, 1, 1, 0);
                $this->m_ls_id = '\\ls1';
            }
        }
        $c = MarkdownConverter::TreatMarkdownSubItem($value) ?? igk_die('not a valid subitem to filter');
        $i = $this->m_citem;
        list($depth, $value) = igk_extract($c, 'depth|value');
        if ($depth != $i->depth) {
            if (count($i->value) == 0) {
                $i->depth = $depth;
            } else {
            }
        }
        if (empty($i->value)) {
            $v =  RtfUtility::List($value, RtfConstants::PUCE_CIRCLE, null, $this->m_ls_id, "\\ilvl" . $depth);
        } else {
            $v = $value . "\\\n";
        }
        $i->value[] = $v;

        return null;
    }

    /**
     * 
     * @param string $output 
     * @return string 
     */
    public function rtrimOutput(string $output)
    {
        $c = rtrim($output);
        if (igk_str_endwith($c, "\\")) {
            $c .= "\n";
            $output = $c;
        }
        return $output;
    }
    public function __construct(IMarkdownFilterHost $doc)
    {
        $this->m_host = $doc;
    }
    public function title($text, int $level, ?string $slug = null): string
    {
        $size = igk_getv($this->m_host->titleFontSizes, $level);
        $ft = igk_getv($this->m_host->titleFonts, $level, 1);
        $clindex = igk_getv($this->titleColorIndexes, $level, '3');
        return '{\\fs' . ($size * 2) . '\\f' . $ft . '\\cf' . $clindex . '{' . $text . '}' . "}\n";
    }
    public function par($text): string
    {
        return "\\pard\n" . $this->_prepareFormat($text) . RtfConstants::LF;
    }
    public function default($text): string
    {
        return $this->par($text);
    }
}
