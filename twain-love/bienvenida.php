<?php
session_start();

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header('Location: login.php');
    exit;
}

// Conexión a la base de datos
require_once 'db.php';

// Filtrado por nombre
$nombre_busqueda = '';
if (isset($_GET['buscar']) && !empty(trim($_GET['buscar']))) {
    $nombre_busqueda = trim($_GET['buscar']);
    $sql_asistentes = "SELECT nombre, telefono, alimentacion, edad, asistencia, valor_tarjeta, pagado
                       FROM asistentes 
                       WHERE nombre LIKE ?";
    $stmt = $conn->prepare($sql_asistentes);
    $like_param = '%' . $nombre_busqueda . '%';
    $stmt->bind_param("s", $like_param);
    $stmt->execute();
    $result_asistentes = $stmt->get_result();
} else {
    $sql_asistentes = "SELECT nombre, telefono, alimentacion, edad, asistencia, valor_tarjeta, pagado FROM asistentes";
    $result_asistentes = $conn->query($sql_asistentes);
}

if (!$result_asistentes) {
    die("Error en la consulta asistentes: " . $conn->error);
}

// Consulta resumen
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
        .buscador {
            margin-bottom: 20px;
        }
        .fila-pagado {
        background-color: #d4edda; /* verde suave */
    }
    </style>
</head>
<body>
    <h1>Bienvenido al Panel de Administración</h1>

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
            <td><?= $rowResumen['cantidad_vegetariano'] ?></td>
            <td><?= $rowResumen['cantidad_vegano'] ?></td>
            <td><?= $rowResumen['cantidad_celiaco'] ?></td>
        </tr>
    </table>
    <?php else: ?>
        <p>No hay datos en la tabla <strong>resumen_asistentes</strong>.</p>
    <?php endif; ?>

    <h2>Listado de Asistentes</h2>

    <!-- Buscador por nombre -->
    <form method="get" class="buscador">
        <input type="text" name="buscar" placeholder="Buscar por nombre" value="<?= htmlspecialchars($nombre_busqueda) ?>">
        <button type="submit">Buscar</button>
        <a href="bienvenida.php"><button type="button">Limpiar</button></a>
    </form>

    <table>
    <tr>
        <th>Nombre</th>
        <th>Teléfono</th>
        <th>Alimentación</th>
        <th>Edad</th>
        <th>Asistencia</th>
        <th>Valor Tarjeta</th>
        <th>Pagado</th>
    </tr>
    <?php while ($row = $result_asistentes->fetch_assoc()): ?>
<tr class="<?= $row['pagado'] ? 'fila-pagado' : '' ?>">
    <td><?= htmlspecialchars($row['nombre']) ?></td>
    <td><?= htmlspecialchars($row['telefono']) ?></td>
    <td><?= htmlspecialchars($row['alimentacion']) ?></td>
    <td><?= htmlspecialchars($row['edad']) ?></td>
    <td><?= htmlspecialchars($row['asistencia']) ?></td>
    <td>$<?= htmlspecialchars($row['valor_tarjeta']) ?></td>
    <td>
        <input type="checkbox" class="pagado-checkbox"
               data-nombre="<?= htmlspecialchars($row['nombre']) ?>"
               <?= $row['pagado'] ? 'checked' : '' ?>>
    </td>
</tr>
<?php endwhile; ?>

</table>


    <!-- Botón de cerrar sesión -->
    <form action="logout.php" method="POST">
        <button type="submit">Cerrar sesión</button>
    </form>

    <script>
document.querySelectorAll('.pagado-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const nombre = this.dataset.nombre;
        const pagado = this.checked ? 1 : 0;

        fetch('actualizar_pagado.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `nombre=${encodeURIComponent(nombre)}&pagado=${pagado}`
        })
        .then(response => response.text())
        .then(data => {
            console.log('Respuesta del servidor:', data);
        })
        .catch(error => {
            alert('Error al actualizar');
            console.error(error);
        });
    });
});
</script>

<script>
document.querySelectorAll('.pagado-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function () {
        const nombre = this.dataset.nombre;
        const pagado = this.checked ? 1 : 0;
        const fila = this.closest('tr');

        fetch('actualizar_pagado.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `nombre=${encodeURIComponent(nombre)}&pagado=${pagado}`
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === 'OK') {
                fila.classList.toggle('fila-pagado', this.checked);
            } else {
                alert('Error al actualizar en la base de datos');
            }
        })
        .catch(error => {
            alert('Error en la solicitud');
            console.error(error);
        });
    });
});
</script>


</body>
</html>
