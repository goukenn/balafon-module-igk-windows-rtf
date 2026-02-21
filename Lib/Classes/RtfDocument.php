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
class RtfDocument extends RtfEntryDocument
{
    private static $sm_RENDRING_CONTEXT;
    private $m_titleFontStyleStyle;
    private $m_properties = [];
    private $m_listtable;
    var $lang;

    public function setProperties($props){
        $this->m_properties = $props;
    }
    public function __get($name)
    {
        if ($this->m_properties){
            return igk_getv($this->m_properties, $name);
        }
    }


    public static function RenderingContext(){
        return self::$sm_RENDRING_CONTEXT>0;
    }
    /**
     * 
     * @param null|array<int level, styling> $fontDefinition 
     * @return void 
     */
    public function setTitleFontStyle($fontDefinition){
        $this->m_titleFontStyleStyle = $fontDefinition;
    }
    /**
     * set style sheet 
     * @param null|array $definition 
     * @return void 
     */
    public function setStyleSheet(?array $definition){
        $heading_names = [
            'Normal',
        ];
        foreach (range(1,9) as $k=>$value) {
            $heading_names[] = "Heading ".$value;
        }
        foreach($definition as $k=>$v){
            $n = igk_getv($heading_names, $k );
            $this->stylesheet[$k] = sprintf("{\\s%s %s %s;}", $k, $v, $n);
        }
    }
    /**
     * font indexed
     * @var array
     */
    var $titleFonts = [];
    /**
     * font sizes
     * @var array
     */
    var $titleFontSizes = [];
    var $colors = ['#000'];
    var $fonts = ['\\froman Times New Roman'];
    /**
     * style sheet definition 
     * @var ?array 
     */
    var $stylesheet;
    /**
     * object of generatorl info 
     * @var mixed
     */
    var $info;

    private $m_listTableRefCount;
    private $m_extends = [];
    private $m_header_state = false;


    /**
     * reference table count 
     * @return null|int 
     */
    public function listTableRefCount(): ?int
    {
        return $this->m_listTableRefCount;
    }

    /**
     * get par default tab in  
     * @param int $iMm 
     * @return void 
     */
    public function setDefaultTab(int $iMm)
    {
        $this->m_states['default-tab'] = "\\pardeftab" . RtfUtility::MmToWtips($iMm);
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
        if (!$this->m_header_state && $this->m_states) {
            $this->m_header_state = true;
        }
        parent::storeState();
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
        $this->m_items[] = RtfUtility::List($text, $bullet, $tabPuce, $id, $level);
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
         
        self::$sm_RENDRING_CONTEXT++;
        $def = new StringBuilder;
        $c_list = ['\\ansi\\deff0'];
        if ($this->lang){
            $c_list[] = $this->lang;
        }
        if ($this->info) {
            $c_list[] = "\n{\\*\\generator " . json_encode($this->info, JSON_UNESCAPED_SLASHES) . "}";
        }
        $c_list[] = "\n" . RtfUtility::FontTables($this->fonts);
        $c_list[] = "\n" . RtfUtility::ColorTableEntryFromWebColor($this->colors);
        $this->_update();      
        $c_list[] = "\n";
        $ln="\n";
        if ($this->m_extends) {
            $c_list = array_merge($c_list, $this->m_extends);
            $ln='';
        }
        if ($this->stylesheet) {
            $c_list[] = $ln . sprintf(implode("\n",['{\\stylesheet','%s','}']), implode("\n", $this->stylesheet))."\n";
        }
        $c_list = array_merge($c_list, $this->m_items);
        foreach ($c_list as $k) {
            if ($k instanceof IRtfRender) {
                $k = $k->render();
            }
            $def->append($k);
        }
        self::$sm_RENDRING_CONTEXT--;
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
        $this->m_items[] = RtfUtility::Header($l);
    }
    /**
     * 
     * @param string $note 
     * @return void 
     */
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
     * get new table new ids
     * @return int 
     */
    public function getListTableNewIds(): int{
        $ex_list = $this->m_listtable ?? $this->m_listtable = $this->_getCreatedOrNewExtends('listtable');   
        $template = $ex_list->updateRefCount()->getRefCount();
        return $template;
    }
    /**
     * 
     * @param mixed $r 
     * @return object 
     */
    public function initMenuList($r, bool $new = false){        
        $ex_list = $this->m_listtable ?? $this->m_listtable = $this->_getCreatedOrNewExtends('listtable');   
        $id = "\\ls";
        $v_def = null; 
        if (!$ex_list->support($r->root) || $new){
            $template = $ex_list->updateRefCount()->getRefCount();
            $id.= $template;
            $listid = "\\listid".$template;
            $v_def = new RtfListDefinitionRendering($template, $id, $listid , $r);
            $ex_list->append($v_def);
            // register 
            $ex_list = $this->_getCreatedOrNewExtends('listoverridetable');
            $ex_list->append(implode('', [
                "{\\listoverride",
                $listid,
                "\\listoverridecount0",
                $id,
                "}"
            ]));            
        } else {
            $sinfo = $ex_list->info($r->root);
            $sinfo->update($r);
            $id = $sinfo->id();
            $v_def=$sinfo;
        }
        
        return (object)[
            'id'=>$id,
            'def' => $v_def,
        ];
    }

