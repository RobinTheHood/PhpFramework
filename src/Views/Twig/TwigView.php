<?php
namespace RobinTheHood\PhpFramework\Views\Twig;

use RobinTheHood\PhpFramework\App;
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

        $loader = new \Twig_Loader_Filesystem($twigConfig['templatesPath']);
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

        $this->setVar('request', new AppServerRequest());
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

    public function __toString()
    {
        return $this->template->render($this->variables);
    }
}
