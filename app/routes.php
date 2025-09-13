<?php
    $controller = $_GET['controller'] ?? 'Ventas';
    $action = $_GET['action'] ?? 'index';

    $controllerClass = $controller . "Controller";
    $controllerFile = "app/controllers/" . $controllerClass . ".php";

    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        $controllerObj = new $controllerClass();
        
        if (method_exists($controllerObj, $action)) {
            $controllerObj->$action();
        } else {
            die("Acción $action no encontrada.");
        }
    } else {
        die("Controlador $controller no encontrado.");
    }
