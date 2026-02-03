<?php
session_start();
include('db.php');

// Verificar si está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="/img/favicon.ico.png" type="image/png">
<title>Recargar Saldo</title>
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
    padding-bottom: 180px;
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
    margin-bottom: 20px;
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
form {
    background: #1e1e1e;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 4px 8px rgba(97, 97, 97, 0.5);
    display: flex;
    flex-direction: column;
    gap: 15px;
}
input[type="number"], input[type="text"], select {
    padding: 10px;
    font-size: 18px;
    border: none;
    border-radius: 10px;
    width: 100%;
}
.opciones {
    display: flex;
    justify-content: space-between;
}
.opciones button {
    flex: 1;
    margin: 0 5px;
    background:rgb(237, 32, 32);
    color: white;
    border: none;
    padding: 10px;
    font-size: 16px;
    border-radius: 10px;
    cursor: pointer;
}
.opciones button.seleccionado {
    background:rgb(237, 32, 32);
}
.opciones button:hover {
    background:rgb(255, 0, 0);
}
button.enviar {
    background:rgb(237, 32, 32);
    padding: 10px;
    font-size: 18px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
}
button.enviar:hover {
    background:rgb(255, 0, 0);
}
form button:hover {
    background:rgb(255, 0, 0);
}
.mensaje {
    margin-top: 20px;
    font-size: 14px;
    text-align: center;
    color: #ccc;
}

.enviar {
    color: white;
    background-color:rgb(205, 7, 7); /* opcional: color de fondo */
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
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

/* HISTORIAL */
.historial {
    width: 100%;
    max-width: 400px;
    margin-top: 30px;
}
.historial h2 {
    font-size: 22px;
    margin-bottom: 10px;
    color:rgb(255, 0, 0);
}
.historial table {
    width: 100%;
    border-collapse: collapse;
}
.historial th, .historial td {
    border: 1px solid #333;
    padding: 8px;
    text-align: center;
}
.historial th {
    background: #1e1e1e;
}

.fila-recarga {
    background-color: #1e1e1e;
}
</style>

<script>
let bancoSeleccionado = '';

function seleccionarBanco(banco) {
    bancoSeleccionado = banco;
    document.getElementById('banco').value = banco;

    const botones = document.querySelectorAll('.opciones button');
    botones.forEach(btn => btn.classList.remove('seleccionado'));
    document.getElementById(banco).classList.add('seleccionado');

    const qrContainer = document.getElementById('qr-container');
    const qrImagen = document.getElementById('qr-imagen');
    const redText = document.getElementById('qr-red-text');

    let red = '';
    if (banco === 'usdt') {
        qrImagen.src = '/img/usdt.jpg';
        red = 'RED (TR20)';
    } else if (banco === 'btc') {
        qrImagen.src = '/img/btc.jpg';
        red = 'RED (BTC)';
    } else if (banco === 'bnb') {
        qrImagen.src = '/img/bnb.jpg';
        red = 'RED (BEP20)';
    } else if (banco === 'eth') {
        qrImagen.src = '/img/eth.jpg';
        red = 'RED (ERC20)';
    }

    redText.textContent = red;
    qrContainer.style.display = 'block';
}
function validarFormulario() {
    if (bancoSeleccionado === '') {
        alert('Por favor selecciona un método de recarga');
        return false;
    }
    return true;
}
</script>
</head>

<body>

<div class="contenedor">
    <h1>Recargar Saldo</h1>

    <form id="formRecarga" method="POST" action="procesar_recarga.php" onsubmit="return validarFormulario();">
        <input type="number" name="monto" placeholder="Ingrese el monto a recargar" required>
        <input type="hidden" name="banco" id="banco" value="">

        <div class="opciones">
            <button type="button" id="usdt" onclick="seleccionarBanco('usdt')">USDT</button>
            <button type="button" id="btc" onclick="seleccionarBanco('btc')">BTC</button>
            <button type="button" id="bnb" onclick="seleccionarBanco('bnb')">BNB</button>
            <button type="button" id="eth" onclick="seleccionarBanco('eth')">ETH</button>  
        </div>

        <div id="qr-container" style="display:none; text-align:center; margin-top:20px;">
    <p id="qr-red-text" style="margin-bottom: 10px; font-weight: bold; font-size: 14px;">RED (TR20)</p>
    <img id="qr-imagen" src="" alt="QR Banco" style="max-width:100%; border-radius:10px;">
    <input type="text" name="referencia" placeholder="Ingrese referencia de su Criptomoneda" required style="margin-top:15px; padding:10px; font-size:16px; border-radius:10px; width:100%;">
</div>


        <button type="submit" class="enviar">Enviar</button>
        

        <div class="mensaje">
            <p><strong>Paso 1:</strong> Recarga mínima 20.000 pesos</p>
            <p><strong>Nota:</strong> La recarga puede demorar de 2 a 24 horas en llegar.</p>
        </div>
    </form>
</div>

<!-- HISTORIAL DE RECARGAS -->
<div class="historial">
    <h2>Historial de Recargas</h2>
    <table>
        <tr>
            <th>Monto</th>
            <th>Cripto</th>
            <th>Referencia</th>
            <th>Estado</th>
            <th>Fecha</th>
        </tr>
        <?php
$user_id = $_SESSION['user_id'];
$resultado = $db->recargas->find(['user_id' => $user_id], ['sort' => ['fecha' => -1]]);
foreach ($resultado as $row) {
    $estado_clase = "estado-" . strtolower($row['estado'] ?? '');
    $fechaStr = $row['fecha'] instanceof MongoDB\BSON\UTCDateTime
        ? $row['fecha']->toDateTime()->format('d/m/Y H:i')
        : (is_string($row['fecha'] ?? '') ? date('d/m/Y H:i', strtotime($row['fecha'])) : '');
    echo "<tr class='fila-recarga'>
            <td>$" . number_format($row['monto'], 0, ',', '.') . "</td>
            <td>" . ucfirst($row['banco'] ?? '') . "</td>
            <td>" . htmlspecialchars($row['referencia'] ?? '') . "</td>
            <td class='$estado_clase'>" . ucfirst($row['estado'] ?? '') . "</td>
            <td>" . $fechaStr . "</td>
          </tr>";
}
?>

    </table>
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

