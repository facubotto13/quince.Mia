<?php
session_start();

// Datos fijos para un solo usuario
$usuario_valido = 'flosano';
$contrasena_valida = 'marchini2025';

if ($_POST['usuario'] === $usuario_valido && $_POST['contrasena'] === $contrasena_valida) {
    $_SESSION['logueado'] = true;
    header('Location: bienvenida.php');
    exit;
} else {
    header('Location: login.php?error=1');
    exit;
}
?>
