<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Manejo de peticiones preflight (CORS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../core/Router.php';
$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));

// Capturar la URI actual
$requestUri = $_SERVER['REQUEST_URI'];

// Enrutamiento v1
if (strpos($requestUri, '/v1/') !== false) {
    require_once __DIR__ . '/../resources/v1/UserResource.php';
    
    $router = new Router('v1', $basePath);
    $userResource = new UserResource();

    // Rutas v1
    $router->addRoute('GET', '/users', [$userResource, 'index']);
    $router->addRoute('GET', '/users/{id}', [$userResource, 'show']);
    $router->addRoute('POST', '/users', [$userResource, 'store']);
    $router->addRoute('PUT', '/users/{id}', [$userResource, 'update']);
    $router->addRoute('DELETE', '/users/{id}', [$userResource, 'destroy']);

    $router->dispatch();
} 
// Enrutamiento v2
elseif (strpos($requestUri, '/v2/') !== false) {
    require_once __DIR__ . '/../resources/v2/AuthResource.php';
    
    $router = new Router('v2', $basePath);
    $authResource = new AuthResource();

    // Rutas v2
    $router->addRoute('POST', '/login', [$authResource, 'login']);
    $router->addRoute('POST', '/logout', [$authResource, 'logout']);
    $router->addRoute('GET', '/me', [$authResource, 'me']);

    $router->dispatch();
}
// V3
elseif (strpos($requestUri, '/v3/') !== false) {
    require_once __DIR__ . '/../resources/v3/TaskResource.php';
    
    $router = new Router('v3', $basePath);
    $taskResource = new TaskResource();

    $router->addRoute('GET', '/tareas', [$taskResource, 'getAllTasks']);
    $router->addRoute('POST', '/tareas', [$taskResource, 'createTask']);
    $router->addRoute('GET', '/tareas/{id}', [$taskResource, 'getTask']);
    $router->addRoute('PUT', '/tareas/{id}', [$taskResource, 'updateTask']);

    $router->dispatch();
}  
// Ruta no encontrada
else {
    http_response_code(404);
    echo json_encode(["message" => "Endpoint o version de API no encontrada"]);
}
?>
