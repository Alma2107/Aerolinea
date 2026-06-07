<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$root = "/aerolinea"; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>VolarAlto Aerolínea</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/estilos_vuelos.css">
</head>
<body>

<nav class="navbar">
    <div class="logo">
        <a href="<?php echo $root; ?>/index.php">✈ VolarAlto</a>
    </div>
    
    <div class="nav-menu-box">
        <a href="<?php echo $root; ?>/index.php" class="nav-item-link">Inicio</a>
        <a href="#" class="nav-item-link">Vuelos</a>
        
        <?php if(isset($_SESSION['usuario_nombre'])): ?>
            <div class="user-profile-menu" id="profileToggle">
                <span class="user-welcome-text">¡Hola, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?>!</span>
                <i class="fa-solid fa-user user-avatar-icon"></i>
                
                <div class="dropdown-menu">
                    <a href="<?php echo $root; ?>/consultas/login/logout.php">
                        <i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión
                    </a>
                    <a href="<?php echo $root; ?>/consultas/login/eliminar_cuenta.php" class="delete-account">
                        <i class="fa-solid fa-trash-can"></i> Eliminar Cuenta
                    </a>
                </div>
            </div>
        <?php else: ?>
            <a href="<?php echo $root; ?>/consultas/login/login.php" class="nav-register-plain">
                Registrarse <i class="fa-solid fa-user user-avatar-icon"></i>
            </a>
        <?php endif; ?>
    </div>
</nav>