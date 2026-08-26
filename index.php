<?php
require_once __DIR__ . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Location: ' . BASE_URL . (!empty($_SESSION['usuario_id']) ? '/crud/list.php' : '/login.php'));
exit;