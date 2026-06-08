<?php
require_once '../../config/conexion.php';
session_start();

// 1. Capturar la cantidad de pasajeros desde el GET del buscador
if (isset($_GET['pasajeros'])) {
    $_SESSION['pasajeros'] = (int)$_GET['pasajeros'];
} else if (!isset($_SESSION['pasajeros'])) {
    $_SESSION['pasajeros'] = 1;
}

// 2. Capturar el tipo de viaje (solo_ida, solo_vuelta, ida_vuelta, multidestino)
$tipo_viaje = $_GET['tipo_viaje'] ?? 'ida_vuelta';

// 3. Capturar origen y destino del buscador (Ej: 'AEP', 'BRC')
$origen  = $_GET['origen'] ?? '';
$destino = $_GET['destino'] ?? '';

// Limpieza de datos de compras anteriores para evitar arrastrar configuraciones viejas
unset(
    $_SESSION['id_vuelo_ida'], 
    $_SESSION['id_vuelo_vuelta'], 
    $_SESSION['planes_pasajeros'], 
    $_SESSION['equipajes'], 
    $_SESSION['servicios'], 
    $_SESSION['id_asiento'], 
    $_SESSION['datos_pasajeros']
);

// --- CONSULTAS CORREGIDAS Y FILTRADAS SEGÚN LA BASE DE DATOS ---

