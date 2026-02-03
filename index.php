<?php
// index.php
include('db.php');
session_start();

$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $celular = trim($_POST['celular']);
    $password = $_POST['password'];

    $user = $db->users->findOne(['celular' => $celular]);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = (string) $user['_id'];
        header("Location: dashboard.php");
        exit();
    }
    if ($user) {
        $mensaje = "Contraseña incorrecta.";
    } else {
        $mensaje = "Número de celular no encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="/img/favicon.ico.png" type="image/png">
<title>Login</title>

<style>
body {
    margin: 0;
    padding: 0;
    font-family: 'Arial', sans-serif;

    /* ✅ Fondo con imagen activa */
    background: url('/img/fondologin.jpg.png') no-repeat center center fixed;
    background-size: cover;

    color: white;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;

    /* Opcional: oscurecer imagen para mayor contraste */
    /* backdrop-filter: brightness(0.7); */
}

.contenedor {
    width: 100%;
    max-width: 400px;
    padding: 20px;
}

h1 {
    font-size: 24px;
    color:rgb(255, 0, 0);
    margin-bottom: 20px;
    text-align: center;
}

form {
    background: rgba(18, 18, 18, 0.88);
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 0 15px rgb(255, 255, 255);
    display: flex;
    flex-direction: column;
    gap: 15px;
}

form input {
    width: 100%;
    box-sizing: border-box;
    padding: 12px;
    background: rgba(18, 18, 18, 0.88);
    border: none;
    border-radius: 8px;
    color: white;
    font-size: 16px;
}

form input::placeholder {
    color: #aaa;
}

form button {
    width: 100%;
    padding: 12px;
    background:rgb(237, 32, 32);
    border: none;
    border-radius: 8px;
    color: white;
    font-size: 18px;
    cursor: pointer;
    transition: background 0.3s;
}

form button:hover {
    background:rgb(255, 0, 0);
}

p {
    margin-top: 10px;
    text-align: center;
    font-size: 14px;
}

p a {
    color:rgb(255, 0, 0);
    text-decoration: none;
}

p a:hover {
    text-decoration: underline;
}
</style>

</head>
<body class="login">

<div class="contenedor">
    <h1></h1>
    <?php if ($mensaje != '') { echo '<p style="color:red; font-weight:bold;">' . htmlspecialchars($mensaje) . '</p>'; } ?>
    <form method="POST">
        <input type="text" name="celular" placeholder="Número de celular" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Ingresar</button>
        <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
    </form>
</div>

</body>
</html>
