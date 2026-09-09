<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../resources/v1/PasswordResource.php';
require_once __DIR__ . '/../resources/v1/AuthResource.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$router = new Router();
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

$passwordResource = new PasswordResource();
$authResource = new AuthResource();

$router->addRoute('POST', '/v1/passwords/generate', [$passwordResource, 'generate']);
$router->addRoute('POST', '/v1/passwords/validate', [$passwordResource, 'validate']);
$router->addRoute('GET', '/v1/passwords/policy', [$passwordResource, 'policy']);
$router->addRoute('POST', '/v1/auth/register', [$authResource, 'register']);
$router->addRoute('POST', '/v1/auth/login', [$authResource, 'login']);

$router->dispatch($requestMethod, $requestUri);
