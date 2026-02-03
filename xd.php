<?php
session_start();
include('db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['action'])) {
    $id = $_GET['id'] ?? '';
    $oid = _id($id);

    switch ($_GET['action']) {
        case 'aprobar':
            if ($oid) {
                $recarga = $db->recargas->findOne(['_id' => $oid]);
                if ($recarga && ($recarga['estado'] ?? '') === 'pendiente') {
                    $db->recargas->updateOne(['_id' => $oid], ['$set' => ['estado' => 'aprobado']]);
                    $monto = (float)($recarga['monto'] ?? 0);
                    $db->users->updateOne(['_id' => _id($recarga['user_id'])], ['$inc' => ['saldo_capital' => $monto]]);
                }
            }
            break;
        case 'eliminar':
            if ($oid) $db->recargas->deleteOne(['_id' => $oid]);
            break;
        case 'aprobar_retiro':
            if ($oid) $db->retiros->updateOne(['_id' => $oid], ['$set' => ['estado' => 'aprobado']]);
            break;
        case 'rechazar_retiro':
            if ($oid) {
                $retiro = $db->retiros->findOne(['_id' => $oid]);
                if ($retiro) {
                    $monto = (float)($retiro['monto'] ?? 0);
                    $db->users->updateOne(['_id' => _id($retiro['user_id'])], ['$inc' => ['saldo_disponible' => $monto]]);
                }
                $db->retiros->updateOne(['_id' => $oid], ['$set' => ['estado' => 'rechazado']]);
            }
            break;
        case 'agregar_plan':
            if (isset($_POST['nombre'], $_POST['precio'], $_POST['porcentaje'], $_POST['duracion'])) {
                $db->planes_disponibles->insertOne([
                    'nombre' => trim($_POST['nombre']),
                    'precio' => (float)$_POST['precio'],
                    'porcentaje_diario' => (float)$_POST['porcentaje'],
                    'duracion' => (int)$_POST['duracion']
                ]);
            }
            break;
        case 'eliminar_plan':
            if ($oid) $db->planes_disponibles->deleteOne(['_id' => $oid]);
            break;
        case 'agregar_saldo':
            if (isset($_POST['user_id'], $_POST['monto'])) {
                $uid = $_POST['user_id'];
                $db->users->updateOne(['_id' => _id($uid)], ['$inc' => ['saldo_disponible' => (float)$_POST['monto']]]);
            }
            break;
        case 'cambiar_password':
            if (isset($_POST['user_id'], $_POST['nueva_clave'])) {
                $db->users->updateOne(
                    ['_id' => _id($_POST['user_id'])],
                    ['$set' => ['password' => password_hash($_POST['nueva_clave'], PASSWORD_BCRYPT)]]
                );
            }
            break;
    }
    header("Location: admin.php");
    exit();
}
?>
