<?php
session_start();
include('db.php');

// Verificar si está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Obtener datos del usuario
$user_id = $_SESSION['user_id'];

// Obtener datos de referidos
$stmt = $conn->prepare("
    SELECT u.id, u.nombre_completo, u.celular, u.saldo_capital, u.saldo_disponible
    FROM users u
    WHERE u.referido_por = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$resultado = $stmt->get_result();

$referidos = [];
$referidos_activos = 0; // Contador de referidos con plan activo

while ($row = $resultado->fetch_assoc()) {
    // Verificar si el referido tiene un plan activo
    $stmt2 = $conn->prepare("
        SELECT COUNT(*) 
        FROM compras 
        WHERE user_id = ? AND activo = 1
    ");
    $stmt2->bind_param("i", $row['id']);
    $stmt2->execute();
    $stmt2->bind_result($planes_activos);
    $stmt2->fetch();
    $stmt2->close();
    
    $row['planes_activos'] = $planes_activos;
    if ($planes_activos > 0) {
        $referidos_activos++;
    }
    
    $referidos[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mi Equipo</title>

<style>
/* ESTILOS GENERALES */
body {
    margin: 0;
    font-family: 'Arial', sans-serif;
    background: url('/inver/img/fondo.jpg') no-repeat center center fixed;
    background-size: cover;
    color: white;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding-bottom: 180px; /* Espacio para botones y menú */
}

.contenedor {
    width: 100%;
    max-width: 400px;
    margin-top: 20px;
    padding: 15px;
}

h1 {
    font-size: 24px;
    color: #00BFFF;
    text-align: center;
    margin-bottom: 15px;
}

.link-referido {
    background: #1e1e1e;
    padding: 15px;
    border-radius: 10px;
    text-align: center;
    font-size: 16px;
    margin-bottom: 20px;
    word-break: break-word;
}

.referido {
    background: #1e1e1e;
    margin-bottom: 15px;
    padding: 15px;
    border-radius: 12px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.5);
}

.referido p {
    margin: 5px 0;
    font-size: 16px;
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
    font-size: 18px;
    text-align: center;
}

.navegacion-inferior a:hover {
    color:rgb(255, 0, 0);
}
</style>

</head>

<body>

<div class="contenedor">
    <h1>Mi Equipo</h1>

    <div class="link-referido">
        Tu link de referido:<br>
        <small><?php echo "https://tuweb.com/register.php?referido=" . $user_id; ?></small>
    </div>

    <?php if (count($referidos) > 0): ?>
        <?php foreach ($referidos as $r): ?>
            <div class="referido">
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($r['nombre_completo']); ?></p>
                <p><strong>Celular:</strong> <?php echo htmlspecialchars($r['celular']); ?></p>
                <p><strong>Saldo Capital:</strong> $<?php echo number_format($r['saldo_capital'], 2); ?></p>
                <p><strong>Saldo Disponible:</strong> $<?php echo number_format($r['saldo_disponible'], 2); ?></p>
                <p><strong>Planes Activos:</strong> <?php echo $r['planes_activos']; ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align:center;">Todavía no tienes referidos 😔</p>
    <?php endif; ?>

    <?php if ($referidos_activos >= 10): ?>
        <div style="text-align:center; margin-top: 20px;">
            <form method="post" action="reclamar_bono.php">
                <button type="submit" style="padding: 15px; font-size: 18px; background-color: #00BFFF; color: white; border: none; border-radius: 10px;">
                    🎁 Reclamar Bono de $25
                </button>
            </form>
        </div>
    <?php endif; ?>
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

<!-- Mensajes de Alerta -->
<?php if (isset($_GET['bono']) && $_GET['bono'] == 'ok'): ?>
<script>
alert('¡Felicidades! Has reclamado tu bono de $50,000 🎉');
</script>
<?php endif; ?>

<?php if (isset($_GET['bono']) && $_GET['bono'] == 'ya_reclamado'): ?>
<script>
alert('Ya has reclamado tu bono anteriormente 🛡️.');
</script>
<?php endif; ?>

</body>
</html>
