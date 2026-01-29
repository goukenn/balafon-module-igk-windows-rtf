<?php
// @author: C.A.D. BONDJE DOUE
// @file: RtfDocument.php
// @date: 20260127 10:00:25
namespace igk\Windows\Rtf;

use IGK\System\Console\Logger;
use IGK\System\IO\StringBuilder;

/**
 * 
 * @package igk\Windows\Rtf
 * @author C.A.D. BONDJE DOUE
 */
class RtfDocument
{
    private $m_options;
    private $m_states;

    var $colors = ['#000'];
    var $fonts = ['\\froman Times New Roman'];
    private $m_citem;
    private $m_items = [];
    private $m_extends = [];
    private $m_header_state = false;

    /**
     * 
     * @param mixed $size 
     * @return void 
     */
    public function setFontSize($size)
    {
        $this->m_states['font-size'] = '\\fs' . $this->_auto_select($size * 2);
    }
    public function setFont(int $index)
    {
        $this->m_states['font'] = '\\f' . $this->_auto_select($index);
    }
    public function setTextColor(int $index)
    {
        $this->m_states['text-color'] = '\\cf' . $this->_auto_select($index);
    }
    public function setBackgroundColor(int $index)
    {
        $this->m_states['background-color'] = '\\cb' . $this->_auto_select($index);
    }
    public function setStrokeWidth(int $size)
    {
        $this->m_states['stroke-width'] = '\\strokewidth' . $this->_auto_select($size);
    }
    protected function _auto_select(int $index)
    {
        return $index < 0 ? ' ' : $index;
    }
    /**
     * reset style 
     * @return void 
     */
    public function resetStyle()
    {
        $this->setFont(0);
        $this->setTextColor(0);
        $this->setBackgroundColor(0);
    }
    /**
     * store State 
     * @return void 
     */
    public function storeState()
    {
        if ($this->m_states) {
            $this->m_items[] = implode('', array_values($this->m_states)) . "\n";
            $this->m_states = [];
            $this->m_header_state = true;
        }
    }
    public function prepareFormat(string $line)
    {
        $line = str_replace([
            "\r\n",
            "\n\r",
        ], ["\n", "\n"], $line);

        $tr = [
            '°' => "\\'b0",
            '²' => "\\'b2",
            '³' => "\\'b3",
            'µ' => "\\'b5",
            'à' => "\\'e0",
            'â' => "\\'e2",
            'ä' => "\\'e4",
            'è' => "\\'e8",
            'é' => "\\'e9",
            'ê' => "\\'ea",
            'ë' => "\\'eb",
            'î' => "\\'ee",
            'ï' => "\\'ef",
            'ô' => "\\'f4",
            'ö' => "\\'f6",
            'ù' => "\\'f9",
            'ÿ' => "\\'ff",
            'û' => "\\'fb",
            'ü' => "\\'fc",
            'ç' => "\\'e7",
            'À' => "\\'c0",
            'Â' => "\\'c2",
            'Ä' => "\\'c4",
            'Ç' => "\\'c7",
            'È' => "\\'c8",
            'É' => "\\'c9",
            'Ê' => "\\'ca",
            'Ë' => "\\'cb",
            'Î' => "\\'ce",
            'Ï' => "\\'cf",
            'Ô' => "\\'d4",
            'Ö' => "\\'d6",
            'Ù' => "\\'d9",
            'Û' => "\\'db",
            'Ü' => "\\'dc",
            // '€' => "\\'80",
            '€' => "\\u8364?",
            '«' => "\\'ab",
            '»' => "\\'bb",
            '—' => "\\'97",
            '–' => "\\'96",
            '£' => "\\'a3",
        ];
        $line = strtr($line, $tr);
        $line = implode(RtfConstants::LF,  explode("\n", $line));
        return $line;
    }
    /**
     * save state
     * @return mixed 
     */
    public function saveState()
    {
        $r = $this->m_states;
        $this->m_states = [];
        return $r;
    }
    /**
     * 
     * @param mixed $state 
     * @return void 
     */
    public function restoreState($state)
    {
        $this->m_states = $state ?? [];
    }
    protected function _update()
    {
        if ($this->m_citem) {
            $this->m_items[] = $this->m_citem;
            $this->m_citem = null;
        }
        $this->storeState();
    }
    public function line(string $line)
    {
        $this->_update();
        $line = $this->prepareFormat($line);
        $this->m_citem = sprintf("{" . $line . "}");
    }
    public function page()
    {
        $this->_update();
        $this->m_items[] = '\\page';
    }

