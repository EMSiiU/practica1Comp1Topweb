<?php
class ApiToken {
    private $conn;
    private $table_name = "api_tokens";

    public $id;
    public $user_id;
    public $token;
    public $expires_at;
    public $revoked;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function invalidatePreviousTokens() {
        // Invalidar tokens anteriores del mismo usuario
        $query = "UPDATE " . $this->table_name . " 
                SET revoked = 1 
                WHERE user_id = :user_id AND revoked = 0";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->user_id);
        return $stmt->execute();
    }

    public function create() {
        // Insertar el registro asociado al user_id
        $query = "INSERT INTO " . $this->table_name . " 
                SET user_id=:user_id, token=:token, expires_at=:expires_at";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":token", $this->token);
        $stmt->bindParam(":expires_at", $this->expires_at);

        return $stmt->execute();
    }

    public function validateToken() {
        // Verificar que el token exista, no esté revocado y no haya expirado[cite: 1]
        $query = "SELECT user_id FROM " . $this->table_name . " 
                WHERE token = ? AND revoked = 0 AND expires_at > NOW() LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->token);
        $stmt->execute();

        return $stmt;
    }

    public function revokeToken() {
        // Marcar token actual como revocado para el logout[cite: 1]
        $query = "UPDATE " . $this->table_name . " 
                SET revoked = 1 
                WHERE token = :token";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $this->token);
        return $stmt->execute();
    }
}
?>