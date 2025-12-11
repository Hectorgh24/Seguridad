<?php
session_start();
require 'config.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $password = $_POST['contrasenia'] ?? '';

    // PROTECCIÓN SQL INJECTION:
    // En lugar de meter la variable directo, pongo un '?'
    // Así la base de datos sabe que lo que viene es SOLO dato, no código.
    $stmt = $pdo->prepare("SELECT id, nombre, contrasenia FROM usuarios WHERE nombre = ? LIMIT 1");
    $stmt->execute([$nombre]);
    $user = $stmt->fetch();

    // Verifico si encontré al usuario y si la contraseña coincide
    // Nota: En un entorno real usaríamos password_verify(), pero seguimos la lógica de la práctica.
    if ($user && $user['contrasenia'] === $password) {
        $_SESSION['user'] = $user['nombre'];
        // Me voy al CRUD seguro
        header('Location: crud.php');
        exit;
    } else {
        $mensaje = 'Credenciales incorrectas (o intento de ataque fallido).';
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login Seguro</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="page">
    <div class="card">
      <div class="header">
        <div>
          <h1 class="title">Login Seguro</h1>
          <p class="subtitle">Protegido contra SQLi con PDO</p>
        </div>
        <span class="pill" style="background:#dcfce7; color:#166534;">Seguro</span>
      </div>

      <?php if ($mensaje): ?>
        <div class="alert error"><?php echo e($mensaje); ?></div>
      <?php endif; ?>

      <form id="loginForm" method="POST" autocomplete="off" onsubmit="return validarFormulario(this)">
        <div class="grid">
          <label for="nombre">Usuario</label>
          <input type="text" id="nombre" name="nombre" placeholder="Intenta inyectar aquí...">
        </div>
        <div class="grid">
          <label for="contrasenia">Contraseña</label>
          <input type="password" id="contrasenia" name="contrasenia" placeholder="Contraseña">
        </div>
        <button type="submit">Iniciar sesión de forma segura</button>
      </form>
      
      <div class="nav" style="margin-top: 20px;">
          <a href="/act20/index.php">Ir a versión Insegura</a>
      </div>
    </div>
  </div>
  <script src="validate.js"></script>
</body>
</html>
