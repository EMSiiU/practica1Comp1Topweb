<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ApiToken.php';

class AuthMiddleware {
    public static function authenticate() {
        $headers = apache_request_headers();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : null;

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode([
                "error" => "unauthorized",
                "message" => "Token inválido, expirado o no proporcionado"
            ]);
            exit;
        }

        $token = $matches[1];

        $database = new Database();
        $db = $database->getConnection();
        $apiToken = new ApiToken($db);
        $apiToken->token = $token;
        
        $stmt = $apiToken->validateToken();

        if ($stmt->rowCount() == 0) {
            http_response_code(401);
            echo json_encode([
                "error" => "unauthorized",
                "message" => "Token inválido, expirado o no proporcionado"
            ]);
            exit;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            "user_id" => $row['user_id'],
            "token" => $token
        ];
    }
}
?>
