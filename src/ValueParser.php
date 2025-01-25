<?php

namespace RobinTheHood\PhpFramework;

class ValueParser
{
    public static function parse($value, $type)
    {
        if ($type == 'percent') {
            return self::percent($value);
        } elseif ($type == 'float') {
            return self::float($value);
        } elseif ($type == 'datetime') {
            return self::datetime($value);
        } elseif ($type == 'password') {
            return self::password($value);
        } elseif ($type == 'currency') {
            return self::currency($value);
        }

        return $value;
    }

    // 19,1% -> 0.191
    public static function percent($value)
    {
        $str = trim($value);
        $str = str_replace('%', '', $str);
        $str = str_replace('.', '', $str);
        $str = str_replace(',', '.', $str);
        $float = ((float) $str) / 100;
        return $float;
    }

    // 17.07.1987 12:54:03 -> 1987-07-17 12:54:03
    public static function dateTime($value)
    {
        $value = trim($value);
        if ($value) {
            return date('Y-m-d H:i:s', strtotime($value));
        }
        return '0000-00-00 00:00:00';
    }

    public static function password($value)
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    public static function float($value)
    {
        $str = trim($value);
        $str = str_replace(',', '.', $str);
        $float = (float) $str;
        return $float;
    }

    public static function currency($value)
    {
        return self::float($value);
    }
}
