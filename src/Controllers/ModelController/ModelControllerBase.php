<?php
namespace RobinTheHood\PhpFramework\Controllers\ModelController;

use RobinTheHood\PhpFramework\Controllers\Controller;
use RobinTheHood\PhpFramework\AppServerRequest;

class ModelControllerBase extends Controller
{
    protected $modelName;
    protected $appName;
    protected $repoClassName;
    protected $repo;
    protected $structure;
    protected $filterdStructure;
    protected $options;
    protected $columnNames;

    public function __construct($modelName = '')
    {
        if ($modelName) {
            $this->modelName = $modelName;
        } else {
            $this->modelName = $this->initModelName();
        }

        $this->appName = $this->initAppName();
        $this->repoClassName = $this->getRepoClassNameWithNamespace($this->modelName);
        $this->repo = new $this->repoClassName;
        $this->structure = $this->repo->getStructure();
        $this->columnNames = $this->getColumnNames($this->structure);
    }

    public function getStructure()
    {
        return $this->structure;
    }

    public function getModelName()
    {
        return $this->modelName;
    }

    public function getAppName()
    {
        return $this->appName;
    }

    public function init($options, $object = null)
    {
        $this->options = $this->initOptions($options, $object);
        $this->filteredStructure = $this->filterStructure($this->structure, $this->options['enabledFields']);
    }

    protected function initOptions($options, $object = null) {
        $modelControllerOptions = new ModelControllerOptions($options, $object, $this);
        return $modelControllerOptions->getInitOptions();
    }

    protected function getControllerTemplateVars()
    {
        return [
            'request' => new AppServerRequest(),
            'options' => $this->options,
            'buttons' => $this->options['buttons'],
            'modelName' => $this->modelName,
            'structure' => $this->filteredStructure
        ];
    }

    protected function render($functions, $templateName, $templateVars)
    {
        $viewClass = $this->options['viewClass'];
        $view = new $viewClass($this->options[$templateName], $templateVars);

        $render = true;
        if (!empty($functions['render'])) {
            $render = $functions['render']($view);
        }

        if ($render) {
            echo $view->render();
        }
    }

    protected function getObjAttValues($obj, $structure)
    {
        $values = [];
        $values['id'] = $obj->getId();
        foreach($structure as $columnName => $definition) {
            $values[$columnName] = $obj->get($columnName);
        }
        return $values;
    }

    protected function filterStructure($structure, $allowedColumnNames = 'all')
    {
        $filteredStructure = [];
        if ($allowedColumnNames == 'all') {
            foreach($structure as $columnName => $definition) {
                $filteredStructure[$columnName] = $definition;
            }
        } elseif (is_array($allowedColumnNames)) {
            foreach ($allowedColumnNames as $columnName) {
                if ($structure[$columnName]) {
                    $filteredStructure[$columnName] = $structure[$columnName];
                }
            }
        }
        return $filteredStructure;
    }

    private function initModelName()
    {
        $controllerClassNameWithNamespace = get_class($this);
        $controllerClassNameElements = explode('\\', $controllerClassNameWithNamespace);
        $lastElement = $controllerClassNameElements[count($controllerClassNameElements) - 1];
        $modelName = str_replace('sController', '', $lastElement);
        return $modelName;
    }

    private function initAppName()
    {
        $controllerClassNameWithNamespace = get_class($this);
        $controllerClassNameElements = explode('\\', $controllerClassNameWithNamespace);
        $appElement = $controllerClassNameElements[count($controllerClassNameElements) - 2];
        $appName = str_replace('Controllers', '', $appElement);
        return $appName;
    }

    protected function getRepoFilePath($modelName)
    {
        $repoFilePath = '/app/Repositories/' . $modelName . 'Repository.php';
        return $repoFilePath;
    }

    protected function getRepoClassName($modelName)
    {
        return $modelName . 'Repository';
    }

    protected function getRepoClassNameWithNamespace($modelName)
    {
        return 'App\Repositories\\' . $this->getRepoClassName($modelName);
    }

    protected function getColumnNames($structure, $options = '')
    {
        if (is_array($options) && is_array($options['disableFields'])) {
            foreach($options['disableFields'] as $fieldName) {
                unset($structure[$fieldName]);
            }
        }

        $columnNames = [];
        foreach($structure as $key => $value) {
            $columnNames[] = $key;
        }
        return $columnNames;
    }
}
