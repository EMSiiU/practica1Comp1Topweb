<?php

class PasswordResource {
    public function generate() {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        
        $length = $input['length'] ?? 16;
        $includeUppercase = $input['includeUppercase'] ?? true;
        $includeLowercase = $input['includeLowercase'] ?? true;
        $includeNumbers = $input['includeNumbers'] ?? true;
        $includeSymbols = $input['includeSymbols'] ?? true;
        $excludeSimilar = $input['excludeSimilarCharacters'] ?? false;
        $excludeAmbiguous = $input['excludeAmbiguousSymbols'] ?? false;
        $count = $input['count'] ?? 1;
        // Validar que exista al menos un conjunto de caracteres
        if (!$includeUppercase && !$includeLowercase && !$includeNumbers && !$includeSymbols) {
            http_response_code(400);
            echo json_encode([
                "error" => "invalid_parameters",
                "message" => "Debe habilitar al menos un tipo de carácter (mayúsculas, minúsculas, números o símbolos)."
            ]);
            return;
        }
        
        $passwords = [];
        for ($i = 0; $i < $count; $i++) {
            $passwords[] = $this->generateSinglePassword(
                $length, $includeUppercase, $includeLowercase, 
                $includeNumbers, $includeSymbols, $excludeSimilar, $excludeAmbiguous
            );
        }

        http_response_code(200);
        echo json_encode([
            "passwords" => $passwords,
            "criteria" => [
                "length" => $length,
                "includeUppercase" => $includeUppercase,
                "includeLowercase" => $includeLowercase,
                "includeNumbers" => $includeNumbers,
                "includeSymbols" => $includeSymbols,
                "excludeSimilarCharacters" => $excludeSimilar,
                "excludeAmbiguousSymbols" => $excludeAmbiguous,
                "count" => $count
            ]
        ]);
    }

