<?php
session_start();
require_once 'config/conexion.php';

// --- CONTROLADORES AJAX ---

// 1. Buscar Aeropuertos
if (isset($_GET['action']) && $_GET['action'] == 'buscar_aeropuerto') {
    header('Content-Type: application/json');
    $busqueda = '%' . $_GET['q'] . '%';
    $stmt = $pdo->prepare("SELECT codigo_iata, ciudad, nombre FROM aeropuertos WHERE ciudad LIKE ? OR codigo_iata LIKE ?");
    $stmt->execute([$busqueda, $busqueda]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// 2. Obtener Fechas de Vuelos
if (isset($_GET['action']) && $_GET['action'] == 'obtener_fechas_vuelos') {
    header('Content-Type: application/json');
    $stmt = $pdo->query("SELECT DISTINCT DATE(fecha_salida) as fecha FROM vuelos WHERE estado_vuelo = 'Programado'");
    echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
    exit;
}

// 3. NUEVO: Consultar Estado de Vuelo por Código PNR
if (isset($_GET['action']) && $_GET['action'] == 'consultar_pnr') {
    header('Content-Type: application/json');
    $pnr = strtoupper(trim($_GET['pnr'] ?? ''));
    
    $stmt = $pdo->prepare("SELECT t.codigo_reserva_pnr, v.numero_vuelo, v.estado_vuelo, 
                                  v.origen_iata, v.destino_iata, v.fecha_salida,
                                  p.nombre, p.apellido, t.numero_asiento
                           FROM tickets_detalle t
                           INNER JOIN vuelos v ON t.id_vuelo = v.id_vuelo
                           INNER JOIN pasajeros p ON t.id_pasajero = p.id_pasajero
                           WHERE t.codigo_reserva_pnr = ?");
    $stmt->execute([$pnr]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// 4. CORREGIDO: Busca por ID o por Nombre de usuario en sesión
if (isset($_GET['action']) && $_GET['action'] == 'historial_reservas') {
    header('Content-Type: application/json');
    
    // Intentamos obtener el ID, si no, usamos el nombre que ya sabemos que tienes guardado
    $id_cliente = $_SESSION['id_cliente'] ?? null;
    $nombre_usuario = $_SESSION['usuario_nombre'] ?? null;
    
    // Si no hay ni ID ni nombre, entonces no está logueado
    if (!$id_cliente && !$nombre_usuario) {
        echo json_encode(['error' => 'No iniciado']);
        exit;
    }
    
    if ($id_cliente) {
        // Opción A: Búsqueda exacta y rápida por ID de cliente
        $stmt = $pdo->prepare("SELECT o.id_orden, o.fecha_compra, o.monto_total_pagado,
                                      t.codigo_reserva_pnr, v.numero_vuelo, v.origen_iata, 
                                      v.destino_iata, v.fecha_salida, v.estado_vuelo
                               FROM compras_ordenes o
                               INNER JOIN tickets_detalle t ON o.id_orden = t.id_orden
                               INNER JOIN vuelos v ON t.id_vuelo = v.id_vuelo
                               WHERE o.id_cliente = ?
                               GROUP BY t.codigo_reserva_pnr
                               ORDER BY o.fecha_compra DESC");
        $stmt->execute([$id_cliente]);
    } else {
        // Opción B: Búsqueda de respaldo por el nombre guardado en tu sesión actual
        $stmt = $pdo->prepare("SELECT o.id_orden, o.fecha_compra, o.monto_total_pagado,
                                      t.codigo_reserva_pnr, v.numero_vuelo, v.origen_iata, 
                                      v.destino_iata, v.fecha_salida, v.estado_vuelo
                               FROM compras_ordenes o
                               INNER JOIN tickets_detalle t ON o.id_orden = t.id_orden
                               INNER JOIN vuelos v ON t.id_vuelo = v.id_vuelo
                               INNER JOIN clientes c ON o.id_cliente = c.id_cliente
                               WHERE c.nombre = ?
                               GROUP BY t.codigo_reserva_pnr
                               ORDER BY o.fecha_compra DESC");
        $stmt->execute([$nombre_usuario]);
    }
    
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

include_once 'includes/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="css/index.css">

<body>
<main class="hero-banner">
    <div class="booking-card">
        <div class="search-tabs">
            <div class="tab-item active" data-tab="vuelos"><i class="fa-solid fa-plane"></i> Vuelos</div>
            <div class="tab-item" data-tab="estado"><i class="fa-solid fa-clock"></i> Estado de vuelo</div>
            <div class="tab-item" data-tab="reserva"><i class="fa-solid fa-tag"></i> Mi reserva</div>
        </div>

        <div class="form-container">
            
            <div id="tab-vuelos" class="tab-content active">
                <form action="consultas/proceso_compra/vuelos.php" method="GET" autocomplete="off">
                    <div class="trip-type-selector" style="margin-bottom: 15px;">
                        <label><input type="radio" name="tipo_viaje" value="solo_ida" id="radio-solo-ida"> Ida</label>
                        <label><input type="radio" name="tipo_viaje" value="solo_vuelta" id="radio-solo-vuelta"> Vuelta</label>
                        <label><input type="radio" name="tipo_viaje" value="ida_vuelta" id="radio-ida-vuelta" checked> Ida y Vuelta</label>
                        <label><input type="radio" name="tipo_viaje" value="multidestino" id="radio-multidestino"> Multitramo</label>
                    </div>

                    <button type="button" id="btn-nuevo-tramo" class="btn-add-tramo" style="display: none;">+ Agregar tramo</button>
                    <div class="tramos-wrapper" id="tramos-contenedor"></div>

                    <div class="search-footer-row">
                        <button type="submit" class="btn-search">Buscar Vuelos</button>
                    </div>
                </form>
            </div>

            <div id="tab-estado" class="tab-content" style="display: none;">
                <div class="buscador-pnr-box" style="padding: 20px 0;">
                    <h3>Consulta el estado de tu viaje</h3>
                    <p style="font-size: 14px; color: #666; margin-bottom: 15px;">Introduce el código de reserva de 6 caracteres (Ej: AX39FT, MZ99EE).</p>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" id="pnr-input" maxlength="6" placeholder="Ej: AX39FT" style="text-transform: uppercase; padding: 12px; border: 1px solid #ccc; border-radius: 4px; flex-grow: 1; font-weight: bold; font-size: 16px;">
                        <button type="button" id="btn-buscar-pnr" class="btn-search" style="width: auto; padding: 0 25px;">Consultar</button>
                    </div>
                    <div id="resultado-pnr" style="margin-top: 20px;"></div>
                </div>
            </div>

            <div id="tab-reserva" class="tab-content" style="display: none;">
                <div class="historial-reservas-box" style="padding: 20px 0;">
                    <h3>Tu Historial de Vuelos Comprados</h3>
                    <div id="lista-historial" style="margin-top: 15px;">
                        <p style="color: #666;">Cargando tus compras...</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<section class="bottom-yellow-section">
    <svg class="wave-svg" viewBox="0 0 1440 70" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0,35 C240,70 480,0 720,35 C960,70 1200,0 1440,35 L1440,70 L0,70 Z" fill="#ffffff"/>
    </svg>
</section>

<script src="js/index.js"></script>
<script src="js/pestanas_navegacion.js"></script>
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