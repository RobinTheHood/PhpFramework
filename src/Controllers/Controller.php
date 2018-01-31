<?php
namespace RobinTheHood\PhpFramework\Controllers;

use RobinTheHood\Debug\Debug;
use RobinTheHood\PhpFramework\Button;
use RobinTheHood\PhpFramework\Request;
use RobinTheHood\PhpFramework\Redirect;

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

    public function isPostRequest()
    {
        if (Request::server('REQUEST_METHOD') === 'POST') {
            return true;
        } else {
            return false;
        }
    }
}
