<?php
session_start();

// 1. VALIDACIÓN DE SEGURIDAD
// Si no se enviaron asientos o no viene el tramo, regresamos a la pantalla de asientos
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['tramo'])) {
    header("Location: asientos.php");
    exit();
}

// Recuperar variables de control de la sesión
$id_vuelo_ida_global = isset($_SESSION['id_vuelo_ida']) ? (int)$_SESSION['id_vuelo_ida'] : 0;
$id_vuelo_vta_global = isset($_SESSION['id_vuelo_vuelta']) ? (int)$_SESSION['id_vuelo_vuelta'] : 0;
$cantidad_pasajeros  = $_SESSION['pasajeros'] ?? 1;

// Recuperar los asientos elegidos por el usuario (si no eligió ninguno, enviamos un arreglo vacío)
$asientos_seleccionados = $_POST['asientos_seleccionados'] ?? [];
$tramo_actual = $_POST['tramo']; // Puede ser 'ida' o 'vuelta'

// 2. GUARDAR EN SESIÓN SEGÚN EL TRAMO CORRESPONDIENTE
if ($tramo_actual === 'vuelta') {
    // Se guardan los asientos del regreso
    $_SESSION['asientos_vuelta'] = $asientos_seleccionados;
} else {
    // Se guardan los asientos de la ida
    $_SESSION['asientos_ida'] = $asientos_seleccionados;
}

// 3. CONTROL DE REDIRECCIÓN INTELIGENTE (FLUJO DEL PASAJE)
if ($tramo_actual === 'ida') {
    // Si acabamos de procesar la 'Ida', verificamos si el cliente también compró un vuelo de 'Vuelta'
    if ($id_vuelo_vta_global > 0) {
        // Tiene viaje redondo: lo mandamos a elegir los asientos del regreso
        header("Location: asientos.php?tramo=vuelta");
        exit();
    } else {
        // Es un viaje de Solo Ida: saltamos directo a la captura de datos de pasajeros
        header("Location: pasajeros.php");
        exit();
    }
} else {
    // Si acabamos de procesar la 'Vuelta' (ya sea porque terminó el viaje redondo o porque era un ticket de Solo Vuelta)
    // El siguiente paso obligatorio es la captura de datos de los pasajeros
    header("Location: pasajeros.php");
    exit();
}
?>