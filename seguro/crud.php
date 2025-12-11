<?php
session_start();
require 'config.php';

// Si no está logueado, lo saco de aquí
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$mensaje = '';

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // CREAR USUARIO (INSERT SEGURO)
    if (isset($_POST['crear'])) {
        $nombre = $_POST['nombre'] ?? '';
        $password = $_POST['contrasenia'] ?? '';
        
        // Uso prepare() para que no puedan meter scripts ni SQL raro
        $sql = "INSERT INTO usuarios (nombre, contrasenia) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([$nombre, $password]);
            $mensaje = 'Usuario creado de forma segura.';
        } catch (PDOException $e) {
            $mensaje = 'Error al crear: ' . e($e->getMessage());
        }
    }

    // ACTUALIZAR USUARIO (UPDATE SEGURO)
    if (isset($_POST['actualizar'])) {
        $id = $_POST['id'] ?? 0;
        $nombre = $_POST['nombre'] ?? '';
        $password = $_POST['contrasenia'] ?? '';

        // Aquí el ID también se pasa como parámetro, evitando la inyección masiva "1 OR 1=1"
        $sql = "UPDATE usuarios SET nombre = ?, contrasenia = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $password, $id]);
        $mensaje = 'Usuario actualizado correctamente.';
    }
}

// ELIMINAR USUARIO (DELETE SEGURO)
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    // Igual aquí, uso ? para que no puedan borrar toda la tabla con OR 1=1
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $mensaje = 'Usuario eliminado de forma segura.';
}

// OBTENER TODOS LOS USUARIOS
$usuarios = $pdo->query('SELECT id, nombre, contrasenia FROM usuarios ORDER BY id ASC');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>CRUD Seguro</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="page">
    <div class="card">
      <div class="header">
        <div>
          <h1 class="title">CRUD de usuarios (Blindado)</h1>
          <p class="subtitle">Salida sanitizada con htmlspecialchars() y consultas preparadas.</p>
        </div>
        <div class="stack">
          <span class="pill" style="background:#dcfce7; color:#166534;">Sesión: <?php echo e($_SESSION['user']); ?></span>
          <div class="nav">
            <a href="logout.php">Cerrar sesión</a>
          </div>
        </div>
      </div>

      <?php if ($mensaje): ?>
        <div class="alert success"><?php echo e($mensaje); ?></div>
      <?php endif; ?>

      <div class="grid two">
        <div class="stack">
          <h3>Crear usuario</h3>
          <form method="POST" onsubmit="return validarFormulario(this)">
            <label>Nombre</label>
            <input type="text" name="nombre" placeholder="Intenta meter un script...">
            <label>Contraseña</label>
            <input type="text" name="contrasenia" placeholder="1234">
            <button type="submit" name="crear">Crear Seguro</button>
          </form>
        </div>

        <div class="stack">
          <h3>Editar usuario</h3>
          <form method="POST">
            <label>ID</label>
            <input name="id" placeholder="ID numérico">
            <label>Nombre</label>
            <input type="text" name="nombre" placeholder="Nuevo nombre">
            <label>Contraseña</label>
            <input type="text" name="contrasenia" placeholder="nueva pass">
            <button type="submit" name="actualizar">Actualizar Seguro</button>
          </form>
        </div>
      </div>

      <h3 style="margin-top:18px;">Usuarios (Sanitizados)</h3>
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Contraseña</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($usuarios as $row): ?>
            <tr>
              <td><?php echo e($row['id']); ?></td>
              <td style="color: green; font-weight: bold;"><?php echo e($row['nombre']); ?></td>
              <td><?php echo e($row['contrasenia']); ?></td>
              <td>
                <a href="?eliminar=<?php echo e($row['id']); ?>" class="button-danger">Eliminar</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <script src="validate.js"></script>
</body>
</html>
