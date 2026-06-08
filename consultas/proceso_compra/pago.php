<?php
session_start();
require_once '../../config/conexion.php';

// 1. CAPTURAR DATOS DE LOS PASAJEROS DEL PASO ANTERIOR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pasajero'])) {
    $_SESSION['datos_pasajeros'] = $_POST['pasajero'];
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

// 2. CARGAR CATÁLOGOS EN MEMORIA (Previene lentitud en pasarela de pago)
$planes_db = $pdo->query("SELECT id_plan, nombre_plan, cargo_extra_plan FROM planes_tarifas")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
$equipajes_db = $pdo->query("SELECT id_tipo_equipaje, nombre_tipo, precio_unitario FROM tipos_equipaje")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
$servicios_db = $pdo->query("SELECT id_servicio, nombre_servicio, precio_servicio FROM servicios_adicionales")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);

// 3. OBTENER INFORMACIÓN DE VUELOS
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

// 4. CALCULO INTEGRAL FINAL DEL TOTAL
$total_final = 0;

for ($i = 1; $i <= $cantidad_pasajeros; $i++) {
    // A. Costo Base de los Vuelos
    $total_final += $precio_ida_base;
    $total_final += $precio_vuelta_base;

    // B. Costo de Planes por Pasajero
    if ($id_vuelo_ida > 0 && isset($planes_seleccionados[$i]['ida'])) {
        $id_p_ida = $planes_seleccionados[$i]['ida'];
        $total_final += (float)($planes_db[$id_p_ida]['cargo_extra_plan'] ?? 0);
    }
    if ($id_vuelo_vuelta > 0 && isset($planes_seleccionados[$i]['vuelta'])) {
        $id_p_vta = $planes_seleccionados[$i]['vuelta'];
        $total_final += (float)($planes_db[$id_p_vta]['cargo_extra_plan'] ?? 0);
    }

    // C. Costo de Equipajes Extra
    if (isset($equipajes_sesion[$i])) {
        foreach ($equipajes_sesion[$i] as $id_tipo => $cant) {
            $precio_eq = (float)($equipajes_db[$id_tipo]['precio_unitario'] ?? 0);
            $total_final += ($precio_eq * (int)$cant);
        }
    }

    // D. Costo de Servicios Adicionales
    if (isset($servicios_sesion[$i])) {
        foreach ($servicios_sesion[$i] as $id_servicio) {
            $total_final += (float)($servicios_db[$id_servicio]['precio_servicio'] ?? 0);
        }
    }
}

// E. Helper de Cálculo para los Asientos Elegidos
function calcularCostoAsiento($asiento_texto) {
    if (empty($asiento_texto)) return 0.00;
    preg_match('/(\d+)/', $asiento_texto, $matches);
    $fila = (int)($matches[1] ?? 1);
    
    if ($fila == 1) return 5000.00;
    if ($fila >= 2 && $fila <= 5) return 12000.00;
    if ($fila == 6 || $fila == 7) return 3000.00;
    if ($fila == 12 || $fila == 13) return 4500.00;
    return 0.00;
}

// Sumar los asientos reales seleccionados de ambos tramos al total final
foreach ($asientos_ida as $asiento) { $total_final += calcularCostoAsiento($asiento); }
foreach ($asientos_vuelta as $asiento) { $total_final += calcularCostoAsiento($asiento); }

// Traer métodos de pago
$metodos = $pdo->query("SELECT id_metodo_pago, nombre_metodo, banco_proveedor FROM metodos_pago")->fetchAll(PDO::FETCH_ASSOC);

include_once '../../includes/header.php';
?>

<link rel="stylesheet" href="css/proceso_compra/pago.css">

<div class="contenedor-pago">
    
    <form action="confirmar.php" method="POST">
        <div class="card">
            <h2>💳 Método de Pago</h2>
            
            <div class="grupo-metodos">
                <?php foreach($metodos as $index => $m): ?>
                    <label class="metodo-item">
                        <input type="radio" name="id_metodo_pago" value="<?=$m['id_metodo_pago']?>" <?=$index === 0 ? 'checked' : ''?>> 
                        <div class="metodo-info">
                            <strong><?=$m['nombre_metodo']?></strong> — <span><?=$m['banco_proveedor']?></span>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
            
            <div class="inputs-tarjeta">
                <input type="text" name="nro_tarjeta" placeholder="Número de Tarjeta" required>
                <input type="text" name="titular_tarjeta" placeholder="Nombre del Titular" required>
            </div>
        </div>
        
        <button type="submit" class="btn-pagar">Finalizar Reserva y Pagar</button>
    </form>

    <div class="sidebar-resumen-final">
        <h3>Detalle Final de Pago</h3>
        <p><strong>Vuelo Ida:</strong> <?=$vuelo_ida_num?></p>
        <?php if ($vuelo_vuelta_num !== ""): ?>
            <p><strong>Vuelo Vuelta:</strong> <?=$vuelo_vuelta_num?></p>
        <?php endif; ?>
        <p><strong>Cantidad de Pasajeros:</strong> x<?=$cantidad_pasajeros?></p>
        
        <p class="titulo-subseccion">Asientos Elegidos:</p>
        <ul class="lista-items">
            <?php if (!empty($asientos_ida)): ?>
                <?php foreach ($asientos_ida as $index => $codigo): 
                    $cargo = calcularCostoAsiento($codigo);
                ?>
                    <li>Pasajero #<?=($index+1)?> (Ida): <strong><?=$codigo?></strong> (+$<?=number_format($cargo, 2)?>)</li>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($asientos_vuelta)): ?>
                <?php foreach ($asientos_vuelta as $index => $codigo): 
                    $cargo = calcularCostoAsiento($codigo);
                ?>
                    <li>Pasajero #<?=($index+1)?> (Vuelta): <strong><?=$codigo?></strong> (+$<?=number_format($cargo, 2)?>)</li>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (empty($asientos_ida) && empty($asientos_vuelta)): ?>
                <li>No se seleccionaron asientos</li>
            <?php endif; ?>
        </ul>
        
        <?php if(!empty($equipajes_sesion)): ?>
            <p class="titulo-subseccion">Equipaje Extra:</p>
            <ul class="lista-items">
                <?php foreach($equipajes_sesion as $num_p => $items): ?>
                    <?php foreach($items as $id => $cant): 
                        $eq = $equipajes_db[$id] ?? null;
                        if (!$eq) continue;
                        $costo = $eq['precio_unitario'] * $cant;
                    ?>
                        <li>P#<?=$num_p?>: <?=$eq['nombre_tipo']?> (x<?=$cant?>) +$<?=number_format($costo, 2)?></li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if(!empty($servicios_sesion)): ?>
            <p class="titulo-subseccion">Servicios Adicionales:</p>
            <ul class="lista-items">
                <?php foreach($servicios_sesion as $num_p => $servicios_p): ?>
                    <?php foreach($servicios_p as $id_serv): 
                        $srv = $servicios_db[$id_serv] ?? null;
                        if (!$srv) continue;
                    ?>
                        <li>P#<?=$num_p?>: <?=$srv['nombre_servicio']?> +$<?=number_format($srv['precio_servicio'], 2)?></li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        
        <hr class="separador-verde">
        <h3 class="total-final-contenedor">
            <span>TOTAL A PAGAR:</span>
            <span>$<?=number_format($total_final, 2)?></span>
        </h3>
    </div>
</div>
</body>
</html>