<?php
// @author: C.A.D. BONDJE DOUE
// @file: RtfBulletNFCTypes.php
// @date: 20260128 16:01:59
namespace igk\Windows\Rtf;


/**
* select bullet type . glue it to activate
* @package igk\Windows\Rtf
* @author C.A.D. BONDJE DOUE
*/
abstract class RtfBulletNFCTypes{
    const Decimal = '\\levelnfc0'; 
    const RomanUpper = '\\levelnfc1';
    const RomanLower = '\\levelnfc2';
    const LatinUpper = '\\levelfc3\\levelnfcn3';
    const NewLatinUpper = '\\levelnfc3\\levelnfcn3'; 
    const NewLatinLower = '\\levelnfc4\\levelnfcn4'; 
    const LatinLower = '\\levelnfc4';
    const Puce = '\\levelnfc23\\levelnfcn23';
}