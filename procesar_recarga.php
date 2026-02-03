<?php
session_start();
include('db.php');

// Verificar si está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Verificar que lleguen todos los datos
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $monto = isset($_POST['monto']) ? intval($_POST['monto']) : 0;
    $banco = isset($_POST['banco']) ? trim($_POST['banco']) : '';
    $referencia = isset($_POST['referencia']) ? trim($_POST['referencia']) : '';

    if ($monto <= 0 || empty($banco) || empty($referencia)) {
        echo "Error: Datos inválidos.";
        exit();
    }

    $db->recargas->insertOne([
        'user_id' => $user_id,
        'monto' => $monto,
        'banco' => $banco,
        'referencia' => $referencia,
        'estado' => 'pendiente',
        'fecha' => new MongoDB\BSON\UTCDateTime()
    ]);

    if (true) {
        // Redirigir a recarga.php con mensaje de éxito
        header("Location: recarga.php?mensaje=recarga_enviada");
        exit();
    } else {
        echo "Error al procesar la recarga. Intenta de nuevo.";
        exit();
    }
} else {
    echo "Acceso denegado.";
    exit();
}
?>
