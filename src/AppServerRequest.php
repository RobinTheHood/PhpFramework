<?php
namespace RobinTheHood\PhpFramework;

use RobinTheHood\NamingConvention\NamingConvention;

class AppServerRequest
{
    private $app = 'Public';
    private $controller = 'Index';
    private $action = 'Index';


    public function __construct()
    {
        if (isset($_GET['app'])) {
            $this->app = NamingConvention::snakeCaseToCamelCaseFirstUpper($_GET['app']);
        }

        if (isset($_GET['controller'])) {
            $this->controller = NamingConvention::snakeCaseToCamelCaseFirstUpper($_GET['controller']);
        }

        if (isset($_GET['action'])) {
            $this->action = NamingConvention::snakeCaseToCamelCaseFirstUpper($_GET['action']);
        }
    }

    public function getUri()
    {
        return $_SERVER['REQUEST_URI'];
    }

    public function getApp()
    {
        return $this->app;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getAction()
    {
        return $this->action;
    }



    public function getControllerFilePath()
    {
        $relativControllerDir = 'app/Controllers/';

        return App::getRootPath() . $relativControllerDir . $this->app . 'Controllers/' . $this->getClass() . '.php';
    }

    public function getNamespace()
    {
        return 'App\Controllers\\' . $this->app . 'Controllers\\';
    }

    public function getClass()
    {
        return $this->controller . 'Controller';
    }

    public function getClassWithNamespace()
    {
        return $this->getNamespace() . $this->getClass();
    }

    public function getInvokeMethod()
    {
        return 'invoke' . $this->action;
    }

    public static function getRequest()
    {
        return new AppServerRequest();
    }
}
