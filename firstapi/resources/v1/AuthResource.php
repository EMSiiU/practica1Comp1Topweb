<?php
require_once __DIR__ . '/../../config/database.php';

class AuthResource {
    private $dbConnection;

    public function __construct() {
        $db = new Database();
        $this->dbConnection = $db->getConnection();
    }

    // Método para el endpoint POST /auth/register
    public function register() {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        if (empty($input['username']) || empty($input['email']) || empty($input['password'])) {
            http_response_code(400);
            echo json_encode(["error" => "invalid_request", "message" => "Faltan datos obligatorios."]);
            return;
        }

        // Validación básica de política (Error 422 según el contrato)
        if (strlen($input['password']) < 12) {
            http_response_code(422);
            echo json_encode([
                "password" => $input['password'],
                "isValid" => false,
                "strength" => "muy_debil",
                "score" => 10,
                "rules" => [
                    ["rule" => "minLength", "description" => "Debe tener al menos 12 caracteres", "passed" => false]
                ]
            ]);
            return;
        }

        try {
            // Verificar si el usuario o correo ya existe
            $stmt = $this->dbConnection->prepare("SELECT id FROM api_users WHERE username = :username OR email = :email");
            $stmt->execute([':username' => $input['username'], ':email' => $input['email']]);
            
            if ($stmt->rowCount() > 0) {
                http_response_code(409);
                echo json_encode(["error" => "conflict", "message" => "El usuario o correo ya existe"]);
                return;
            }

            // Generar UUID v4 para el ID del usuario
            $userId = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );

            // Encriptar la contraseña de forma segura
            $passwordHash = password_hash($input['password'], PASSWORD_BCRYPT);
            $createdAt = date('Y-m-d H:i:s');
            $createdAtIso = date('Y-m-d\TH:i:s\Z');

            // Iniciar transacción
            $this->dbConnection->beginTransaction();

            // Insertar usuario
            $insertUser = $this->dbConnection->prepare("INSERT INTO users (id, username, email, password_hash, created_at) VALUES (:id, :username, :email, :hash, :created_at)");
            $insertUser->execute([
                ':id' => $userId,
                ':username' => $input['username'],
                ':email' => $input['email'],
                ':hash' => $passwordHash,
                ':created_at' => $createdAt
            ]);

            // Insertar en el historial de contraseñas
            $insertHistory = $this->dbConnection->prepare("INSERT INTO password_history (user_id, password_hash, created_at) VALUES (:user_id, :hash, :created_at)");
            $insertHistory->execute([
                ':user_id' => $userId,
                ':hash' => $passwordHash,
                ':created_at' => $createdAt
            ]);

            $this->dbConnection->commit();
            http_response_code(201);
            echo json_encode([
                "id" => $userId,
                "username" => $input['username'],
                "email" => $input['email'],
                "createdAt" => $createdAtIso
            ]);
        } catch (Exception $e) {
            $this->dbConnection->rollBack();
            http_response_code(500);
            echo json_encode([
                "error" => "server_error", 
                "message" => $e->getMessage()
            ]);
        }
    }

    public function login() {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        if (empty($input['username']) || empty($input['password'])) {
            http_response_code(400);
            echo json_encode(["error" => "invalid_request", "message" => "Faltan credenciales."]);
            return;
        }
        $stmt = $this->dbConnection->prepare("SELECT id, password_hash FROM users WHERE username = :username");
        $stmt->execute([':username' => $input['username']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar la contraseña contra el hash almacenado
        if ($user && password_verify($input['password'], $user['password_hash'])) {
            // Generar un token de acceso simulado
            $accessToken = base64_encode(random_bytes(32));
            
            // Respuesta 200 OK con esquema AuthTokenResponse
            http_response_code(200);
            echo json_encode([
                "accessToken" => $accessToken,
                "tokenType" => "Bearer",
                "expiresIn" => 3600
            ]);
        } else {
            http_response_code(401);
            echo json_encode(["error" => "unauthorized", "message" => "Credenciales inválidas"]);
        }
    }
}