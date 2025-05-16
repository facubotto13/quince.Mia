<?php
session_start();
if (isset($_SESSION['logueado']) && $_SESSION['logueado'] === true) {
    header('Location: bienvenida.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
</head>
<body>
  <h2>Login</h2>
  <?php
  if (isset($_GET['error'])) {
      echo "<p style='color:red;'>Usuario o contraseña incorrectos</p>";
  }
  ?>
  <form action="validar.php" method="post">
    <input type="text" name="usuario" placeholder="Usuario" required><br>
    <input type="password" name="contrasena" placeholder="Contraseña" required><br>
    <button type="submit">Entrar</button>
  </form>

  <!-- Botón Volver -->
  <form action="./QuinceMia.php" method="get" style="margin-top: 10px;">
    <button type="submit">Volver</button>
  </form>
</body>
</html>
