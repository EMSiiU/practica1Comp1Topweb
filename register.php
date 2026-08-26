<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/validation.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errores = [];
$nombre = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    $errores = validarCamposObligatorios(
        ['nombre', 'email', 'password', 'password_confirm'],
        $_POST
    );

    if (empty($errores)) {
        if (!validarEmail($email)) {
            $errores[] = 'El correo electrónico no tiene un formato válido.';
        }
        if (!validarLongitud($nombre, 2, 100)) {
            $errores[] = 'El nombre debe tener entre 2 y 100 caracteres.';
        }
        if (!validarLongitud($password, 8, 72)) {
            $errores[] = 'La contraseña debe tener entre 8 y 72 caracteres.';
        }
        if ($password !== $passwordConfirm) {
            $errores[] = 'Las contraseñas no coinciden.';
        }
    }

    if (empty($errores)) {
        $pdo = getConexion();

        // Verificamos que el correo no esté registrado
        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email');
        $stmt->execute(['email' => $email]);

        if ($stmt->fetch()) {
            $errores[] = 'Ya existe una cuenta registrada con ese correo.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                'INSERT INTO usuarios (nombre, email, password) VALUES (:nombre, :email, :password)'
            );
            $stmt->execute([
                'nombre' => $nombre,
                'email' => $email,
                'password' => $hash,
            ]);

            $_SESSION['mensaje_exito'] = 'Registro exitoso. Ahora puedes iniciar sesión.';
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - MiApp</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>
    <main class="contenedor">
        <h1>Crear cuenta</h1>

        <?php if (!empty($errores)): ?>
            <div class="alerta alerta-error">
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="register.php">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($nombre) ?>" required>

            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>

            <label for="password_confirm">Confirmar contraseña</label>
            <input type="password" id="password_confirm" name="password_confirm" required>

            <button type="submit">Registrarme</button>
        </form>

        <p><a href="login.php">Ya tengo una cuenta</a></p>
    </main>
</body>
</html>
