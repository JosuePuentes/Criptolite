<?php
include('db.php');

$mensaje = '';

$referido_url = isset($_GET['referido']) ? trim($_GET['referido']) : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $celular = trim($_POST['celular']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $referido = !empty($_POST['referido']) ? trim($_POST['referido']) : null;

    $existente = $db->users->findOne(['celular' => $celular]);

    if ($existente) {
        $mensaje = "El número de celular ya está registrado.";
    } else {
        $doc = [
            'nombre_completo' => $nombre,
            'celular' => $celular,
            'password' => $password,
            'referido_por' => $referido,
            'saldo_capital' => 0,
            'saldo_disponible' => 0,
            'bono_reclamado' => 0,
            'correo' => '',
            'cedula' => '',
            'banco' => '',
            'cuenta_banco' => ''
        ];
        $result = $db->users->insertOne($doc);
        if ($result->getInsertedId()) {
            header("Location: index.php?registro=ok");
            exit();
        }
        $mensaje = "Error al registrar. Intenta de nuevo.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="/img/favicon.ico.png" type="image/png">
<title>Registro</title>

<style>
body {
    margin: 0;
    padding: 0;
    font-family: 'Arial', sans-serif;

    /* ✅ Fondo con imagen activa */
    background: url('/img/fondo.jpg') no-repeat center center fixed;
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
<body class="registro">

<div class="contenedor">
    <h1></h1>
    <?php if ($mensaje != '') { echo "<p style='color:red;'>$mensaje</p>"; } ?>
    <form method="POST">
        <input type="text" name="nombre" placeholder="Nombre completo" required>
        <input type="text" name="celular" placeholder="Número de celular" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <input type="password" name="confirmar_password" placeholder="Confirmar contraseña" required>
        <input type="text" name="referido" placeholder="Código de referido (opcional)" value="<?php echo $referido_url; ?>">
        <button type="submit">Registrarme</button>
        <p>¿Ya tienes cuenta? <a href="index.php">Iniciar sesión</a></p>
    </form>
</div>

</body>
</html>