    /**
     * 
     * @param string $text 
     * @param '\\uc0\\u8226' $bullet 
     * @return void 
     */
    public function list(
        string $text,
        $bullet = RtfConstants::PUCE_CIRCLE,
        $tabPuce = null,
        $id = '\\ls1',
        $level = '\\ilvl0'
    ) {
        $this->resetParagrah();
        $tabPuce = $tabPuce ?? RtfConstants::TAB_PUCE;
        $this->m_items[] = $tabPuce;
        $this->m_items[] = sprintf(RtfConstants::LISTITEM_FMT, $id . $level, $bullet, $text) . RtfConstants::LF;
    }
    /**
     * set marking in cm
     * @param mixed $t 
     * @param mixed $r 
     * @param mixed $b 
     * @param mixed $l 
     * @return void 
     */
    public function setMargin($t, $r, $b, $l)
    {
        $this->m_header_state && igk_die('margin must be set before');
        $this->m_states['margin'] = sprintf(
            RtfConstants::MARGIN_FMT,
            RtfUtility::CmToWtips($l),
            RtfUtility::CmToWtips($r),
            RtfUtility::CmToWtips($b),
            RtfUtility::CmToWtips($t)
        );
    }
    public function setMarginMm($t, $r, $b, $l)
    {
        $this->m_header_state && igk_die('margin must be set before');
        $this->m_states['margin'] = sprintf(
            RtfConstants::MARGIN_FMT,
            RtfUtility::MmToWtips($l),
            RtfUtility::MmToWtips($r),
            RtfUtility::MmToWtips($b),
            RtfUtility::MmToWtips($t)
        );
    }
    public function setPaperSize(int $w, int $h)
    {
        $this->m_states['paper-size'] =
            sprintf(
                RtfConstants::PAGE_SIZE_FMT,
                RtfUtility::CmToWtips($w),
                RtfUtility::CmToWtips($h)
            );
    }
    public function setFirstLineIndent(int $size)
    {
        $this->m_states['first-line-indent'] = sprintf(RtfConstants::FI_FMT, RtfUtility::CmToWtips($size));
    }
    /**
     * 
     * @param int $size 
     * @return void 
     */
    public function setLineIndent(int $size)
    {
        $this->m_states['line-indent'] = sprintf(RtfConstants::LI_FMT, RtfUtility::CmToWtips($size));
    }
    /**
     * set view kind
     * @param int $w 
     * @param int $h 
     * @param int $kind 
     * @return void 
     */
    public function setViewKind(int $w, int $h, int $kind = 0)
    {
        $this->m_states['view-kind'] = sprintf(
            RtfConstants::VIEW_FMT,
            RtfUtility::CmToWtips($w),
            RtfUtility::CmToWtips($h),
            $kind
        );
    }
    public function resetParagrah()
    {
        $this->_update();
        $this->m_items[] = RtfConstants::RESET_PARARGRAPH;
    }
    /**
     * render the document 
     * @return string 
     */
    public function render(): string
    {
        $def = new StringBuilder;
        $c_list = ['\\ansi\\deff0'];
        $c_list[] = "\n" . RtfUtility::FontTables($this->fonts);
        $c_list[] = "\n" . RtfUtility::ColorTableEntryFromWebColor($this->colors);
        $this->_update();
        if ($this->m_extends) {
            $c_list = array_merge($c_list, ["\n"], $this->m_extends);
        }
        // if ($this->m_citem) {
        //     $this->m_items[] = $this->m_citem;
        //     $this->m_citem = null;
        // }
        $c_list = array_merge($c_list, ["\n"], $this->m_items);
        foreach ($c_list as $k){
            if ($k instanceof IRtfRender){
                $k = $k->render();
            }
            $def->append($k);
        }
        return sprintf(RtfConstants::DOC_FMT, $def . '');
    }
    /**
     * save to file 
     * @param string $file 
     * @return void 
     */
    public function save(string $file)
    {
        return igk_io_w2file($file, $this->render());
    }

