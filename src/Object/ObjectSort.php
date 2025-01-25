<?php

namespace RobinTheHood\PhpFramework\Object;

class ObjectSort
{
    private static $objectMethod;

    private static function compareAsc($objectA, $objectB)
    {
        $method = self::$objectMethod;
        $valueA = $objectA->$method();
        $valueB = $objectB->$method();
        if ($valueA > $valueB) {
            return 1;
        } else {
            return -1;
        }
    }

    private static function compareDesc($objectA, $objectB)
    {
        $method = self::$objectMethod;
        $valueA = $objectA->$method();
        $valueB = $objectB->$method();
        if ($valueA > $valueB) {
            return -1;
        } else {
            return 1;
        }
    }

    public static function sort($objectArray, $method, $order = 'asc')
    {
        self::$objectMethod = $method;
        if ($order == 'desc') {
            usort($objectArray, ['RobinTheHood\PhpFramework\Object\ObjectSort', 'compareDesc']);
        } else {
            usort($objectArray, ['RobinTheHood\PhpFramework\Object\ObjectSort', 'compareAsc']);
        }
        return $objectArray;
    }
}
