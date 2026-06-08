<?php
require_once '../../config/conexion.php';
session_start();

// 1. CAPTURAR DATOS SI VIENEN POR POST (Desde la pantalla de vuelos/planes)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_vuelo_ida'])) {
        $_SESSION['id_vuelo_ida'] = (int)$_POST['id_vuelo_ida'];
        if ($_SESSION['id_vuelo_ida'] > 0) {
            $_SESSION['id_vuelo'] = (int)$_POST['id_vuelo_ida']; 
        }
    }
    
    if (isset($_POST['id_vuelo_vuelta'])) {
        $_SESSION['id_vuelo_vuelta'] = (int)$_POST['id_vuelo_vuelta'];
        if (isset($_SESSION['id_vuelo_ida']) && (int)$_SESSION['id_vuelo_ida'] === 0) {
            $_SESSION['id_vuelo'] = (int)$_POST['id_vuelo_vuelta'];
        }
    }

    if (isset($_POST['planes'])) {
        $_SESSION['planes_pasajeros'] = $_POST['planes'];
        $primer_pasajero = reset($_POST['planes']);
        $_SESSION['id_plan'] = $primer_pasajero['ida'] ?? $primer_pasajero['vuelta'] ?? 1;
    }
}

// Variables de sesión consolidadas
$id_vuelo_ida    = $_SESSION['id_vuelo_ida'] ?? $_SESSION['id_vuelo'] ?? 1;
$id_vuelo_vuelta = $_SESSION['id_vuelo_vuelta'] ?? 0;
$id_plan_actual  = $_SESSION['id_plan'] ?? 1;
$cantidad_pasajeros = $_SESSION['pasajeros'] ?? 1;

// CONSULTA: Datos del vuelo de Ida
$stmtV = $pdo->prepare("SELECT numero_vuelo, precio_base_vuelo FROM vuelos WHERE id_vuelo = ?");
$stmtV->execute([$id_vuelo_ida]);
$vuelo_sel = $stmtV->fetch(PDO::FETCH_ASSOC) ?: ['numero_vuelo' => 'No asignado', 'precio_base_vuelo' => 0.00];

// CONSULTA: Datos del plan/tarifa base
$stmtP = $pdo->prepare("SELECT nombre_plan, cargo_extra_plan FROM planes_tarifas WHERE id_plan = ?");
$stmtP->execute([$id_plan_actual]);
$plan_sel = $stmtP->fetch(PDO::FETCH_ASSOC) ?: ['nombre_plan' => 'Estándar', 'cargo_extra_plan' => 0.00];

// CÁLCULO BASE INITIAL: (Vuelo Ida + Plan) * Pasajeros
$subtotal_inicial = ((float)$vuelo_sel['precio_base_vuelo'] + (float)$plan_sel['cargo_extra_plan']) * $cantidad_pasajeros;

// Sumar el vuelo de vuelta si existe en la sesión
$vuelo_vuelta_num = "";
if ($id_vuelo_vuelta > 0) {
    $stmtVV = $pdo->prepare("SELECT numero_vuelo, precio_base_vuelo FROM vuelos WHERE id_vuelo = ?");
    $stmtVV->execute([$id_vuelo_vuelta]);
    $vuelo_v_data = $stmtVV->fetch(PDO::FETCH_ASSOC);
    if ($vuelo_v_data) {
        $subtotal_inicial += ((float)$vuelo_v_data['precio_base_vuelo'] * $cantidad_pasajeros);
        $vuelo_vuelta_num = $vuelo_v_data['numero_vuelo'];
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
                <p class="text-inclusion">🎒 Bolso de mano bajo el asiento incluido en tu plan <strong><?=$plan_sel['nombre_plan']?></strong>.</p>
                
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
        <p><strong>Vuelo Ida:</strong> <?=$vuelo_sel['numero_vuelo']?></p>
        <?php if ($vuelo_vuelta_num !== ""): ?>
            <p><strong>Vuelo Vuelta:</strong> <?=$vuelo_vuelta_num?></p>
        <?php endif; ?>
        <p><strong>Tarifa:</strong> <?=$plan_sel['nombre_plan']?></p>
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