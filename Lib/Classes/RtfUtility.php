<?php
// @author: C.A.D. BONDJE DOUE
// @file: RtfUtility.php
// @date: 20260127 09:17:21
namespace igk\Windows\Rtf;

use IGK\Helper\Activator;
use IGK\System\Drawing\Colorf;
use IGK\System\Number;
use IGK\System\Text\RegexMatcherContainer;
use IGK\System\Text\RegexMatcherUtility;

/**
 * 
 * @package igk\Windows\Rtf
 * @author C.A.D. BONDJE DOUE
 */
class RtfUtility extends RtfConstants
{
    /**
     * build header expression 
     * @param string $header 
     * @return string 
     */
    public static function Header(string $header): string{
         return sprintf("{\\header %s\\par}\n", $header);
    }
    private static function _SplitPlaceHolderStringDetection(string $haystack)
    {
        $rl = [];
        if (preg_match('/^\\b[a-zA-Z\\-\\p{L}]{1,}\\b\\s+/u',ltrim($haystack))){
            return $rl;
        }
        $regex = new RegexMatcherContainer;
        $pos = 0;
        // define
        $src = $haystack;
        // + | ignore a string that can be associate to word 
        // $regex->match('^\\b[a-zA-Z\\-]{1,}\\b\\s+', 'break'); 
        $regex->match('\\s+', 'break');
        $regex->appendStringDetection('string', true);
        $toffset = 0;
        while ($g = $regex->detect($src, $pos)) {
            if ($e = $regex->end($g, $src, $pos)) {
                igk_debug_wln('split-:'.$e->tokenID , "value: ".$e->value);
                if ($e->tokenID == 'string') {
                    $g = substr($src, $toffset, $e->from);
                    if (!empty($g)) {
                        $rl[] = $g;
                    }
                    $rl[] = new RtfFixedPlaceHolder(igk_str_remove_quote($e->value));
                    $toffset = $e->to;
                }
                if ($e->tokenID=='break'){
                    if (!empty($g = substr($src, $toffset, $e->from))){
                        $rl[] =$g;
                    }
                    $toffset = strlen($src);
                    break;
                }
            }
        }
        $g = substr($src, $toffset);
        if (!empty($g)) {
            $rl[] = $g;
        }
        return $rl;
    }
    /**
     * build place holder info
     * @param string $haystack 
     * @return IRtfBulletPlaceHolderInfo 
     */
    public static function BulletPlaceHolderInfo(string $haystack, $separator = RtfConstants::BulletSeparator)
    {
        if (count(array_filter(explode(' ', $haystack))) <= 1) {
            return null;
        }

        $cp = (function ($haystack, $separator) {
            $_T = array_keys(func_get_args());
            $_T[] = '_T';
            list($placeholder, $type, $list, $level, $root, $bulletDefinition, $from) = (function ($l) use ($separator) {
                $tb = self::_SplitPlaceHolderStringDetection($l);
                $list = [];
                $type = '';
                $level = null;
                $root = '';
                $hroot = [];
                // offset position in line 
                $bulletDefinition = (object)['offset' => 1, 'positions' => []]; // contains definition of every marked element
                $rpos = 0;
                $placeholder = implode('', array_map(function ($i) use ($separator, &$hroot, &$level, &$list, &$type, &$rpos, $bulletDefinition) {
                    if (!is_string($i)) {
                        $v = $i . '';
                        if (empty($type)) {
                            $type = RtfBulletNFCTypes::Puce;
                            //$list[] = RtfBulletNFCTypes::Puce;
                            $hroot[] = $v;
                        }
                        $s = strlen($v);
                        $bulletDefinition->offset += $s;
                        $rpos+=$s;
                        return $v;
                    }
                    $i = self::BuildPlaceHolder($i, $separator, $bulletDefinition->offset, $rpos);
                    if (!$i || empty($i->placeholder)){
                        return null;
                    }
                    $list = array_merge($list, $i->list);
                    $type = $i->type;
                    $v = $i->placeholder;
                    $lc = count($i->list);

                    if (is_null($level)) {
                        $level = $lc - 1;
                        $hroot[] = $i->rootPlaceholder;
                    } else {
                        $level += $lc;
                    }
                    $bulletDefinition->positions = array_merge($bulletDefinition->positions, $i->positions);
                    return $v;
                }, $tb));
                
                $root = implode($hroot);
                $from = substr($l, 0, $rpos);
                return [$placeholder, $type, $list, $level, $root, $bulletDefinition, $from];
            })($haystack, $separator);
            // + | fixture 
            if (empty($list) && $type) {
                $list[] = $type;
            }
            $level = $level ?? 0;
            
            $c = get_defined_vars();
            foreach ($_T as $k) unset($c[$k]);
            return $c;
        })($haystack, $separator);
        if (empty($cp['placeholder'])){
            return null;
        }
        return Activator::CreateNewInstance(IRtfBulletPlaceHolderInfo::class, $cp);
    }

