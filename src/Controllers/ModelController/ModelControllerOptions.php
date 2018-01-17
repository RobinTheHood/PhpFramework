<?php
/*
*** enabledFields ***
    all:

*** autoFieldTypes ***
    on:

*** fieldNames ***
    [attributeName => labelName]

*** fieldValues ***
    [
        attributeName => [optionValue => labelText]
    ]

*** actions ***
    all:

*** viewClass ***
    Beschreibung

*** indexTemplate ***
    Beschreibung

*** showTemplate ***
    Beschreibung

*** newTemplate ***
    Beschreibung

*** editTemplate ***
    Beschreibung
*/

namespace RobinTheHood\PhpFramework\Controllers\ModelController;

use RobinTheHood\PhpFramework\App;
use RobinTheHood\PhpFramework\ArrayHelper;
use RobinTheHood\PhpFramework\Button;
use RobinTheHood\Debug\Debug;


class ModelControllerOptions
{
    protected $initOptions;

    public function __construct(array $options, $object, $modelController)
    {
        $this->init($options, $object, $modelController);
    }

    public function init(array $options, $object, $modelController)
    {
        $initOptions = $options;

        $this->initViewClass($initOptions, $modelController);
        $this->initFields($initOptions, $modelController);
        $this->initTemplates($initOptions, $modelController);
        $this->initActions($initOptions, $object, $modelController);

        $this->initOptions = $initOptions;
        return $initOptions;
    }

    public function getInitOptions()
    {
        return $this->initOptions;
    }

    protected function initViewClass(& $options, $modelController)
    {
        $appName = $modelController->getAppName();
        $viewClass = '\App\Views\Standard' . $appName . 'View';

        ArrayHelper::setIfUnset($options, 'viewClass', $viewClass);
    }

    protected function initTemplates(& $options, $modelController)
    {
        $appName = $modelController->getAppName();
        $modelName = $modelController->getModelName();

        $baseName = $appName . '/' . $modelName . 's/';

        $indexTemplate = $baseName . 'Index.html.twig';
        $showTemplate = $baseName . 'Show.html.twig';
        $newTemplate = $baseName . 'NewEdit.html.twig';
        $editTemplate = $baseName . 'NewEdit.html.twig';
        $multiEditTemplate = $baseName . 'MultiEdit.html.twig';
        $multiEditFormTemplate = $baseName . 'MultiEditForm.html.twig';

        $config = App::getConfig();

        if (!$this->templateExists($indexTemplate)) {
            $indexTemplate = $config['template']['index'];
        }

        if (!$this->templateExists($showTemplate)) {
            $showTemplate = $config['template']['show'];
        }

        if (!$this->templateExists($newTemplate)) {
            $newTemplate = $config['template']['new'];
        }

        if (!$this->templateExists($editTemplate)) {
            $editTemplate = $config['template']['edit'];
        }

        if (!$this->templateExists($multiEditTemplate)) {
            $multiEditTemplate = $config['template']['multiEdit'];
        }

        if (!$this->templateExists($multiEditFormTemplate)) {
            $multiEditFormTemplate = $config['template']['multiEditForm'];
        }

        ArrayHelper::setIfUnset($options, 'indexTemplate', $indexTemplate);
        ArrayHelper::setIfUnset($options, 'showTemplate', $showTemplate);
        ArrayHelper::setIfUnset($options, 'newTemplate', $newTemplate);
        ArrayHelper::setIfUnset($options, 'editTemplate', $newTemplate);
        ArrayHelper::setIfUnset($options, 'multiEditTemplate', $multiEditTemplate);
        ArrayHelper::setIfUnset($options, 'multiEditFormTemplate', $multiEditFormTemplate);
    }

    protected function templateExists($templateFile)
    {
        $config = App::getConfig();
        $templateBasePath = $config['twig']['templatesPath'];
        return file_exists($templateBasePath . '/' . $templateFile);
    }

