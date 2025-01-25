<?php

namespace RobinTheHood\PhpFramework\Object;

use RobinTheHood\PhpFramework\ValueFormater;

class ObjectValueFormater extends ValueFormater
{
    private $object;

    public function __construct($object, array $types = [])
    {
        $this->object = $object;
        $this->types = $types;
    }

    protected function getValue($varName)
    {
        return $this->object->get($varName);
    }
}
