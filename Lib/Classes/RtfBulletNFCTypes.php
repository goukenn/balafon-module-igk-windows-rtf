<?php
// @author: C.A.D. BONDJE DOUE
// @file: RtfBulletNFCTypes.php
// @date: 20260128 16:01:59
namespace igk\Windows\Rtf;


/**
* select bullet type 
* @package igk\Windows\Rtf
* @author C.A.D. BONDJE DOUE
*/
abstract class RtfBulletNFCTypes{
    const Decimal = '\\levelnfc0'; 
    const RomanUpper = '\\levelnfc1';
    const RomanLower = '\\levelnfc2';
    const LatinUpper = '\\levelnfc3';
    const LatinLower = '\\levelnfc4';
    const Puce = '\\levelnfc23';
}