<?php
require_once __DIR__ . '/../Controllers/Controller.php';

// Autoload simple: busca la clase en Controllers/ o Models/
spl_autoload_register(function ($clase) {
    $rutas = [__DIR__ . "/../Controllers/$clase.php", __DIR__ . "/../Models/$clase.php"];
    foreach ($rutas as $ruta) {
        if (file_exists($ruta)) {
            require_once $ruta;
            return;
        }
    }
});

// Parseo de la URL: /controlador/accion/parametro
$url = trim($_SERVER['REQUEST_URI'], '/');
$url = explode('?', $url)[0];
$partes = $url === '' ? [] : explode('/', $url);

$nombreControlador = !empty($partes[0]) ? ucfirst($partes[0]) . 'Controller' : 'AuthController';
$accion            = $partes[1] ?? 'index';
$parametro          = $partes[2] ?? null;

if (!class_exists($nombreControlador)) {
    http_response_code(404);
    die("Página no encontrada.");
}

$controlador = new $nombreControlador();

if (!method_exists($controlador, $accion)) {
    http_response_code(404);
    die("Acción no encontrada.");
}

$parametro !== null ? $controlador->$accion($parametro) : $controlador->$accion();