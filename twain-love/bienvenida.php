<?php
session_start();

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header('Location: login.php');
    exit;
}

// Conexión a la base de datos
require_once 'db.php'; // Asegúrate que $conn es la variable de conexión

// Consultar la tabla asistentes
$sql_asistentes = "SELECT nombre, telefono, alimentacion, edad, asistencia, valor_tarjeta FROM asistentes";
$result_asistentes = $conn->query($sql_asistentes);
if (!$result_asistentes) {
    die("Error en la consulta asistentes: " . $conn->error);
}

// Consultar la tabla resumen_asistentes (solo una fila)
$sql_resumen = "SELECT * FROM resumen_asistentes LIMIT 1";
$result_resumen = $conn->query($sql_resumen);
if (!$result_resumen) {
    die("Error en la consulta resumen_asistentes: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Página de Bienvenida</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #aaa;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #efefef;
        }
        h2 {
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <h1>Bienvenido al Panel de Administración</h1>

    <h2>Listado de Asistentes</h2>
    <table>
        <tr>
            <th>Nombre</th>
            <th>Teléfono</th>
            <th>Alimentación</th>
            <th>Edad</th>
            <th>Asistencia</th>
            <th>Valor Tarjeta</th>
        </tr>
        <?php while ($row = $result_asistentes->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['nombre']) ?></td>
            <td><?= htmlspecialchars($row['telefono']) ?></td>
            <td><?= htmlspecialchars($row['alimentacion']) ?></td>
            <td><?= htmlspecialchars($row['edad']) ?></td>
            <td><?= htmlspecialchars($row['asistencia']) ?></td>
            <td>$<?= htmlspecialchars($row['valor_tarjeta']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <h2>Resumen General de Asistentes</h2>

    <form method="post" action="actualizar_resumen.php">
        <button type="submit" name="actualizar_resumen">Actualizar Resumen</button>
    </form>

    <?php if ($rowResumen = $result_resumen->fetch_assoc()): ?>
    <table>
        <tr>
            <th>Cantidad Cena</th>
            <th>Cantidad Brindis</th>
            <th>Cantidad Total</th>
            <th>Suma Valores</th>
            <th>Normal</th>
            <th>Vegetariana</th>
            <th>Vegano</th>
            <th>Celíaco</th>
        </tr>
        <tr>
            <td><?= $rowResumen['cantidad_cena'] ?></td>
            <td><?= $rowResumen['cantidad_brindis'] ?></td>
            <td><?= $rowResumen['cantidad_total'] ?></td>
            <td>$<?= $rowResumen['suma_valores'] ?></td>
            <td><?= $rowResumen['cantidad_normal'] ?></td>
            <td><?= $rowResumen['cantidad_vegetariana'] ?></td>
            <td><?= $rowResumen['cantidad_vegano'] ?></td>
            <td><?= $rowResumen['cantidad_celiaco'] ?></td>
        </tr>
    </table>
    <?php else: ?>
        <p>No hay datos en la tabla <strong>resumen_asistentes</strong>.</p>
    <?php endif; ?>
    
</body>
</html>
