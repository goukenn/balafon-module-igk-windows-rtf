<?php
// @author: C.A.D. BONDJE DOUE
// @file: RtfFontFamilyCommand.php
// @date: 20260129 18:38:22
namespace igk\Windows\Rtf;


/**
* 
* @package igk\Windows\Rtf
* @author C.A.D. BONDJE DOUE
*/
abstract class RtfFontFamilyCommand{
    const Nil = '\\fnil';
    const Roman = '\\froman';
    const Swiss= '\\fswiss';
    const Modern = '\\fmodern';
    const Script = '\\fscript';
    const Decor = '\\fdecor';
    const Tech = '\\ftech';
}