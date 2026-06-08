<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tramo = $_POST['tramo'] ?? 'ida';
    $asientos = $_POST['asientos_seleccionados'] ?? [];

    // Guardamos los asientos indexados por tramo en la sesión de forma limpia
    if ($tramo === 'ida') {
        $_SESSION['asientos_ida'] = $asientos;
        
        // Si el vuelo tiene tramo de vuelta, redirigimos al mapa de vuelta
        if (isset($_SESSION['id_vuelo_vuelta']) && $_SESSION['id_vuelo_vuelta'] > 0) {
            header("Location: asientos.php?tramo=vuelta");
            exit();
        }
    } else {
        $_SESSION['asientos_vuelta'] = $asientos;
    }

    // Si ya completó los tramos requeridos, pasa a cargar los datos de los pasajeros
    header("Location: pasajeros.php");
    exit();
}