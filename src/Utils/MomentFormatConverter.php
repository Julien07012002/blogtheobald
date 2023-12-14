<?php


namespace App\Utils;


class MomentFormatConverter
{
    /**
     * Ceci définit le mappage entre le format de date PHP ICU (clé) et le format de date moment.js (valeur)
     */
    private const FORMAT_CONVERT_RULES = [
        // année
        'yyyy' => 'YYYY', 'yy' => 'YY', 'y' => 'YYYY',
        // jour
        'dd' => 'DD', 'd' => 'D',
        // jour de la semaine
        'EE' => 'ddd', 'EEEEEE' => 'dd',
        // fuseau horaire
        'ZZZZZ' => 'Z', 'ZZZ' => 'ZZ',
        // lettre 'T'
        '\'T\'' => 'T',
    ];

    /**
     * Renvoie le format moment.js associé.
     */
    public function convert(string $format): string
    {
        return strtr($format, self::FORMAT_CONVERT_RULES);
    }
}
