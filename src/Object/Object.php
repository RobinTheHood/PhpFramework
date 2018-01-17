<?php
namespace RobinTheHood\PhpFramework\Object;

use RobinTheHood\NamingConvention\NamingConvention;
use RobinTheHood\PhpFramework\ValueParser;
use RobinTheHood\PhpFramework\Button;

class Object
{
    public function getClassNameWithNamespace()
    {
        return get_class($this);
    }

    public function getClassName()
    {
        $classNameWithNameSpace = $this->getClassNameWithNamespace();
        $array = explode('\\', $classNameWithNameSpace);
        return end($array);
    }

    public function get($name)
    {
        $function = 'get' . ucfirst($name);
        return $this->$function();
    }

    public function loadFromArray($array, $fieldTypes = [])
    {
        $array = $this->parseArrayValues($array, $fieldTypes);
        foreach ($array as $key => $value) {
            $name = $key;
            $this->$name = $value;
        }
    }

    public function loadFromPost($fieldTypes = [])
    {
        $post = $this->filterPost();

        $array = [];
        foreach($post as $key => $value) {
            $camelCaseKey = NamingConvention::snakeCaseToCamelCase($key);
            $array[$camelCaseKey] = $value;
        }

        $this->loadFromArray($array, $fieldTypes);
    }

    private function filterPost()
    {
        $className = $this->getClassName();
        if (!empty($_POST[$className]) && !empty($_POST[$className][0])) {
            return $_POST[$className][0];
        }
        return $_POST;
    }

    private function parseArrayValues($array, $fieldTypes)
    {
        foreach ($array as $varName => $value) {
            //if ($fieldTypes[$varName] == 'password' && $value) {
            //    $array[$varName] = ValueParser::password($value);
            //} else {
            if (isset($fieldTypes[$varName])) {
                $array[$varName] = ValueParser::parse($value, $fieldTypes[$varName]);
            }
            //}
        }
        return $array;
    }

    public function getButton($action)
    {
        if ($action == 'modelShow') {
            return (new Button())->change([
                'controller' => $this->getClassName() . 's',
                'action' => 'show',
                'id' => $this->get('id')
            ]);
        } elseif ($action == 'modelEdit') {
            return (new Button())->change([
                'controller' => $this->getClassName() . 's',
                'action' => 'edit',
                'id' => $this->get('id')
            ]);

        } elseif ($action == 'modelDelete') {
            return (new Button())->change([
                'controller' => $this->getClassName() . 's',
                'action' => 'delete',
                'id' => $this->get('id')
            ]);

        }
    }
}
