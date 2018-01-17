<?php
namespace RobinTheHood\PhpFramework\Views;

use RobinTheHood\PhpFramework\Views\StandardView;

class ModelView extends StandardView
{
    public function __construct($stdTemplate)
    {
        $this->setTemplate($stdTemplate);
    }

    public function show()
    {

    }

    public function showModel($options)
    {
        if (!$options['showTemplate']) {
            $this->loadTmplVar('CONTENT', '/app/Templates/haml/Default/Show.haml');
        } else {
            $this->loadTmplVar('CONTENT', $options['showTemplate']);
        }
        $this->showView();
    }

    public function showModelIndex($options)
    {
        if (!$options['indexTemplate']) {
            $this->loadTmplVar('CONTENT', '/app/Templates/haml/Default/Index.haml');
        } else {
            $this->loadTmplVar('CONTENT', $options['indexTemplate']);
        }
        $this->showView();
    }

    public function showModelNew($options)
    {
        if (!$options['newTemplate']) {
        $this->loadTmplVar('CONTENT', '/app/Templates/haml/Default/NewEdit.haml');
        } else {
            $this->loadTmplVar('CONTENT', $options['newTemplate']);
        }
        $this->showView();
    }

    public function showModelEdit($options)
    {
        if (!$options['editTemplate']) {
            $this->loadTmplVar('CONTENT', '/app/Templates/haml/Default/NewEdit.haml');
        } else {
            $this->loadTmplVar('CONTENT', $options['editTemplate']);
        }
        $this->showView();
    }

    public function showModelMultiEdit($options)
    {
        if (!$options['multiEditTemplate']) {
            $this->loadTmplVar('CONTENT', '/app/Templates/haml/Default/MultiEdit.haml');
        } else {
            $this->loadTmplVar('CONTENT', $options['multiEditTemplate']);
        }
        $this->showView();
    }
}
