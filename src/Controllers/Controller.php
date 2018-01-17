<?php
namespace RobinTheHood\PhpFramework\Controllers;

use RobinTheHood\PhpFramework\Button;
use RobinTheHood\PhpFramework\Redirect;
use RobinTheHood\Debug\Debug;

abstract class Controller
{
    public function preInvoke()
    {

    }

    public function postInvoke()
    {

    }

    public function invoke404()
    {
        Debug::error('404 Page not found.');
    }

    public function redirect(Button $button)
    {
        Redirect::redirect($button);
    }
}
