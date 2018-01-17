<?php
namespace RobinTheHood\PhpFramework;

class ValueParser
{
    static public function parse($value, $type)
    {
        if ($type == 'percent') {
            return self::percent($value);
        } else if ($type == 'datetime') {
            return self::datetime($value);
        } else if ($type == 'password') {
            return self::password($value);
        }
        return $value;
    }

    // 19,1% -> 0.191
    static public function percent($value)
    {
        $str = trim($value);
        $str = str_replace('%', '', $str);
        $str = str_replace('.', '', $str);
        $str = str_replace(',', '.', $str);
        $float = ((float) $str) / 100;
        return $float;
    }

    // 17.07.1987 12:54:03 -> 1987-07-17 12:54:03
    static public function dateTime($value)
    {
        $value = trim($value);
        if ($value) {
            return date('Y-m-d H:i:s', strtotime($value));
        }
        return '0000-00-00 00:00:00';
    }

    static public function password($value)
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }
}