    /**
     * 
     * @param string $cp 
     * @param ':.-)=/' $separator 
     * @return mixed
     */
    public static function BuildPlaceHolder(string $cp, $separator = RtfConstants::BulletSeparator, &$position = 1, & $rpos=0)
    {
        $g = implode("|", RegexMatcherUtility::EscapeCharList(str_split($separator, 1)));
        $m = $cp;
        $o = '';
        $mt = [];
        $c = 0;
        $_type = null;
        $_continue = true;
        $v_positions = [];
        $tfoffset = $position;
        $_fcappend = function ($s) use (&$root, &$o, &$v_positions, &$tfoffset) {
            if (is_null($root)) {
                $root = $s;
            }
            $o .= $s;
            // $n = count($v_positions);
            $pos = $tfoffset; // + (strlen($o) - (($n+1) * 4) -1);
            $v_positions[] = $pos;
            // $n = count($v_positions);
            $tfoffset += strlen($s) - 4 + 1;
        };
        $j = 0; 
        while ($_continue && preg_match('/' . $g . '/', $m, $tab, PREG_OFFSET_CAPTURE)) {
            list($glue, $offset) = $tab[0];
            $ch = substr($m, 0, $offset); 
            $rpos += strlen($ch) + $j ;
            $j=1;
            if (($ipos = strpos($ch, ' ')) !== false) {
                $ch = substr($ch, 0, $ipos);
                $_continue = false;
                if (empty($ch)) {
                    continue;
                }
            }
            $hex = RtfUtility::ToHex($c);
            $tc = trim($ch);
            $_type = self::DetectBulletType($tc);
            $hex = RtfUtility::ToHex($c);
            $_fcappend(str_replace($tc, "\\'" . $hex, $ch) . $glue);
            $m = substr($m, $offset + strlen($glue));
            $mt[] = $_type;
            $c++;
        }
        $gc = '';
        $pos_up = $m && preg_match('/^\\s/', $m) ? 1 : 0;
        $rpos+=$j;
        if ($_continue && $m && !preg_match('/^\\s/', $m)) {
            
            if ($_continue && ($gc = empty(trim($m))) && (strlen($m))) {
                $_fcappend($m);
                $m = trim($m);
            }
            if ($_continue && !$gc) {
                $tc = explode(' ', trim($m), 2)[0];
                if (is_numeric($tc) || (strlen($tc) == 1)) {
                    $_type = self::DetectBulletType($tc);
                    $hex = RtfUtility::ToHex($c);
                    $_fcappend("\\'" . $hex);
                    $mt[] = $_type;
                    $pos_up = 1;
                    $rpos+=strlen($m);
                }
            }
        }
        if ($v_positions) {
            $n = count($v_positions);
            $p = $pos_up + $n + (strlen($o) - ($n * 4));
            $position = $p; // update next position 
            
        }
        return (object)['placeholder' => $o, 'list' => $mt, 'positions' => $v_positions, 'type' => $_type, 'rootPlaceholder' => $root];
    }
    /**
     * detect type 
     * @param mixed $a 
     * @return null|string 
     */
    static function DetectBulletType($a): ?string
    {
        if (is_numeric($a)) {
            return RtfBulletNFCTypes::Decimal;
        }
        if (Number::IsRomanNumeral(strtoupper($a))) {
            if (preg_match('/^[a-z]+$/', $a)) {
                return RtfBulletNFCTypes::RomanLower;
            }
            return RtfBulletNFCTypes::RomanUpper;
        }
        if (preg_match('/^[a-z]+$/', $a)) {
            return RtfBulletNFCTypes::NewLatinLower;
        }
        return RtfBulletNFCTypes::NewLatinUpper;
    }
    /**
     * get color web from color definition 
     * @param string $cl 
     * @return string 
     */
    public static function ColorEntryFromWebColor(string $cl): string
    {
        $c = Colorf::FromString($cl)->toByte();
        return self::DefColor($c);
    }
    /**
     * 
     * @param mixed|{R; G} $c 
     * @return string 
     */
    public static function DefColor($c): string
    {
        return sprintf(self::COLOR_FMT, $c->R, $c->G, $c->B);
    }
    public static function ColorTableEntryFromWebColor(array $colors): string
    {
        return sprintf(self::COLOR_TABLE_FMT, implode(';', array_map(function ($a) {
            if (is_string($a)) {
                return self::ColorEntryFromWebColor($a);
            }
            if ($a instanceof Colorf) {
                return self::DefColor($a->toByte());
            }
        }, $colors)) . ';');
    }
    /**
     * 
     * @param array $tab 
     * @return string 
     */
    public static function FontTables(array $tab): string
    {
        $i = 0;
        return sprintf(self::FONT_TABLE_FMT, implode('', array_map(function ($a) use (&$i) {
            return '{\\f' . ($i++) . trim($a) . ';}';
        }, $tab)));
    }

