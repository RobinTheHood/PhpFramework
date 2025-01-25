<?php

namespace RobinTheHood\PhpFramework\Database\DatabaseObject;

use RobinTheHood\NamingConvention\NamingConvention;

class DatabaseObjectCreator
{
    public static function createObjectsFromArray($array, $className)
    {
        $objects = [];
        foreach ($array as $row) {
            $objects[] = self::createObjectFromArray($row, $className);
        }

        return $objects;
    }

    private static function createObjectFromArray($array, $className)
    {
        $array = self::snakeCaseToCamelCaseArray($array);

        $className = 'App\\Models\\' . $className;
        $obj = new $className($array);

        return $obj;
    }

    private static function snakeCaseToCamelCaseArray($array)
    {
        $resultArray = [];
        foreach ($array as $keySnakeCase => $value) {
            $keyCamelCase = NamingConvention::snakeCaseToCamelCase($keySnakeCase);
            $resultArray[$keyCamelCase] = $value;
        }
        return $resultArray;
    }
}
