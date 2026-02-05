<?php
// @author: C.A.D. BONDJE DOUE
// @file: RtfListDefinitionRendering.php
// @date: 20260201 18:53:30
namespace igk\Windows\Rtf;


/**
* 
* @package igk\Windows\Rtf
* @author C.A.D. BONDJE DOUE
*/
class RtfListDefinitionRendering{
    private $m_id;
    private $m_listid;
    private $m_definition;
    private $m_templateid;
    public function getRoot(){
        return $this->m_definition->root;
    }
    public function __construct(string $templateid, string $id, string $listid, $r)
    {
        $this->m_templateid = $templateid;
        $this->m_listid= $listid;
        $this->m_id = $id;
        $this->m_definition = $r;
    }
    public function update($r){
        if (($st = $this->m_definition->placeholder) < ($sp = $r->placeholder)){
            if (strstr($sp, $st) && ($this->m_definition->level < $r->level) ){
                $this->m_definition->placeholder = $sp;
                $this->m_definition->list = $r->list;
                $this->m_definition->bulletDefinition = $r->bulletDefinition;
                $this->m_definition->type = $r->type;
                $this->m_definition->level = $r->level;
            }else{
                igk_die(RtfConstants::LOG . 'can\'t update placeholder');
            }
           // igk_wln_e(__FILE__.":".__LINE__ , $this->render());
        }
    }
    public function id(){
        return $this->m_id;
    }
     
    public function render()
    {
        return implode([
            '{\\list\\listtemplateid' . $this->m_templateid . '\\listhybrid',
                $this->getListLevel(),
                $this->m_listid,
            '}'
        ]);
    }
    public function getListLevel(){
        return RtfUtility::BuildListLevel($this->m_definition);
    }
    public function __toString()
    {
        return RtfDocument::RenderingContext() ? $this->render() : __CLASS__;
    }
}