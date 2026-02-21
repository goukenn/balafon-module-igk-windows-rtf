<?php
// @author: C.A.D. BONDJE DOUE
// @file: RtfBookBuilder.php
// @date: 20260210 08:00:00
namespace igk\Windows\Rtf\Builder;

use IGK\Helper\Activator;
use IGK\Helper\IO;
use IGK\System\Console\Logger;
use IGK\System\IO\Markdown\MarkdownConverter;
use IGK\System\IO\Path;
use igk\Windows\Rtf\Markdown\IRtfFontStyleDefinition;
use igk\Windows\Rtf\Markdown\RtfFromMarkdownListener;
use igk\Windows\Rtf\Markdown\RtfMarkdowFilterHost;
use igk\Windows\Rtf\RtfConstants;
use igk\Windows\Rtf\RtfDocument;
use igk\Windows\Rtf\RtfEntryPage;
use igk\Windows\Rtf\RtfFonts;

/**
 * 
 * @package igk\Windows\Rtf\Builder
 * @author C.A.D. BONDJE DOUE
 */
class RtfBookBuilder
{
    var $bookTitle;
    var $metadata;

    private $m_engine;

    /**
     * create a document 
     * @return RtfDocument 
     */
    public function createBookDocument(): RtfDocument
    {
        $doc = new RtfDocument();

        $doc->fonts = [
            RtfFonts::Calibri,
            RtfFonts::Helvetica,
            RtfFonts::Arial,
            RtfFonts::CourierNew,
            RtfFonts::Consolas,
            RtfFonts::Menlo,
        ];
        $doc->colors = [
            "#222",
            "#040816",
            "#1F497D",
            "#1B7CBC",
            "#aaa", // gray
            "#EEE", // broked-white
            "#00f",
            "#3344DD", // royal blue - for link 
            "#444", // royal blue - for link ,
            "#FF0",
            "#555", // title subcolor
            "#999", // title subcolor 2 
        ];
        $doc->titleFontSizes = [
            1 => 18,
            2 => 16,
            3 => 14,
            4 => 12,
            5 => 10,
            6 => 8,
        ];
        $doc->titleFonts = [
            1 => 2,
            2 => 1
        ];
        $doc->setStyleSheet([
            1 => "\\outlinelevel0",
            2 => "\\outlinelevel1",
            3 => "\\outlinelevel2",
            4 => "\\outlinelevel4",
        ]);

        $doc->setTitleFontStyle([
            1 => "\\sb480\\fs64\\cf4\\sa480",
            2 => "\\sb480\\fs54\\cf4",
            3 => "\\sb480\\fs48\\cf11",
            4 => "\\sb480\\fs40\\cf11",
            5 => "\\sb480\\fs32\\cf4",
            6 => "\\sb480\\fs24\\cf4",
        ]);
        $doc->lang = "\\lang1036";
        $doc->setProperties([
            'footer-font-size' => '\\fs16',
            'header-font-size' => '\\fs16'
        ]);

        $g = new MarkdownConverter;
        $container = new RtfMarkdowFilterHost($doc);
        $listener = new RtfFromMarkdownListener($container);
        $listener->quoteFontIndex = 1;
        $listener->quoteColorIndex = 4;
        $listener->setStyle('inline-code', Activator::CreateNewInstance(
            IRtfFontStyleDefinition::class,
            ['fontFamily' => 4, 'foreColor' => 4, 'bgColor' => 6]
        ));
        $listener->setStyle('link', Activator::CreateNewInstance(
            IRtfFontStyleDefinition::class,
            ["u" => 1, 'foreColor' => 7]
        ));
        $listener->setStyle('fence-code', Activator::CreateNewInstance(
            IRtfFontStyleDefinition::class,
            ['fontFamily' => 5, 'paragrahBgColor' => 6, 'fontSize' => 8]
        ));
        $listener->emptyOutputListener = function () use ($g) {
            return empty($g->getOutput());
        };
        $g->setOutputTreatmentListener($listener);

        $gard = new RtfEntryPage();
        $summary = new RtfEntryPage();
        $summary->line("Table of Content");
        $doc->append($gard);
        $this->m_engine = $g;
        return $doc;
    }
    /**
     * build books
     * @param string $location_dir 
     * @param string $output_file 
     * @param string $bookTitle  
     * @param null|RtfDocument $doc 
     * @param null|RtfBookBuilder $builder 
     * @return bool 
     */
    public static function Build(
        string $location_dir,
        ?string $output_file,
        ?string $bookTitle = null,
        ?IRtfBookBuilderOptions $options = null,
        ?RtfDocument $doc = null,
        ?RtfBookBuilder $builder = null
    ): bool {
        // if (!is_dir($location_dir)) {
        //     return false;
        // }
        $builder = $builder ?? new static;
        $builder->bookTitle = $bookTitle ?? "Book";
        $doc = $doc ?? $builder->createBookDocument();
        if ($options) {
            if (($ctn = $builder->m_engine->getOutputTreatmentListener()) instanceof RtfFromMarkdownListener) {
                $ctn->withPage = $options->withPage;
                $ctn->baseURL = $options->baseURL;
            }
        }
        $location = igk_str_rm_last(igk_uri($location_dir), '/', 1);
        $is_file = is_file($location);
        $outfile = $output_file ?? Path::Combine($is_file ? dirname($location) : $location, 'book.rtf');
        $rm_file = null;
        if ($is_file){
            if (!preg_match('/^(chapter|chaptire)_/', basename($location))){
                $c = igk_io_tempfile('chapter_');              
                igk_io_w2file($c, file_get_contents($location));
                rename($c, $c = $c.'.md');
                $location = $c;
                $rm_file = $c;
            }
        }
        $infolist = self::LoadBookFiles($location);
        if ($infolist->metadata) {
            $builder->metadata = json_decode(file_get_contents($infolist->metadata[0]));
        }

        $builder->buildBook($infolist, $doc);

        if ($rm_file){
            unlink($rm_file);
        }
        Logger::success('output: '.$outfile);
        return igk_io_w2file($outfile, $doc->render());
    }
    /**
     * buildBook 
     * @param mixed $infolist 
     * @param mixed $doc 
     * @return void 
     */
    protected function buildBook($infolist, $doc)
    {
        $v_is_debug = true;
        $page = false;
        $props = implode(array_filter([$doc->lang, $doc->{'header-font-size'}, $doc->{'footer-font-size'}]));
        $book_title = $this->bookTitle;
        $g = $this->m_engine;
        $con = $g->getOutputTreatmentListener();
        $page_num = $con->withPage ? RtfConstants::PAGENUMBER : '';
        $files = $infolist->chapters;
        // $files = array_slice($files, 0, 2);
        Logger::info('[rtfbookbuilder] > treat chapter');
        foreach ($files as $k => $f) {
            Logger::info('file: ' . $f);
            $src = file_get_contents($f);
            $src = str_replace('â"' . "\n", 'â"' . "\n", $src);
            if ($page) {
                $con->resetBulletDefinition();
            }
            $g->transform($src, null, null);
            $o = $g->getOutput();
            if ($page) {
                // update sections 
                $doc->section();
            }
            $p = $this->getTitlePresentation($k);
            $title = sprintf('%s - %s', $book_title, $p);
            $doc->setHeader(sprintf('\\pard\\tx9360%s{\\ql %s}\\tab{\\qr%s}', $props, $title, $page_num));
            $doc->setFooter(sprintf('\\pard\\qc%s %s', $props, $page_num));
            $doc->bookmark($k, '');
            $doc->appendItem($o);
            $page = true;
        }

        $dt = [[
            'title' => 'Annexes %s',
            'data' => $infolist->annexes,
            'count' => 0
        ]];
        Logger::info('[rtfbookbuilder] > treat extra');
        while (count($dt) > 0) {
            $rt = array_shift($dt);
            $t = $rt['data'];
            $title = $rt['title'];
            $count = &$rt['count'];
            // $t = array_slice($t,2);
            foreach ($t as $k => $f) {
                Logger::info('file: ' . $f);
                if ($page) {
                    $con->resetBulletDefinition();
                    $doc->section();
                }
                $src = file_get_contents($f);
                // $src = 'info du igk\\p\\m'; 
                $g->transform($src, null, null);
                $o = $g->getOutput();
                $v_title = sprintf($title, chr(ord('A') + $count));
                $doc->setHeader(sprintf('\\pard\\tx9360%s{\\ql %s}\\tab{\\qr%s}', $props, $v_title, $page_num));

                $doc->bookmark($k, '');
                $doc->appendItem($o);
                $page = true;
                $count++;
            }
        }
    }
    /**
     * retrieve title presentation
     * @param string $key 
     * @return string 
     */
    public function getTitlePresentation(string $key): string
    {
        $v_dtitle = $this->_defaultTitleFromName($key);
        if (($m = $this->metadata) && ($titles = igk_getv($m, 'titles'))) {
            return igk_getv($titles, $key, $v_dtitle);
        }
        return $v_dtitle;
    }
    /**
     * get default chapter name 
     * @param string $l 
     * @return string|string[]|null 
     */
    protected function _defaultTitleFromName(string $l)
    {
        $l = preg_replace('/(?:(chapitre|chapter)_\\d+_|annexe_\\[a-z]+_)/i', '', $l);
        $l = igk_io_basenamewithoutext($l);
        $l = preg_replace('/_+/', ' ', $l);
        return $l;
    }
    /**
     * 
     * @param string $location 
     * @return object 
     */
    public static function LoadBookFiles(string $location)
    {
        $robj = (object)['preface' => [], 'chapters' => [], 'annexes' => [], 'blorb' => [], 'summary' => [], 'index' => [], 'metadata' => []];
        $ln = strlen($location);
        $fc_treat =  function ($a) use (&$files, & $ln, $robj) {
            $k = strtolower(substr($a, $ln + 1));
            if (preg_match("/\/(chapter|chapitre)_.+\.md$/i", $a)) {
                $files = &$robj->chapters;
                $files[$k] = $a;
                return true;
            }
            if (preg_match("/\/(annexe)_.+\.md$/i", $a)) {
                $files = &$robj->annexes;
                $files[$k] = $a;
                return true;
            }
            if (preg_match("/\/(index\.md)$/i", $a)) {
                $files = &$robj->index;
                $files[$k] = $a;
                return true;
            }
            if (preg_match("/\/(readme\.md)$/i", $a)) {
                $files = &$robj->readme;
                $files[$k] = $a;
                return true;
            }
            if (preg_match("/\/(metadata.json)$/i", $a)) {
                $files = &$robj->metadata;
                $files[] = $a;
                return true;
            }
            return false;
        };
        if (is_file($location)) {
            $ln = strlen(dirname($location));
            $fc_treat($location);
        } else {
            IO::GetFiles(
                $location,
                $fc_treat,
                false
            );
        }
        $fc_order =  function ($a, $b) {
            return strnatcmp($a, $b);
        };
        uksort($robj->chapters, $fc_order);
        uksort($robj->annexes, $fc_order);
        uksort($robj->preface, $fc_order);

        return $robj;
    }
}
