<?php
session_start();
include('db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = $db->users->findOne(['_id' => _id($user_id)]);
if (!$user) {
    session_destroy();
    header("Location: index.php");
    exit();
}
$nombre = $user['nombre_completo'] ?? '';
$capital = $user['saldo_capital'] ?? 0;
$disponible = $user['saldo_disponible'] ?? 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard</title>

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
    color: white;
    margin-bottom: 20px;
    text-align: center;
}

/* Saldos con diseño circular */
.saldo-box {
    background-color: #0d1721;
    padding: 20px;
    border-radius: 15px;
    margin-bottom: 20px;
    box-shadow: 0 0 10px rgba(0,0,0,0.5);
}

.item {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    margin-right: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.capital {
    background-color: #ff7f0e; /* naranja */
}

.disponible {
    background-color: #2ca02c; /* verde */
}

.text .label {
    font-size: 16px;
    margin: 0;
    color: white;
}

.text .value {
    font-size: 18px;
    font-weight: bold;
    margin: 5px 0 0 0;
    color: white;
}

/* SLIDER */
.slider {
    position: relative;
    width: 100%;
    max-width: 400px;
    height: 180px;
    overflow: hidden;
    border-radius: 15px;
    margin-bottom: 20px;
}

.slides {
    display: flex;
    transition: transform 0.5s ease-in-out;
    height: 100%;
}

.slides img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.slider-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0,0,0,0.5);
    color: white;
    border: none;
    padding: 8px 12px;
    cursor: pointer;
    z-index: 2;
    font-size: 20px;
    border-radius: 5px;
}

#prevBtn {
    left: 10px;
}

#nextBtn {
    right: 10px;
}

/* BOTÓN TELEGRAM FLOTANTE Y MOVIBLE */
.boton-telegram {
    position: fixed;
    bottom: 150px;
    right: 20px;
    background-color: #0088cc;
    color: white;
    border: none;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    font-size: 30px;
    text-align: center;
    line-height: 60px;
    cursor: move;
    z-index: 1001;
    text-decoration: none;
    box-shadow: 0 4px 8px rgba(0,0,0,0.4);
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
    color: rgb(255, 0, 0);
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
    color: rgb(255, 0, 0);
}
</style>
</head>

<body>

<div class="contenedor">
    <h1>Bienvenido, <?php echo htmlspecialchars($nombre); ?> 👋</h1>

    <!-- SALDO VISUAL -->
    <div class="saldo-box">
        <div class="item">
            <div class="icon capital">💼</div>
            <div class="text">
                <p class="label">Saldo Capital</p>
                <p class="value">$<?php echo number_format($capital, 2); ?></p>
            </div>
        </div>
        <div class="item">
            <div class="icon disponible">💳</div>
            <div class="text">
                <p class="label">Saldo Disponible</p>
                <p class="value">$<?php echo number_format($disponible, 2); ?></p>
            </div>
        </div>
    </div>

    <!-- SLIDER CON FLECHAS -->
    <div class="slider" id="slider">
        <button class="slider-arrow" id="prevBtn">&#10094;</button>
        <div class="slides" id="slides">
            <img src="/img/1.png" alt="Imagen 1">
            <img src="/img/1.png" alt="Imagen 2">
            <img src="/img/1.png" alt="Imagen 3">
            <img src="/img/1.png" alt="Imagen 4">
            <img src="/img/1.png" alt="Imagen 5">
        </div>
        <button class="slider-arrow" id="nextBtn">&#10095;</button>
    </div>
</div>

<!-- BOTÓN TELEGRAM -->
<a href="https://t.me/TU_USUARIO_O_CANAL" class="boton-telegram" id="botonTelegram" title="Soporte Telegram" target="_blank">✉️</a>

<!-- BARRA DE BOTONES -->
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
    <a href="perfil.php">⚙️<br>Soporte</a>
    <a href="logout.php">🚪<br>Salir</a>
</div>

<script>
// SLIDER CON FLECHAS
let slides = document.getElementById('slides');
let currentIndex = 0;
const totalImages = slides.children.length;

document.getElementById('nextBtn').addEventListener('click', () => {
    currentIndex = (currentIndex + 1) % totalImages;
    slides.style.transform = `translateX(-${currentIndex * 100}%)`;
});

document.getElementById('prevBtn').addEventListener('click', () => {
    currentIndex = (currentIndex - 1 + totalImages) % totalImages;
    slides.style.transform = `translateX(-${currentIndex * 100}%)`;
});

// BOTÓN TELEGRAM MOVIBLE
const botonTelegram = document.getElementById("botonTelegram");
let offsetX, offsetY, isDragging = false;

botonTelegram.addEventListener("mousedown", (e) => {
    isDragging = true;
    offsetX = e.clientX - botonTelegram.getBoundingClientRect().left;
    offsetY = e.clientY - botonTelegram.getBoundingClientRect().top;
    botonTelegram.style.cursor = "grabbing";
});

document.addEventListener("mousemove", (e) => {
    if (isDragging) {
        botonTelegram.style.left = `${e.clientX - offsetX}px`;
        botonTelegram.style.top = `${e.clientY - offsetY}px`;
        botonTelegram.style.right = "auto";
        botonTelegram.style.bottom = "auto";
        botonTelegram.style.position = "fixed";
    }
});

document.addEventListener("mouseup", () => {
    isDragging = false;
    botonTelegram.style.cursor = "move";
});
</script>

</body>
</html>
