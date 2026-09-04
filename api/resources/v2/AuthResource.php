<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/ApiUser.php';
require_once __DIR__ . '/../../models/ApiToken.php';
require_once __DIR__ . '/../../core/AuthMiddleware.php';

class AuthResource {
    private $db;
    private $user;
    private $apiToken;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new ApiUser($this->db);
        $this->apiToken = new ApiToken($this->db);
    }

    // POST /login
    public function login() {
        header("Content-Type: application/json");
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->username) || empty($data->password)) {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos"]);
            return;
        }

        $this->user->username = $data->username;
        $stmt = $this->user->findByUsername();

        // Si las credenciales son inválidas, mensaje genérico
        $genericError = [
            "error" => "invalid_credentials",
            "message" => "Usuario o contraseña incorrectos"
        ];

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Validar que el usuario exista y status = 'ACTIVE'
            if ($row['status'] !== 'ACTIVE' || !password_verify($data->password, $row['password_hash'])) {
                http_response_code(401);
                echo json_encode($genericError);
                return;
            }

            // Invalidar tokens anteriores]
            $this->apiToken->user_id = $row['id'];
            $this->apiToken->invalidatePreviousTokens();

            // Generar token (Mínimo 32 bytes, hex)
            $token = bin2hex(random_bytes(32)); 
            
            // Definir expiración (ej. 60 minutos)
            $expiration_time = 60; 
            $expires_at = date('Y-m-d H:i:s', strtotime("+$expiration_time minutes"));

            $this->apiToken->token = $token;
            $this->apiToken->expires_at = $expires_at;

            if ($this->apiToken->create()) {
                http_response_code(200);
                echo json_encode([
                    "access_token" => $token,
                    "token_type" => "Bearer",
                    "expires_at" => $expires_at
                ]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Error al generar el token"]);
            }
        } else {
            http_response_code(401); // 401 Unauthorized
            echo json_encode($genericError);
        }
    }

    // POST /logout
    public function logout() {
        header("Content-Type: application/json");
        $authData = AuthMiddleware::authenticate();

        $this->apiToken->token = $authData['token'];
        if ($this->apiToken->revokeToken()) {
            http_response_code(200);
            echo json_encode(["message" => "Sesión cerrada correctamente"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error al cerrar sesión"]);
        }
    }

    // GET /me
    public function me() {
        header("Content-Type: application/json");
        // Filtro de autenticación
        $authData = AuthMiddleware::authenticate(); 

        $this->user->id = $authData['user_id'];
        $stmt = $this->user->findById();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            http_response_code(200);
            echo json_encode([
                "id" => $row['id'],
                "username" => $row['username'],
                "email" => $row['email'],
                "status" => $row['status'],
                "created_at" => $row['created_at']
            ]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Usuario no encontrado"]);
        }
    }
}
?>