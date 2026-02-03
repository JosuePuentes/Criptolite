<?php
session_start();
include('db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['action'])) {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    switch ($_GET['action']) {
        case 'aprobar':
            $conn->query("UPDATE recargas SET estado = 'aprobado' WHERE id = $id");
            $recarga = $conn->query("SELECT user_id, monto FROM recargas WHERE id = $id")->fetch_assoc();
            if ($recarga) {
                $user_id = intval($recarga['user_id']);
                $monto = floatval($recarga['monto']);
                $conn->query("UPDATE users SET saldo_capital = saldo_capital + $monto WHERE id = $user_id");
            }
            break;
        case 'eliminar':
            $conn->query("DELETE FROM recargas WHERE id = $id");
            break;
        case 'aprobar_retiro':
            $conn->query("UPDATE retiros SET estado = 'aprobado' WHERE id = $id");
            break;
        case 'rechazar_retiro':
            $retiro = $conn->query("SELECT user_id, monto FROM retiros WHERE id = $id")->fetch_assoc();
            if ($retiro) {
                $user_id = intval($retiro['user_id']);
                $monto = floatval($retiro['monto']);
                $conn->query("UPDATE users SET saldo_disponible = saldo_disponible + $monto WHERE id = $user_id");
            }
            $conn->query("UPDATE retiros SET estado = 'rechazado' WHERE id = $id");
            break;
        case 'agregar_plan':
            if (isset($_POST['nombre'], $_POST['precio'], $_POST['porcentaje'], $_POST['duracion'])) {
                $nombre = $conn->real_escape_string($_POST['nombre']);
                $precio = floatval($_POST['precio']);
                $porcentaje = floatval($_POST['porcentaje']);
                $duracion = intval($_POST['duracion']);
                $conn->query("INSERT INTO planes (nombre, precio, porcentaje_diario, duracion) VALUES ('$nombre', $precio, $porcentaje, $duracion)");
            }
            break;
        case 'eliminar_plan':
            $conn->query("DELETE FROM planes WHERE id = $id");
            break;
        case 'agregar_saldo':
            if (isset($_POST['user_id'], $_POST['monto'])) {
                $user_id = intval($_POST['user_id']);
                $monto = floatval($_POST['monto']);
                $conn->query("UPDATE users SET saldo_disponible = saldo_disponible + $monto WHERE id = $user_id");
            }
            break;
        case 'cambiar_password':
            if (isset($_POST['user_id'], $_POST['nueva_clave'])) {
                $user_id = intval($_POST['user_id']);
                $nueva_clave = password_hash($_POST['nueva_clave'], PASSWORD_BCRYPT);
                $conn->query("UPDATE users SET clave = '$nueva_clave' WHERE id = $user_id");
            }
            break;
    }
    header("Location: admin.php");
    exit();
}
?>
