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
    <title>FlySmart - Aerolinea</title>
    <link rel="stylesheet" href="/Aerolinea/css/estilos.css">
    <?php foreach ($pageStyles as $pageStyle): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($pageStyle, ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach; ?>
</head>
<body>

<header class="main-header">

    <div class="logo">
      <a href="/Aerolinea/index.php">FlySmart</a>
    </div>

    <nav class="menu-central">
        <a href="/Aerolinea/index.php">Inicio</a>
        <a href="#promociones-funcionales">Promos</a>
        <a href="#recomendados">Recomendados</a>
        <a href="#nosotros">Nosotros</a>
        <a href="#servicios">Servicios</a>
        <a href="/Aerolinea/consultas/reservas/mis_viajes.php">Mis viajes</a>
    </nav>

    <nav class="nav-menu">
        <?php if(isset($_SESSION['usuario_id'])): ?>

            <div class="user-menu">
                <button class="user-menu-button" type="button" aria-haspopup="true" aria-expanded="false">
                    <span>Hola, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?></span>
                    <span class="caret">▾</span>
                </button>
                <div class="user-menu-dropdown" role="menu" aria-label="Menú del usuario">
                    <a href="/Aerolinea/consultas/reservas/mis_viajes.php" role="menuitem">Mis viajes</a>
                    <a href="/Aerolinea/consultas/reservas/historial.php" role="menuitem">Historial</a>
                    <a href="/Aerolinea/consultas/reservas/checkin.php" role="menuitem">Check-in</a>
                    <a href="/Aerolinea/consultas/login/logout.php" role="menuitem">Desloguear</a>
                    <a href="/Aerolinea/consultas/login/eliminar_cuenta.php" class="danger-link" role="menuitem" onclick="return confirm('¿Estás seguro de eliminar tu cuenta?')">Eliminar cuenta</a>
                </div>
            </div>
            
        <?php else: ?>

                <a href="/Aerolinea/consultas/login/login.php"
               class="btn-nav">
               Iniciar Sesion
            </a>

                <a href="/Aerolinea/consultas/login/registro.php"
               class="btn-nav btn-primary">
               Registrarse
            </a>

        <?php endif; ?>
    </nav>

</header>

