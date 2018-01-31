<?php
namespace RobinTheHood\PhpFramework\Object;

use RobinTheHood\NamingConvention\NamingConvention;
use RobinTheHood\PhpFramework\Button;
use RobinTheHood\PhpFramework\Request;
use RobinTheHood\PhpFramework\ArrayHelper;
use RobinTheHood\PhpFramework\ValueParser;

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
        $post = Request::postAll();

        if (!empty($post[$className]) && !empty($post[$className][0])) {
            return $post[$className][0];
        }
        return $post;
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

    public static function getAllFromPost($className, $fieldTypes = [])
    {
        $objs = [];

        $post = Request::postAll();
        $arrays = ArrayHelper::getIfSet($post, $className, []);

        foreach($arrays as &$array) {
            if (ArrayHelper::getIfSet($array, 'multiEditAction') != 'delete') {
                $objClassNameWithNamespace = 'App\\Models\\' . $className;
                $obj = new $objClassNameWithNamespace();
                $obj->loadFromArray($array, $fieldTypes);
                $objs[] = $obj;
            }
        }
        return $objs;
    }
}
