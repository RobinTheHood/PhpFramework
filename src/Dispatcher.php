<?php

namespace RobinTheHood\PhpFramework;

use RobinTheHood\PhpFramework\Redirect;
use RobinTheHood\PhpFramework\AppServerRequest;
use RobinTheHood\PhpFramework\Exceptions\ControllerFileNotExsistException;
use RobinTheHood\PhpFramework\Exceptions\ControllerClassNotExsistException;
use RobinTheHood\PhpFramework\Exceptions\ControllerMethodNotExsistException;

class Dispatcher
{
    public function invoke(AppServerRequest $request, $debugMode)
    {
        $thowException = ($debugMode || $request->getInvokeMethod() == 'invoke404');

        try {
            $this->loadControllerFile($request);
        } catch (ControllerFileNotExsistException $e) {
            if ($thowException) {
                throw $e;
            } else {
                $this->invoke404();
            }
        }

        try {
            $controller = $this->createControllerInstance($request);
        } catch (ControllerClassNotExsistException $e) {
            if ($thowException) {
                throw $e;
            } else {
                $this->invoke404();
            }
        }

        try {
            $this->invokeController($controller, $request);
        } catch (ControllerMethodNotExsistException $e) {
            if ($thowException) {
                throw $e;
            } else {
                $this->invoke404();
            }
        }
    }

    private function loadControllerFile($request)
    {
        $path = $request->getControllerFilePath();

        if (!file_exists($path)) {
            throw new ControllerFileNotExsistException('Controller File not exsist. File:' . $path);
        }

        require_once $path;
    }

    private function createControllerInstance($request)
    {
        $class = $request->getClassWithNamespace();

        if (!class_exists($class)) {
            throw new ControllerClassNotExsistException(
                "Controller Class $class not exsist in File: " . $request->getControllerFilePath()
            );
        }

        return new $class();
    }


    private function invokeController($controller, $request)
    {
        $method = $request->getInvokeMethod();

        if (!$this->controllerMethodExists($controller, $method)) {
            throw new ControllerMethodNotExsistException(
                "Controller Method $method not exsist in " . get_class($controller) . '. Query: ' . $request->getUri()
            );
        }

        $controller->preInvoke();
        $controller->$method();
        $controller->postInvoke();
    }

    public function invoke404()
    {
        Redirect::status404(Button::app('public', ['module' => 'index', 'action' => '404']));
    }

    private function controllerMethodExists($controller, $method)
    {
        $controllerMethods = get_class_methods($controller);
        foreach ($controllerMethods as $controllerMethod) {
            if ($controllerMethod == $method) {
                return true;
            }
        }
        return false;
    }
}
