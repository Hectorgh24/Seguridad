<?php
// Configuración de la base de datos
$host = 'localhost';
$db   = 'act20';
$user = 'root';
$pass = ''; // En XAMPP Linux suele estar vacío
$charset = 'utf8mb4';

// Configuración de conexión PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    // Aquí le digo a la BD que si algo falla, me lance una excepción (error visible)
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // Quiero que los datos vengan como un array asociativo, es más fácil de leer
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Desactivo la emulación para usar sentencias preparadas REALES (más seguro)
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Intento conectarme usando la configuración segura
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Si falla, mato el proceso y muestro el error
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Función de Sanitización (Anti-XSS)
// Esta es la función que Víctor menciona como 'e()' en su reporte.
// Convierte caracteres peligrosos como <, >, ' en texto HTML seguro.
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>
