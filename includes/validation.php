<?php
// Funciones de validación reutilizables para los formularios de la aplicacion.
function validarCamposObligatorios(array $campos, array $datos): array
{
    $errores = [];
    foreach ($campos as $campo) {
        if (!isset($datos[$campo]) || trim((string)$datos[$campo]) === '') {
            $errores[] = "El campo '$campo' es obligatorio.";
        }
    }
    return $errores;
}

function validarEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validarLongitud(string $valor, int $min, int $max): bool
{
    $len = mb_strlen($valor);
    return $len >= $min && $len <= $max;
}
