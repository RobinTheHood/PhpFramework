<?php

namespace RobinTheHood\PhpFramework\Html;

use RobinTheHood\NamingConvention\NamingConvention;
use RobinTheHood\PhpFramework\Object\ObjectValueFormater;

class HtmlObject
{
    protected $object;

    public function getObj()
    {
        return $this->object;
    }

    public function getValue($varName)
    {
        if (is_object($this->object)) {
            return $this->object->get($varName);
        } else {
            return $this->object[$varName];
        }
    }

    public function getFormatedValue($varName, $type)
    {
        $objectValueFormater = new ObjectValueFormater($this->object);
        return $objectValueFormater->get($varName, $type);
    }

    public function getAttributeId($varName = '')
    {
        if ($varName) {
            return $this->object->getClassName() . '_' . $this->index . '_' . $varName;
        }
        return $this->object->getClassName() . '_' . $this->index;
    }

    public function getAttributeClass($varName)
    {
        return $this->object->getClassName() . '_' . $varName;
    }

    public function getAttributeName($varName)
    {
        $objectNameSnakeCase = NamingConvention::camelCaseToSnakeCase($this->object->getClassName());
        return $this->object->getClassName() . '[' . $this->index . '][' . $varName . ']';
    }
}
