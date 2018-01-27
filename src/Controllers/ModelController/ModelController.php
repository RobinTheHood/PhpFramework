<?php
namespace RobinTheHood\PhpFramework\Controllers\ModelController;

use RobinTheHood\PhpFramework\Button;
use RobinTheHood\PhpFramework\Controllers\ModelController\ModelControllerBase;
use RobinTheHood\PhpFramework\Html\HtmlObjectForm;
use RobinTheHood\PhpFramework\Session;
use RobinTheHood\PhpFramework\ValueFormater;
use RobinTheHood\PhpFramework\Request;
use RobinTheHood\PhpFramework\ArrayHelper;

/*
get:
    + invokeModelIndex
    + invokeModelShow
    + invokeModelEdit

modify:
    + invokeModelIndex
    + invokeModelShow
    + invokeModelNew
    + invokeModelEdit

postModify:
    + invokeModelNew
    + invokeModelEdit

postDone
    + invokeModelNew
    + invokeModelEdit

render:
    + invokeModelIndex
    + invokeModelShow
    + invokeModelNew
    + invokeModelEdit
*/

class ModelController extends ModelControllerBase
{
    public function __construct($objClassName = '')
    {
        parent::__construct($objClassName);
    }

    public function invokeModelIndex($options = '', $functions = [])
    {
        $this->init($options);

        if (!empty($functions['get'])) {
            $objs = $functions['get']();
        } else {
            $objs = $this->repo->getAll();
        }

        $rows = [];
        foreach($objs as $obj) {
            $row['model'] = $obj;
            $row['values'] = $this->getObjAttValues($obj, $this->filteredStructure);

            if (!empty($functions['modify'])) {
                $row['values'] = $functions['modify']($row['values'], $obj);
            }

            $row['valueFormater'] = new ValueFormater($row['values'], $this->options['fieldTypes']);

            $rows[] = $row;
        }

        $templateVars = [
            'controller' => $this->getControllerTemplateVars(),
            'rows' => $rows
        ];

        $this->render($functions, 'indexTemplate', $templateVars);

        return $templateVars;
    }

    public function invokeModelShow($options = '', $functions = [])
    {
        if (!empty($functions['get'])) {
            $obj = $functions['get']();
        } else {
            $obj = $this->repo->get($this->getId());
            if (!$obj) {
                die('Object not found.');
            }
        }

        $this->init($options, $obj);

        $values = $this->getObjAttValues($obj, $this->filteredStructure);

        if (!empty($functions['modify'])) {
            $values = $functions['modify']($values, $obj);
        }

        $valueFormater = new ValueFormater($values, $this->options['fieldTypes']);

        if (Request::get('format') === 'json') {
            $temp = json_encode($values);
            echo str_replace('\u0000', '', $temp);
            return;
        }

        $templateVars = [
            'controller' => $this->getControllerTemplateVars(),
            'valueFormater' => $valueFormater,
            'values' => $values,
            'model' => $obj
        ];

        $this->render($functions, 'showTemplate', $templateVars);

        return $templateVars;
    }

    public function invokeModelNew($options = '', $functions = [])
    {
        $this->init($options);

        $objClassNameWithNamespace = 'App\\Models\\' . $this->modelName;
        $obj = new $objClassNameWithNamespace();

        if (!empty($functions['modify'])) {
            $obj = $functions['modify']($obj);
        }

        if ($this->isPostRequest()) {
            $obj->loadFromPOST($this->options['fieldTypes']);

            if (!empty($functions['postModify'])) {
                $obj = $functions['postModify']($obj);
            }

            $id = $this->repo->add($obj);
            $obj->setId($id);

            if (!empty($functions['postDone'])) {
                $functions['postDone']($obj);
            } else {
                $this->redirect(new Button([
                    'app' => 'customer',
                    'controller' => $this->modelName . 's'
                ]));
            }
        }

        $templateVars = [
            'controller' => $this->getControllerTemplateVars(),
            'form' => new HtmlObjectForm($obj, $this->options),
            'model' => $obj
        ];

        $this->render($functions, 'newTemplate', $templateVars);

        return $templateVars;
    }

