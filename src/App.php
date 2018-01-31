<?php
namespace RobinTheHood\PhpFramework;

use RobinTheHood\Database\Database;
use RobinTheHood\PhpFramework\Dispatcher;
use RobinTheHood\PhpFramework\AppServerRequest;
use RobinTheHood\PhpFramework\Module\ModuleLoader;
use Symfony\Component\EventDispatcher\EventDispatcher;

class App
{
    private static $rootPath;
    private static $configPath;

    private static $config;

    private static $eventDispatcher;

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

        self::$eventDispatcher = new EventDispatcher();

        $moduleLoader = new ModuleLoader([
            'rootPath' => App::getRootPath()
        ]);
        $moduleLoader->setEventDispatcher(self::$eventDispatcher);
        $moduleLoader->load();
        
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

    public static function getEventDispatcher()
    {
        return self::$eventDispatcher;
    }

    public static function loadConfig()
    {
        require_once self::$configPath;
        return self::getConfig();
    }
}
