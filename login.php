<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/validation.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// verifica que no haya una sesipm activa
if (!empty($_SESSION['usuario_id'])) {
    header('Location: /crud/list.php');
    exit;
}

$errores = [];
$email = '';
$mensajeExito = $_SESSION['mensaje_exito'] ?? '';
unset($_SESSION['mensaje_exito']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $errores = validarCamposObligatorios(['email', 'password'], $_POST);

    if (empty($errores)) {
        $pdo = getConexion();

        $stmt = $pdo->prepare('SELECT id, nombre, password FROM usuarios WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();

        if (!$usuario || !password_verify($password, $usuario['password'])) {
            $errores[] = 'Correo o contraseña incorrectos.';
        } else {
            session_regenerate_id(true);
            $_SESSION['usuario_id']     = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];

            header('Location: /crud/list.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión - MiApp</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <main class="contenedor">
        <h1>Iniciar sesión</h1>

        <?php if ($mensajeExito): ?>
            <div class="alerta alerta-exito"><?= htmlspecialchars($mensajeExito) ?></div>
        <?php endif; ?>

        <?php if (!empty($errores)): ?>
            <div class="alerta alerta-error">
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Entrar</button>
        </form>

        <p><a href="register.php">Crear una cuenta nueva</a></p>
    </main>
</body>
</html>
