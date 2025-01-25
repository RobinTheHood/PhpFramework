<?php

namespace RobinTheHood\PhpFramework;

use RobinTheHood\DateTime\DateTime;

class ValueFormater
{
    private $values;
    private $types;

    public function __construct(array $values, array $types = [])
    {
        $this->values = $values;
        $this->types = $types;
    }

    public function get($varName, $type = null)
    {
        if (!$type) {
            $type = isset($this->types[$varName]) ? $this->types[$varName] : null;
        }

        switch ($type) {
            case 'string':
                return $this->getString($varName);
            case 'int':
                return $this->getInt($varName);
            case 'float':
                return $this->getFloat($varName);
            case 'percent':
                return $this->getPercent($varName);
            case 'datetime':
                return $this->getDateTime($varName);
            case 'date':
                return $this->getDate($varName);
            case 'time':
                return $this->getTime($varName);
            case 'currency':
                return $this->getCurrency($varName);
        }

        return $this->getString($varName);
    }

    protected function getValue($varName)
    {
        return $this->values[$varName];
    }

    public function getString($varName)
    {
        return $this->getValue($varName);
    }

    public function getInt($varName)
    {
        return $this->getValue($varName);
    }

    public function getFloat($varName)
    {
        $float = $this->getValue($varName);
        $str = str_replace('.', ',', $float);
        return $str;

        return $this->getValue($varName);
    }

    public function getPercent($varName)
    {
        $value = $this->getValue($varName);
        $str = number_format(((float) $value) * 100, 2, ',', '.');
        $str = rtrim($str, "0");
        $str = rtrim($str, ",");
        return $str . ' %';
    }

    public function getDateTime($varName)
    {
        return $this->getValue($varName);
    }

    public function getDate($varName)
    {
        $value = $this->getValue($varName);
        return DateTime::shortDate($value);
    }

    public function getTime($varName)
    {
        return $this->getValue($varName);
    }

    // 19.1 -> 19,10 €
    public function getCurrency($varName)
    {
        $value = $this->getValue($varName);
        $str = number_format((float) $value, 2, ',', '.');
        return $str . ' €';
    }
}
