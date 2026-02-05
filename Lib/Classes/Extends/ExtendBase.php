<?php
// @author: C.A.D. BONDJE DOUE
// @file: ExtendBase.php
// @date: 20260128 15:10:57
namespace igk\Windows\Rtf\Extends;

use igk\Windows\Rtf\RtfConstants;

/**
* 
* @package igk\Windows\Rtf\Extends
* @author C.A.D. BONDJE DOUE
*/
abstract class ExtendBase{
    var $list =[];
    protected $name;
    public function clear(){
        $this->list = [];
    }
    function append($s){
        $this->list[] = $s;
    }
    public function count(){
        return count($this->list);
    }
    public function __toString()
    {
        return sprintf("{\\*\\%s%s}", $this->name, implode("", $this->list))."\n";
    }
}