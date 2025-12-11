<?php
session_start();
require 'config.php';

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $password = $_POST['contrasenia'] ?? '';

    // Consulta vulnerable (inyección SQL)
    $sql = "SELECT * FROM usuarios WHERE nombre = '$nombre' AND contrasenia = '$password' LIMIT 1";
    $resultado = mysqli_query($conn, $sql);

    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $_SESSION['user'] = $nombre;
        header('Location: /inseguro/crud.php');
        exit;
    }
    $mensaje = 'Credenciales inválidas o usuario inexistente.';
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Inseguro</title>
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
  <div class="page">
    <div class="card">
      <div class="header">
        <div>
          <h1 class="title">Login (versión insegura)</h1>
          <p class="subtitle">Inyección SQL</p>
        </div>
        <span class="pill">Inseguro</span>
      </div>

      <?php if ($mensaje): ?>
        <div class="alert error"><?php echo $mensaje; ?></div>
      <?php endif; ?>

      <form id="loginForm" method="POST" autocomplete="off">
        <div class="grid">
          <label for="nombre">Usuario</label>
          <input type="text" id="nombre" name="nombre" placeholder="admin' OR '1'='1">
        </div>
        <div class="grid">
          <label for="contrasenia">Contraseña</label>
          <input type="password" id="contrasenia" name="contrasenia" placeholder="admin123">
        </div>
        <button type="submit">Iniciar sesión</button>
      </form>

      <div class="stack" style="margin-top: 14px;">
        <p class="muted">Nota: vulnerable a inyección SQL con <span class="code">admin' OR '1'='1</span> para saltarse el login.</p>
        <p class="muted">Credenciales válidas de prueba: admin / admin123</p>
      </div>
    </div>
  </div>
  <script src="validate.js"></script>
</body>
</html>

