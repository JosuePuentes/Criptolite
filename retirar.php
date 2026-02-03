<?php
session_start();
include('db.php');

// Verificar si está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);

// Obtener datos del usuario
$user = $conn->query("SELECT nombre_completo, saldo_disponible, banco, cuenta_banco FROM users WHERE id = $user_id")->fetch_assoc();

// Procesar solicitud de retiro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $monto = floatval($_POST['monto']);
    $banco = $conn->real_escape_string($_POST['banco']);
    
    if ($monto > 0 && $monto <= $user['saldo_disponible']) {
        // Insertar retiro
        $conn->query("INSERT INTO retiros (user_id, monto, banco, estado, fecha) 
                      VALUES ($user_id, $monto, '$banco', 'pendiente', NOW())");
        
        // Descontar saldo disponible
        $conn->query("UPDATE users SET saldo_disponible = saldo_disponible - $monto WHERE id = $user_id");
        
        header("Location: retirar.php?success=1");
        exit();
    } else {
        $error = "Monto inválido o insuficiente.";
    }
}

// Obtener historial de retiros
$historial = $conn->query("SELECT * FROM retiros WHERE user_id = $user_id ORDER BY fecha DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Retirar Fondos</title>
<style>
body {
    margin: 0;
    font-family: 'Arial', sans-serif;
    background: url('/inver/img/fondo.jpg') no-repeat center center fixed;
    background-size: cover;
    color: white;
    padding-bottom: 180px;
}
h1 {
    text-align: center;
    color: #00BFFF;
    margin: 20px 0;
}
form {
    max-width: 400px;
    margin: 0 auto 30px auto;
    background: #1e1e1e;
    padding: 20px;
    border-radius: 12px;
}
form label {
    display: block;
    margin-bottom: 5px;
}
form input, form select {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 8px;
    border: none;
    background: #333;
    color: white;
}
form button {
    width: 100%;
    padding: 12px;
    background:rgb(237, 32, 32);
    border: none;
    border-radius: 8px;
    color: white;
    font-size: 16px;
    cursor: pointer;
}
form button:hover {
    opacity: 0.9;
}
.success {
    text-align: center;
    color: lightgreen;
    margin-bottom: 15px;
}
.error {
    text-align: center;
    color: red;
    margin-bottom: 15px;
}
.tabla {
    width: 100%;
    max-width: 800px;
    margin: 0 auto;
    border-collapse: collapse;
}
.tabla th, .tabla td {
    border: 1px solid #333;
    padding: 10px;
    text-align: center;
}
.tabla th {
    background: #1e1e1e;
}
.estado-pendiente {
    color: orange;
}
.estado-aprobado {
    color: lightgreen;
}
.estado-rechazado {
    color: red;
}

/* BARRA DE BOTONES */
.barra-botones {
    position: fixed;
    bottom: 90px;
    width: 100%;
    background: #1e1e1e;
    display: flex;
    justify-content: space-around;
    padding: 15px 0;
    border-top: 1px solid #333;
    z-index: 1000;
}
.barra-botones a {
    text-align: center;
    color: white;
    font-size: 26px;
    font-weight: bold;
    text-decoration: none;
}
.barra-botones a:hover {
    color:rgb(255, 0, 0);
}
form button:hover {
    background:rgb(255, 0, 0);
}

/* MENÚ INFERIOR */
.navegacion-inferior {
    position: fixed;
    bottom: 0;
    width: 100%;
    background: #121212;
    display: flex;
    justify-content: space-around;
    align-items: center;
    padding: 14px 0;
    border-top: 1px solid #333;
    z-index: 999;
}
.navegacion-inferior a {
    color: white;
    text-decoration: none;
    font-size: 20px;
    text-align: center;
}
.navegacion-inferior a:hover {
    color:rgb(255, 0, 0);
}

.fila-recarga {
    background-color: #1e1e1e;
}
</style>
</head>

<body>

<h1>Retirar Fondos</h1>

<?php if (isset($_GET['success'])): ?>
<div class="success">¡Retiro solicitado correctamente!</div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post">
    <label>Saldo disponible:</label>
    <input type="text" value="$<?= number_format($user['saldo_disponible'], 0, ',', '.') ?>" disabled>

    <label>Banco Asociado:</label>
    <select name="banco" required>
        <option value="<?= htmlspecialchars($user['banco']) ?>"><?= htmlspecialchars($user['banco']) ?> (<?= htmlspecialchars($user['cuenta_banco']) ?>)</option>
    </select>

    <label>Monto a Retirar:</label>
    <input type="number" name="monto" min="1" max="<?= intval($user['saldo_disponible']) ?>" required>

    <button type="submit">Solicitar Retiro</button>
</form>

<h2 style="text-align:center; margin-top:40px;">Historial de Retiros</h2>
<table class="tabla">
    <thead>
        <tr>
            <th>ID</th>
            <th>Monto</th>
            <th>Banco</th>
            <th>Estado</th>
            <th>Fecha</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $historial->fetch_assoc()): ?>
            <tr class="fila-recarga">
            <td><?= $row['id'] ?></td>
            <td>$<?= number_format($row['monto'], 0, ',', '.') ?></td>
            <td><?= htmlspecialchars($row['banco']) ?></td>
            <td class="estado-<?= strtolower($row['estado']) ?>"><?= ucfirst($row['estado']) ?></td>
            <td><?= date('d/m/Y H:i', strtotime($row['fecha'])) ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- BARRA DE BOTONES RECARGA Y RETIRAR -->
<div class="barra-botones">
    <a href="recarga.php">💳<br>Recarga</a>
    <a href="retirar.php">👛<br>Retirar</a>
</div>

<!-- MENÚ INFERIOR -->
<div class="navegacion-inferior">
    <a href="dashboard.php">🏠<br>Hogar</a>
    <a href="planes.php">📈<br>Inversión</a>
    <a href="equipo.php">👥<br>Equipo</a>
    <a href="perfil.php">👤<br>Perfil</a>
    <a href="logout.php">🚪<br>Salir</a>
</div>

</body>
</html>
