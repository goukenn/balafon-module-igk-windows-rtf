<?php
// @author: C.A.D. BONDJE DOUE
// @file: RtfLevelList.php
// @date: 20260219 12:56:20
namespace igk\Windows\Rtf;


/**
* 
* @package igk\Windows\Rtf
* @author C.A.D. BONDJE DOUE
*/
class RtfLevelList{
    private $m_childs;
    private $m_parent;
    private $m_level;
    private $m_root;
    /**
     * check if started
     * @return bool 
     */
    public function start():bool{
        return is_null($this->m_level);
    }
    public function getRoot(){
        return $this->m_root;
    }
    public function setRoot(?string $root){
        $this->m_root = $root;
    }
    /**
     * 
     * @return mixed 
     */
    public function getLevel(){
        return $this->m_level;
    }
    /**
     * set level current item 
     * @param int $value 
     * @return void 
     */
    public function setLevel(int $value){
        $this->m_level = $value;
    }
    /**
     * 
     * @return mixed 
     */
    public function getParent(){
        return $this->m_parent;
    }
    public function getIsRoot(){
        return is_null($this->m_parent);
    }
    public function getChilds(){
        return $this->m_childs;
    }
    /**
     * 
     * @param RtfLevelList $level 
     * @return $this 
     */
    public function append(RtfLevelList $level){
        ($level===$this) && igk_die('not allowed');
        $level->m_parent = $this;
        if (is_null($level->m_level)){
            $level->m_level += 1;
        }
        $this->m_childs[] = $level;
        return $this;
    }
    /**
     * get parent from root 
     * @param string $root 
     * @return mixed|null 
     */
    public function getParentFromRoot(string $root){
        while($p = $this->getParent()){
            if ($p->getRoot() == $root){
                return $p;
            }
        }
        return null;
    }
}