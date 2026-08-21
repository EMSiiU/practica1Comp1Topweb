<?php
declare(strict_types=1);

// Carga variables de entorno desde .env
function cargarEnv(string $ruta): void
{
    if (!file_exists($ruta)) {
        return;
    }

    foreach (file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
        $linea = trim($linea);

        if ($linea === '' || str_starts_with($linea, '#')) {
            continue; //ignorar líneas vacías
        }

        [$clave, $valor] = array_pad(explode('=', $linea, 2), 2, '');
        $clave = trim($clave);
        $valor = trim($valor, " \t\n\r\0\x0B\"'");

        if ($clave !== '' && getenv($clave) === false) {
            putenv("$clave=$valor");
        }
    }
}

cargarEnv(__DIR__ . '/../.env');

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3308');
define('DB_NAME', getenv('DB_NAME') ?: 'miapp_db');
define('DB_USER', getenv('DB_USER') ?: 'miapp_user');
define('DB_PASS', getenv('DB_PASS') ?: '');

function getConexion(): PDO
{
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, DB_USER, DB_PASS, $opciones);
    } catch (PDOException $e) {
        error_log('Error de conexión a la base de datos: ' . $e->getMessage());
        die('No fue posible conectar con la base de datos. Intenta más tarde.');
    }
}