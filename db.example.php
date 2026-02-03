<?php
// db.example.php — Copia como db.local.php para desarrollo local y completa con tus datos.
// En Render/Vercel configura las variables de entorno DB_HOST, DB_USER, DB_PASS, DB_NAME.

$host = "localhost";           // ej: srv1922.hstgr.io o localhost
$user = "tu_usuario_mysql";
$pass = "tu_contraseña_mysql";
$db   = "nombre_base_datos";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");
