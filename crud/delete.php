<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: list.php');
    exit;
}

$pdo = getConexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = :id');
    $stmt->execute(['id' => $id]);

    $_SESSION['mensaje_exito'] = 'Usuario eliminado correctamente.';
    header('Location: list.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, nombre, email FROM usuarios WHERE id = :id');
$stmt->execute(['id' => $id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    header('Location: list.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Eliminar usuario - MiApp</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>
    <main class="contenedor">
        <h1>Eliminar usuario</h1>

        <p>
            ¿Seguro que deseas eliminar a
            <strong><?= htmlspecialchars($usuario['nombre']) ?></strong>
            (<?= htmlspecialchars($usuario['email']) ?>)?
            Esta acción no se puede deshacer.
        </p>

        <form method="post" action="delete.php">
            <input type="hidden" name="id" value="<?= (int)$usuario['id'] ?>">
            <button type="submit" class="boton-peligro">Sí, eliminar</button>
        </form>

        <p><a href="list.php">Cancelar</a></p>
    </main>
</body>
</html>
