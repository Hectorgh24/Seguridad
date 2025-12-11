<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['crear'])) {
        $nombre = $_POST['nombre'] ?? '';
        $password = $_POST['contrasenia'] ?? '';
        // Sin sanitizar: inyección SQL y XSS
        $sql = "INSERT INTO usuarios (nombre, contrasenia) VALUES ('$nombre', '$password')";
        mysqli_query($conn, $sql);
        $mensaje = 'Usuario creado (consulta vulnerable).';
    }

    if (isset($_POST['actualizar'])) {
        $id = $_POST['id'] ?? 0;
        $nombre = $_POST['nombre'] ?? '';
        $password = $_POST['contrasenia'] ?? '';
        $sql = "UPDATE usuarios SET nombre='$nombre', contrasenia='$password' WHERE id=$id";
        mysqli_query($conn, $sql);
        $mensaje = 'Usuario actualizado (consulta vulnerable).';
    }
}

if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $sql = "DELETE FROM usuarios WHERE id=$id";
    mysqli_query($conn, $sql);
    $mensaje = 'Usuario eliminado (consulta vulnerable).';
}

$usuarios = mysqli_query($conn, 'SELECT id, nombre, contrasenia FROM usuarios ORDER BY id ASC');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CRUD Inseguro</title>
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
  <div class="page">
    <div class="card">
      <div class="header">
        <div>
          <h1 class="title">CRUD de usuarios (inseguro)</h1>
          <p class="subtitle">Salida sin escapar: permite XSS y SQL Injection.</p>
        </div>
        <div class="stack">
          <span class="pill">Sesión: <?php echo $_SESSION['user']; ?></span>
          <div class="nav">
            <a href="logout.php">Cerrar sesión</a>
          </div>
        </div>
      </div>

      <?php if ($mensaje): ?>
        <div class="alert success"><?php echo $mensaje; ?></div>
      <?php endif; ?>

      <div class="grid two">
        <div class="stack">
          <h3>Crear usuario</h3>
          <form id="createForm" method="POST">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" placeholder="Ejemplo<script>alert(1)</script>">
            <label for="contrasenia">Contraseña</label>
            <input type="text" id="contrasenia" name="contrasenia" placeholder="1234">
            <button type="submit" name="crear">Crear (vulnerable)</button>
          </form>
        </div>

        <div class="stack">
          <h3>Editar usuario</h3>
          <form id="updateForm" method="POST">
            <label for="id">ID</label>
            <input id="id" name="id" min="1" placeholder="1">
            <label for="nombreEdit">Nombre</label>
            <input type="text" id="nombreEdit" name="nombre" placeholder="Nuevo nombre">
            <label for="passEdit">Contraseña</label>
            <input type="text" id="passEdit" name="contrasenia" placeholder="nueva pass">
            <button type="submit" name="actualizar">Actualizar (vulnerable)</button>
          </form>
        </div>
      </div>

      <h3 style="margin-top:18px;">Usuarios (sin sanitizar)</h3>
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
          <?php while ($row = mysqli_fetch_assoc($usuarios)): ?>
            <tr>
              <td><?php echo $row['id']; ?></td>
              <td><?php echo $row['nombre']; // XSS ?></td>
              <td><?php echo $row['contrasenia']; ?></td>
              <td>
                <a href="?eliminar=<?php echo $row['id']; ?>" class="button-danger">Eliminar</a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
  <script src="validate.js"></script>
</body>
</html>

