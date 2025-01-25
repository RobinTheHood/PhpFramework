<?php

namespace RobinTheHood\PhpFramework\Views\Twig;

use RobinTheHood\PhpFramework\App;
use RobinTheHood\PhpFramework\Module\ModuleLoader;
use RobinTheHood\PhpFramework\Button;
use RobinTheHood\PhpFramework\Session;
use RobinTheHood\PhpFramework\AppServerRequest;
use RobinTheHood\PhpFramework\Views\View;

class TwigView extends View
{
    protected $twig;

    public function __construct($relativTemplateFile = '', array $variables = [])
    {
        $appConfig = App::getConfig();
        $twigConfig = $appConfig['twig'];
        $this->setTemplatesPath($twigConfig['templatesPath']);

        $loader = new Twig_Loader_Filesyste($twigConfig['templatesPath']);

        //Add Module Namespaces
        $moduleDescriptors = ModuleLoader::getConfig();
        foreach ($moduleDescriptors as $moduleDescriptor) {
            $relativePath = $moduleDescriptor['path'] . '/Templates/Twig';
            $twigNamespace = $moduleDescriptor['twigNamespace'];
            $loader->addPath(App::getRootPath() . $relativePath, $twigNamespace);
        }

        $twig = new \Twig_Environment($loader, [
            'debug' => $twigConfig['debug'],
            'cache' => $twigConfig['cachePath']
        ]);
        $this->twig = $twig;

        if ($relativTemplateFile) {
            $this->load($relativTemplateFile);
        }

        if ($variables) {
            $this->setVars($variables);
        }

        $this->setVars([
            'request' => new AppServerRequest(),
            'button' => new Button(),
            '_this' => $this
        ]);
    }

    public function getTwig()
    {
        return $this->twig;
    }

    public function load($relativTemplateFile)
    {
        $this->templatesPath = $relativTemplateFile;
        $this->template = $this->twig->load($relativTemplateFile);
    }

    public function render()
    {
        return $this->template->render($this->variables);
    }

    public function display()
    {
        $this->template->display($this->variables);
    }

    public function getFlashMassage($type)
    {
        if ($type == 'success') {
            return Session::dropValue('FlashSuccess', 'Flash');
        }
        if ($type == 'info') {
            return Session::dropValue('FlashInfo', 'Flash');
        }
        if ($type == 'warning') {
            return Session::dropValue('FlashWarning', 'Flash');
        }
        if ($type == 'danger') {
            return Session::dropValue('FlashDanger', 'Flash');
        }
    }

    public function hasFlashMassage($type)
    {
        if ($type == 'success') {
            return Session::getValue('FlashSuccess', 'Flash');
        }
        if ($type == 'info') {
            return Session::getValue('FlashInfo', 'Flash');
        }
        if ($type == 'warning') {
            return Session::getValue('FlashWarning', 'Flash');
        }
        if ($type == 'danger') {
            return Session::getValue('FlashDanger', 'Flash');
        }
    }

    public function __toString()
    {
        return $this->template->render($this->variables);
    }
}
