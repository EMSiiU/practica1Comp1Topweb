<?php
class ApiUser {
    private $conn;
    private $table_name = "api_users";

    public $id;
    public $username;
    public $email;
    public $password_hash;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function findByUsername() {
        // Buscar usuario por nombre de usuario
        $query = "SELECT id, username, password_hash, status 
                FROM " . $this->table_name . " 
                WHERE username = ? LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->username);
        $stmt->execute();

        return $stmt;
    }

    public function findById() {
        // Buscar usuario por ID para el endpoint /me
        $query = "SELECT id, username, email, status, created_at 
                FROM " . $this->table_name . " 
                WHERE id = ? LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        return $stmt;
    }
}
?>