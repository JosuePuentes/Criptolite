<?php
session_start();
include('db.php');

// Verificar si está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener información actual del usuario
$stmt = $conn->prepare("SELECT nombre_completo, celular, correo, cedula, banco, cuenta_banco FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($nombre, $celular, $correo, $cedula, $banco, $cuenta_banco);
$stmt->fetch();
$stmt->close();

// Procesar actualización
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nuevo_nombre = trim($_POST['nombre']);
    $nuevo_celular = trim($_POST['celular']);
    $nuevo_correo = trim($_POST['correo']);
    $nueva_cedula = trim($_POST['cedula']);
    $nuevo_banco = trim($_POST['banco']);
    $nueva_cuenta = trim($_POST['cuenta_banco']);
    $nuevo_password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;

    if ($nuevo_password) {
        $stmt = $conn->prepare("UPDATE users SET nombre_completo = ?, celular = ?, correo = ?, cedula = ?, banco = ?, cuenta_banco = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssssssi", $nuevo_nombre, $nuevo_celular, $nuevo_correo, $nueva_cedula, $nuevo_banco, $nueva_cuenta, $nuevo_password, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET nombre_completo = ?, celular = ?, correo = ?, cedula = ?, banco = ?, cuenta_banco = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $nuevo_nombre, $nuevo_celular, $nuevo_correo, $nueva_cedula, $nuevo_banco, $nueva_cuenta, $user_id);
    }

    if ($stmt->execute()) {
        header("Location: perfil.php?actualizado=ok");
        exit();
    } else {
        echo "Error al actualizar. Intenta de nuevo.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Perfil</title>

<style>
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
    margin-bottom: 15px;
}

form {
    background: #1e1e1e;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.5);
}

input[type="text"], input[type="password"], input[type="email"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 12px;
    border: none;
    border-radius: 8px;
    background: #2a2a2a;
    color: white;
}

button {
    width: 100%;
    padding: 12px;
    background:rgb(237, 32, 32);
    color: white;
    border: none;
    font-size: 16px;
    border-radius: 8px;
    cursor: pointer;
}

button:hover {
    background:rgb(204, 0, 0);
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
    <h1>Mi Perfil</h1>

    <?php if (isset($_GET['actualizado']) && $_GET['actualizado'] == 'ok') : ?>
        <p style="color: #00FF00; text-align: center;">¡Perfil actualizado exitosamente!</p>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" placeholder="Nombre completo" required>
        <input type="text" name="celular" value="<?php echo htmlspecialchars($celular); ?>" placeholder="Número de celular" required>
        <input type="email" name="correo" value="<?php echo htmlspecialchars($correo); ?>" placeholder="Correo electrónico">
        <input type="text" name="cedula" value="<?php echo htmlspecialchars($cedula); ?>" placeholder="Cédula">
        <input type="text" name="banco" value="<?php echo htmlspecialchars($banco); ?>" placeholder="Moneda (usdt,btc,bnb,eth)">
        <input type="text" name="cuenta_banco" value="<?php echo htmlspecialchars($cuenta_banco); ?>" placeholder="Wallet">
        <input type="password" name="password" placeholder="Nueva contraseña (opcional)">
        <button type="submit">Actualizar Perfil</button>
    </form>
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
