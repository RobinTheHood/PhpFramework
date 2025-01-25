<?php

namespace RobinTheHood\PhpFramework;

class Request
{
    public static function get($name, $filter = FILTER_DEFAULT)
    {
        return filter_input(INPUT_GET, $name, $filter);
    }

    public static function getAll()
    {
        $result = filter_input_array(INPUT_GET);
        if (is_array($result)) {
            return $result;
        }
        return [];
    }

    public static function getInt($name)
    {
        return self::get($name, FILTER_VALIDATE_INT);
    }

    public static function post($name, $filter = FILTER_DEFAULT)
    {
        return filter_input(INPUT_POST, $name, $filter);
    }

    public static function postAll()
    {
        $result = filter_input_array(INPUT_POST);
        if (is_array($result)) {
            return $result;
        }
        return [];
    }

    public static function postInt($name)
    {
        return self::post($name, FILTER_VALIDATE_INT);
    }

    public static function server($name, $filter = FILTER_DEFAULT)
    {
        return filter_input(INPUT_SERVER, $name, $filter);
    }
}
