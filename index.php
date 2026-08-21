<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Location: ' . (!empty($_SESSION['usuario_id']) ? '/crud/list.php' : '/login.php'));
exit;


