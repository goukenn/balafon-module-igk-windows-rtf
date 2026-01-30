<?php
// @author: C.A.D. BONDJE DOUE
// @file: RtfEntryPage.php
// @date: 20260129 13:43:30
namespace igk\Windows\Rtf;

use IGK\System\IO\StringBuilder;

/**
* 
* @package igk\Windows\Rtf
* @author C.A.D. BONDJE DOUE
*/
class RtfEntryPage extends RtfEntryDocument implements IRtfRender{
    var $parent;
    var $newPage = true;
    var $appendPage = false;
    /**
     * 
     * @return string 
     */
    public function render(): string
    {
        $this->_update();
        $def = new StringBuilder;
        $c_list = $this->m_items;
        if ($c_list && $this->newPage){
            array_unshift($c_list, "\\page\n");
            if ($this->appendPage)
                $c_list[] = "\\page\n";
        }
         foreach ($c_list as $k) {
            if ($k instanceof IRtfRender) {
                $k = $k->render();
            }
            $def->append($k);
        }
        return $def.'';
    }
    public function ln(){

    }

}