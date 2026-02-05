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
use IGK\System\Regex\Replacement;
use IGK\System\Text\Regex;
use IGK\System\Text\RegexMatcherUtility;
use IGK\System\Text\RegexReplaceContent;
use igk\Windows\Rtf\RtfBulletNFCTypes;
use igk\Windows\Rtf\RtfConstants;
use igk\Windows\Rtf\RtfLevelJustification;
use igk\Windows\Rtf\RtfTable;
use igk\Windows\Rtf\RtfUtility;


/**
 * listener used to transform markdown -> rtf  
 * @package igk\Windows\Rtf\Markdown
 * @author C.A.D. BONDJE DOUE
 */
class RtfFromMarkdownListener implements IMarkdownElementListener
{
    const TableSize = 165;
    /**
     * 
     * @var IMarkdownFilterHost
     */
    private $m_host;
    private $m_headerReplacement;


    // TODO - view As mardown 
    /**
     * line feed flag
     * @var mixed
     */
    private $ln_flag = false;
    var $ignoreConsecutiveLF = false;
    var $titleColorIndexes = [];
    private $m_citem; // current item 
    // private $m_buffer;
    private $m_styles;
    private $m_list_style_model = [];
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
     * d - 
     * @return void 
     */
    public function setHeaderReplacement($rt)
    {
        $this->m_headerReplacement = $rt;
    }
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
    protected function updateCurrentItem()
    {
        if ($this->m_citem) {
            $this->appendToOutput($this->endState());
        }
    }
    /**
     * append to output 
     * @param mixed $s 
     * @return void 
     */
    protected function appendToOutput($s)
    {
        igk_is_debug() && Logger::warn('append: ' . $s);
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
    // private function _preset_buffer(?string $buffer)
    // {
    //     $this->m_buffer = $buffer;
    // }

    protected function _willtreat_text_litteral_string($v)
    {
        return $v;
    }
    protected function _willtreat_tag_definition($v)
    {
        return $v;
    }
    protected function _willtreat_text_bold($v)
    {
        return RtfUtility::Bold($this->_prepareAndEscape(substr($v, 2, -2)));
    }
    function beforeBufferLine($o, $host, &$linefeed)
    {
        // + | --------------------------------------------------------------------
        // + | call when method not handled by markdown - and treat line feed
        // + |
        $v_i = $this->m_citem;
        if ($v_i) {
            if (($v_i->type == 'lf') && $v_i->lineFeed) {
                $linefeed = false;
            }
            if ($o->tokenID != $v_i->tokenID) {
                $this->updateCurrentItem();
            }
            //$v_i->lineFeed = false;
        }
    }
    public function prepareTextBeforeAppendToBuffer(string $tc)
    {
        return $this->_prepareFormat($tc);
    }
    /**
     * 
     * @param mixed $token_id 
     * @param mixed $value 
     * @param bool|mixed $root 
     * @param ?bool $buffer 
     * @return bool 
     */
    public function filter($token_id, $value, bool $root, $callback = null, $capture = null, $options = null)
    {
        $_prefix = $root ? '_filter_' : '_willtreat_';
        // 
        // if (!$options['isSubState'] && ($fc = igk_getv([
        //     'line-feed'=>function($options){
        //         igk_wln(__FILE__.":".__LINE__ , $options);
        //     }
        // ], $token_id ))){
        //     $fc($options,$value);
        // }
        if ($i = $this->m_citem) {

            switch ($i->type) {
                case 'lf':
                    if (!in_array($token_id, ['empty-line', 'line-feed'])) {
                        // $this->beforeBufferLine();
                        $this->m_citem = null;
                    }
                    break;
            }
        }

        if (method_exists($this, $fc = $_prefix . StringUtility::FuncName($token_id))) {
            $g = call_user_func_array([$this, $fc], [$value, $token_id, $callback, $capture]);

            if (($g == null) && ($this->m_citem)) {
                if (!$options['isSubState'] && !empty($options['buffer'])) {
                    Logger::warn('update buffer');
                    $this->appendToOutput($options['buffer']);
                    $options['buffer'] = '';
                }
            }

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
    private function _create_new_item($type, $token_id = null)
    {
        return (object)[
            'type' => $type,
            'value' => [],
            'tokenID' => $token_id,
            'lf_end' => false
        ];
    }
    /**
     * 
     * @return string 
     */
    public function endState(): string
    {
        $i = $this->m_citem;
        $this->m_citem = null;
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
    /**
     * escape litteral 
     * @param mixed $v 
     * @return mixed 
     */
    private function _escape(string $v)
    {
        return $this->m_host->escape($v);
    }
    private function _prepareAndEscape($v)
    {
        return $this->_escape($this->_prepareFormat($v));
    }
    public function getProperties($list)
    {
        return array_filter(array_map(
            function ($a) {
                return $this->m_host->{$a};
            },
            $list
        ));
    }
    private function _instruct(string $name, $args, $g = null)
    {
        $st = igk_getv([
            'pagebreak' => "\\page\n",
            'page-break' => "\\page\n",
            'section' => function ($args) {
                $t = ["\\sect\\sectd \n"];
                if ($args) {
                    $st = igk_json_parse('{' . $args . '}');

                    if ($header = igk_getv($st, 'h')) {
                        $props = implode($this->getProperties(['lang', 'header-font-size']));
                        if (!empty($props)) $props .= ' ';
                        $rp = new Replacement;
                        $this->initHeaderFormatReplacement($rp);
                        $header = $this->_prepareFormat($header);
                        $header = preg_replace("/(?<=[^\\\\])(\}|\{)/", "\\\\$1", $header);
                        $header = $rp->replace($header);
                        $t[] = RtfUtility::Header($props . $header);
                    }
                }
                $this->ln_flag = true;
                return implode("", $t);
            }
        ], $name);
        if ($st instanceof \Closure) {
            return $st($args, $g);
        }
        return $st;
    }
    public function initHeaderFormatReplacement($rp)
    {
        if ($r = $this->m_headerReplacement) {
            foreach ($r as $k => $v) {
                $rp->add($k, $v);
            }
            return;
        }
        $rp->add('/%f_page-right%/', '\\tx9360\\tab{\\qr\\chpgn}');
    }
    private function _filter_md_instruction_start($value, $r, $callback, $g)
    {
        $n = $g->beginCaptures[1][0];
        $indexof = strpos($value, '{');
        $g = substr($value, $indexof + 1, -1);
        return $this->_instruct($n, $g);
    }
    private function _filter_md_instruction($value, $r, $callback, $g)
    {
        $n = $g->captures[1][0];
        $arg = null;
        if (count($g->captures) > 2) {
            $arg = $g->captures[2][0];
        }
        return $this->_instruct($n, $arg);
    }
    private function _filter_table_segment($value)
    {
        if ($this->m_citem && ($this->m_citem->type == 'table')) {
            $this->m_citem->tab->firstHeader = true;
            $this->m_citem->lf_end = false;
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
                igk_is_debug() && Logger::warn('render table : ' . $i);
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
        if ($value && igk_str_startwith($value, '|')) {
            $value = trim($value);
        }
        $v = $this->_prepareFormat(igk_str_rm_start(igk_str_rm_last($value, '|', 1), '|', 1));
        $p = explode('|', $v);
        $tab->rows[] = $p;
        $tab->colCount = max($tab->colCount, count($p));
        $this->m_citem->lf_end = false;
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
        $this->m_citem->lf_end = false;
        return null;
    }

    private function _filter_hr(string $value)
    {
        $this->updateCurrentItem();
        $this->ln_flag = true;
        return "\pard{\plain{\*\pgdblspace0}\brdrb\brdrs\brdrw20\par\n}";
    }
    private function _filter_text_italic(string $value)
    {
        $value = substr($value, 1, -1);
        $value = $this->_prepareFormat($value);
        return RtfUtility::Italic($value);
    }
    private function _filter_text_bold(string $value)
    {
        $value = substr($value, 2, -2);
        $value = $this->_prepareFormat($value);
        return RtfUtility::Bold($value);
    }
    /**
     * 
     * @param bool $write 
     * @return null 
     */
    private function _write_lf($write = false)
    {
        if ($this->ln_flag || ($this->m_citem && ($this->m_citem->type != 'lf'))) {
            // $this->appendToOutut($this->endState());
            // because maybe used by a block container 
            igk_is_debug() && !$this->ln_flag && Logger::info(sprintf('LF ------ detect on [%s]', $this->m_citem->type));
            if ($this->m_citem && ($this->m_citem->type != 'lf')) {
                if ($this->m_citem->lf_end) {
                    $this->updateCurrentItem();
                    $this->_create_lf(true);
                } else
                    $this->m_citem->lf_end = true;
            }
            $this->ln_flag = false;
            return null;
        }
        if (is_null($this->m_citem)) {
            $this->_create_lf($write);
        }
        return null;
    }
    private function _create_lf($write)
    {
        $this->m_citem = $this->_create_new_item('lf');
        $this->m_citem->value[] = "";
        $this->m_citem->lineFeed = false;
        if ($write) {
            $this->m_citem->mark = true;
        }
    }
    public function endLineFeedToBuffer(&$lfMarker): ?string
    {
        if ($this->m_citem && ($this->m_citem->type == 'lf') && $this->m_citem->mark) {
            if ($this->ignoreConsecutiveLF) {
                $this->m_citem->mark = false;
            }
            $this->m_citem->lineFeed = &$lfMarker;
            return RtfUtility::LF;
        }
        return null;
    }
    private function _filter_line_feed(string $value)
    {
        return $this->_write_lf(true);
    }
    private function _filter_text_litteral_string(string $value)
    {
        return $value;
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
            $c = substr($uri, 1);
            $c = StringUtility::RemoveAccents($c);
            // $c = strtr($c, [
            //     "à"=>"a",
            //     "ä"=>"a",
            //     "â"=>"a",                
            //     "é"=>"e",
            //     "ë"=>"e",
            //     "ê"=>"e",
            //     "è"=>"e",
            // ]);

            $value = sprintf(RtfUtility::LINK_TO_MARK_FMT, $c, $text);
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
        $value = str_replace("\n", "\\\n", $this->_prepareAndEscape($value));
        return sprintf("\\pard" . $pardef . "\n{%s}\\\n\\pard ", implode(" ", array_filter([$style, $value])));
    }
    private function _filter_order_list_item($value, $c, $callback, $g)
    {
        if (is_null($this->m_olist_ids)) {
            $this->m_olist_ids = [];
        }
        return self::_BuildList($value, 'olist', $g->tokenID, $this->m_olist_ids, $this, RtfBulletNFCTypes::Decimal, "\\'00. ", 3, 1, 1);
    }
    private $m_olist_ids;
    private static function _BuildList($value, $_LS, $token_id,  &$list, $q, $type = RtfBulletNFCTypes::Puce, $format = RtfConstants::PUCE_CIRCLE, $size = 1, $startAt = 1, $position = 0)
    {
        $cond2 = false;
        $v_new = is_null($q->m_citem) || ($cond2 = ($q->m_citem->type != $_LS));
        $lsid = '';
        if ($v_new) {
            if ($cond2) {
                $rbuffer = $q->endState();
                $q->appendToOutut($rbuffer);
            }
            $startAt = intval(Regex::Get('number', "/^(?P<number>\\d+)/", $value, 1));
            $q->m_citem = $q->_create_new_item($_LS, $token_id);
            $q->m_citem->depth = 0;
            $q->m_citem->format = '{%s}' . "\n";
            $idx = count($list) + 10;
            $q->m_citem->listid = '\\ls' . $idx;
            $q->listOverride($q->m_citem->listid, "\\listid" . $idx, $format, $type, $startAt, $size, $position);
            $list[] = $q->m_citem->listid;
        }
        $lsid = $q->m_citem->listid;
        $c = null;


        $c = MarkdownConverter::TreatMarkdownSubItem($value) ?? igk_die('not a valid subitem to filter');
        $i = $q->m_citem;
        list($depth, $value) = igk_extract($c, 'depth|value');
        $value = $q->_prepareFormat($value);
        if ($depth != $i->depth) {
            if (count($i->value) == 0) {
                $i->depth = $depth;
            } else {
                throw new \Exception('Not implement more depth');
            }
        }
        if (empty($i->value)) {
            $v =  RtfUtility::List($value, RtfConstants::PUCE_CIRCLE, null, $lsid, "\\ilvl" . $depth);
        } else {
            $v = $value . "\\\n";
        }
        $i->value[] = $v;
        $i->lf_end = false;
        return null;
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
                $this->appendToOutput($rbuffer);
            }
            $this->m_citem = $this->_create_new_item($_LS);
            $this->m_citem->depth = 0;
            $this->m_citem->format = '{%s}' . "\n";
            if (!$this->m_ls_id) {
                $this->listOverride('\\ls1', "\\listid1", RtfConstants::PUCE_CIRCLE, RtfBulletNFCTypes::Puce, 1, 1, 0);
                $this->m_ls_id = '\\ls1';
            }
        }
        $c = MarkdownConverter::TreatMarkdownSubItem($value) ?? igk_die('not a valid subitem to filter');
        $i = $this->m_citem;
        list($depth, $value) = igk_extract($c, 'depth|value');
        $value = $this->_prepareFormat($value);
        if ($depth != $i->depth) {
            if (count($i->value) == 0) {
                $i->depth = $depth;
            } else {
                throw new \Exception('Not implement more depth');
            }
        }
        if (empty($i->value)) {
            $v =  RtfUtility::List($value, RtfConstants::PUCE_CIRCLE, null, $this->m_ls_id, "\\ilvl" . $depth);
        } else {
            $v = $value . "\\\n";
        }
        $i->value[] = $v;
        $i->lf_end = false;
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
    /**
     * detec titile 
     * @param mixed $text 
     * @param int $level 
     * @param null|string $slug 
     * @return string 
     */
    public function title($text, int $level, ?string $slug = null): string
    {
        igk_is_debug() && Logger::info('write title ' . $text);
        $hmark = '';
        if ($slug) {
            if (false !== ($pos = strpos($text, '{'))) {
                $cpos = $pos;
                igk_str_read_brank($text, $cpos, '}', '{');
                $text = substr($text, 0, $pos) . substr($text, $cpos + 1);
                $hmark = RtfUtility::BookMark(igk_str_rm_start($slug, '#', 1), '');
            } else{
                $hmark = RtfUtility::BookMark($slug, '');
            }
        }

        $size = igk_getv($this->m_host->titleFontSizes, $level);
        $ft = igk_getv($this->m_host->titleFonts, $level, 1);
        $clindex = igk_getv($this->titleColorIndexes, $level, '3');
        $suffix = '';
        $this->updateCurrentItem();
        $style = '\\fs' . ($size * 2) . '\\f' . $ft . '\\cf' . $clindex;
        if ($level > 1) { // on greater markdown title level check of bullet place holder 
            // TODO: just apply markdown maker list format - BULLET DETECTION
            // detect list marker 
            $v = $text;
            $r = RtfUtility::BulletPlaceHolderInfo($v);
            if (!is_null($r)) {
                $p = $this->_initListMenu($r);
                $v = $this->_prepareFormat(ltrim(substr($v, strlen($r->from) + 1)));
                $v = RtfUtility::List($v, $r->from, null, $p->id, "\\ilvl" . $r->level);
                $text = $this->m_host->getTitleStyleId($r->level + 2) . $v;
            } else {
                // $hmark = $this->m_host->getTitleStyleId($level);
                $text = $this->_prepareFormat($text);
                $style = $this->m_host->getTitleStyleId($level) ?? $style;
            }
        } else {
            $text = $this->_prepareFormat($text);
            $lv = $this->m_host->getTitleStyleId(1);
            $text = sprintf("{%s %s\\\n}\\pard", $lv ?? '\\s1', $text);
            $this->m_citem = $this->_create_new_item('title');
            $this->m_citem->value[] = $text;
            return '';
        }
        // + | because of a title every title must keep next 
        $v = $hmark . '{' . RtfConstants::KEEP_NEXT . $style . '{' . $text . '}' . "}" . $suffix;

        $this->m_citem = $this->_create_new_item('title');
        $this->m_citem->value[] = $v;
        $this->m_citem->lf_end = true;
        return '';
    }
    private $m_title_menu_id;
    /**
     * 
     * @param string $id 
     * @param string $listid 
     * @param '\\uc0\\u8226' $puce 
     * @param '\\levelnfc23\\levelnfcn23' $type 
     * @param int $startAt 
     * @param int $size 
     * @param int $position 
     * @param '\\leveljc0' $justify 
     * @return mixed 
     */
    private function listOverride(
        string $id,
        $listid = '\\listid1',
        $puce = RtfConstants::PUCE_CIRCLE,
        $type = RtfBulletNFCTypes::Puce,
        $startAt = 1,
        $size = 1,
        $position = 0,
        $justify = RtfLevelJustification::Left
    ) {
        return call_user_func_array([$this->m_host, __FUNCTION__], func_get_args());
    }
    /**
     * 
     * @param mixed $i 
     * @return mixed
     */
    protected function _initListMenu($i)
    {

        return $this->m_host->initMenuList($i);
    }
    // /**
    //  * 
    //  * @param mixed $c 
    //  * @return mixed|object 
    //  * @deprecated init _initListMenu 
    //  */
    // private function _formatMenuInfo($c, $reset=false)
    // {
    //     $format = trim($c);
    //     $ftp = true;
    //     if (preg_match("/(([a-zA-Z0-9]+)\.?)$/", $format, $tab)){
    //         $ftp = is_numeric($tab[2]);
    //         $ref = substr($format, strlen($tab[2]));
    //         $format = "\\'00".$ref.' ';
    //     }
    //     if (!$reset && isset($this->m_list_style_model[$format])){
    //         // if (isset($this->m_title_menu_id[$format])){
    //         //     return $this->m_title_menu_id[$format];
    //         // }
    //         return $this->m_list_style_model[$format];
    //     }
    //     $c_i = $this->m_host->listTableRefCount()+1;        
    //     $id = '\\ls'.$c_i;
    //     $lid = '\\listid'.$c_i;
    //     $size = strlen($format)-3;
    //     $this->listOverride($id, $lid, 
    //     $format, 
    //     // RtfConstants::PUCE_CIRCLE, 
    //     // "\\uc0\\u9642 ",
    //     //"\\'00.",
    //     // RtfBulletNFCTypes::Puce.
    //     ($ftp ? 
    //     RtfBulletNFCTypes::Decimal:
    //     RtfBulletNFCTypes::NewLatinUpper),
    //     1, 
    //     $size,
    //     1,
    //     RtfLevelJustification::Left,
    //     'square',
    //     );
    //     // RtfBulletNFCTypes::LatinUpper. 
    //     // RtfBulletNFCTypes::NewLatinUpper, 
    //     // 1, strlen($format)-3, 1);

    //     //$this->m_title_menu_id[$format] = $p;
    //     $p = $this->m_list_style_model[$format] = (object)['id'=>$id, 'format'=>$format];
    //     return $p;
    // }
    public function resetChapter()
    {
        $this->m_title_menu_id = null;
    }
    public function par($text): string
    {
        return "\\pard\n" . $this->_prepareFormat($text);
    }
    /**
     * 
     * @param mixed $text 
     * @return string 
     */
    public function default($text): string
    {
        igk_is_debug() && Logger::success('writing default listener');
        $cp = str_replace("\\\n", "\n", $text);
        $f = $this->par($cp);
        return $f;
    }
}
