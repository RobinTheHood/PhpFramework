<?php

namespace RobinTheHood\PhpFramework;

class ObjectFilter
{
    public static function filter($objctArray, $methodArray, $checkValues = [])
    {
        $result = [];
        foreach ($objctArray as $object) {
            $value = self::getValue($object, $methodArray, $checkValues);
            if ($value) {
                $result[] = $object;
            }
        }
        return $result;
    }

    private static function getValue($object, $methodArray, $checkValues = [])
    {
        if (substr($methodArray[0], 0, 4) == 'OrIs') {
            $value = false;
        } else {
            $value = true;
        }

        foreach ($methodArray as $method) {
            if (substr($method, 0, 5) == 'isNot') {
                $methodName = 'is' . substr($method, 5, strlen($method) - 5);
                $value = $value && !$object->$methodName();
            } elseif (substr($method, 0, 4) == 'OrIs') {
                $methodName = 'is' . substr($method, 4, strlen($method) - 4);
                $value = $value || $object->$methodName();
            } elseif (self::hasPrefix('OrHasValue', $method)) {
                $methodName = 'get' . self::stripPrefix('OrHasValue', $method);
                $value = $value || $object->$methodName() == $checkValues[0];
            } elseif (self::hasPrefix('hasValue', $method)) {
                $methodName = 'get' . self::stripPrefix('hasValue', $method);
                $result = false;
                foreach ($checkValues as $checkValue) {
                    if ($object->$methodName() == $checkValue) {
                        $result = true;
                        break;
                    }
                }
                $value = $value && $result;
                //$value = $value && $object->$methodName() == $checkValues[0];
            } elseif (self::hasPrefix('hasNo', $method)) {
                $methodName = 'get' . self::stripPrefix('hasNo', $method);
                $value = $value && !$object->$methodName();
            } elseif (self::hasPrefix('has', $method)) {
                $methodName = 'get' . self::stripPrefix('has', $method);
                $value = $value && $object->$methodName();
            } else {
                $value = $value && $object->$method();
            }
        }

        return $value;
    }

    private static function hasPrefix($prefix, $string)
    {
        $len = strlen($prefix);
        return substr($string, 0, $len) == $prefix;
    }

    private static function stripPrefix($prefix, $string)
    {
        $len = strlen($prefix);
        return substr($string, $len, strlen($string) - $len);
    }
}
