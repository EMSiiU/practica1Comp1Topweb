<?php
require_once __DIR__ . '/../config/db.php';

// se incluye a todas las paginas del CRUD, si no existe una sesion, redirecciona al login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . appUrl('login.php'));
    exit;
}

