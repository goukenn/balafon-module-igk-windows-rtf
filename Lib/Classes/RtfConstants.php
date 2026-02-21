<?php
// @author: C.A.D. BONDJE DOUE
// @file: RtfConstants.php
// @date: 20260127 09:17:27
namespace igk\Windows\Rtf;


/**
 * 
 * @package igk\Windows\Rtf
 * @author C.A.D. BONDJE DOUE
 */
abstract class RtfConstants
{
    const LOG = '[rtf] - ';
    const BulletSeparator = ':.-)=/';
    const LF = "\\\n";
    const SECTION = "\\section";
    const PAGE = "\\page";
    const PARAGRAPH = "\\par";
    const TAB = "\\tab";
    const LINE = "\\line";
    const RESET_PARARGRAPH = "\\pard";
    const UNICODE_REPLACE_COUNTER_FMT = "\\uc%s";
    const LINK_FMT = "{\\field{\\*\\fldinst{HYPERLINK \"%s\"}}{\\fldrslt %s}}";
    const LINK_TO_MARK_FMT = "{\\field{\\*\\fldinst{HYPERLINK \\\\l \"%s\"}}{\\fldrslt %s}}";
    const BOOK_MARK_FMT = "{\\*\\bkmkstart %s}%s{\\*\\bkmkend %s}";
    const KEEP_NEXT = '\\keepn';
    const KEEP = '\\keep';
    /**
     *  first line indent 
     * */
    const FI_FMT = "\\fi%s";
    /**
     * line indent 
     */
    const LI_FMT = "\\li%s";

    const DOC_FMT = '{\\rtf1%s}';
    const COLOR_FMT = '\\red%s\\green%s\\blue%s';
    const COLOR_TABLE_FMT = '{\\colortbl;%s}';
    const FONT_TABLE_FMT = '{\\fonttbl%s}';
    const BOLD_FMT = '\\b %s\\b0 ';
    const ITALIC_FMT = '\\i %s\\i0 ';
    const STRIKE_FMT = '\\strike %s\\strike0 ';
    const UNDERLINE_FMT = '\\ul %s\\ul0 ';
    const EXTEND_FMT = '{\\*\\%s}';

    const LISTTEXT = "\\listtext";

    const PUCE_CIRCLE = "\\uc0\\u8226";
    const PUCE_SQARE = "\\uc0\\u9642";
    const PUCE_CHECK = "\\uc0\\u10003";

    const ITEM_LVL_FMT = "\\ilvl%s";
    const LIST_FMT = "\\ls%s";
    const SET_COLOR_FMT = "\\cf%s";
    const SET_BGCOLOR_FMT = "\\chcbpat%s";
    const SET_PAR_BGCOLOR_FMT = "\\cbpat%s";
    const SET_CELL_BGCOLOR_FMT = "\\clcbpat%s";
    const SET_TAB_BGCOLOR_FMT = "\\shading%s";

    const LISTITEM_FMT = "%s{" . self::LISTTEXT . "\t%s\t}%s";

    const PAGE_SIZE_FMT = "\\paperw%s\\paperh%s";
    const MARGIN_FMT = "\\margl%s\\margr%s\\margb%s\\margt%s";
    const VIEW_FMT = "\\vieww%s\\viewh%s\\viewkind%s";
    const BRD_COLOR_FMT = "\\brdrcf%s";

    const TAB_STOPS = "\\tx220\\tx720\\tx1120\\tx1680\\tx2240\\tx2800\\tx3360\\tx3920\\tx4480\\tx5040\\tx5600\\tx6160\\tx6720";

    const TAB_PUCE = "\\tx220\\tx720";
    const FIELD_NUMPAGE = "{\\field{\\*\\fldinst NUMPAGES}}";
    const PAGENUMBER = "\\chpgn";

    // char - code command  
    const DBL_CH = "\\dbch";
    const HICH_CH = "\\hich";
    const LOCH_CH = "\\loch";


    const ParagrahMargin_FMT = "\\sb%s\\sa%s\\ri%s\\li%s";
    const MARGIN_BEFORE_FMT = "\\sb%s";
    const MARGIN_AFTER_FMT = "\\sa%s";


    const ALTERNATE_FONT_FTM = "\\af%s";

    const STYLE_LEVEL_FONT_FTM = "\\s%s";

    /**
     * 
     * @return array 
     */
    public static function GetCharSymbols()
    {
        return [
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
            'â€¢'=> "\\'95",
        ];
    }
}
