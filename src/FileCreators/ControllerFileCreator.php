<?php
namespace RobinTheHood\PhpFramework\FileCreators;

use RobinTheHood\Debug\Debug;
use RobinTheHood\PhpFramework\Console\Creators\FileCreator;

class ControllerFileCreator extends FileCreator
{

    public function createModelControllerFile($objName, $app)
    {
        $app = ucfirst($app);
        if ($app == '') {
            $app = 'PublicControllers';
        }
        $objName .= 's';

        $controllerTmpl = file_get_contents(ROOT . '/vendor/php-framework/framework/src/Console/Templates/ObjController.tmpl');
        $controllerTmpl = str_replace('{CLASS_NAME}',     $objName,     $controllerTmpl);

        $controllerName = $objName . 'Controller.php';
        $controllerPath = ROOT . '/app/Controllers/' . $app . '/' . $controllerName;

        Debug::out('create: ' . $controllerPath);
        file_put_contents($controllerPath, $controllerTmpl);
    }
}
