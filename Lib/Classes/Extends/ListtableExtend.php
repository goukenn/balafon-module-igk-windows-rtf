<?php
// @author: C.A.D. BONDJE DOUE
// @file: ListtableExtend.php
// @date: 20260128 15:10:05
namespace igk\Windows\Rtf\Extends;

use igk\Windows\Rtf\RtfListDefinitionRendering;

/**
* 
* @package igk\Windows\Rtf\Extends
* @author C.A.D. BONDJE DOUE
*/
class ListtableExtend extends ExtendBase{
    protected $name="listtable";
    private $_count;
    private $m_list;

    public function getRefCount(){
        return $this->_count;
    }
    public function support(string $key): bool{
        return isset($this->m_list[$key]);
    }
    public function info(string $key){
        return igk_getv($this->m_list, $key);
    }

    public function append($d){
        if ($d instanceof RtfListDefinitionRendering){
            $this->m_list[$d->getRoot()] = $d;
        }
        parent::append($d);        
    }
    
    /**
     * 
     * @return $this 
     */
    public function updateRefCount(){
        $this->_count++;
        return $this;
    }
    public function clear(){
        $this->_count = 0;
        parent::clear();
    }
}