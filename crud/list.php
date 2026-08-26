<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

$pdo = getConexion();
$stmt = $pdo->query('SELECT id, nombre, email, fecha_registro FROM usuarios ORDER BY id DESC');
$usuarios = $stmt->fetchAll();

$mensaje = $_SESSION['mensaje_exito'] ?? '';
unset($_SESSION['mensaje_exito']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios - MiApp</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>
    <main class="contenedor">
        <div class="cabecera">
            <h1>Usuarios registrados</h1>
            <p>
                Sesión iniciada como <strong><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong>
                — <a href="<?= BASE_URL ?>/logout.php">Cerrar sesión</a>
            </p>
        </div>

        <?php if ($mensaje): ?>
            <div class="alerta alerta-exito"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <p><a href="create.php">+ Agregar usuario</a></p>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Fecha de registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($usuarios)): ?>
                    <tr><td colspan="5">No hay usuarios registrados todavía.</td></tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?= (int)$usuario['id'] ?></td>
                            <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                            <td><?= htmlspecialchars($usuario['email']) ?></td>
                            <td><?= htmlspecialchars($usuario['fecha_registro']) ?></td>
                            <td>
                                <a href="edit.php?id=<?= (int)$usuario['id'] ?>">Editar</a>
                                |
                                <a href="delete.php?id=<?= (int)$usuario['id'] ?>">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
