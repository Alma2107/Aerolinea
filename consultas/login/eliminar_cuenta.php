<?php
require_once '../../config/conexion.php';
session_start();

if (isset($_SESSION['usuario_id'])) {
    $stmt = $pdo->prepare("UPDATE clientes SET estado_cuenta = 0 WHERE id_cliente = :id_cliente");
    $stmt->execute(['id_cliente' => $_SESSION['usuario_id']]);
    
    $_SESSION = array();
    session_destroy();
}
header("Location: ../../index.php");
exit();
?>