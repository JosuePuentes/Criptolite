<?php
session_start();
include('db.php');

// Verificar si está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Verificar si ya reclamó el bono
$stmt = $conn->prepare("SELECT bono_reclamado FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($bono_reclamado);
$stmt->fetch();
$stmt->close();

if ($bono_reclamado == 1) {
    // Ya reclamó el bono
    header("Location: equipo.php?bono=ya_reclamado");
    exit();
}

$bono = 25; // Monto del bono

// Actualizar saldo_capital y marcar bono como reclamado
$stmt = $conn->prepare("
    UPDATE users
    SET saldo_capital = saldo_capital + ?, bono_reclamado = 1
    WHERE id = ?
");
$stmt->bind_param("ii", $bono, $user_id);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: equipo.php?bono=ok");
    exit();
} else {
    $stmt->close();
    echo "Hubo un error al reclamar tu bono. Inténtalo nuevamente.";
}
?>
