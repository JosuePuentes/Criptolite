<?php
date_default_timezone_set('America/Bogota');
require 'db.php';

$planes_activos = $conn->query("SELECT * FROM compras WHERE activo = 1");

while ($plan = $planes_activos->fetch_assoc()) {
    $compra_id = $plan['id'];
    $user_id = $plan['user_id'];
    $ganancia_diaria = $plan['ganancia_diaria'];
    $dias_transcurridos = $plan['dias_transcurridos'];
    $duracion = $plan['duracion'];
    $precio_invertido = $plan['precio'];
    $unidad_duracion = $plan['unidad_duracion'];
    $fecha_inicio = $plan['fecha_inicio'];

    $fecha_actual = new DateTime();
    $fecha_inicio_dt = new DateTime($fecha_inicio);

    if ($unidad_duracion == 'minutos') {
        $diferencia = $fecha_inicio_dt->diff($fecha_actual);
        $minutos_pasados = ($diferencia->days * 24 * 60) + ($diferencia->h * 60) + $diferencia->i;
        if ($minutos_pasados >= ($dias_transcurridos + 1)) {
            // Sumar ganancia
            $conn->query("UPDATE users SET saldo_disponible = saldo_disponible + $ganancia_diaria WHERE id = $user_id");
            $conn->query("INSERT INTO historial_ganancias (user_id, compra_id, monto, fecha) VALUES ($user_id, $compra_id, $ganancia_diaria, NOW())");
            $conn->query("UPDATE compras SET dias_transcurridos = dias_transcurridos + 1 WHERE id = $compra_id");
            echo "⏱️ Ganancia de minuto sumada para usuario $user_id (plan ID $compra_id)<br>";
        }
        if (($dias_transcurridos + 1) >= $duracion) {
            $conn->query("UPDATE users SET saldo_capital = saldo_capital + $precio_invertido WHERE id = $user_id");
            $conn->query("UPDATE compras SET activo = 0 WHERE id = $compra_id");
            echo "🎯 Plan de minutos terminado, capital devuelto para usuario $user_id<br>";
        }
    } else { // Caso normal de dias
        if ($dias_transcurridos < $duracion) {
            $conn->query("UPDATE users SET saldo_disponible = saldo_disponible + $ganancia_diaria WHERE id = $user_id");
            $conn->query("INSERT INTO historial_ganancias (user_id, compra_id, monto, fecha) VALUES ($user_id, $compra_id, $ganancia_diaria, NOW())");
            $conn->query("UPDATE compras SET dias_transcurridos = dias_transcurridos + 1 WHERE id = $compra_id");
            echo "📅 Ganancia de día sumada para usuario $user_id (plan ID $compra_id)<br>";
        }
        if (($dias_transcurridos + 1) >= $duracion) {
            $conn->query("UPDATE users SET saldo_capital = saldo_capital + $precio_invertido WHERE id = $user_id");
            $conn->query("UPDATE compras SET activo = 0 WHERE id = $compra_id");
            echo "🎯 Plan de días terminado, capital devuelto para usuario $user_id<br>";
        }
    }
}
?>