// Vuelos de Ida: Filtramos donde coincida origen, destino y esté Programado
$stmtIda = $pdo->prepare("SELECT id_vuelo, numero_vuelo, fecha_salida, precio_base_vuelo 
                          FROM vuelos 
                          WHERE origen_iata = :origen AND destino_iata = :destino AND estado_vuelo = 'Programado'");
$stmtIda->execute(['origen' => $origen, 'destino' => $destino]);
$vuelos_ida = $stmtIda->fetchAll(PDO::FETCH_ASSOC);

// Vuelos de Vuelta: Invertimos el origen y el destino para el regreso
$vuelos_vuelta = [];
if ($tipo_viaje !== 'solo_ida') {
    $stmtVuelta = $pdo->prepare("SELECT id_vuelo, numero_vuelo, fecha_salida, precio_base_vuelo 
                                 FROM vuelos 
                                 WHERE origen_iata = :destino AND destino_iata = :origen AND estado_vuelo = 'Programado'");
    $stmtVuelta->execute(['origen' => $origen, 'destino' => $destino]);
    $vuelos_vuelta = $stmtVuelta->fetchAll(PDO::FETCH_ASSOC);
}

// Traer planes tarifarios disponibles
$planes = $pdo->query("SELECT id_plan, nombre_plan, descripcion, cargo_extra_plan FROM planes_tarifas")->fetchAll(PDO::FETCH_ASSOC);

$cantidad_pasajeros = $_SESSION['pasajeros'];

include_once '../../includes/header.php';
?>

<link rel="stylesheet" href="css/proceso_compra/vuelos.css">

<div class="contenedor-vuelos">
    
    <form action="equipaje.php" method="POST">
        
        <div class="card">
            <h2>🛫 1. Selecciona tus Vuelos</h2>
            
            <?php if ($tipo_viaje !== 'solo_vuelta'): ?>
                <div class="bloque-vuelo">
                    <h4>Vuelo de Ida (<?=$origen?> ➡️ <?=$destino?>):</h4>
                    <?php if(empty($vuelos_ida)): ?>
                        <p class="alerta-no-vuelos">No se encontraron vuelos de ida programados para esta ruta.</p>
                    <?php else: ?>
                        <?php foreach($vuelos_ida as $index => $v): ?>
                            <label class="opcion-vuelo">
                                <input type="radio" name="id_vuelo_ida" value="<?=$v['id_vuelo']?>" data-precio="<?=$v['precio_base_vuelo']?>" class="selector-vuelo" <?= $index === 0 ? 'checked' : '' ?> required>
                                <strong><?=$v['numero_vuelo']?></strong> - Salida: <?=$v['fecha_salida']?> | Base: <strong>$<?=number_format($v['precio_base_vuelo'], 2)?></strong>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <input type="hidden" name="id_vuelo_ida" value="0" data-precio="0" class="selector-vuelo">
            <?php endif; ?>

            <?php if ($tipo_viaje !== 'solo_ida'): ?>
                <div class="bloque-vuelo">
                    <h4>Vuelo de Vuelta / Regreso (<?=$destino?> ➡️ <?=$origen?>):</h4>
                    <?php if(empty($vuelos_vuelta)): ?>
                        <p class="alerta-no-vuelos">No se encontraron vuelos de regreso programados para esta ruta.</p>
                    <?php else: ?>
                        <?php foreach($vuelos_vuelta as $index => $v): ?>
                            <label class="opcion-vuelo">
                                <input type="radio" name="id_vuelo_vuelta" value="<?=$v['id_vuelo']?>" data-precio="<?=$v['precio_base_vuelo']?>" class="selector-vuelo" <?= ($index === 0) ? 'checked' : '' ?> required>
                                <strong><?=$v['numero_vuelo']?></strong> - Regreso: <?=$v['fecha_salida']?> | Base: <strong>$<?=number_format($v['precio_base_vuelo'], 2)?></strong>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <input type="hidden" name="id_vuelo_vuelta" value="0" data-precio="0" class="selector-vuelo">
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>🎒 2. Personaliza las Tarifas por Pasajero</h2>
            
            <?php for($i = 1; $i <= $cantidad_pasajeros; $i++): ?>
                <div class="card-pasajero">
                    <h3>👤 Pasajero #<?=$i?></h3>
                    
                    <?php if ($tipo_viaje !== 'solo_vuelta'): ?>
                        <div class="tramo-tarifa">
                            <h4>✈️ Tarifa para la IDA:</h4>
                            <div class="grid-planes">
                                <?php foreach($planes as $index => $p): ?>
                                    <label class="opcion-plan">
                                        <input type="radio" name="planes[<?=$i?>][ida]" value="<?=$p['id_plan']?>" data-precio="<?=$p['cargo_extra_plan']?>" class="selector-plan" <?= $index === 0 ? 'checked' : '' ?> required>
                                        <strong><?=$p['nombre_plan']?></strong> (+$<?=number_format($p['cargo_extra_plan'], 0)?>)
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($tipo_viaje !== 'solo_ida'): ?>
                        <div class="tramo-tarifa">
                            <h4>🛬 Tarifa para la VUELTA:</h4>
                            <div class="grid-planes">
                                <?php foreach($planes as $index => $p): ?>
                                    <label class="opcion-plan">
                                        <input type="radio" name="planes[<?=$i?>][vuelta]" value="<?=$p['id_plan']?>" data-precio="<?=$p['cargo_extra_plan']?>" class="selector-plan" <?= $index === 0 ? 'checked' : '' ?> required>
                                        <strong><?=$p['nombre_plan']?></strong> (+$<?=number_format($p['cargo_extra_plan'], 0)?>)
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            <?php endfor; ?>
        </div>

        <button type="submit" class="btn-continuar">Continuar al Equipaje</button>
    </form>

    <div class="sidebar-resumen">
        <h3>Resumen de tu Viaje</h3>
        <p><strong>Pasajeros:</strong> x<?=$cantidad_pasajeros?></p>
        
        <div id="detalle-precio-dinamico" class="detalle-dinamico"></div>

        <hr class="separador">
        <h3 class="total-contenedor">
            <span>Total Inicial:</span>
            <span class="precio-verde" id="total-vista">$0.00</span>
        </h3>
    </div>
</div>

<div id="js-variables-vuelo" 
     data-pasajeros="<?=$cantidad_pasajeros?>" 
     data-tipo-viaje="<?=$tipo_viaje?>">
</div>

<script src="js/proceso_compra/vuelos.js"></script>
</body>
</html>