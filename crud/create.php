<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/validation.php';

$errores = [];
$nombre = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $errores = validarCamposObligatorios(['nombre', 'email', 'password'], $_POST);

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
    }

    if (empty($errores)) {
        $pdo = getConexion();

        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email');
        $stmt->execute(['email' => $email]);

        if ($stmt->fetch()) {
            $errores[] = 'Ya existe un usuario con ese correo.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                'INSERT INTO usuarios (nombre, email, password) VALUES (:nombre, :email, :password)'
            );
            $stmt->execute(['nombre' => $nombre, 'email' => $email, 'password' => $hash]);

            $_SESSION['mensaje_exito'] = 'Usuario creado correctamente.';
            header('Location: list.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar usuario - MiApp</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <main class="contenedor">
        <h1>Agregar usuario</h1>

        <?php if (!empty($errores)): ?>
            <div class="alerta alerta-error">
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="create.php">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($nombre) ?>" required>

            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Guardar</button>
        </form>

        <p><a href="list.php">Volver al listado</a></p>
    </main>
</body>
</html>