    public function popupBulletList(string $root){
        $v_tab = $this->getListTable();
        $v_tab->popRoot($root);
    }
    public function getListTable(){
        return $this->m_listtable;
    }
    /**
     */
    public function getTitleStyleId(int $level): ?string{
        
        if (isset($this->stylesheet[$level]))
            return "\\s".($level).$this->getTitleFontStyle($level);
        return null;
    }
    /**
     * 
     * @param int $level 
     * @return mixed 
     */
    public function getTitleFontStyle(int $level){
        return igk_getv($this->m_titleFontStyleStyle,$level);
    }
    /**
     * create a list override for this document 
     * @param string $id 
     * @param string $listid 
     * @param string $puce 
     * @param mixed $type 
     * @param int $startAt 
     * @param int $size size of the puce marker - in case of place holder 
     * @param int $position 
     * @param ?string|'upper-alpha' $levelmarker 
     * @return void 
     */
    public function listOverride(
        string $id,
        $listid = '\\listid1',
        $puce = RtfConstants::PUCE_CIRCLE,
        $type = RtfBulletNFCTypes::Puce,
        $startAt = 1,
        $size = 1,
        $position = 0,
        $justify = RtfLevelJustification::Left,
        ?string $levelmarker = null,
        ?int $levelid = null
    ) {
        $ex_list = $this->_getCreatedOrNewExtends('listtable');
        // $template = $ex_list->updateRefCount()->getRefCount();
        $template = $ex_list->getRefCount();
        $v_size = $size > 0 ? "\\'" . RtfUtility::ToHex($size) :  ' ';
        $this->m_listTableRefCount = $template;
        $v_pos = $position > 0 ?
            "\\'" . RtfUtility::ToHex($position) : '';

        $ex_list->append(implode('', array_filter([
            '{\\list\\listtemplateid' . $template . '\\listhybrid',
            "{\\listlevel",
            $type,
            $justify ?? "\\leveljc0",
            "\\levelfollow0", // tabr
            "\\levelstartat" . $startAt, // start at 
            "\\levelindent0", // tab supplement 
            "{\\leveltext" . $v_size . $puce . ";",
            $levelid ? "\\leveltemplateid" . $levelid : '',
            "}", // tabr
            "{\\levelnumbers" . $v_pos . ";}", // no numbers
            $levelmarker ? "{\\*\\levelmarker \\{" . $levelmarker . "\\}}" : null, // level marker - css level marker - use on macos
            "\\fi-360\\li720", // no numbers
            "}",
            $listid,
            '}'
        ])));
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
    public function table(RtfTable $table)
    {
        $this->_update();
        $this->m_items[] = $table;
    }
    public function append(IRtfRender $table)
    {
        if (($table instanceof self) || ($table === $this)) {
            igk_die('not allowed');
        }
        $this->_update();
        $this->m_items[] = $table;
    }
    /**
     * 
     * @param string $url 
     * @param string $text 
     * @return void 
     */
    public function linkto(string $url, string $text)
    {
        $this->_update();
        $l = $this->prepareFormat($text);
        $this->m_items[] = sprintf(RtfConstants::LINK_FMT, $url, $l);
    }
    /**
     * 
     * @param string $id 
     * @param string $text 
     * @return void 
     */
    public function linktoBookmark(string $id, string $text)
    {
        $this->_update();
        $l = $this->prepareFormat($text);
        $this->m_items[] = sprintf(RtfConstants::LINK_TO_MARK_FMT, $id, $l);
    }
    /**
     * 
     * @param string $id 
     * @param string $text 
     * @return void 
     */
    public function bookmark(string $id, string $text)
    {
        $this->_update();
        $l = $this->prepareFormat($text);
        $this->m_items[] = RtfUtility::BookMark($id, $l) . "\n";
    }
    public function image(string $path, int $withMm = 70, int $hightMm = 40)
    {
        if (!file_exists($path)) {
            return;
        }
        $this->_update();
        list($width, $height) = getimagesize($path, $info);
        (!$width || !$height) && igk_die('failed to get image size');
        $fileinfo  = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($fileinfo, $path);
        $type = igk_getv([
            'image/png' => '\pngblip',
            'image/jpeg' => '\jpegblip',
            'image/jpg' => '\jpegblip',
            'image/wmp' => '\wmetafile',
        ], strtolower($mime_type), '\\dibitmap');

        $this->m_items[] = "\n" . sprintf(
            implode("", [
                '{\\pict',
                '%s',
                "\\picw%s",
                "\\pich%s",
                "\\picwgoal%s",
                "\\pichgoal%s",
                "\n" . RtfUtility::ImageToRTFHex($path),
                '}'
            ]),
            $type,
            $width,
            $height,
            RtfUtility::MmToWtips($withMm),
            RtfUtility::MmToWtips($hightMm),
        ) . "\n\\par\n";
    }

    public function setAlign($t)
    {
        $m = igk_getv([
            0 => 'l',
            1 => 'c',
            2 => 'r',
            3 => 'j',
            'r' => 'r',
            'right' => 'right',
            'c' => 'c',
            'center' => 'c',
            'l' => 'l',
            'left' => 'l',
            'j' => 'j',
            'justify' => 'j'
        ], $t, 'l');
        $this->m_states['align'] = "\\q" . $m;
    }
    /**
     * append string item 
     * @param string $item 
     * @return void 
     */
    public function appendItem(string $item)
    {
        $this->_update();
        $this->m_items[] = $item;
    }
    /**
     * create a new section 
     * @return void 
     */
    public function section()
    {
        $this->appendItem(sprintf("\\sect\\sectd%s \n", implode([$this->lang])));
    }
    /**
     * 
     * @param string $value 
     * @param string $style 
     * @return void 
     */
    public function markStyle(string $value, $style = "\\s1")
    {
        $this->_update();
        $this->m_items[] = RtfUtility::MarkStyle($value, $style);
    }
}
