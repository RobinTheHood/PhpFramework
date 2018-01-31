<?php
namespace RobinTheHood\PhpFramework;

use RobinTheHood\NamingConvention\NamingConvention;

class ArrayHelper
{
    public static function getFirst($array)
    {
        if (isset($array) && isset($array[0])) {
            return $array[0];
        }
        return null;
    }

    public static function setIfUnset(& $array, $index, $value)
    {
        if ( !isset($array[$index]) ) {
            $array[$index] = $value;
        }
    }

    public static function getIfSet(array $array, $index, $default = null)
    {
        return isset($array[$index]) ? $array[$index] : $default;
    }
}
