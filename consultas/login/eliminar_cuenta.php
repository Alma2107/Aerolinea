<?php
session_start();
require_once '../../config/conexion.php';

if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: ../../index.php");
    exit;
}

try {
    // Borramos al usuario basándonos en su nombre guardado en la sesión
    // TIP: Si guardás el ID de usuario en sesión al loguearte, es mejor filtrar por id_usuario
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE nombre = :nom");
    $stmt->execute(['nom' => $_SESSION['usuario_nombre']]);

    // Destruimos la sesión
    session_unset();
    session_destroy();

    // Redirigimos con un aviso o directo al index
    header("Location: ../../index.php?cuenta_eliminada=true");
    exit;

} catch (PDOException $e) {
    die("Error al eliminar la cuenta: " . $e->getMessage());
}