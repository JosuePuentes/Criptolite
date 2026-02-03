<?php
/**
 * Script de un solo uso: crea el usuario administrador en la base de datos.
 * Ejecuta una vez y luego BORRA este archivo por seguridad.
 *
 * Cómo usar: abre en el navegador (o ejecuta por CLI):
 *   https://tu-dominio.com/crear_admin.php?ejecutar=1
 *
 * Usuario creado:
 *   Celular (login): Lamente
 *   Contraseña: admin123
 *   Nombre: Lamente
 */
if (!isset($_GET['ejecutar']) || $_GET['ejecutar'] !== '1') {
    die('Para crear el admin, abre esta URL con ?ejecutar=1 y luego borra este archivo.');
}

require 'db.php';

$celular = 'Lamente';
$password = 'admin123';
$existente = $db->users->findOne(['celular' => $celular]);

if ($existente) {
    echo '<p>El usuario <strong>Lamente</strong> ya existe. Puedes iniciar sesión con:<br>Celular: <strong>Lamente</strong><br>Contraseña: <strong>admin123</strong></p>';
    echo '<p><strong>Borra este archivo (crear_admin.php) por seguridad.</strong></p>';
    exit;
}

$doc = [
    'nombre_completo' => 'Lamente',
    'celular'         => $celular,
    'password'        => password_hash($password, PASSWORD_BCRYPT),
    'referido_por'    => null,
    'saldo_capital'   => 0,
    'saldo_disponible'=> 0,
    'bono_reclamado'  => 0,
    'correo'          => '',
    'cedula'          => '',
    'banco'           => '',
    'cuenta_banco'    => ''
];

$db->users->insertOne($doc);

echo '<h2>Usuario administrador creado</h2>';
echo '<p>Inicia sesión en la app con:</p>';
echo '<ul><li><strong>Celular:</strong> Lamente</li><li><strong>Contraseña:</strong> admin123</li></ul>';
echo '<p><strong>Importante:</strong> Borra el archivo <code>crear_admin.php</code> del servidor por seguridad.</p>';
