<?php
// Redirección abierta: cualquier URL es aceptada
if (isset($_GET['url'])) {
    $url = $_GET['url'];
    header("Location: $url");
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Redirección Insegura</title>
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
  <div class="page">
    <div class="card">
      <h1 class="title">Redirección abierta</h1>
      <p class="subtitle">Pasa cualquier URL en <span class="code">?url=</span> y serás enviado allí.</p>
      <div class="nav">
        <a href="index.php">Volver al login</a>
      </div>
      <form method="GET">
        <label for="url">URL destino</label>
        <input id="url" name="url" placeholder="https://example.com">
        <button type="submit">Redirigir (sin validar)</button>
      </form>
    </div>
  </div>
</body>
</html>

