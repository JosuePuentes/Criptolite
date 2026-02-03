<?php
// db.example.php — Copia este archivo como db.php y completa con tus datos.
// Nunca subas db.php a GitHub (contiene contraseñas).

$host = "localhost";           // ej: srv1922.hstgr.io o localhost
$user = "tu_usuario_mysql";
$pass = "tu_contraseña_mysql";
$db   = "nombre_base_datos";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");