    /**
     * bold utility 
     * @param string $value 
     * @return string 
     */
    public static function Bold(string $value)
    {
        return sprintf(self::BOLD_FMT, $value);
    }
    public static function Italic(string $value)
    {
        return sprintf(self::ITALIC_FMT, $value);
    }
    /**
     * 
     * @param string $value 
     * @return string 
     */
    public static function Underline(string $value)
    {
        return sprintf(self::UNDERLINE_FMT, $value);
    }
    public static function Strike(string $value)
    {
        return sprintf(self::STRIKE_FMT, $value);
    }
    /**
     * 
     * @param string $value 
     * @param string $format 
     * @return mixed 
     */
    public static function Format(string $value, string $format)
    {
        $g = ['i' => 'Italic', 'b' => 'Bold', 'u' => 'Underline', 's' => 'Strike'];
        $f = trim($format);
        if ($f) {
            foreach ($g as $k => $v) {
                if (strpos($f, $k) !== false) {
                    $value = call_user_func_array([static::class, $v], [$value]);
                }
            }
        }
        return $value;
    }

    public static function CmToWtips(int $i)
    {
        return $i * 567;
    }
    public static function MmToWtips(int $i)
    {
        return ceil($i * 56.7);
    }
    /**
     * 
     * @param null|int $c 
     * @param null|int $d 
     * @return string|null 
     */
    public static function GetBorderColor(?int $c, ?int $d = null): ?string
    {
        $cl = $c ?? $d;
        if (!is_null($cl)) {
            return sprintf(RtfConstants::BRD_COLOR_FMT, $cl);
        }
        return null;
    }
    /**
     * 
     * @param mixed $id 
     * @param mixed $text 
     * @return string 
     */
    public static function BookMark($id, $text)
    {
        return sprintf(RtfConstants::BOOK_MARK_FMT, $id, $text, $id);
    }

    public static function ImageToRTFHex($imagePath)
    {
        // Lire l'image
        $imageData = file_get_contents($imagePath);

        // Convertir en hexadécimal
        $hexData = bin2hex($imageData);

        // Formater en lignes de 64 caractères
        $hexLines = str_split($hexData, 64);
        $formattedHex = implode("\n", $hexLines);

        return $formattedHex;
    }
    /**
     * 
     * @param mixed $size 
     * @return string 
     */
    public static function ToHex($v, $size = 2)
    {
        return str_pad(dechex($v), $size, '0', STR_PAD_LEFT);
    }
    /**
     * get list item 
     * @param string $text 
     * @param '\\uc0\\u8226' $bullet 
     * @param mixed $tabPuce 
     * @param string $id 
     * @param string $level 
     * @return string 
     */
    public static function List(
        string $text,
        $bullet = RtfConstants::PUCE_CIRCLE,
        $tabPuce = null,
        ?string $id = '\\ls1',
        ?string $level = '\\ilvl0'
    ) {
        $tabPuce = $tabPuce ?? RtfConstants::TAB_PUCE;
        return $tabPuce . sprintf(RtfConstants::LISTITEM_FMT, $id . $level, $bullet, $text) . RtfConstants::LF;
    }

    /**
     * mark styling 
     * @param string $value 
     * @param null|string $tyle 
     * @return string 
     */
    public static function MarkStyle(string $value, ?string $style = "\\s1"): string
    {
        return sprintf("{\\%s %s\par}\n", $style, $value);
    }

    /**
     * 
     * @param mixed $info 
     * @return void 
     */
    public static function BuildListLevel($info, int $startAt = 1, $levelid = null): string
    {
        $out = [];
        $l = $info->level;
        $placeholder = $info->placeholder;
        $TLEN = strlen($placeholder);
        $i = 0;
        $v_pos = '';
        list($v_levelmarker, $v_startAt, $definitions) = igk_extract($info->bulletDefinition ?? [], 'levelmarker|startAt|positions');
        while ($l >= 0) {
            $v_type = $info->list[$i];
            $v_size = '';
            $v_placeholder = '';
            $cp = igk_getv($definitions, $i);
            $next = $TLEN - $cp;
            if ($l > 0) {
                $next = $definitions[$i + 1] - $cp - 1;
            }

            $v_placeholder = substr($placeholder, 0, (($i + 1) * 3) + $cp + $next);
            $v_size = "\\'" . self::ToHex(strlen($v_placeholder) - (($i + 1) * 3));
            $v_levelnumber = $cp ?? $i + 1;
            $v_startAt = $v_startAt ?? $startAt;
            $v_pos .= "\\'" . self::ToHex($v_levelnumber);
            $v_levelmarker = $v_levelmarker ?? 'disc';
            $out[] = implode([
                "{\\listlevel",
                $v_type,
                $justify ?? "\\leveljc0",
                "\\levelfollow0", // tabr
                "\\levelstartat" . $v_startAt, // start at 
                "\\levelindent0", // tab supplement 
                "{\\leveltext" . $v_size . $v_placeholder . ";",
                $levelid ? "\\leveltemplateid" . $levelid : '',
                "}", // tabr
                "{\\levelnumbers" . $v_pos . ";}", // no numbers
                $v_levelmarker ? "{\\*\\levelmarker \\{" . $v_levelmarker . "\\}}" : null, // level marker - css level marker - use on macos
                "\\fi-360\\li720", // no numbers
                "}",
            ]);
            $l--;
            $i++;
        }
        return implode("\n", $out);
    }
}
