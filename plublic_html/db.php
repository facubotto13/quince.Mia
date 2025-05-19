<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "quince";

$conn = new mysqli($servername, $username, $password, $database);

// Verifica conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
