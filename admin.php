<?php

include('db.php'); // solo incluimos la base de datos, sin sesión



// Procesar acciones (igual que antes)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);

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
    }
    header("Location: admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel de Administración</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            $resultado = $conn->query("SELECT r.*, u.nombre_completo, u.correo 
                                       FROM recargas r 
                                       JOIN users u ON r.user_id = u.id 
                                       WHERE r.estado = 'pendiente' ORDER BY r.fecha DESC");
            while($row = $resultado->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>" . htmlspecialchars($row['nombre_completo']) . "</td>
                        <td>{$row['correo']}</td>
                        <td>$" . number_format($row['monto'], 0, ',', '.') . "</td>
                        <td>" . ucfirst($row['banco']) . "</td>
                        <td>{$row['referencia']}</td>
                        <td class='estado-" . strtolower($row['estado']) . "'>" . ucfirst($row['estado']) . "</td>
                        <td>" . date('d/m/Y H:i', strtotime($row['fecha'])) . "</td>
                        <td>
                            <a href='admin.php?action=aprobar&id={$row['id']}'><button class='boton-verde'>Aprobar</button></a>
                            <a href='admin.php?action=eliminar&id={$row['id']}' onclick=\"return confirm('¿Eliminar esta recarga?');\"><button class='boton-rojo'>Eliminar</button></a>
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
            $retiros = $conn->query("SELECT r.*, u.nombre_completo, u.correo 
                                     FROM retiros r 
                                     JOIN users u ON r.user_id = u.id 
                                     WHERE r.estado = 'pendiente' ORDER BY r.fecha DESC");
            while($row = $retiros->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>" . htmlspecialchars($row['nombre_completo']) . "</td>
                        <td>{$row['correo']}</td>
                        <td>$" . number_format($row['monto'], 0, ',', '.') . "</td>
                        <td>{$row['banco']}</td>
                        <td class='estado-" . strtolower($row['estado']) . "'>" . ucfirst($row['estado']) . "</td>
                        <td>" . date('d/m/Y H:i', strtotime($row['fecha'])) . "</td>
                        <td>
                            <a href='admin.php?action=aprobar_retiro&id={$row['id']}'><button class='boton-verde'>Aprobar</button></a>
                            <a href='admin.php?action=rechazar_retiro&id={$row['id']}' onclick=\"return confirm('¿Rechazar este retiro?');\"><button class='boton-rojo'>Rechazar</button></a>
                        </td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>
</section>

<!-- Planes -->
<?php
// Conexión y acciones de planes
$conexion = new mysqli("srv1922.hstgr.io", "u765282126_costeno", "1083041309Ll.", "u765282126_costeno");
if ($conexion->connect_error) die("Error: " . $conexion->connect_error);

if (isset($_POST['agregar'])) {
    $stmt = $conexion->prepare("INSERT INTO planes_disponibles (nombre, porcentaje_diario, precio, duracion, unidad_duracion) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sddis", $_POST['nombre'], $_POST['porcentaje'], $_POST['precio'], $_POST['duracion'], $_POST['unidad']);
    $stmt->execute(); $stmt->close();
}
if (isset($_POST['eliminar'])) {
    $conexion->query("DELETE FROM planes_disponibles WHERE id = " . intval($_POST['plan_id']));
}
$planes = $conexion->query("SELECT * FROM planes_disponibles");
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
            <?php while ($plan = $planes->fetch_assoc()): ?>
            <tr>
                <td><?= $plan['id'] ?></td>
                <td><?= htmlspecialchars($plan['nombre']) ?></td>
                <td><?= $plan['porcentaje_diario'] ?>%</td>
                <td>$<?= number_format($plan['precio'], 2) ?></td>
                <td><?= $plan['duracion'] ?></td>
                <td><?= $plan['unidad_duracion'] ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                        <button type="submit" name="eliminar" class="boton-rojo" onclick="return confirm('¿Eliminar este plan?')">Eliminar</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</section>

</body>
</html>