    public function setFooter(string $footer)
    {
        $this->_update();
        $l = $this->prepareFormat($footer);
        $this->m_items[] = sprintf("{\\footer %s\\par}", $l);
    }
    public function setHeader(string $header)
    {
        $this->_update();
        $l = $this->prepareFormat($header);
        $this->m_items[] = sprintf("{\\header %s\\par}", $l);
    }
    public function setFooterNote(string $note)
    {
        $this->_update();
        $l = $this->prepareFormat($note);
        $this->m_items[] = sprintf("{\\footernote\\pard\\plain %s\\par}", $l);
    }
    protected function _getCreatedOrNewExtends($name)
    {
        if (!($ex_list = igk_getv($this->m_extends, $name))) {
            $ex_list = $this->createExtends($name) ?? igk_die('failed to create an extendsion');
            $this->m_extends[$name] =  $ex_list;
        }
        return $ex_list;
    }
    /**
     * 
     * @param string $id 
     * @param string $listid 
     * @param string $puce 
     * @param mixed $type 
     * @param int $startAt 
     * @param int $size size of the puce marker - in case of place holder 
     * @param int $position 
     * @return void 
     */
    public function listOverride(
        string $id,
        $listid = '\\listid1',
        $puce = RtfConstants::PUCE_CIRCLE, 
        $type = RtfBulletNFCTypes::Puce,
        $startAt = 1,
        $size = 1,
        $position = 0 ,
        $justify = RtfLevelJustification::Left,      
    ) {
        $ex_list = $this->_getCreatedOrNewExtends('listtable');
        $template = $ex_list->count() + 1;
        $v_size = str_pad(dechex($size), 2, STR_PAD_LEFT, '0');
        $v_pos = $position>0?
                "\\'".str_pad(dechex($position), 2, STR_PAD_LEFT, '0') : '';
        // Logger::info('size '.$v_size);
        $ex_list->append(implode('', [
            '{\\list\\listtemplateid' . $template . '\\listhybrid',
            "{\\listlevel",
            $type,
            $justify ?? "\\leveljc0", 
            "\\levelfollow0", // tabr
            "\\levelstartat".$startAt, // start at 
            "\\levelindent0", // tab supplement 
            "{\\leveltext\\'".$v_size . $puce . ";}", // tabr
            "{\\levelnumbers".$v_pos.";}", // no numbers
            // "{\\levelmarker \\{lower-roman\\}}" , // level marker - css level marker - use on macos
            "\\fi-360\\li720", // no numbers
            "}",
            $listid,
            '}'
        ]));
        $ex_list = $this->_getCreatedOrNewExtends('listoverridetable');
        $ex_list->append(implode('', [
            "{\\listoverride",
            $listid,
            "\\listoverridecount0",
            $id,
            "}"
        ]));
    }
    /**
     * create an extends 
     * @param string $name 
     * @return object|null 
     */
    public function createExtends(string $name)
    {
        $cl = __NAMESPACE__ . "\\Extends\\" . ucfirst($name) . "Extend";
        if (class_exists($cl)) {
            return new $cl;
        }
        return null;
    }
    /**
     * append table to document
     * @param RtfTable $table 
     * @return void 
     */
    public function table(RtfTable $table){
        $this->_update();
        $this->m_items[] = $table;
    }
}
