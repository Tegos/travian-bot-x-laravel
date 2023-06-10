<?php

namespace App\Support\Helpers;

final class StringHelper
{
    public static function normalizeString(string $string = ''): string
    {
        $string = html_entity_decode($string);
        $string = self::normalizeChars($string);
        $string = self::normalizeSpaces($string);

        return trim($string);
    }

    public static function normalizeSpaces(string $str = ''): string
    {
        return strval(preg_replace('!\s+!u', ' ', $str));
    }

    public static function normalizeChars(string $str = ''): string
    {
        return strval(preg_replace('/\W/u', ' ', $str));
    }

    public static function getStringBetween($str, $from, $to, $withFromAndTo = false): string
    {
        $sub = substr($str, strpos($str, $from) + strlen($from), strlen($str));
        if ($withFromAndTo) {
            return $from . substr($sub, 0, strrpos($sub, $to)) . $to;
        }

        return substr($sub, 0, strrpos($sub, $to));
    }
}
