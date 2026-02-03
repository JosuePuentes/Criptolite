<?php
date_default_timezone_set('America/Bogota');
require 'db.php';

$planes_activos = $db->compras->find(['activo' => 1]);

foreach ($planes_activos as $plan) {
    $compra_id = $plan['_id'];
    $user_id = $plan['user_id'];
    $ganancia_diaria = (float)($plan['ganancia_diaria'] ?? 0);
    $dias_transcurridos = (int)($plan['dias_transcurridos'] ?? 0);
    $duracion = (int)($plan['duracion'] ?? 0);
    $precio_invertido = (float)($plan['precio'] ?? 0);
    $unidad_duracion = $plan['unidad_duracion'] ?? 'dias';
    $fecha_inicio = $plan['fecha_inicio'] ?? null;

    $fecha_actual = new DateTime();
    $fecha_inicio_dt = $fecha_inicio instanceof MongoDB\BSON\UTCDateTime
        ? $fecha_inicio->toDateTime()
        : new DateTime($fecha_inicio ?? 'now');

    if ($unidad_duracion === 'minutos') {
        $diferencia = $fecha_inicio_dt->diff($fecha_actual);
        $minutos_pasados = ($diferencia->days * 24 * 60) + ($diferencia->h * 60) + $diferencia->i;
        if ($minutos_pasados >= ($dias_transcurridos + 1)) {
            $db->users->updateOne(['_id' => _id($user_id)], ['$inc' => ['saldo_disponible' => $ganancia_diaria]]);
            $db->historial_ganancias->insertOne(['user_id' => $user_id, 'compra_id' => (string)$compra_id, 'monto' => $ganancia_diaria, 'fecha' => new MongoDB\BSON\UTCDateTime()]);
            $db->compras->updateOne(['_id' => $compra_id], ['$inc' => ['dias_transcurridos' => 1]]);
            echo "⏱️ Ganancia de minuto sumada para usuario $user_id (plan ID $compra_id)<br>";
        }
        if (($dias_transcurridos + 1) >= $duracion) {
            $db->users->updateOne(['_id' => _id($user_id)], ['$inc' => ['saldo_capital' => $precio_invertido]]);
            $db->compras->updateOne(['_id' => $compra_id], ['$set' => ['activo' => 0]]);
            echo "🎯 Plan de minutos terminado, capital devuelto para usuario $user_id<br>";
        }
    } else {
        if ($dias_transcurridos < $duracion) {
            $db->users->updateOne(['_id' => _id($user_id)], ['$inc' => ['saldo_disponible' => $ganancia_diaria]]);
            $db->historial_ganancias->insertOne(['user_id' => $user_id, 'compra_id' => (string)$compra_id, 'monto' => $ganancia_diaria, 'fecha' => new MongoDB\BSON\UTCDateTime()]);
            $db->compras->updateOne(['_id' => $compra_id], ['$inc' => ['dias_transcurridos' => 1]]);
            echo "📅 Ganancia de día sumada para usuario $user_id (plan ID $compra_id)<br>";
        }
        if (($dias_transcurridos + 1) >= $duracion) {
            $db->users->updateOne(['_id' => _id($user_id)], ['$inc' => ['saldo_capital' => $precio_invertido]]);
            $db->compras->updateOne(['_id' => $compra_id], ['$set' => ['activo' => 0]]);
            echo "🎯 Plan de días terminado, capital devuelto para usuario $user_id<br>";
        }
    }
}
