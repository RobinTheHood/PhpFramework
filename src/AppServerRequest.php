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
        if ($app = Request::get('app')) {
            $this->app = NamingConvention::snakeCaseToCamelCaseFirstUpper($app);
        }

        if ($controller = Request::get('controller')) {
            $this->controller = NamingConvention::snakeCaseToCamelCaseFirstUpper($controller);
        }

        if ($action = Request::get('action')) {
            $this->action = NamingConvention::snakeCaseToCamelCaseFirstUpper($action);
        }
    }

    public function getUri()
    {
        return Request::server('REQUEST_URI');
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
