<?php
namespace RobinTheHood\PhpFramework\Html;

use RobinTheHood\PhpFramework\Html\HtmlObject;
use RobinTheHood\PhpFramework\Button;
use RobinTheHood\Html\HtmlInput;
use RobinTheHood\Html\HtmlLabel;

class HtmlObjectForm extends HtmlObject
{
    protected $options;
    protected $index;

    public function __construct($object, $options)
    {
        $this->object = $object;
        $this->options = $options;
        $this->index = 0;
    }

    public function setIndex($index)
    {
        $this->index = (int) $index;
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function getAction($values = [])
    {
        return (new Button)->change($values);
    }

    public function isEdit()
    {
        return $this->object->getId() != -1;
    }

    public function get($varName, $attributes = [])
    {
        $type = !empty($this->options['fieldTypes'][$varName]) ? $this->options['fieldTypes'][$varName] : null;
        if ($type == 'hidden') {
            return $this->getHidden($varName);
        } elseif ($type == 'text') {
            return $this->getText($varName, $attributes);
        } elseif ($type == 'datetime') {
            return $this->getDateTime($varName, $attributes);
        } elseif ($type == 'float') {
            return $this->getFloat($varName, $attributes);
        } elseif ($type == 'percent') {
            return $this->getPercent($varName, $attributes);
        } elseif ($type == 'currency') {
            return $this->getCurrency($varName, $attributes);
        } elseif ($type == 'password') {
            return $this->getPassword($varName, $attributes);
        } elseif ($type == 'select') {
            return $this->getSelect($varName, $attributes);
        }
        return $this->getString($varName, $attributes);
    }

    public function createHidden($name)
    {
        $htmlInput = $this->createHtmlInput('input', $name, [
            'type' => 'hidden',
        ]);

        return $htmlInput->render();
    }

    public function getHidden($varName)
    {
        $value = $this->getFormatedValue($varName, 'string');
        $labelText = $this->getLabelText($varName);

        $htmlInput = $this->createHtmlInput('input', $varName, [
            'type' => 'hidden',
            'value' => $value,
        ]);

        return $htmlInput->render();
    }

    public function getLabel($varName)
    {
        $labelText = $this->getLabelText($varName);

        $htmlLabel = $this->createHtmlLabel($labelText, $varName);
        return $htmlLabel->render();
    }

    public function getString($varName, $attributes)
    {
        $value = $this->getFormatedValue($varName, 'string');
        $labelText = $this->getLabelText($varName);

        $htmlInput = $this->createHtmlInput('input', $varName, [
            'type' => 'text',
            'value' => $value,
            'placeholder' => $labelText
        ]);
        $htmlInput->setAttributes($attributes);

        return $htmlInput->render();
    }

    public function getText($varName, $attributes)
    {
        $value = $this->getFormatedValue($varName, 'string');
        $labelText = $this->getLabelText($varName);

        $htmlInput = $this->createHtmlInput('textarea', $varName, [
            'value' => $value,
            'placeholder', $labelText
        ]);
        $htmlInput->setAttributes($attributes);

        return $htmlInput->render();
    }

    public function getDateTime($varName, $attributes)
    {
        $value = $this->getFormatedValue($varName, 'datetime');
        $labelText = $this->getLabelText($varName);

        $htmlInput = $this->createHtmlInput('input', $varName, [
            'type' => 'datetime-local',
            'value' => $value,
            'placeholder', $labelText
        ]);
        $htmlInput->setAttributes($attributes);

        return $htmlInput->render();
    }

    public function getDate($varName, $attributes)
    {
        $value = $this->getFormatedValue($varName, 'date');
        $labelText = $this->getLabelText($varName);

        $htmlInput = $this->createHtmlInput('input', $varName, [
            'type' => 'date',
            'value' => $value,
            'placeholder', $labelText
        ]);
        $htmlInput->setAttributes($attributes);

        return $htmlInput->render();
    }

    public function getTime($varName, $attributes)
    {
        $value = $this->getFormatedValue($varName, 'time');
        $labelText = $this->getLabelText($varName);

        $htmlInput = $this->createHtmlInput('input', $varName, [
            'type' => 'time',
            'value' => $value,
            'placeholder', $labelText
        ]);
        $htmlInput->setAttributes($attributes);

        return $htmlInput->render();
    }

    public function getPercent($varName, $attributes)
    {
        $value = $this->getFormatedValue($varName, 'percent');
        $labelText = $this->getLabelText($varName);

        $htmlInput = $this->createHtmlInput('input', $varName, [
            'type' => 'text',
            'value' => $value,
            'placeholder', $labelText
        ]);
        $htmlInput->setAttributes($attributes);

        return $htmlInput->render();
    }

    public function getCurrency($varName, $attributes)
    {
        $value = $this->getFormatedValue($varName, 'currency');
        $labelText = $this->getLabelText($varName);

        $htmlInput = $this->createHtmlInput('input', $varName, [
            'type' => 'text',
            'value' => $value,
            'placeholder', $labelText
        ]);
        $htmlInput->setAttributes($attributes);

        return $htmlInput->render();
    }

    public function getFloat($varName, $attributes)
    {
        $value = $this->getFormatedValue($varName, 'float');
        $labelText = $this->getLabelText($varName);

        $htmlInput = $this->createHtmlInput('input', $varName, [
            'type' => 'text',
            'value' => $value,
            'placeholder', $labelText
        ]);
        $htmlInput->setAttributes($attributes);

        return $htmlInput->render();
    }

    public function getPassword($varName, $attributes)
    {
        $value = $this->getFormatedValue($varName, 'string');
        $labelText = $this->getLabelText($varName);

        $htmlInput = $this->createHtmlInput('input', $varName, [
            'value' => $value,
            'type' => 'password',
            'placeholder', $labelText
        ]);
        $htmlInput->setAttributes($attributes);

        return $htmlInput->render();
    }

    public function getSelect($varName, $attributes)
    {
        $value = $this->getFormatedValue($varName, 'string');
        $values = $this->options['fieldValues'][$varName];

        $htmlInput = $this->createHtmlInput('select', $varName, [
            'value' => $value
        ]);
        $htmlInput->setAttributes($attributes);
        $htmlInput->setValues($values);

        return $htmlInput->render();
    }

    private function createHtmlInput($tagName, $varName, $attributes)
    {
        $id = $this->getAttributeId($varName);
        $class = $this->getAttributeClass($varName);
        $name = $this->getAttributeName($varName);

        $htmlInput = new HtmlInput($tagName, [
            'id' => $id,
            'class' => $class,
            'name' => $name,
        ]);
        $htmlInput->setAttributes($attributes);

        return $htmlInput;
    }

    private function createHtmlLabel($labelText, $varName)
    {
        $name = $this->getAttributeName($varName);
        $htmlLabel = new HtmlLabel($labelText, [
            'for' => $name
        ]);
        return $htmlLabel;
    }

    public function getLabelText($varName)
    {
        return !empty($this->options['fieldNames'][$varName]) ? $this->options['fieldNames'][$varName] : $varName;
    }
}
