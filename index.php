<?php
session_start();
require_once 'config/conexion.php';

if (isset($_GET['action']) && $_GET['action'] == 'buscar_aeropuerto') {
    header('Content-Type: application/json');
    $busqueda = '%' . $_GET['q'] . '%';
    $stmt = $pdo->prepare("SELECT codigo_iata, ciudad, nombre FROM aeropuertos WHERE ciudad LIKE ? OR codigo_iata LIKE ?");
    $stmt->execute([$busqueda, $busqueda]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if (isset($_GET['action']) && $_GET['action'] == 'obtener_fechas_vuelos') {
    header('Content-Type: application/json');
    $stmt = $pdo->query("SELECT DISTINCT DATE(fecha_salida) as fecha FROM vuelos WHERE estado_vuelo = 'Programado'");
    echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
    exit;
}

include_once 'includes/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="css/estilos_vuelos.css">

<body>
<main class="hero-banner">
    <div class="booking-card">
        <div class="search-tabs">
            <div class="tab-item active"><i class="fa-solid fa-plane"></i> Vuelos</div>
            <div class="tab-item"><i class="fa-solid fa-check"></i> Check in</div>
            <div class="tab-item"><i class="fa-solid fa-clock"></i> Estado de vuelo</div>
            <div class="tab-item"><i class="fa-solid fa-tag"></i> Mi reserva</div>
        </div>

        <div class="form-container">
            <form action="consultas/proceso_compra/vuelos.php" method="GET" autocomplete="off">
                
                <div class="trip-type-selector">
                    <label><input type="radio" name="tipo_viaje" value="solo_ida" id="radio-solo-ida"> Ida</label>
                    <label><input type="radio" name="tipo_viaje" value="ida_vuelta" id="radio-ida-vuelta" checked> Vuelta</label>
                    <label><input type="radio" name="tipo_viaje" value="multidestino" id="radio-multidestino"> Multidestino</label>
                </div>

                <button type="button" id="btn-nuevo-tramo" class="btn-add-tramo" style="display: none;">+ Agregar tramo</button>

                <div class="tramos-wrapper" id="tramos-contenedor"></div>

                <div class="search-footer-row">
                    <button type="submit" class="btn-search">Buscar Vuelos</button>
                </div>

            </form>
        </div>
    </div>
</main>

<section class="bottom-yellow-section">
    <svg class="wave-svg" viewBox="0 0 1440 70" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0,35 C240,70 480,0 720,35 C960,70 1200,0 1440,35 L1440,70 L0,70 Z" fill="#ffffff"/>
    </svg>
</section>

<script src="js/buscador_vuelos.js"></script>
<script>
const profileToggle = document.getElementById('profileToggle');
if(profileToggle) {
    profileToggle.addEventListener('click', function(e) {
        this.classList.toggle('active');
        e.stopPropagation();
    });
    document.addEventListener('click', function() {
        profileToggle.classList.remove('active');
    });
}
</script>
</body>
</html>
</body>
</html>
</body>
</html>
</body>
</html>