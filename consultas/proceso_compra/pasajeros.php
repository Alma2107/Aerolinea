<?php
require_once '../../config/conexion.php';
session_start();

// 1. CAPTURAR DATOS DE LA PANTALLA DE ASIENTOS (Si se procesa directo aquí)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asientos_seleccionados'])) {
    $tramo_recibido = $_POST['tramo'] ?? 'ida';
    if ($tramo_recibido === 'vuelta') {
        $_SESSION['asientos_vuelta'] = $_POST['asientos_seleccionados'];
    } else {
        $_SESSION['asientos_ida'] = $_POST['asientos_seleccionados'];
    }
}

// Variables de sesión consolidadas
$id_vuelo_ida         = $_SESSION['id_vuelo_ida'] ?? 0;
$id_vuelo_vuelta      = $_SESSION['id_vuelo_vuelta'] ?? 0;
$cantidad_pasajeros   = $_SESSION['pasajeros'] ?? 1;
$planes_seleccionados = $_SESSION['planes_pasajeros'] ?? [];
$equipajes_sesion     = $_SESSION['equipajes'] ?? [];
$servicios_sesion     = $_SESSION['servicios'] ?? [];
$asientos_ida         = $_SESSION['asientos_ida'] ?? [];
$asientos_vuelta      = $_SESSION['asientos_vuelta'] ?? [];

// 2. CARGAR CATÁLOGOS EN MEMORIA (Optimización N+1 Queries)
$planes_db = $pdo->query("SELECT id_plan, nombre_plan, cargo_extra_plan FROM planes_tarifas")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
$equipajes_db = $pdo->query("SELECT id_tipo_equipaje, nombre_tipo, precio_unitario FROM tipos_equipaje")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
$servicios_db = $pdo->query("SELECT id_servicio, nombre_servicio, precio_servicio FROM servicios_adicionales")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);

// 3. CONSULTA DE VUELOS PARA EL RESUMEN
$vuelo_ida_num = "No asignado";
$precio_ida_base = 0.00;
if ($id_vuelo_ida > 0) {
    $stmtI = $pdo->prepare("SELECT numero_vuelo, precio_base_vuelo FROM vuelos WHERE id_vuelo = ?");
    $stmtI->execute([$id_vuelo_ida]);
    $v_ida = $stmtI->fetch(PDO::FETCH_ASSOC);
    if ($v_ida) {
        $vuelo_ida_num = $v_ida['numero_vuelo'];
        $precio_ida_base = (float)$v_ida['precio_base_vuelo'];
    }
}

$vuelo_vuelta_num = "";
$precio_vuelta_base = 0.00;
if ($id_vuelo_vuelta > 0) {
    $stmtV = $pdo->prepare("SELECT numero_vuelo, precio_base_vuelo FROM vuelos WHERE id_vuelo = ?");
    $stmtV->execute([$id_vuelo_vuelta]);
    $v_vta = $stmtV->fetch(PDO::FETCH_ASSOC);
    if ($v_vta) {
        $vuelo_vuelta_num = $v_vta['numero_vuelo'];
        $precio_vuelta_base = (float)$v_vta['precio_base_vuelo'];
    }
}

// --- 4. ALGORITMO COMPLETO DE RECALCULO DE TOTAL INTEGRAL ---
$total_acumulado = 0;

for ($i = 1; $i <= $cantidad_pasajeros; $i++) {
    // A. Agregar costos de los vuelos base
    $total_acumulado += $precio_ida_base;
    $total_acumulado += $precio_vuelta_base;

    // B. Agregar cargos de los planes tarifarios elegidos por pasajero
    if ($id_vuelo_ida > 0 && isset($planes_seleccionados[$i]['ida'])) {
        $id_p_ida = $planes_seleccionados[$i]['ida'];
        $total_acumulado += (float)($planes_db[$id_p_ida]['cargo_extra_plan'] ?? 0);
    }
    if ($id_vuelo_vuelta > 0 && isset($planes_seleccionados[$i]['vuelta'])) {
        $id_p_vta = $planes_seleccionados[$i]['vuelta'];
        $total_acumulado += (float)($planes_db[$id_p_vta]['cargo_extra_plan'] ?? 0);
    }

    // C. Agregar cargos de equipajes extra asignados al pasajero
    if (isset($equipajes_sesion[$i])) {
        foreach ($equipajes_sesion[$i] as $id_tipo => $cant) {
            $precio_eq = (float)($equipajes_db[$id_tipo]['precio_unitario'] ?? 0);
            $total_acumulado += ($precio_eq * (int)$cant);
        }
    }

    // D. Agregar cargos de servicios a bordo asignados al pasajero
    if (isset($servicios_sesion[$i])) {
        foreach ($servicios_sesion[$i] as $id_servicio) {
            $total_acumulado += (float)($servicios_db[$id_servicio]['precio_servicio'] ?? 0);
        }
    }
}

