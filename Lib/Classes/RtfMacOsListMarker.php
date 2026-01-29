<?php
// @author: C.A.D. BONDJE DOUE
// @file: RtfMacOsListMarker.php
// @date: 20260128 16:21:39
namespace igk\Windows\Rtf;


/**
* 
* @package igk\Windows\Rtf
* @author C.A.D. BONDJE DOUE
*/
abstract class RtfMacOsListMarker{
    const Disc = 'disc';
    const Circle = 'circle';
    const Square = 'square';
    const Hyphen = 'hyphen';
    const Dash = 'dash';
    const Decimal = "decimal";
    const DecimalZero = "decimal-zero";
    const RomanUpper = "upper-roman";
    const RomanLower = "lower-roman";
    const LatinUpper = "upper-latin";
    const LatinLower = "lower-latin";
    const AlphaLower = "lower-alpha";
    const AlphaUpper = "upper-alpha";
}