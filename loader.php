<?php

function testAutoload($className)
{
    $dirs = array('core', 'models', 'controllers');
    $found = false;

    foreach($dirs as $dir)
    {
        $fileName = dirname(__FILE__).'/'.$dir.'/'.$className.'.php';
        if(file_exists($fileName))
        {
            require_once($fileName);
            $found = true;
            break;
        }
    }

    return $found;
}

spl_autoload_register('testAutoload');