    public function invokeModelEdit($options = '', $functions = [])
    {
        if (!empty($functions['get'])) {
            $obj = $functions['get']();
        } else {
            $obj = $this->repo->get($this->getId());
            if (!$obj) {
                die('Object not found.');
            }
        }

        $this->init($options, $obj);

        if (!empty($functions['modify'])) {
            $obj = $functions['modify']($obj);
        }

        if ($this->isPostRequest()) {
            $obj->loadFromPost($this->options['fieldTypes']);

            if (!empty($functions['postModify'])) {
                $obj = $functions['postModify']($obj);
            }

            $this->repo->update($obj);

            if (!empty($functions['postDone'])) {
                $functions['postDone']($obj);
            } else {
                $this->redirect(new Button([
                    'app' => 'customer',
                    'controller' => $this->modelName . 's'
                ]));
            }
        }

        $templateVars = [
            'controller' => $this->getControllerTemplateVars(),
            'form' => new HtmlObjectForm($obj, $this->options),
            'model' => $obj
        ];

        $this->render($functions, 'editTemplate', $templateVars);

        return $templateVars;
    }

    public function invokeModelMultiEdit($options = '', $functions = [])
    {
        $this->init($options);

        if ($this->isPostRequest()) {
            $post = Request::postAll();
            $arrays = ArrayHelper::getIfSet($post, $this->modelName, []);

            foreach($arrays as &$array) {
                if (!empty($functions['postDefault'])) {
                    $array = $functions['postDefault']($array);
                }

                if ($array['id'] != -1) {
                    $obj = $this->repo->get($array['id']);
                    if ($obj) {
                        if (!empty($array['multiEditAction']) && $array['multiEditAction'] == 'delete') {
                            $id = $this->repo->delete($obj);
                        } else {
                            if (!empty($functions['postEdit'])) {
                                $array = $functions['postEdit']($array);
                            }
                            $obj->loadFromArray($array, $this->options['fieldTypes']);
                            $id = $this->repo->update($obj);
                        }
                    }
                } else {
                    if (isset($array['multiEditAction']) && $array['multiEditAction'] != 'delete') {
                        if (!empty($functions['postNew'])) {
                            $array = $functions['postNew']($array);
                        }
                        $objClassNameWithNamespace = 'App\\Models\\' . $this->modelName;
                        $obj = new $objClassNameWithNamespace();
                        $obj->loadFromArray($array, $this->options['fieldTypes']);
                        $id = $this->repo->add($obj);
                    }
                }
            }
            if (!empty($functions['postDone'])) {
                $functions['postDone']();
            } else {
                $this->redirect((new Button())->change());
            }
        }

        if (!empty($functions['get'])) {
            $objs = $functions['get']();
        } else {
            $objs = $this->repo->getAll();
        }

        Session::setValue($objs, 'objs', 'ModelMultiEdit' . $this->modelName);

        $forms = [];
        $index = 0;
        foreach($objs as $obj) {
            $form = new HtmlObjectForm($obj, $this->options);
            $form->setIndex($index++);
            $forms[] = $form;
        }

        $templateVars = [
            'controller' => $this->getControllerTemplateVars(),
            'forms' => $forms
        ];

        $this->render($functions, 'multiEditTemplate', $templateVars);

        return $templateVars;
    }

    public function invokeModelMultiEditAdd($options = '', $functions = [])
    {
        $this->init($options);

        $objClassNameWithNamespace = 'App\\Models\\' . $this->modelName;
        $obj = new $objClassNameWithNamespace();

        if (!empty($functions['new'])) {
            $obj = $functions['new']($obj);
        }

        $objs = Session::getValue('objs', 'ModelMultiEdit' . $this->modelName);
        $objs[] = $obj;
        Session::setValue($objs, 'objs', 'ModelMultiEdit' . $this->modelName);

        $form = new HtmlObjectForm($obj, $this->options);
        $form->setIndex(count($objs)-1);

        $templateVars = [
            'controller' => $this->getControllerTemplateVars(),
            'form' => $form
        ];

        $this->render($functions, 'multiEditFormTemplate', $templateVars);

        return $templateVars;
    }

    public function invokeModelDelete($options = '', $functions = [])
    {
        if (!empty($functions['get'])) {
            $obj = $functions['get']();
        } else {
            $obj = $this->repo->get($this->getId());
            if (!$obj) {
                die('Object not found.');
            }
        }

        $this->init($options, $obj);

        $delete = true;
        if (!empty($functions['check'])) {
            $delete = $functions['check']($obj);
        }

        if (!$delete) {
            return false;
        }

        $this->repo->delete($obj);

        if (!empty($functions['postDone'])) {
            $functions['postDone']();
        } else {
            $this->redirect((new Button)->change([
                'controller' => $this->modelName . 's',
                'action' => 'index'
            ]));
        }
    }
}
