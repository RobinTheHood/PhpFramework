<?php
namespace RobinTheHood\PhpFramework;

class Session
{
    public static function getValue($name, $environment = 'global')
    {
        self::startSessionIfNotStarted();
        if (isset($_SESSION[$environment][$name])) {
            return $_SESSION[$environment][$name];
        }
    }

    public static function dropValue($name, $environment = 'global')
    {
        $value = self::getValue($name, $environment);
        self::setValue('', $name, $environment);
        return $value;
    }

    public static function setValue($value, $name, $environment = 'global')
    {
        self::startSessionIfNotStarted();
        return $_SESSION[$environment][$name] = $value;
    }

    public static function startSessionIfNotStarted()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
}
