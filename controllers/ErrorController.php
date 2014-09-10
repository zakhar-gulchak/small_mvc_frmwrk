<?php

class ErrorController
{
    public function indexAction()
    {
        header('HTTP/1.1 400 Bad Request');
    }
}
