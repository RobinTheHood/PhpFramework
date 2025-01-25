<?php

namespace RobinTheHood\PhpFramework\Views;

class View
{
    protected $variables = [];
    protected $templatesPath = '';
    protected $relativTemplateFile = '';

    public function __construct($templatesPath)
    {
        $this->setTemplatePath($templatesPath);
    }

    public function setTemplatesPath($templatesPath)
    {
        $this->templatesPath = $templatesPath;
    }

    public function load($relativTemplateFile)
    {
        $this->relativTemplateFile = $relativTemplateFile;
    }

    public function setVar($name, $value)
    {
        $this->variables[$name] = $value;
    }

    public function setVars($array)
    {
        foreach ($array as $name => $value) {
            $this->setVar($name, $value);
        }
    }

    public function getVar($name)
    {
        return $this->variables[$name];
    }

    public function getVars()
    {
        return $this->variables;
    }

    public function __toString()
    {
    }
}
