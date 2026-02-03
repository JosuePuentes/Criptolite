<?php
session_start();
date_default_timezone_set('America/Bogota');
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

function calcularTiempos($fecha_inicio, $dias_transcurridos, $duracion) {
    $ts = $fecha_inicio instanceof MongoDB\BSON\UTCDateTime
        ? $fecha_inicio->toDateTime()->getTimestamp()
        : (is_numeric($fecha_inicio) ? $fecha_inicio : strtotime($fecha_inicio));
    return [
        'proxima_ganancia' => $ts + (($dias_transcurridos + 1) * 24 * 60 * 60),
        'fin_plan' => $ts + ($duracion * 24 * 60 * 60)
    ];
}

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
            echo "<script>alert('¡Plan comprado exitosamente!');window.location='plan.php';</script>";
        } else {
            echo "<script>alert('No tienes saldo suficiente para este plan.');</script>";
        }
    }
}

$planes_disponibles = $db->planes_disponibles->find([]);
$planes_activos_cursor = $db->compras->find(['user_id' => $user_id, 'activo' => 1]);
$planes_activos_list = iterator_to_array($planes_activos_cursor);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Planes</title>

<style>
/* ESTILO GENERAL */
body {
    margin: 0;
    font-family: 'Arial', sans-serif;
    background: #121212;
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
    background: #28a745;
    color: white;
    cursor: pointer;
    font-weight: bold;
}

button:hover {
    background: #218838;
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
    color: #00BFFF;
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
    color: #00BFFF;
}
</style>

</head>

<body>

<div class="contenedor">
    <!-- PLANES DISPONIBLES -->
    <div class="section">
        <h2>Planes Disponibles</h2>
        <?php foreach ($planes_disponibles as $plan): ?>
            <div class="card">
                <strong><?= htmlspecialchars($plan['nombre'] ?? '') ?></strong><br><br>
                Precio: $<?= number_format($plan['precio'] ?? 0, 2) ?><br>
                Ganancia diaria: <?= $plan['porcentaje_diario'] ?? 0 ?>%<br>
                Duración: <?= $plan['duracion'] ?? 0 ?> días
                <form method="POST" style="margin-top: 10px;">
                    <input type="hidden" name="comprar_plan_id" value="<?= (string)($plan['_id'] ?? '') ?>">
                    <button type="submit">Comprar Plan</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- PLANES ACTIVOS -->
    <div class="section">
        <h2>Mis Planes Activos</h2>
        <?php if (count($planes_activos_list) > 0): ?>
            <?php foreach ($planes_activos_list as $compra): ?>
                <?php $tiempos = calcularTiempos($compra['fecha_inicio'] ?? null, $compra['dias_transcurridos'] ?? 0, $compra['duracion'] ?? 0); $cid = (string)($compra['_id'] ?? ''); ?>
                <div class="card">
                    <strong><?= htmlspecialchars($compra['plan_nombre'] ?? '') ?></strong><br><br>
                    Inversión: $<?= number_format($compra['precio'] ?? 0, 2) ?><br>
                    Ganancia Diaria: $<?= number_format($compra['ganancia_diaria'] ?? 0, 2) ?><br>
                    Duración: <?= $compra['duracion'] ?? 0 ?> días<br>
                    Días Transcurridos: <?= $compra['dias_transcurridos'] ?? 0 ?><br>
                    <div class="contador" id="contador_<?= $cid ?>"></div>
                    <script>
                        function actualizarContador_<?= preg_replace('/[^a-z0-9]/', '_', $cid) ?>() {
                            var ahora = new Date().getTime();
                            var proxima_ganancia = <?= $tiempos['proxima_ganancia'] * 1000 ?>;
                            var fin_plan = <?= $tiempos['fin_plan'] * 1000 ?>;

                            var faltan_ganancia = proxima_ganancia - ahora;
                            var faltan_fin = fin_plan - ahora;

                            if (faltan_ganancia < 0) faltan_ganancia = 0;
                            if (faltan_fin < 0) faltan_fin = 0;

                            var horas_g = Math.floor((faltan_ganancia % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            var minutos_g = Math.floor((faltan_ganancia % (1000 * 60 * 60)) / (1000 * 60));
                            var segundos_g = Math.floor((faltan_ganancia % (1000 * 60)) / 1000);

                            var dias_f = Math.floor(faltan_fin / (1000 * 60 * 60 * 24));
                            var horas_f = Math.floor((faltan_fin % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));

                            document.getElementById("contador_<?= $cid ?>").innerHTML =
                                "Próxima ganancia en: " + horas_g + "h " + minutos_g + "m " + segundos_g + "s<br>" +
                                "Fin del plan en: " + dias_f + " días " + horas_f + " horas";
                        }
                        setInterval(actualizarContador_<?= preg_replace('/[^a-z0-9]/', '_', $cid) ?>, 1000);
                    </script>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center;">No tienes planes activos.</p>
        <?php endif; ?>
    </div>
</div>

<!-- BARRA DE BOTONES -->
<div class="barra-botones">
    <a href="recarga.php">
        💳<br>Recarga
    </a>
    <a href="retirar.php">
        👛<br>Retirar
    </a>
</div>

<!-- NAVEGACIÓN INFERIOR -->
<div class="navegacion-inferior">
    <a href="dashboard.php">🏠<br>Hogar</a>
    <a href="planes.php">📈<br>Inversión</a>
    <a href="equipo.php">👥<br>Equipo</a>
    <a href="perfil.php">👤<br>Perfil</a>
</div>

</body>
</html>
