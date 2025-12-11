-- Script de creación de la base de datos act20 y tabla usuarios
CREATE DATABASE IF NOT EXISTS act20 CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE act20;

DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL,
  contrasenia VARCHAR(255) NOT NULL
);

-- Datos de ejemplo
INSERT INTO usuarios (nombre, contrasenia) VALUES
('admin', 'admin123'),
('victor', 'victor117'),
('manuel', 'manuel228');

