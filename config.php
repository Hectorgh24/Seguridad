<?php
// 1. Activar reporte de errores (Para que no salga pantalla negra 500)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Credenciales CORRECTAS para XAMPP
$host = 'localhost';
$usuario = 'root';
$contrasenia = ''; // <--- IMPORTANTE: Vacío, sin espacios ni texto 'admin'
$bd = 'act20';

// 3. Conectar
$conn = mysqli_connect($host, $usuario, $contrasenia, $bd);

// 4. Verificar
if (!$conn) {
    die('Error fatal de conexión: ' . mysqli_connect_error());
}
?>
