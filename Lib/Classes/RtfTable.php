<?php
// @author: C.A.D. BONDJE DOUE
// @file: RtfTable.php
// @date: 20260128 18:56:44
namespace igk\Windows\Rtf;

use IGK\System\IO\StringBuilder;

/**
* build a rtf table definition 
* @package igk\Windows\Rtf
* @author C.A.D. BONDJE DOUE
*/
class RtfTable implements IRtfRender{
    /**
     * current row
     * @var mixed
     */
    private $m_crow;

    var $rows = [];
    var $def = [];

    public function render():string{
        $sb = new StringBuilder;
        $lrow = $this->rows;
        $count = 0;
        while(count($lrow)>0){
            $r = array_shift($lrow);
            $is_last = count($lrow)==0;
            $c = '';
            if (isset($this->def[$count])){
                $c .= $this->def[$count]."\n";
            }
            $c .= '\\pard\\intbl ';
            foreach($r as $ct){
                $c .= $ct.'\\cell ';
            }
            if ($is_last){
                $c.= "\\lastrow";
            }
            $sb->appendLine($c."\\row");
            $count++;
        }

        return $sb. '';
    }
    /**
     * 
     * @param mixed $index 
     * @param mixed $top 
     * @param mixed $right 
     * @param mixed $bottom 
     * @param mixed $left 
     * @return void 
     */
    public function setBorderDefinition(int $index, $top, $right, $bottom, $left){
        $ds = RtfBorderTypes::BDR_SINGLE;
        $this->def[$index] = sprintf('%s', implode("\n", [
            sprintf("\clbrdrt\brdrw%s%s", $top->size ?? 15, $top->style ?? $ds),
            sprintf("\clbrdrl\brdrw%s%s", $left->size ?? 15, $left->style ?? $ds),
            sprintf("\clbrdrb\brdrw%s%s", $bottom->size ?? 15, $bottom->style ?? $ds),
            sprintf("\clbrdrr\brdrw%sùs", $right->size ?? 15, $right->style ?? $ds),
        ]));
    }
}