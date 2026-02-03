<?php
// db.php — MongoDB Atlas. Variable de entorno: MONGODB_URI
// En local: crea db.local.php que defina $mongoClient y $db (o usa env).

if (file_exists(__DIR__ . '/db.local.php')) {
    require __DIR__ . '/db.local.php';
    return;
}

$uri = getenv('MONGODB_URI');
if (empty($uri)) {
    die('Configura la variable de entorno MONGODB_URI (ej: mongodb+srv://user:pass@cluster.mongodb.net/criptolite)');
}

require_once __DIR__ . '/vendor/autoload.php';

$mongoClient = new MongoDB\Client($uri);
$dbName = getenv('MONGODB_DB_NAME');
if (empty($dbName)) {
    $path = parse_url($uri, PHP_URL_PATH);
    $dbName = $path ? trim($path, '/') : 'criptolite';
}
$db = $mongoClient->selectDatabase($dbName);

// Colecciones (acceso: $db->users, $db->recargas, etc.)
// Helper: id a ObjectId (para consultas por _id)
function _id($id) {
    if ($id instanceof MongoDB\BSON\ObjectId) return $id;
    try {
        return new MongoDB\BSON\ObjectId($id);
    } catch (Exception $e) {
        return null;
    }
}
