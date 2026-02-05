<?php
// @author: C.A.D. BONDJE DOUE
// @file: RtfMarkdown.php
// @date: 20260205 10:22:24
namespace igk\Windows\Rtf;

use IGK\Helper\Activator;
use IGK\System\IO\Markdown\MarkdownConverter;
use igk\Windows\Rtf\Markdown\IRtfFontStyleDefinition;
use igk\Windows\Rtf\Markdown\RtfFromMarkdownListener;
use igk\Windows\Rtf\Markdown\RtfMarkdowFilterHost;

/**
 * 
 * @package igk\Windows\Rtf
 * @author C.A.D. BONDJE DOUE
 */
class RtfMarkdown
{
    public static function InitDocument(RtfDocument $doc): RtfDocument
    {
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
            "#BBB", // gray
            "#EEE", // broked-white
            "#00f",
            "#3344DD", // royal blue - for link 
            "#444", // royal blue - for link ,
            "#FF0"
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
            3 => "\\sb480\\fs48\\cf5",
            4 => "\\sb480\\fs40\\cf5",
            5 => "\\sb480\\fs32\\cf4",
            6 => "\\sb480\\fs24\\cf4",
        ]);
        $doc->lang = "\\lang1036";
        $doc->setProperties([
            'footer-font-size' => '\\fs16',
            'header-font-size' => '\\fs16'
        ]);
        return $doc;
    }
    public static function Convert(string $md, ?RtfDocument $doc = null)
    {
        $doc = $doc ?? self::InitDocument(new RtfDocument());
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
        // $listener->emptyOutputListener = function () use ($g): bool {
        //     return empty($g->getOutput());
        // };
        $g->setOutputTreatmentListener($listener);

        $o = $g->transform($md,null,null);

        $doc->appendItem($o);

        return $doc->render();
    }
}
