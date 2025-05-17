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
    <title>Panel de Administración</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 30px;
            color: #333;
        }

        h1, h2 {
            color: #222;
            text-align: center;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin: 20px 0;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .fila-pagado {
            background-color: #d4edda !important; /* verde suave */
        }

        .buscador {
            text-align: center;
            margin: 20px 0;
        }

        .buscador input[type="text"] {
            padding: 8px;
            width: 250px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .buscador button {
            padding: 8px 16px;
            margin-left: 10px;
            background-color: #007bff;
            border: none;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }

        .buscador button:hover {
            background-color: #0056b3;
        }

        form {
            text-align: center;
            margin-top: 20px;
        }

        form button {
            padding: 10px 20px;
            background-color: #28a745;
            border: none;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }

        form button:hover {
            background-color: #218838;
        }

        .cerrar-sesion {
            margin-top: 40px;
            text-align: center;
        }

        .cerrar-sesion button {
            background-color: #dc3545;
        }

        .cerrar-sesion button:hover {
            background-color: #c82333;
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
                <th>Cena</th>
                <th>Brindis</th>
                <th>Total</th>
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
        <p style="text-align:center;">No hay datos en la tabla <strong>resumen_asistentes</strong>.</p>
    <?php endif; ?>

    <h2>Listado de Asistentes</h2>

    <form method="get" class="buscador">
        <input type="text" name="buscar" placeholder="Buscar por nombre" value="<?= htmlspecialchars($nombre_busqueda) ?>">
        <button type="submit">Buscar</button>
        <a href="bienvenida.php"><button type="button">Limpiar</button></a>
    </form>

    <table>
        <tr>
            <th>Eliminar</th>
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
                <td>
                    <button class="eliminar-icono" data-nombre="<?= htmlspecialchars($row['nombre']) ?>" title="Eliminar">
                        🗑️
                    </button>
                </td>
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

    <div class="cerrar-sesion">
        <form action="logout.php" method="POST">
            <button type="submit">Cerrar sesión</button>
        </form>
    </div>

    <!-- Modal -->
    <div id="modal-confirmacion" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
        background-color:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
        <div style="background:white; padding:20px; border-radius:8px; text-align:center; box-shadow:0 4px 10px rgba(0,0,0,0.2);">
            <p>¿Estás seguro de que deseas eliminar este asistente?</p>
            <button id="confirmar-eliminar" style="background-color:#dc3545; color:white; padding:10px 20px; border:none; border-radius:4px; margin-right:10px;">Sí, eliminar</button>
            <button id="cancelar-eliminar" style="background-color:#6c757d; color:white; padding:10px 20px; border:none; border-radius:4px;">Cancelar</button>
        </div>
    </div>

    <script>
        let nombreEliminar = '';
        let filaEliminar = null;

        document.querySelectorAll('.eliminar-icono').forEach(icono => {
            icono.addEventListener('click', function () {
                nombreEliminar = this.dataset.nombre;
                filaEliminar = this.closest('tr');
                document.getElementById('modal-confirmacion').style.display = 'flex';
            });
        });

        document.getElementById('cancelar-eliminar').addEventListener('click', function () {
            document.getElementById('modal-confirmacion').style.display = 'none';
            nombreEliminar = '';
            filaEliminar = null;
        });

        document.getElementById('confirmar-eliminar').addEventListener('click', function () {
            fetch('eliminar_asistente.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `nombre=${encodeURIComponent(nombreEliminar)}`
            })
            .then(response => response.text())
            .then(data => {
                if (data.trim() === 'OK') {
                    filaEliminar.remove();
                } else {
                    alert('Error al eliminar el asistente.');
                }
                document.getElementById('modal-confirmacion').style.display = 'none';
            })
            .catch(error => {
                alert('Error en la solicitud');
                console.error(error);
                document.getElementById('modal-confirmacion').style.display = 'none';
            });
        });

        // Checkbox pagado
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
                        if (pagado) {
                            fila.classList.add('fila-pagado');
                        } else {
                            fila.classList.remove('fila-pagado');
                        }
                    } else {
                        alert('Error al actualizar el estado de pago.');
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