    protected function initFields(& $options, $modelController)
    {
        $structure = $modelController->getStructure();

        // EnabledFields
        ArrayHelper::setIfUnset($options, 'enabledFields', []);

        // FieldTypes
        $options['fieldTypes']['id'] = 'hidden';
        $options['fieldTypes']['object'] = 'object';
        ArrayHelper::setIfUnset($options, 'fieldTypes', []);
        if ($options['autoFieldTypes'] == 'on') {
            foreach($structure as $columnName => $definition) {
                if (empty($options['fieldTypes'][$columnName])) {
                    if ($columnName == 'id' && $definition[0] == 'TYPE_INT') {
                        $options['fieldTypes'][$columnName] = 'hidden';
                    } elseif ($definition[0] == 'TYPE_TEXT') {
                        $options['fieldTypes'][$columnName] = 'text';
                    } elseif ($definition[0] == 'TYPE_FLOAT') {
                        $options['fieldTypes'][$columnName] = 'float';
                    } elseif ($definition[0] == 'TYPE_DATE_TIME') {
                        $options['fieldTypes'][$columnName] = 'datetime';
                    } elseif ($definition[0] == 'TYPE_INT' && $definition[1]) {
                        $options['fieldTypes'][$columnName] = 'select';
                    }
                }
            }
        }

        // FieldNames
        if (isset($options['disableFieldNames']) &&  $options['disableFieldNames'] == 'all') {
            $options['disableFieldNames'] = [];
            foreach($structure as $columnName => $definition) {
                $options['disableFieldNames'][$columnName] = true;
            }
        }

        //FieldAddons
        if (isset($options['autoFieldAdds']) && $options['autoFieldAdds'] == 'off') {
            $options['fieldAddons'] = [];
            foreach($structure as $columnName => $definition) {
                $options['fieldAddons'][$columnName] = false;
            }
        } else {
            $options['fieldAddons'] = [];
            foreach($structure as $columnName => $definition) {
                $options['fieldAddons'][$columnName] = true;
            }
        }
    }

    protected function initActions(& $options, $object, $modelController)
    {
        $modelName = $modelController->getModelName();
        $controller = $modelName . 's';
        $buttons = [];

        if (!isset($options['actions'])) {
            $options['actions'] = '';
        }

        if (isset($options['actions']) &&  $options['actions'] == 'all') {
            $options['actions'] = ['index', 'show', 'new', 'edit', 'delete', 'multiEdit'];
        } elseif (!is_array($options['actions'])) {
            $options['actions'] = [];
        }

        // Index
        if (in_array('index', $options['actions'])) {
            ArrayHelper::setIfUnset($options, 'indexVisible', true);
        }

        $buttons['modelIndex'] = (new Button)->change([
            'controller' => $controller,
            'action' => 'index'
        ]);

        //
        // ArrayHelper::setIfUnset($options, 'indexUrl', (new Button)->change([
        //     'controller' => $controller,
        //     'action' => 'index'
        // ]));

        ArrayHelper::setIfUnset($options, 'indexName', 'list');


        // Show
        if (in_array('show', $options['actions'])) {
            ArrayHelper::setIfUnset($options, 'showVisible', true);
        }

        if ($object) {
            $buttons['modelShow'] = (new Button)->change([
                'controller' => $controller,
                'action' => 'show',
                'id' => $object->getId()
            ]);
        }

        ArrayHelper::setIfUnset($options, 'showName', 'show');


        // New
        if (in_array('new', $options['actions'])) {
            ArrayHelper::setIfUnset($options, 'newVisible', true);
        }

        $buttons['modelNew'] = (new Button)->change([
            'controller' => $controller,
            'action' => 'new',
            'id' => null
        ]);

        ArrayHelper::setIfUnset($options, 'newName', 'new ' . $modelName);


        // Edit
        if (in_array('edit', $options['actions'])) {
            ArrayHelper::setIfUnset($options, 'editVisible', true);
        }

        if ($object) {
            $buttons['modelEdit'] = (new Button)->change([
                'controller' => $controller,
                'action' => 'edit',
                'id' => $object->getId()
            ]);
        }

        ArrayHelper::setIfUnset($options, 'editName', 'edit');


        // Delete
        if (in_array('delete', $options['actions'])) {
            ArrayHelper::setIfUnset($options, 'deleteVisible', true);
        }

        if ($object) {
            $buttons['modelDelete'] = (new Button)->change([
                'controller' => $controller,
                'action' => 'delete',
                'id' => $object->getId()
            ]);
        }

        ArrayHelper::setIfUnset($options, 'deleteName', 'delete');

        // MultiEdit
        if (in_array('multiEdit', $options['actions'])) {
            ArrayHelper::setIfUnset($options, 'multiEditVisible', true);
        }

        $buttons['modelMultiEdit'] = (new Button())->change([
            'controller' => $controller,
            'action' => 'multiEdit'
        ]);

        ArrayHelper::setIfUnset($options, 'multiEditName', 'multi edit');
        ArrayHelper::setIfUnset($options, 'multiEditSaveName', 'save');

        // MultiEditAdd
        $buttons['modelMultiEditAdd'] = (new Button())->change([
            'controller' => $controller,
            'action' => 'multiEditAdd'
        ]);

        $options['buttons'] = $buttons;
    }
}
