<?php
session_start();
date_default_timezone_set('America/Bogota');
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['comprar_plan_id'])) {
    $plan_id = $_POST['comprar_plan_id'];
    $plan = $db->planes_disponibles->findOne(['_id' => _id($plan_id)]);

    if ($plan) {
        $user = $db->users->findOne(['_id' => _id($user_id)]);
        $saldo = $user['saldo_capital'] ?? 0;
        $precio = (float)($plan['precio'] ?? 0);

        if ($saldo >= $precio) {
            $ganancia_diaria = $precio * ((float)($plan['porcentaje_diario'] ?? 0) / 100);
            $db->compras->insertOne([
                'user_id' => $user_id,
                'plan_id' => $plan_id,
                'plan_nombre' => $plan['nombre'] ?? '',
                'porcentaje_diario' => $plan['porcentaje_diario'] ?? 0,
                'ganancia_diaria' => $ganancia_diaria,
                'precio' => $precio,
                'duracion' => (int)($plan['duracion'] ?? 0),
                'fecha_inicio' => new MongoDB\BSON\UTCDateTime(),
                'activo' => 1,
                'dias_transcurridos' => 0
            ]);
            $db->users->updateOne(
                ['_id' => _id($user_id)],
                ['$inc' => ['saldo_capital' => -$precio]]
            );
            echo "<script>alert('¡Plan comprado exitosamente!');window.location='planes.php';</script>";
        } else {
            echo "<script>alert('No tienes saldo suficiente para este plan.');</script>";
        }
    }
}

$planes_disponibles = $db->planes_disponibles->find([]);
$planes_activos = $db->compras->find(['user_id' => $user_id, 'activo' => 1]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Planes</title>

<style>
body {
    margin: 0;
    font-family: 'Arial', sans-serif;
    background: url('/img/fondo.jpg') no-repeat center center fixed;
    background-size: cover;
    color: white;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding-bottom: 180px; /* espacio para botones */
}

/* CONTENEDOR PRINCIPAL */
.contenedor {
    width: 100%;
    max-width: 400px;
    margin-top: 20px;
    padding: 15px;
}

.section {
    margin-bottom: 30px;
}

.card {
    background: #1e1e1e;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.5);
    margin-bottom: 20px;
}

h2 {
    font-size: 24px;
    color: #00BFFF;
    margin-bottom: 15px;
    text-align: center;
}

input, button {
    width: 100%;
    margin: 8px 0;
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
}

button {
    background:rgb(237, 32, 32);
    color: white;
    cursor: pointer;
    font-weight: bold;
}

button:hover {
    background:rgb(255, 0, 0);
}

/* BOTONES DE RECARGA Y RETIRO */
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
    font-size: 24px;
    font-weight: bold;
    text-decoration: none;
}

.barra-botones a:hover {
    color:rgb(255, 0, 0);
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
</style>
</head>

<body>

<div class="contenedor">
    <div class="section">
        <h2>Planes Disponibles</h2>
        <?php foreach ($planes_disponibles as $plan): ?>
            <div class="card">
                <strong><?= htmlspecialchars($plan['nombre']) ?></strong><br><br>
                Precio: $<?= number_format($plan['precio'], 2) ?><br>
                Ganancia diaria: <?= $plan['porcentaje_diario'] ?>%<br>
                Duración: <?= $plan['duracion'] ?> días
                <form method="POST" style="margin-top: 10px;">
                    <input type="hidden" name="comprar_plan_id" value="<?= (string)($plan['_id'] ?? '') ?>">
                    <button type="submit">Comprar Plan</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>

    <div class="section">
        <h2>Mis planes activos</h2>
        <?php $planes_activos_list = iterator_to_array($planes_activos); if (count($planes_activos_list) > 0): ?>
            <?php foreach ($planes_activos as $compra): ?>
                <div class="card">
                    <strong><?= htmlspecialchars($compra['plan_nombre']) ?></strong><br><br>
                    Inversión: $<?= number_format($compra['precio'], 2) ?><br>
                    Ganancia Diaria: $<?= number_format($compra['ganancia_diaria'], 2) ?><br>
                    Duración: <?= $compra['duracion'] ?> días<br>
                    Días Transcurridos: <?= $compra['dias_transcurridos'] ?><br>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center;">No tienes planes activos.</p>
        <?php endif; ?>
    </div>
</div>

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
