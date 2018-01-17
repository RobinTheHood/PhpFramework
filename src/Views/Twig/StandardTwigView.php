<?php
namespace RobinTheHood\PhpFramework\Views;

use RobinTheHood\PhpFramework\Views\TwigView;
use RobinTheHood\PhpFramework\Session;

abstract class StandardTwigView extends TiwgView
{
    protected $flashTemplateFile = '/app/Templates/haml/Default/Flash.haml';
    protected $title;
    protected $description;
    protected $canonicalUrl;
    protected $prevUrl;
    protected $nextUrl;
    protected $imageUrls;

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setCanonical($canonicalUrl)
    {
        $this->canonicalUrl = $canonicalUrl;
    }

    public function setPagePrev($prevUrl)
    {
        $this->prevUrl = $prevUrl;
    }

    public function setPageNext($nextUrl)
    {
        $this->nextUrl = $nextUrl;
    }

    public function addImage($imageUrl)
    {
        $this->imageUrls[] = $imageUrl;
    }

    public function setFlashTemplate($template)
    {
        $this->flashTemplateFile = $template;
    }

    protected function addflashMessage()
    {
        $success = Session::dropValue('FlashSuccess', 'Flash');
        $warning = Session::dropValue('FlashWarning', 'Flash');
        $error = Session::dropValue('FlashError', 'Flash');
        $info = Session::dropValue('FlashInfo', 'Flash');
        $this->loadTmplVar('FLASH', $this->flashTemplateFile);

        $this->addHamlVar('flashSuccess', $success);
        $this->addHamlVar('flashError', $error);
        $this->addHamlVar('flashWarning', $warning);
        $this->addHamlVar('flashInfo', $info);
    }

    protected function addMeta()
    {
        $this->addTmplVar('HTML_HEAD_TITLE',                $this->title);
        $this->addTmplVar('HTML_HEAD_ROBOT',                $this->robot);
        $this->addTmplVar('HTML_HEAD_DESCRIPTION',          $this->description);
        $this->addTmplVar('HTML_HEAD_KEYWORDS',             $this->keywords);
    }

    public function display()
    {
        $this->addMeta();
        $this->addflashMessage();
        parent::showView($debug);
        $this->cache();
    }
}
