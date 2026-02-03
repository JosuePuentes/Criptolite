<?php
session_start();
include('db.php');

// Verificar si está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$user = $db->users->findOne(['_id' => _id($user_id)]);
if (($user['bono_reclamado'] ?? 0) == 1) {
    header("Location: equipo.php?bono=ya_reclamado");
    exit();
}

$bono = 25;
$r = $db->users->updateOne(
    ['_id' => _id($user_id)],
    ['$inc' => ['saldo_capital' => $bono], '$set' => ['bono_reclamado' => 1]]
);
if ($r->getModifiedCount() > 0 || $r->getMatchedCount() > 0) {
    header("Location: equipo.php?bono=ok");
    exit();
}
echo "Hubo un error al reclamar tu bono. Inténtalo nuevamente.";
?>
