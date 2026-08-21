<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/validation.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: list.php');
    exit;
}

$pdo = getConexion();

$stmt = $pdo->prepare('SELECT id, nombre, email FROM usuarios WHERE id = :id');
$stmt->execute(['id' => $id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    header('Location: list.php');
    exit;
}

$errores = [];
$nombre = $usuario['nombre'];
$email  = $usuario['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $errores = validarCamposObligatorios(['nombre', 'email'], $_POST);

    if (empty($errores)) {
        if (!validarEmail($email)) {
            $errores[] = 'El correo electrónico no tiene un formato válido.';
        }
        if (!validarLongitud($nombre, 2, 100)) {
            $errores[] = 'El nombre debe tener entre 2 y 100 caracteres.';
        }
        if ($password !== '' && !validarLongitud($password, 8, 72)) {
            $errores[] = 'Si vas a cambiar la contraseña, debe tener entre 8 y 72 caracteres.';
        }
    }

    if (empty($errores)) {
        // Verificar que el correo no pertenezca a otro usuario distinto.
        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email AND id != :id');
        $stmt->execute(['email' => $email, 'id' => $id]);

        if ($stmt->fetch()) {
            $errores[] = 'Ese correo ya está en uso por otro usuario.';
        } else {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare(
                    'UPDATE usuarios SET nombre = :nombre, email = :email, password = :password WHERE id = :id'
                );
                $stmt->execute(['nombre' => $nombre, 'email' => $email, 'password' => $hash, 'id' => $id]);
            } else {
                $stmt = $pdo->prepare(
                    'UPDATE usuarios SET nombre = :nombre, email = :email WHERE id = :id'
                );
                $stmt->execute(['nombre' => $nombre, 'email' => $email, 'id' => $id]);
            }

            $_SESSION['mensaje_exito'] = 'Usuario actualizado correctamente.';
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
    <title>Editar usuario - MiApp</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <main class="contenedor">
        <h1>Editar usuario</h1>

        <?php if (!empty($errores)): ?>
            <div class="alerta alerta-error">
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="edit.php?id=<?= (int)$id ?>">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($nombre) ?>" required>

            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

            <label for="password">Nueva contraseña (opcional)</label>
            <input type="password" id="password" name="password" placeholder="Dejar en blanco para no cambiarla">

            <button type="submit">Actualizar</button>
        </form>

        <p><a href="list.php">Volver al listado</a></p>
    </main>
</body>
</html>
