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
    /**
     * 
     * @param string $key 
     * @return bool 
     */
    public function support(string $key): bool{
        return isset($this->m_list[$key]);
    }
    /**
     * 
     * @param string $key 
     * @return mixed 
     */
    public function info(string $key){
        $c = igk_getv($this->m_list, $key);
        if ($c && is_array($c)){
            return $c[count($c)-1];
        }
        return $c;
    } 
    /**
     * 
     * @param mixed $d 
     * @return void 
     */
    public function append($d){
        if ($d instanceof RtfListDefinitionRendering){
            $r = $d->getRoot();
            if (isset($this->m_list[$r])){
                $g = $this->m_list[$r];
                $td = null;
                if (!is_array($g)){
                    $td = [$g , $d];
                }else{
                    $g[] = $d;
                    $td = $g;
                }
                $this->m_list[$r] = $td;
            }else{
                $this->m_list[$r] = $d;
            }
        }
        parent::append($d);        
    }
    /**
     * 
     * @param mixed $r 
     * @return void 
     */
    public function popRoot($r){
        if (isset($this->m_list[$r])){
            $c = $this->m_list[$r];
            $unset = true;
            if (is_array($c)){
                array_pop($c);
                $unset = false;
            }
            if ($unset || empty($c)){
                unset($this->m_list[$r]);
            }else{
                 $this->m_list[$r] = $c;
            }
        }
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
    /**
     * reset root definition 
     * @return void 
     */
    public function resetDefinition(){
        $this->m_list= [];
    }
}