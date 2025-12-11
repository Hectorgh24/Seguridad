<?php
$host = 'localhost';
$usuario = 'root';
$contrasenia = 'admin';
$bd = 'act20';

$conn = mysqli_connect($host, $usuario, $contrasenia, $bd);

if (!$conn) {
    die('Error de conexión: ' . mysqli_connect_error());
}

