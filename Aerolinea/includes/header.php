<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageStyles = $pageStyles ?? [];
$pageStyles = is_array($pageStyles) ? $pageStyles : [$pageStyles];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlySmart - Aerolínea</title>
    <link rel="stylesheet" href="/Aerolinea/css/estilos.css">
    <?php foreach ($pageStyles as $pageStyle): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($pageStyle, ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach; ?>
</head>
<body>

<header class="main-header">

    <div class="logo">
      <a href="/Aerolinea/index.php">  ✈️ FlySmart</a>
    </div>

    <nav class="menu-central">
        <a href="/Aerolinea/index.php">Inicio</a>
        <a href="#viajes">Viajes</a>
        <a href="#recomendados">Recomendados</a>
        <a href="#nosotros">Nosotros</a>
        <a href="#servicios">Servicios</a>
    </nav>

    <nav class="nav-menu">
        <?php if(isset($_SESSION['usuario_id'])): ?>

            <span class="user-welcome">
                Hola, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?>
            </span>

                <a href="/Aerolinea/consultas/login/logout.php"
               class="btn-nav">
               Cerrar Sesión
            </a>

                <a href="/Aerolinea/consultas/login/eliminar_cuenta.php"
               class="btn-nav btn-danger"
               onclick="return confirm('¿Estás seguro de eliminar tu cuenta?')">
               Eliminar Cuenta
            </a>

        <?php else: ?>

                <a href="/Aerolinea/consultas/login/login.php"
               class="btn-nav">
               Iniciar Sesión
            </a>

                <a href="/Aerolinea/consultas/login/registro.php"
               class="btn-nav btn-primary">
               Registrarse
            </a>

        <?php endif; ?>
    </nav>

</header>