    // Método para el endpoint POST /passwords/validate
    public function validate() {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        if (empty($input['password'])) {
            http_response_code(400);
            echo json_encode([
                "error" => "invalid_request",
                "message" => "El campo 'password' es obligatorio."
            ]);
            return;
        }

        $password = $input['password'];
        $username = $input['username'] ?? ''; 

        $rules = [];
        $suggestions = [];
        $score = 0;

        // Regla: Longitud mínima
        $passedMinLength = strlen($password) >= 12;
        $rules[] = [
            "rule" => "minLength",
            "description" => "Debe tener al menos 12 caracteres",
            "passed" => $passedMinLength
        ];
        if (!$passedMinLength) $suggestions[] = "Aumenta la longitud a por lo menos 12 caracteres.";
        if ($passedMinLength) $score += 20;

        // Regla: Letras mayúsculas
        $passedUpper = preg_match('/[A-Z]/', $password) === 1;
        $rules[] = [
            "rule" => "hasUppercase",
            "description" => "Debe incluir al menos una letra mayúscula",
            "passed" => $passedUpper
        ];
        if (!$passedUpper) $suggestions[] = "Agrega al menos una letra mayúscula.";
        if ($passedUpper) $score += 15;

        // Regla: Letras minúsculas
        $passedLower = preg_match('/[a-z]/', $password) === 1;
        $rules[] = [
            "rule" => "hasLowercase",
            "description" => "Debe incluir al menos una letra minúscula",
            "passed" => $passedLower
        ];
        if (!$passedLower) $suggestions[] = "Agrega al menos una letra minúscula.";
        if ($passedLower) $score += 15;

        // Regla: Números
        $passedNumber = preg_match('/[0-9]/', $password) === 1;
        $rules[] = [
            "rule" => "hasNumber",
            "description" => "Debe incluir al menos un número",
            "passed" => $passedNumber
        ];
        if (!$passedNumber) $suggestions[] = "Incluye al menos un número.";
        if ($passedNumber) $score += 15;

        // Regla: Símbolos
        $passedSymbol = preg_match('/[^a-zA-Z0-9]/', $password) === 1;
        $rules[] = [
            "rule" => "hasSymbol",
            "description" => "Debe incluir al menos un símbolo especial",
            "passed" => $passedSymbol
        ];
        if (!$passedSymbol) $suggestions[] = "Agrega al menos un símbolo especial (por ejemplo: !, @, #, $).";
        if ($passedSymbol) $score += 15;

        // Regla: Evitar secuencias obvias
        $hasSequential = preg_match('/(123|234|345|456|567|678|789|abc|bcd|cde|def)/i', $password) === 1;
        $rules[] = [
            "rule" => "noSequentialChars",
            "description" => "No debe contener secuencias obvias (abc, 123)",
            "passed" => !$hasSequential
        ];
        if ($hasSequential) $suggestions[] = "Evita secuencias lógicas consecutivas como '123' o 'abc'.";
        if (!$hasSequential) $score += 10;
        
        // Regla: No contener el nombre de usuario (si se proporciona)
        if (!empty($username)) {
            $hasUsername = stripos($password, $username) !== false;
            $rules[] = [
                "rule" => "noUsernameInPassword",
                "description" => "No debe contener tu nombre de usuario",
                "passed" => !$hasUsername
            ];
            if ($hasUsername) $suggestions[] = "No incluyas tu nombre de usuario en la contraseña.";
            if (!$hasUsername) $score += 10;
        } else {
            $score += 10;
        }

        // Determinar si es válida (cumple todo lo obligatorio)
        $isValid = $passedMinLength && $passedUpper && $passedLower && $passedNumber && $passedSymbol && !$hasSequential;
        
        // Calcular categoría de fortaleza
        if ($score < 40) $strength = "muy_debil";
        elseif ($score < 60) $strength = "debil";
        elseif ($score < 80) $strength = "media";
        elseif ($score < 100) $strength = "fuerte";
        else $strength = "muy_fuerte";

        // Devolver respuesta 200 OK
        http_response_code(200);
        echo json_encode([
            "password" => $password,
            "isValid" => $isValid,
            "strength" => $strength,
            "score" => $score,
            "rules" => $rules,
            "suggestions" => $suggestions
        ]);
    }

    // Método auxiliar para generar la cadena aleatoria
    private function generateSinglePassword($length, $uppercase, $lowercase, $numbers, $symbols, $excludeSimilar, $excludeAmbiguous) {
        $chars = '';
        
        // Construir el conjunto de caracteres basados en los booleanos
        if ($uppercase) $chars .= $excludeSimilar ? 'ABCDEFGHJKLMNPQRSTUVWXYZ' : 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if ($lowercase) $chars .= $excludeSimilar ? 'abcdefghijkmnopqrstuvwxyz' : 'abcdefghijklmnopqrstuvwxyz';
        if ($numbers) $chars .= $excludeSimilar ? '23456789' : '0123456789';
        if ($symbols) $chars .= $excludeAmbiguous ? '!@#$%^&*()' : '!@#$%^&*()-_=+[]{}|;:,.<>/?';
        
        $password = '';
        $charLength = strlen($chars);
        
        // Selección aleatoria criptográficamente segura
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $charLength - 1)];
        }
        
        return $password;
    }

    // Método para el endpoint GET /passwords/policy
    public function policy() {
        // En una aplicación real, estos valores podrían leerse de una tabla de configuración en la base de datos.
        // Para esta práctica, devolvemos el esquema fijo definido en el contrato OpenAPI.
        http_response_code(200);
        echo json_encode([
            "minLength" => 12,
            "maxLength" => 64,
            "requireUppercase" => true,
            "requireLowercase" => true,
            "requireNumbers" => true,
            "requireSymbols" => true,
            "disallowCommonPasswords" => true,
            "disallowUsernameInPassword" => true,
            "disallowSequentialCharacters" => true,
            "passwordHistoryLimit" => 5,
            "expirationDays" => 90
        ]);
    }
}
