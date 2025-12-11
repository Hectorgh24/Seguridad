<?php
session_start();
session_unset();
session_destroy();
// Me aseguro de redirigir a la carpeta correcta
header('Location: /act20/seguro/index.php');
exit;
?>
