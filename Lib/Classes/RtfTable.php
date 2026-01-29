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
class RtfTable implements IRtfRender
{
    /**
     * current row
     * @var mixed
     */
    private $m_crow;

    var $rows = [];
    var $def = [];

    public function render(): string
    {
        $sb = new StringBuilder;
        $lrow = $this->rows;
        $count = 0;
        while (count($lrow) > 0) {
            $r = array_shift($lrow);
            $is_last = count($lrow) == 0;
            $c = '';
            if (isset($this->def[$count])) {
                $c .= $this->def[$count] . "\n";
            }
            $c .= '\\pard\\intbl ';
            foreach ($r as $ct) {
                $c .= $ct . '\\cell ';
            }
            if ($is_last) {
                $c .= "\\lastrow";
            }
            $sb->appendLine($c . "\\row");
            $count++;
        }

        return $sb . '';
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
    public function getCellDefinition(int $cellSizeInMm, $top, $right, $bottom, $left, $color=null)
    {
        $ds = RtfBorderTypes::BDR_SINGLE;

        return sprintf('%s', implode("\n", [
            sprintf("\\clbrdrt\\brdrw%s%s%s", $top->size ?? 15, $top->style ?? $ds, RtfUtility::GetBorderColor($top->color, $color)),
            sprintf("\\clbrdrr\\brdrw%s%s%s", $right->size ?? 15, $right->style ?? $ds,RtfUtility::GetBorderColor($top->color, $color)),
            sprintf("\\clbrdrb\\brdrw%s%s%s", $bottom->size ?? 15, $bottom->style ?? $ds,RtfUtility::GetBorderColor($top->color, $color)),
            sprintf("\\clbrdrl\\brdrw%s%s%s", $left->size ?? 15, $left->style ?? $ds,RtfUtility::GetBorderColor($top->color, $color)),
            sprintf("\\cellx" . RtfUtility::MmToWtips($cellSizeInMm))
        ]));
    }
    public function setCell(int $index, $cells)
    {
        $t_s = [
            'solid'=>RtfBorderTypes::BDR_SINGLE,
            'single'=>RtfBorderTypes::BDR_SINGLE,
            'dash'=>RtfBorderTypes::BDR_DASH,
            'dot'=>RtfBorderTypes::BDR_DOT,
            'double'=>RtfBorderTypes::BDR_DOUBLE,
            'dashdot'=>RtfBorderTypes::BDR_DASHDOT,
            'dashdotdot'=>RtfBorderTypes::BDR_DASHDOTDOT

        ];
        $fc_transform = function ($a) use ($t_s){
            return (object)['size'=>$a[1], 
                'style'=>igk_getv($t_s, strtolower($a[0]), $a[0]),
                'color'=>igk_getv($a,2)];
        };
        $tout = [];
        foreach ($cells as $i) {
            list($v_numsize, $v_size) = igk_extract($i,'0|size');
            list($v_def, $def) = igk_extract($i,'1|def');
            $v_size = $v_numsize ?? $v_size; igk_getv($i, 0);
            $v_def = $v_def?? $def ?? igk_die('missing border definition');
            $v_def = array_map($fc_transform, $v_def);  

            switch ($tc = count($v_def)) {
                case 1:
                    // all border size 
                    $v_def = array_fill_keys(range(0, 3), $v_def[0]);
                    break;
                case 2: 
                    $s = array_fill_keys(['.0','.2'], $v_def[0]);
                    $t = array_fill_keys(['.1','.3'], $v_def[1]); 
                    $v_def =  array_merge($s, $t);
                    ksort($v_def);
                    $v_def = array_values($v_def);
                    break;
                default:
                    ($tc!=4) && igk_die('missing border definition');
                    $v_def = array_values($v_def);
                    break;
            }
            $r = $this->getCellDefinition($v_size, $v_def[0],$v_def[1],$v_def[2],$v_def[3]);  
            $tout[] = $r;
        }
        $this->def[$index] = implode("\n", $tout);
        return $tout;
    }
}
