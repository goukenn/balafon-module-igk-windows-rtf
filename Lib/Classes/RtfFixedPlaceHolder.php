<?php
// @author: C.A.D. BONDJE DOUE
// @file: RtfFixedPlaceHolder.php
// @date: 20260201 10:32:05
namespace igk\Windows\Rtf;


/**
* 
* @package igk\Windows\Rtf
* @author C.A.D. BONDJE DOUE
*/
class RtfFixedPlaceHolder{
    var $value;
    public function __construct(string $value)
    {
        $this->value = $value;
    }
    public function __toString()
    {
        return $this->value;
    }
}