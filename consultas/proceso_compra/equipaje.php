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

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; max-width: 1200px; margin: 20px auto; padding: 0 20px; font-family: sans-serif;">
    
    <form action="servicios.php" method="POST" id="form-equipaje">
        
        <?php for($i = 1; $i <= $cantidad_pasajeros; $i++): ?>
            <div class="card" style="border: 1px solid #ddd; padding: 20px; border-radius: 8px; background:#fff; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <h3 style="margin-top:0; color:#0056b3;">👤 Pasajero #<?=$i?>: Equipaje adicional</h3>
                <p style="color:green; margin-bottom:15px; font-size:13px;">🎒 Bolso de mano bajo el asiento incluido en tu plan <strong><?=$plan_sel['nombre_plan']?></strong>.</p>
                
                <div style="display:grid; grid-template-columns: 1fr; gap:15px;">
                    <?php foreach($equipajes_disponibles as $e): ?>
                        <div style="border: 1px solid #ddd; padding: 15px; border-radius: 6px; display:flex; justify-content:space-between; align-items:center; background:#fafafa;">
                            <div>
                                <h4 style="margin:0;"><?=$e['nombre_tipo']?></h4>
                                <p style="font-size: 12px; color: #555; margin:5px 0 0 0;"><?=$e['descripcion']?></p>
                                <p style="margin:5px 0 0 0; color:#0056b3;"><strong>$<?=number_format($e['precio_unitario'], 2)?> cada uno</strong></p>
                            </div>
                            <input type="number" 
                                   name="equipaje[<?=$i?>][<?=$e['id_tipo_equipaje']?>]" 
                                   value="0" 
                                   min="0" 
                                   max="5" 
                                   data-precio="<?=$e['precio_unitario']?>" 
                                   data-nombre="Pasajero #<?=$i?>: <?=$e['nombre_tipo']?>"
                                   class="input-equipaje"
                                   style="width: 60px; padding: 8px; text-align:center; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endfor; ?>

        <button type="submit" style="background:#0056b3; color:#fff; padding:14px 25px; border:none; border-radius:4px; cursor:pointer; font-weight:bold; margin-top:10px; font-size:15px; width: 100%;">Continuar a Servicios</button>
    </form>

    <div style="border: 1px solid #0056b3; padding: 20px; border-radius: 8px; background: #f4f8ff; height: fit-content; position: sticky; top: 20px;">
        <h3 style="color:#0056b3; border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-top:0;">Resumen de tu Viaje</h3>
        <p><strong>Vuelo Ida:</strong> <?=$vuelo_sel['numero_vuelo']?></p>
        <?php if ($vuelo_vuelta_num !== ""): ?>
            <p><strong>Vuelo Vuelta:</strong> <?=$vuelo_vuelta_num?></p>
        <?php endif; ?>
        <p><strong>Tarifa:</strong> <?=$plan_sel['nombre_plan']?></p>
        <p><strong>Pasajeros:</strong> x<?=$cantidad_pasajeros?></p>
        
        <div id="bloque-maletas" style="display:none; margin-top:12px;">
            <p style="margin-bottom:4px; font-weight:bold;">Equipaje extra añadido:</p>
            <ul id="lista-maletas-resumen" style="margin:0; padding-left:20px; font-size:13px; color:#444;"></ul>
        </div>

        <hr style="border:0; border-top:1px dashed #ccc; margin-top:15px;">
        <h4 style="margin:10px 0; display:flex; justify-content:space-between; font-size:16px;">
            <span>Total Acumulado:</span>
            <span style="color:green;" id="total-vista" data-base="<?=$subtotal_inicial?>">$<?=number_format($subtotal_inicial, 2)?></span>
        </h4>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const totalVista = document.getElementById('total-vista');
    const bloqueMaletas = document.getElementById('bloque-maletas');
    const listaMaletas = document.getElementById('lista-maletas-resumen');
    const baseDinámica = parseFloat(totalVista.getAttribute('data-base'));

    function recalcularMaletas() {
        let cargoTotalEquipajes = 0;
        let htmlResumen = "";
        const inputs = document.querySelectorAll('.input-equipaje');

        inputs.forEach(input => {
            const cantidad = parseInt(input.value) || 0;
            if (cantidad > 0) {
                const precioUnidad = parseFloat(input.getAttribute('data-precio')) || 0;
                const nombreDetalle = input.getAttribute('data-nombre');
                const subtotalItem = precioUnidad * cantidad;

                cargoTotalEquipajes += subtotalItem;
                htmlResumen += `<li>${nombreDetalle} (x${cantidad}) +$${subtotalItem.toFixed(2)}</li>`;
            }
        });

        if (cargoTotalEquipajes > 0) {
            bloqueMaletas.style.display = "block";
            listaMaletas.innerHTML = htmlResumen;
        } else {
            bloqueMaletas.style.display = "none";
            listaMaletas.innerHTML = "";
        }

        let nuevoTotalFinal = baseDinámica + cargoTotalEquipajes;
        totalVista.textContent = '$' + nuevoTotalFinal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    // Escuchar eventos de cambio de número o tipeo manual
    document.querySelectorAll('.input-equipaje').forEach(input => {
        input.addEventListener('input', recalcularMaletas);
        input.addEventListener('change', recalcularMaletas);
    });
});
</script>
</body>
</html>