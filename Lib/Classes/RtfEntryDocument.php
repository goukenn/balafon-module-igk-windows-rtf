<?php
// @author: C.A.D. BONDJE DOUE
// @file: RtfEntryDocument.php
// @date: 20260129 13:43:18
namespace igk\Windows\Rtf;


/**
 * 
 * @package igk\Windows\Rtf
 * @author C.A.D. BONDJE DOUE
 */
abstract class RtfEntryDocument
{

    protected $m_states;
    protected $m_items = [];
    protected $m_citem;
    private $rtlist = null;
    public function prepareFormat(string $line)
    {

        $line = str_replace([
            "\r\n",
            "\n\r",
        ], ["\n", "\n"], $line);
        if (is_null($this->rtlist)) {
            $tr = array_merge(RtfConstants::GetCharSymbols(), [                 
                // '€' => "\\'80",
                '€' => "\\u8364?",
                '«' => "\\'ab",
                '»' => "\\'bb",
                '—' => "\\'97",
                '–' => "\\'96",
                '£' => "\\'a3",
                // no detected symbols
                // 'Ã®' => "\\'ee"
                '│'=>'\\u9474 ;',
                '┌'=>'\\u9484 ;',
                '├'=>'\\u9500 ;',
                '└'=>'\\u9492 ;',
                '─'=>'\\u9472 ;',
                // '┐'=>'\\u9491 ;',
                '┐'=>'\\u9488 ;',
                '┤'=>'\\u9508 ;',
                '┘'=>'\\u9496 ;', 
                //'┘'=>'\\u9496 ;',
                '┬'=>'\\u9516 ;',
                '┴'=>'\\u9524 ;',
                '┼'=>'\\u9532 ;',
                '✅'=>"\\uc0\\u9989",
                '❌'=>"\\uc0\\u10060",
                '⚠️'=>'\\uc0\\u9888 \\u65039',
                '😄'=>'\\uc0\\u55357 \\u56836',
                '👉'=>"\\uc0\\u55357 \\u56393",
            ]);
            $lt = [
                'Ã©' => 'é',
                'Ã¨' => 'è',
                'Ãª' => 'ê',
                'Ã´' => 'ô', 
                'Ã ' => 'à',
                'Ã§' => 'ç',
                'Å“' => 'oe',
                'Ã‰' => 'É',
                'Ã€' => 'À',
                'â”œ' => '├',
                'â”¤' => '┤',
                'â”Œ' => '┌',
                'â"Œ' => '┌',
                'â”‚' => '│',
                'â"‚' => '│',
                'â””' => '└',
                'â""' => '└',
                'â”€' => '─',
                'â"€' => '─',
                'â€¢' => '•',
                'â”˜' => '┘',
                'â"˜' => '┘',
                'â”' => '┐',
                'â"' => '┐',
                'â”¬' => '┬',
                'â"¬' => '┬',
                'â†' => '←',
                'â†“' => '↓',
                'Ã—' => '×',
                'Ã¿' => 'ÿ',
                'â†’' => '→',
                'ðŸŽ‰' => '🎉',
                'âœ…' => '✅',
                'âœ—' => '❌',
                'âŒ' => '❌',
                'âŒ'=>'❌',
                'â–¼' => '▼',
                'ÃŠ' => 'Ê',
                'Ãª' => 'ê',
                'Ã®' => 'î',
                'Ã¢' => 'â',
                'Ã»' => 'û',
                'Ãˆ' => 'È',
                'ÃƒÂª' => 'ê',
                'ÃƒÂ©' => 'é',
                'Ãƒâ€°' => 'É',
                'ÃƒÂ¨' => 'è',
                'ÃƒÂ¹' => 'ù',
                'Ã¹'=>'ù',
                'ÃƒÂ´' => 'ô',
                'ÃƒÂ ' => 'à',
                'ÃƒÂ¢' => 'â',
                'ÃƒÂ®' => 'î',
                'NÃ…â€œud' => 'Noeud',
                'nÃ…â€œud' => 'noeud',
                'oÃ¹' => 'où'
            ];
            foreach ($lt as $k => $v) {              
                if (isset($tr[$v])) {
                    $tr[$k] = $tr[$v];
                }
            }
            $this->rtlist = $tr;
        }
        $tr = $this->rtlist;
        $line = strtr($line, $tr);
        //$line = implode(RtfConstants::LF,  explode("\n", $line));
        return $line;
    }
    /**
     * escape litteral 
     * @param string $text 
     * @return string|string[]|null 
     */
    public function escape(string $text)
    {
        $header = preg_replace("/(?<=[^\\\\])(\}|\{)/", "\\\\$1", $text);
        return $header;
    }
    /**
     * 
     * @return void 
     */
    public function clearPar()
    {
        $this->m_states['clear-par'] = "\\pard";
    }
    public function saveState()
    {
        $r = $this->m_states;
        $this->m_states = [];
        return $r;
    }
    /**
     * 
     * @param mixed $state 
     * @return void 
     */
    public function restoreState($state)
    {
        $this->m_states = $state ?? [];
    }
    protected function _update()
    {
        if ($this->m_citem) {
            $this->m_items[] = $this->m_citem;
            $this->m_citem = null;
        }
        $this->storeState();
    }
    /**
     * store State 
     * @return void 
     */
    public function storeState()
    {
        if ($this->m_states) {
            $this->m_items[] = implode('', array_values($this->m_states)) . "\n";
            $this->m_states = [];
        }
    }
    abstract function render(): string;
    /**
     * just append line fied
     * @return void 
     */
    public function ln()
    {
        $this->_update();
        $this->m_items[] = "\\\n";
    }

    /**
     * 
     * @param string $text 
     * @param int $levelIndex 
     * @return void 
     */
    public function title(string $text, int $levelIndex)
    {
        $empty = count($this->m_items);
        $this->_update();
        if (!$empty)
            $this->ln();
        $this->setFontSize($this->getFontFromLevel($levelIndex));
        $this->line($text);
        $this->ln();
        $this->clearPar();
    }
    public function getFontFromLevel($levelIndex)
    {
        $rf = igk_getv($this, 'titleFontSizes');
        return $rf ? igk_getv($rf, $levelIndex) : null;
    }

    /**
     * save state
     * @return mixed 
     */

    public function line(string $line)
    {
        $this->_update();
        $line = $this->prepareFormat($line);
        $this->m_citem = sprintf("{" . $line . "}");
    }
    public function page()
    {
        $this->_update();
        $this->m_items[] = '\\page' . "\n";
    }
    protected function _auto_select(int $index)
    {
        return $index < 0 ? ' ' : $index;
    }
    /**
     * 
     * @param mixed $size 
     * @return void 
     */
    public function setFontSize($size)
    {
        $this->m_states['font-size'] = '\\fs' . $this->_auto_select($size * 2);
    }
    public function setFont(int $index)
    {
        $this->m_states['font'] = '\\f' . $this->_auto_select($index);
    }
    public function setTextColor(int $index)
    {
        $this->m_states['text-color'] = '\\cf' . $this->_auto_select($index);
    }
    public function setBackgroundColor(int $index)
    {
        $this->m_states['background-color'] = '\\cb' . $this->_auto_select($index);
    }
    public function setStrokeWidth(int $size)
    {
        $this->m_states['stroke-width'] = '\\strokewidth' . $this->_auto_select($size);
    }
}
