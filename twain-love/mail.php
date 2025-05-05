<?php
// Conexión
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "quince";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener cantidad
$cantidad = intval($_POST['numAsistentes']);

// Insertar en tabla rsvps
$stmt = $conn->prepare("INSERT INTO rsvps (cantidad_asistentes) VALUES (?)");
$stmt->bind_param("i", $cantidad);
$stmt->execute();
$rsvp_id = $stmt->insert_id;
$stmt->close();

// Insertar cada asistente
for ($i = 1; $i <= $cantidad; $i++) {
    $nombre = $_POST["nombre$i"];
    $telefono = $_POST["telefono$i"];
    $alimentacion = $_POST["alimentacion$i"];
    $edad = $_POST["edad$i"];
    $asistencia = $_POST["asistencia$i"];

    // Calcular valor
    if ($asistencia === "Brindis") {
        $valor = 31000;
    } else {
        $valor = ($edad === "Mayor") ? 62000 : 31000;
    }

    $stmt = $conn->prepare("INSERT INTO asistentes (rsvp_id, nombre, telefono, alimentacion, edad, asistencia, valor_tarjeta) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssi", $rsvp_id, $nombre, $telefono, $alimentacion, $edad, $asistencia, $valor);
    $stmt->execute();
    $stmt->close();
}

$conn->close();

// Mostrar mensaje de éxito
echo "<script>
    document.getElementById('success').style.display = 'block';
    document.getElementById('rsvp-form').reset();
</script>";
?>
