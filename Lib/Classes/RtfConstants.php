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
abstract class RtfConstants{
    const LF = "\\\n";
    const SECTION = "\\section";
    const PAGE = "\\page";
    const PARAGRAPH = "\\par";
    const TAB = "\\tab";
    const LINE = "\\line";
    const RESET_PARARGRAPH = "\\pard";
    const UNICODE_REPLACE_COUNTER_FMT = "\\uc%s";
    const LINK_FMT = "{\\field{\\*\\fldinst{HYPERLINK \"%s\"}}{\\fldrslt %s}}";
    const LINK_TO_MARK_FMT = "{\\field{\\*\\fldinst{HYPERLINK \\\\l %s}}{\\fldrslt %s}}";
    const BOOK_MARK_FMT = "{\\*\\bkmkstart %s}%s{\\*\\bkmkend %s}";
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
    const BOLD_FMT ='\\b %s\\b0';
    const ITALIC_FMT ='\\i %s\\i0';
    const STRIKE_FMT ='\\strike %s\\strike0';
    const UNDERLINE_FMT ='\\ul %s\\ul0';
    const EXTEND_FMT = '{\\*\\%s}';

    const LISTTEXT = "\\listtext";

    const PUCE_CIRCLE = "\\uc0\\u8226";
    const PUCE_SQARE = "\\uc0\\u9642";
    const PUCE_CHECK = "\\uc0\\u10003";

    const ITEM_LVL_FMT ="\\ilvl%s";
    const LIST_FMT ="\\ls%s";

    const LISTITEM_FMT = "%s{".self::LISTTEXT."\t%s\t}%s";

    const PAGE_SIZE_FMT = "\\paperw%s\\paperh%s";
    const MARGIN_FMT = "\\margl%s\\margr%s\\margb%s\\margt%s";
    const VIEW_FMT = "\\vieww%s\\viewh%s\\viewkind%s";
    const BRD_COLOR_FMT= "\\brdrcf%s";

    const TAB_STOPS="\\tx220\\tx720\\tx1120\\tx1680\\tx2240\\tx2800\\tx3360\\tx3920\\tx4480\\tx5040\\tx5600\\tx6160\\tx6720";

    const TAB_PUCE = "\\tx220\\tx720";
}