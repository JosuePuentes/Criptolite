<?php
include('db.php');

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $oid = _id($id);
    if (!$oid) { header("Location: admin.php"); exit(); }

    switch ($_GET['action']) {
        case 'aprobar':
            $recarga = $db->recargas->findOne(['_id' => $oid]);
            if ($recarga && ($recarga['estado'] ?? '') === 'pendiente') {
                $db->recargas->updateOne(['_id' => $oid], ['$set' => ['estado' => 'aprobado']]);
                $uid = $recarga['user_id'];
                $monto = (float)($recarga['monto'] ?? 0);
                $db->users->updateOne(['_id' => _id($uid)], ['$inc' => ['saldo_capital' => $monto]]);
            }
            break;
        case 'eliminar':
            $db->recargas->deleteOne(['_id' => $oid]);
            break;
        case 'aprobar_retiro':
            $db->retiros->updateOne(['_id' => $oid], ['$set' => ['estado' => 'aprobado']]);
            break;
        case 'rechazar_retiro':
            $retiro = $db->retiros->findOne(['_id' => $oid]);
            if ($retiro) {
                $uid = $retiro['user_id'];
                $monto = (float)($retiro['monto'] ?? 0);
                $db->users->updateOne(['_id' => _id($uid)], ['$inc' => ['saldo_disponible' => $monto]]);
            }
            $db->retiros->updateOne(['_id' => $oid], ['$set' => ['estado' => 'rechazado']]);
            break;
    }
    header("Location: admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="/img/favicon.ico.png" type="image/png">
<title>Panel de Administración</title>
<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background-color: #121212;
    color: #f0f0f0;
    margin: 0;
    padding: 20px;
}
h1 {
    text-align: center;
    color: #00BFFF;
    margin-top: 40px;
}
section {
    max-width: 1200px;
    margin: 0 auto 60px auto;
}
table {
    width: 100%;
    border-collapse: collapse;
    background: #1e1e1e;
    border-radius: 8px;
    overflow: hidden;
}
th, td {
    padding: 12px;
    border-bottom: 1px solid #333;
    text-align: center;
}
th {
    background: #222;
    color: #00BFFF;
}
tr:hover {
    background: #2a2a2a;
}
button {
    padding: 8px 14px;
    border: none;
    border-radius: 6px;
    color: white;
    cursor: pointer;
    margin: 2px;
}
.boton-verde { background-color: #28a745; }
.boton-rojo { background-color: #dc3545; }
.estado-pendiente { color: orange; }
.estado-aprobado { color: lightgreen; }
.estado-rechazado { color: red; }

form {
    background: #1e1e1e;
    padding: 20px;
    border-radius: 8px;
    margin-top: 20px;
}
input, select {
    padding: 10px;
    width: 100%;
    margin-bottom: 10px;
    border: none;
    border-radius: 6px;
}
</style>
</head>
<body>

<!-- Recargas -->
<section>
    <h1>Recargas Pendientes</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th><th>Usuario</th><th>Correo</th><th>Monto</th><th>Banco</th><th>Referencia</th><th>Estado</th><th>Fecha</th><th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $recargas = $db->recargas->find(['estado' => 'pendiente'], ['sort' => ['fecha' => -1]]);
            foreach ($recargas as $row) {
                $u = $db->users->findOne(['_id' => _id($row['user_id'])]);
                $nombre = $u ? ($u['nombre_completo'] ?? '') : '';
                $correo = $u ? ($u['correo'] ?? '') : '';
                $rid = (string)$row['_id'];
                $fechaStr = $row['fecha'] instanceof MongoDB\BSON\UTCDateTime
                    ? $row['fecha']->toDateTime()->format('d/m/Y H:i')
                    : (is_string($row['fecha'] ?? '') ? date('d/m/Y H:i', strtotime($row['fecha'])) : '');
                echo "<tr>
                        <td>" . substr($rid, -8) . "</td>
                        <td>" . htmlspecialchars($nombre) . "</td>
                        <td>" . htmlspecialchars($correo) . "</td>
                        <td>$" . number_format($row['monto'] ?? 0, 0, ',', '.') . "</td>
                        <td>" . ucfirst($row['banco'] ?? '') . "</td>
                        <td>" . htmlspecialchars($row['referencia'] ?? '') . "</td>
                        <td class='estado-" . strtolower($row['estado'] ?? '') . "'>" . ucfirst($row['estado'] ?? '') . "</td>
                        <td>{$fechaStr}</td>
                        <td>
                            <a href='admin.php?action=aprobar&id={$rid}'><button class='boton-verde'>Aprobar</button></a>
                            <a href='admin.php?action=eliminar&id={$rid}' onclick=\"return confirm('¿Eliminar esta recarga?');\"><button class='boton-rojo'>Eliminar</button></a>
                        </td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>
</section>

<!-- Retiros -->
<section>
    <h1>Retiros Pendientes</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th><th>Usuario</th><th>Correo</th><th>Monto</th><th>Banco</th><th>Estado</th><th>Fecha</th><th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $retiros = $db->retiros->find(['estado' => 'pendiente'], ['sort' => ['fecha' => -1]]);
            foreach ($retiros as $row) {
                $u = $db->users->findOne(['_id' => _id($row['user_id'])]);
                $nombre = $u ? ($u['nombre_completo'] ?? '') : '';
                $correo = $u ? ($u['correo'] ?? '') : '';
                $rid = (string)$row['_id'];
                $fechaStr = $row['fecha'] instanceof MongoDB\BSON\UTCDateTime
                    ? $row['fecha']->toDateTime()->format('d/m/Y H:i')
                    : (is_string($row['fecha'] ?? '') ? date('d/m/Y H:i', strtotime($row['fecha'])) : '');
                echo "<tr>
                        <td>" . substr($rid, -8) . "</td>
                        <td>" . htmlspecialchars($nombre) . "</td>
                        <td>" . htmlspecialchars($correo) . "</td>
                        <td>$" . number_format($row['monto'] ?? 0, 0, ',', '.') . "</td>
                        <td>" . htmlspecialchars($row['banco'] ?? '') . "</td>
                        <td class='estado-" . strtolower($row['estado'] ?? '') . "'>" . ucfirst($row['estado'] ?? '') . "</td>
                        <td>{$fechaStr}</td>
                        <td>
                            <a href='admin.php?action=aprobar_retiro&id={$rid}'><button class='boton-verde'>Aprobar</button></a>
                            <a href='admin.php?action=rechazar_retiro&id={$rid}' onclick=\"return confirm('¿Rechazar este retiro?');\"><button class='boton-rojo'>Rechazar</button></a>
                        </td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>
</section>

<!-- Planes -->
<?php
if (isset($_POST['agregar']) && !empty($_POST['nombre'])) {
    $db->planes_disponibles->insertOne([
        'nombre' => $_POST['nombre'],
        'porcentaje_diario' => (float)($_POST['porcentaje'] ?? 0),
        'precio' => (float)($_POST['precio'] ?? 0),
        'duracion' => (int)($_POST['duracion'] ?? 0),
        'unidad_duracion' => $_POST['unidad'] ?? 'dias'
    ]);
}
if (isset($_POST['eliminar']) && !empty($_POST['plan_id'])) {
    $db->planes_disponibles->deleteOne(['_id' => _id($_POST['plan_id'])]);
}
$planes = $db->planes_disponibles->find([]);
?>

<section>
    <h1>Gestión de Planes</h1>
    <form method="POST">
        <input type="text" name="nombre" placeholder="Nombre del plan" required>
        <input type="number" step="0.01" name="porcentaje" placeholder="Porcentaje diario (%)" required>
        <input type="number" step="0.01" name="precio" placeholder="Precio" required>
        <input type="number" name="duracion" placeholder="Duración" required>
        <select name="unidad" required>
            <option value="dias">Días</option>
            <option value="meses">Meses</option>
        </select>
        <button type="submit" name="agregar" class="boton-verde">Agregar Plan</button>
    </form>

    <table>
        <thead>
            <tr><th>ID</th><th>Nombre</th><th>% Diario</th><th>Precio</th><th>Duración</th><th>Unidad</th><th>Acción</th></tr>
        </thead>
        <tbody>
            <?php foreach ($planes as $plan): $pid = (string)($plan['_id'] ?? ''); ?>
            <tr>
                <td><?= substr($pid, -8) ?></td>
                <td><?= htmlspecialchars($plan['nombre'] ?? '') ?></td>
                <td><?= $plan['porcentaje_diario'] ?? 0 ?>%</td>
                <td>$<?= number_format($plan['precio'] ?? 0, 2) ?></td>
                <td><?= $plan['duracion'] ?? 0 ?></td>
                <td><?= $plan['unidad_duracion'] ?? 'dias' ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="plan_id" value="<?= $pid ?>">
                        <button type="submit" name="eliminar" class="boton-rojo" onclick="return confirm('¿Eliminar este plan?')">Eliminar</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

</body>
</html>
