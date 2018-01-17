<?php
namespace RobinTheHood\PhpFramework;

use RobinTheHood\PhpFramework\Dispatcher;
use RobinTheHood\PhpFramework\AppServerRequest;
use RobinTheHood\Database\Database;

class App
{
    private static $rootPath;
    private static $configPath;

    private static $config;

    public static function init(array $options)
    {
        if ($options['rootPath']) {
            self::$rootPath = $options['rootPath'];
            define('APP_ROOT', self::$rootPath);
        }

        if ($options['configPath']) {
            self::$configPath = $options['configPath'];
        }
    }

    public static function start()
    {
        self::loadConfig();

        $request = AppServerRequest::getRequest();

        Database::newConnection(self::$config['database']);

        $dispatcher = new Dispatcher();
        $dispatcher->invoke($request, true);
    }

    public static function getRootPath()
    {
        return self::$rootPath;
    }

    public static function setConfig($config)
    {
        self::$config = $config;
    }

    public static function getConfig()
    {
        return self::$config;
    }

    public static function loadConfig()
    {
        require_once self::$configPath;
        return self::getConfig();
    }
}
