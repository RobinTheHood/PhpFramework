<?php

namespace RobinTheHood\PhpFramework;

use RobinTheHood\NamingConvention\NamingConvention;
use RobinTheHood\PhpFramework\Module\ModuleLoader;

class AppServerRequest
{
    private $module = '';
    private $app = 'Public';
    private $controller = 'Index';
    private $action = 'Index';


    public function __construct()
    {
        if ($module = Request::get('module')) {
            $this->module = NamingConvention::snakeCaseToCamelCaseFirstUpper($module);
        }

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

    public function getModule()
    {
        return $this->module;
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
        if ($this->module) {
            $moduleDescriptor = ModuleLoader::getModuleDescriptor($this->module);
            $relativControllerDir = $moduleDescriptor['path'] . '/Controllers/';
        } else {
            $relativControllerDir = 'app/Controllers/';
        }

        return App::getRootPath() . $relativControllerDir . $this->app . 'Controllers/' . $this->getClass() . '.php';
    }

    public function getNamespace()
    {
        if ($this->module) {
            $moduleDescriptor = ModuleLoader::getModuleDescriptor($this->module);
            $baseNamespace = $moduleDescriptor['namespace'] . '\Controllers\\';
        } else {
            $baseNamespace = 'App\Controllers\\';
        }

        return $baseNamespace . $this->app . 'Controllers\\';
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
