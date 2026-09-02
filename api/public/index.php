<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../resources/v1/UserResource.php';

$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));

$router = new Router('v1', $basePath);
$userResource = new UserResource();

// rutas
$router->addRoute('GET', '/users', [$userResource, 'index']);
$router->addRoute('GET', '/users/{id}', [$userResource, 'show']);
$router->addRoute('POST', '/users', [$userResource, 'store']);
$router->addRoute('PUT', '/users/{id}', [$userResource, 'update']);
$router->addRoute('DELETE', '/users/{id}', [$userResource, 'destroy']);

$router->dispatch();
?>