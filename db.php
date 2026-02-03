<?php
// db.php — En producción usa variables de entorno (Render/Vercel).
// En local: crea db.local.php con $host, $user, $pass, $db y $conn (o usa env).

if (file_exists(__DIR__ . '/db.local.php')) {
    require __DIR__ . '/db.local.php';
    return;
}

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: '';
$pass = getenv('DB_PASS') ?: '';
$db   = getenv('DB_NAME') ?: '';

if (!$user || !$db) {
    die('Configura DB: variables de entorno DB_HOST, DB_USER, DB_PASS, DB_NAME o crea db.local.php');
}

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");
