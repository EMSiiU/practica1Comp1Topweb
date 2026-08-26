<?php
// se incluye a todas las paginas del CRUD, si no existe una sesion, redirecciona al login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