// E. Helper para calcular cargos de asientos dinámicamente según la regla de la cabina
function obtenerCargoAsiento($asiento_texto) {
    if (empty($asiento_texto)) return 0.00;
    preg_match('/(\d+)/', $asiento_texto, $matches);
    $fila = (int)($matches[1] ?? 1);
    
    if ($fila == 1) return 5000.00;
    if ($fila >= 2 && $fila <= 5) return 12000.00;
    if ($fila == 6 || $fila == 7) return 3000.00;
    if ($fila == 12 || $fila == 13) return 4500.00;
    return 0.00;
}

// Sumar al total acumulado los cargos de los asientos seleccionados
foreach ($asientos_ida as $asiento) { $total_acumulado += obtenerCargoAsiento($asiento); }
foreach ($asientos_vuelta as $asiento) { $total_acumulado += obtenerCargoAsiento($asiento); }

include_once '../../includes/header.php';
?>

<link rel="stylesheet" href="css/proceso_compra/pasajeros.css">

<div class="contenedor-pasajeros">
    
    <form action="pago.php" method="POST">
        <h2 class="titulo-seccion">Información de los Pasajeros</h2>
        
        <?php for($i = 1; $i <= $cantidad_pasajeros; $i++): ?>
            <div class="card">
                <h3>Pasajero #<?=$i?></h3>
                
                <div class="grid-inputs">
                    <input type="text" name="pasajero[<?=$i?>][nombre]" placeholder="Nombre" required>
                    <input type="text" name="pasajero[<?=$i?>][apellido]" placeholder="Apellido" required>
                    
                    <select name="pasajero[<?=$i?>][tipo_doc]" required>
                        <option value="DNI">DNI</option>
                        <option value="Pasaporte">Pasaporte</option>
                    </select>
                    
                    <input type="text" name="pasajero[<?=$i?>][doc]" placeholder="Documento" required>
                    <input type="date" name="pasajero[<?=$i?>][fnac]" required>
                </div>
                
                <div class="bloque-asistencia">
                    <label>
                        <input type="checkbox" name="pasajero[<?=$i?>][asistencia]" value="1"> 
                        Requiere asistencia especial
                    </label>
                    <textarea name="pasajero[<?=$i?>][detalles_medicos]" placeholder="Detalles médicos si requiere asistencia..."></textarea>
                </div>
            </div>
        <?php endfor; ?>
        
        <button type="submit" class="btn-pago">Ir al Pago</button>
    </form>

    <div class="sidebar-resumen">
        <h3>Resumen de tu Viaje</h3>
        <p><strong>Vuelo Ida:</strong> <?=$vuelo_ida_num?></p>
        <?php if ($vuelo_vuelta_num !== ""): ?>
            <p><strong>Vuelo Vuelta:</strong> <?=$vuelo_vuelta_num?></p>
        <?php endif; ?>
        <p><strong>Pasajeros:</strong> x<?=$cantidad_pasajeros?></p>
        
        <p class="titulo-subseccion-resumen">Asientos Asignados:</p>
        <ul class="lista-resumen">
            <?php foreach ($asientos_ida as $index => $asiento_texto): 
                $cargo = obtenerCargoAsiento($asiento_texto);
            ?>
                <li>Pasajero #<?=($index + 1)?> (Ida): Asiento <strong><?=$asiento_texto?></strong> (+$<?=number_format($cargo, 2)?>)</li>
            <?php endforeach; ?>

            <?php foreach ($asientos_vuelta as $index => $asiento_texto): 
                $cargo = obtenerCargoAsiento($asiento_texto);
            ?>
                <li>Pasajero #<?=($index + 1)?> (Vuelta): Asiento <strong><?=$asiento_texto?></strong> (+$<?=number_format($cargo, 2)?>)</li>
            <?php endforeach; ?>
        </ul>
        
        <?php if(!empty($equipajes_sesion)): ?>
            <p class="titulo-subseccion-resumen">Equipaje Extra:</p>
            <ul class="lista-resumen">
                <?php foreach($equipajes_sesion as $num_p => $items): ?>
                    <?php foreach($items as $id => $cant): 
                        $eq = $equipajes_db[$id] ?? null;
                        if (!$eq) continue;
                        $costo_parcial = $eq['precio_unitario'] * $cant;
                    ?>
                        <li>Pasajero #<?=$num_p?>: <?=$eq['nombre_tipo']?> (x<?=$cant?>) +$<?=number_format($costo_parcial, 2)?></li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if(!empty($servicios_sesion)): ?>
            <p class="titulo-subseccion-resumen">Servicios adicionales:</p>
            <ul class="lista-resumen">
                <?php foreach($servicios_sesion as $num_p => $servicios_p): ?>
                    <?php foreach($servicios_p as $id_serv): 
                        $srv = $servicios_db[$id_serv] ?? null;
                        if (!$srv) continue;
                    ?>
                        <li>Pasajero #<?=$num_p?>: <?=$srv['nombre_servicio']?> +$<?=number_format($srv['precio_servicio'], 2)?></li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        
        <hr class="separador">
        <h4 class="total-contenedor">
            <span>Total Final Acumulado:</span>
            <span class="total-precio">$<?=number_format($total_acumulado, 2)?></span>
        </h4>
    </div>
</div>
</body>
</html>