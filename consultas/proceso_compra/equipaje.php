<?php
require_once '../../config/conexion.php';
session_start();

// 1. CAPTURAR DATOS SI VIENEN POR POST (Desde la pantalla de vuelos/planes)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_vuelo_ida'])) {
        $_SESSION['id_vuelo_ida'] = (int)$_POST['id_vuelo_ida'];
    }
    
    if (isset($_POST['id_vuelo_vuelta'])) {
        $_SESSION['id_vuelo_vuelta'] = (int)$_POST['id_vuelo_vuelta'];
    }

    if (isset($_POST['planes'])) {
        $_SESSION['planes_pasajeros'] = $_POST['planes'];
    }
}

// Variables de sesión consolidadas
$id_vuelo_ida       = $_SESSION['id_vuelo_ida'] ?? 0;
$id_vuelo_vuelta    = $_SESSION['id_vuelo_vuelta'] ?? 0;
$cantidad_pasajeros = $_SESSION['pasajeros'] ?? 1;
$planes_seleccionados = $_SESSION['planes_pasajeros'] ?? [];

// Mapear los nombres y precios de los planes para buscarlos rápido
$planes_db = $pdo->query("SELECT id_plan, nombre_plan, cargo_extra_plan FROM planes_tarifas")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);

// CONSULTA: Datos del vuelo de Ida
$vuelo_ida_num = "No asignado";
$precio_ida_base = 0.00;

if ($id_vuelo_ida > 0) {
    $stmtV = $pdo->prepare("SELECT numero_vuelo, precio_base_vuelo FROM vuelos WHERE id_vuelo = ?");
    $stmtV->execute([$id_vuelo_ida]);
    $vuelo_sel = $stmtV->fetch(PDO::FETCH_ASSOC);
    if ($vuelo_sel) {
        $vuelo_ida_num = $vuelo_sel['numero_vuelo'];
        $precio_ida_base = (float)$vuelo_sel['precio_base_vuelo'];
    }
}

// CONSULTA: Datos del vuelo de vuelta
$vuelo_vuelta_num = "";
$precio_vuelta_base = 0.00;

if ($id_vuelo_vuelta > 0) {
    $stmtVV = $pdo->prepare("SELECT numero_vuelo, precio_base_vuelo FROM vuelos WHERE id_vuelo = ?");
    $stmtVV->execute([$id_vuelo_vuelta]);
    $vuelo_v_data = $stmtVV->fetch(PDO::FETCH_ASSOC);
    if ($vuelo_v_data) {
        $vuelo_vuelta_num = $vuelo_v_data['numero_vuelo'];
        $precio_vuelta_base = (float)$vuelo_v_data['precio_base_vuelo'];
    }
}

// --- CÁLCULO EXACTO Y DINÁMICO DEL SUBTOTAL INICIAL ---
$subtotal_inicial = 0;

for ($i = 1; $i <= $cantidad_pasajeros; $i++) {
    // Sumar base de vuelos por cada pasajero
    $subtotal_inicial += $precio_ida_base;
    $subtotal_inicial += $precio_vuelta_base;

    // Sumar cargo extra de plan de Ida si aplica
    if ($id_vuelo_ida > 0 && isset($planes_seleccionados[$i]['ida'])) {
        $id_p_ida = $planes_seleccionados[$i]['ida'];
        $subtotal_inicial += (float)($planes_db[$id_p_ida]['cargo_extra_plan'] ?? 0);
    }

    // Sumar cargo extra de plan de Vuelta si aplica
    if ($id_vuelo_vuelta > 0 && isset($planes_seleccionados[$i]['vuelta'])) {
        $id_p_vta = $planes_seleccionados[$i]['vuelta'];
        $subtotal_inicial += (float)($planes_db[$id_p_vta]['cargo_extra_plan'] ?? 0);
    }
}

// Obtener catálogo de equipajes
$equipajes_disponibles = $pdo->query("SELECT id_tipo_equipaje, nombre_tipo, descripcion, precio_unitario FROM tipos_equipaje")->fetchAll(PDO::FETCH_ASSOC);
include_once '../../includes/header.php';
?>

<link rel="stylesheet" href="css/proceso_compra/equipaje.css">

<div class="contenedor-equipaje">
    
    <form action="servicios.php" method="POST" id="form-equipaje">
        
        <?php for($i = 1; $i <= $cantidad_pasajeros; $i++): ?>
            <div class="card">
                <h3>👤 Pasajero #<?=$i?>: Equipaje adicional</h3>
                <p class="text-inclusion">🎒 Configura las maletas extra para este pasajero si su tarifa base no las cubre.</p>
                
                <div class="grid-equipajes">
                    <?php foreach($equipajes_disponibles as $e): ?>
                        <div class="item-equipaje">
                            <div>
                                <h4><?=$e['nombre_tipo']?></h4>
                                <p class="descripcion"><?=$e['descripcion']?></p>
                                <p class="precio"><strong>$<?=number_format($e['precio_unitario'], 2)?> cada uno</strong></p>
                            </div>
                            <input type="number" 
                                   name="equipaje[<?=$i?>][<?=$e['id_tipo_equipaje']?>]" 
                                   value="0" 
                                   min="0" 
                                   max="5" 
                                   data-precio="<?=$e['precio_unitario']?>" 
                                   data-nombre="Pasajero #<?=$i?>: <?=$e['nombre_tipo']?>"
                                   class="input-equipaje">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endfor; ?>

        <button type="submit" class="btn-continuar">Continuar a Servicios</button>
    </form>

    <div class="sidebar-resumen">
        <h3>Resumen de tu Viaje</h3>
        <p><strong>Vuelo Ida:</strong> <?=$vuelo_ida_num?></p>
        <?php if ($vuelo_vuelta_num !== ""): ?>
            <p><strong>Vuelo Vuelta:</strong> <?=$vuelo_vuelta_num?></p>
        <?php endif; ?>
        <p><strong>Pasajeros:</strong> x<?=$cantidad_pasajeros?></p>
        
        <div id="bloque-maletas" class="bloque-maletas">
            <p>Equipaje extra añadido:</p>
            <ul id="lista-maletas-resumen" class="lista-maletas"></ul>
        </div>

        <hr class="separador">
        <h4 class="total-contenedor">
            <span>Total Acumulado:</span>
            <span class="total-precio" id="total-vista" data-base="<?=$subtotal_inicial?>">$<?=number_format($subtotal_inicial, 2)?></span>
        </h4>
    </div>
</div>

<script src="js/proceso_compra/equipaje.js"></script>
</body>
</html>