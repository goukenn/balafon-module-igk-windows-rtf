<?php
// @author: C.A.D. BONDJE DOUE
// @file: RtfUtility.php
// @date: 20260127 09:17:21
namespace igk\Windows\Rtf;

use IGK\System\Drawing\Colorf;

/**
 * 
 * @package igk\Windows\Rtf
 * @author C.A.D. BONDJE DOUE
 */
class RtfUtility extends RtfConstants
{

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
        $id = '\\ls1',
        $level = '\\ilvl0'
    ) {
        $tabPuce = $tabPuce ?? RtfConstants::TAB_PUCE; 
        return $tabPuce . sprintf(RtfConstants::LISTITEM_FMT, $id . $level, $bullet, $text) . RtfConstants::LF;
    }
}
