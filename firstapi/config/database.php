<?php
require_once __DIR__ . '/../../config/db.php';

class Database
{
    public function getConnection(): PDO
    {
        return getConexion();
    }
}
?>
