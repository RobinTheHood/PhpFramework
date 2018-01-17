<?php
namespace RobinTheHood\PhpFramework;

use RobinTheHood\NamingConvention\NamingConvention;


class Button
{
    protected $values = [];

    public function __construct(array $values = [])
    {
        if ($values) {
            $this->values = $values;
        } else {
            $this->values = $_GET;
        }
    }

    public function change(array $values = [])
    {
        $button = clone $this;

        foreach($values as $index => $value)
        {
            $button->values[$index] = $value;
            if ($value === null) {
                unset($button->values[$index]);
            }
        }
        return $button;
    }

    public function createUrl()
    {
        $values = $this->stdValuesToSnakeCase($this->values);
        $count = 0;
        $urlValues = '';
        foreach ($values as $key => $value) {
            if ($value != null) {
                if($count++) {
                    $urlValues .= '&';
                }
                $keySnakeCase = NamingConvention::camelCaseToSnakeCase($key);
                $urlValues .= $keySnakeCase .'=' . $value;
            }
        }
        $url = 'index.php?' . $urlValues;
        // if ($anker) {
        //     $url .= '#' . $anker;
        // }
        //
        // if ($domain) {
        //     $url = 'https://' . $domain . $url;
        // }

        return $url;
    }

    private function stdValuesToSnakeCase($values)
    {
        if (isset($values['app']) && $values['app']) {
            $values['app'] = NamingConvention::camelCaseToSnakeCase($values['app']);
        }
        if (isset($values['controller']) && $values['controller']) {
            $values['controller'] = NamingConvention::camelCaseToSnakeCase($values['controller']);
        }
        if (isset($values['action']) && $values['action']) {
            $values['action'] = NamingConvention::camelCaseToSnakeCase($values['action']);
        }
        return $values;
    }

    public function __toString()
    {
        return $this->createUrl();
    }
}
