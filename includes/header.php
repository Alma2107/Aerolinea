<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlySmart - Aerolínea</title>
    <link rel="stylesheet" href="/aerolinea/css/estilos.css">
</head>
<body>
    <header class="main-header">
        <div class="logo">✈️ FlySmart</div>
        <nav class="nav-menu">
            <?php if(isset($_SESSION['usuario_id'])): ?>
                <span class="user-welcome">Hola, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?></span>
                <a href="/aerolinea/consultas/login/logout.php" class="btn-nav">Cerrar Cuenta</a>
                <a href="/aerolinea/consultas/login/eliminar_cuenta.php" class="btn-nav btn-danger" onclick="return confirm('¿Estás seguro de eliminar tu cuenta?')">Eliminar Cuenta</a>
            <?php else: ?>
                <a href="/aerolinea/consultas/login/login.php" class="btn-nav">Iniciar Sesión</a>
                <a href="/aerolinea/consultas/login/registro.php" class="btn-nav">Registrarse</a>
            <?php endif; ?>
        </nav>
    </header>