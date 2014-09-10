<?php
    require_once('config.php'); // configuration values
    require_once('loader.php'); // spl_autoload_register

    // default module and action
    $module = 'addresses';
    $action = 'get';

    $rqst = new Request();
    if($uri = $rqst->getUrlElements())
    {
        $action = $rqst->getRequestMethod();
        $module = ucfirst($uri[0]);
        if(strlen($uri[1])>0)
            $params = $uri[1];
    }

    if(!file_exists('./controllers/' . $module . 'Controller.php'))
    {
        $module = 'Error';
        $action = 'index';
    }

    $classname = $module.'Controller';
    $methodname = $action.'Action';
    if(!class_exists($classname) || !method_exists($classname, $methodname))
    {
        $classname = 'ErrorController';
        $methodname = 'indexAction';
    }

    try {
        $object = new $classname();
        $object->$methodname($params);
    } catch (Exception $e) {
        echo json_encode(array(
            'error'=>true,
            'errorMessage'=>$e->getMessage(),
        ));
    }


