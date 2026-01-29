<?php
// @author: C.A.D. BONDJE DOUE
// @file: RtfBorderTypes.php
// @date: 20260128 20:10:45
namespace igk\Windows\Rtf;


/**
* 
* @package igk\Windows\Rtf
* @author C.A.D. BONDJE DOUE
*/
abstract class RtfBorderTypes{ 
    const BDR_SINGLE = '\\brdrs';
    const BDR_DOUBLE = '\\brdrdb';
    const BDR_DOT = '\\brdrdot';
    const BDR_DASH = '\\brdrdash';
    const BDR_DASHDOT = '\\brdrdashdot';
    const BDR_DASHDOTDOT = '\\brdrdashdotdot';
    const BDR_HAIR = '\\brdrhair';
    const BDR_THIN = '\\brdrth';
    const BDR_THIN_SMALL_GAP = '\\brdrthtnsg';
    const BDR_THIN_MEDIUM_GAP = '\\brdrtnthmg';
    const BDR_THIN_THIN_SMALL_GAP = '\\brdrtnthtnsg';
    const BDR_EMBOSS = '\\brdremboss';
    const BDR_ENGRAVE = '\\brdrengrave';
    const BDR_NONE = '\\brdrnone';
}