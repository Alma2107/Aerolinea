<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$siteBaseUrl = '/Aerolinea';
$pageStyles = $pageStyles ?? [];
$pageStyles = is_array($pageStyles) ? $pageStyles : [$pageStyles];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlySmart - Aerolinea</title>
    <link rel="stylesheet" href="<?= $siteBaseUrl ?>/css/estilos.css">
    <?php foreach ($pageStyles as $pageStyle): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($pageStyle, ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach; ?>
</head>
<body>

<header class="main-header">

    <div class="logo">
      <a href="<?= $siteBaseUrl ?>/index.php">FlySmart</a>
    </div>

    <nav class="menu-central">
        <a href="<?= $siteBaseUrl ?>/index.php">Inicio</a>
        <a href="<?= $siteBaseUrl ?>/index.php#promociones-funcionales">Promos</a>
        <a href="<?= $siteBaseUrl ?>/index.php#recomendados">Recomendados</a>
        <a href="<?= $siteBaseUrl ?>/index.php#nosotros">Nosotros</a>
        <a href="<?= $siteBaseUrl ?>/index.php#servicios">Servicios</a>
        <a href="<?= $siteBaseUrl ?>/consultas/reservas/mis_viajes.php">Mis viajes</a>
    </nav>

    <nav class="nav-menu">
        <?php if(isset($_SESSION['usuario_id'])): ?>

            <div class="user-menu">
                <button class="user-menu-button" type="button" aria-haspopup="true" aria-expanded="false">
                    <span>Hola, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?></span>
                    <span class="caret">▾</span>
                </button>
                <div class="user-menu-dropdown" role="menu" aria-label="Menú del usuario">
                    <a href="<?= $siteBaseUrl ?>/consultas/reservas/mis_viajes.php" role="menuitem">Mis viajes</a>
                    <a href="<?= $siteBaseUrl ?>/consultas/reservas/historial.php" role="menuitem">Historial</a>
                    <a href="<?= $siteBaseUrl ?>/consultas/reservas/checkin.php" role="menuitem">Check-in</a>
                    <a href="<?= $siteBaseUrl ?>/consultas/login/logout.php" role="menuitem">Desloguear</a>
                    <a href="<?= $siteBaseUrl ?>/consultas/login/eliminar_cuenta.php" class="danger-link" role="menuitem" onclick="return confirm('¿Estás seguro de eliminar tu cuenta?')">Eliminar cuenta</a>
                </div>
            </div>
            
        <?php else: ?>

                <a href="<?= $siteBaseUrl ?>/consultas/login/login.php"
               class="btn-nav">
               Iniciar Sesion
            </a>

                <a href="<?= $siteBaseUrl ?>/consultas/login/registro.php"
               class="btn-nav btn-primary">
               Registrarse
            </a>

        <?php endif; ?>
    </nav>

</header>

