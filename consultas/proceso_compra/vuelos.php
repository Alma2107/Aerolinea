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

// Traer vuelos disponibles
$vuelos = $pdo->query("SELECT id_vuelo, numero_vuelo, fecha_salida, precio_base_vuelo FROM vuelos WHERE estado_vuelo = 'Programado'")->fetchAll(PDO::FETCH_ASSOC);
// Traer planes tarifarios disponibles
$planes = $pdo->query("SELECT id_plan, nombre_plan, descripcion, cargo_extra_plan FROM planes_tarifas")->fetchAll(PDO::FETCH_ASSOC);

$cantidad_pasajeros = $_SESSION['pasajeros'];

include_once '../../includes/header.php';
?>

<link rel="stylesheet" href="css/estilos-vuelos.css">

<div class="contenedor-vuelos">
    
    <form action="equipaje.php" method="POST">
        
        <div class="card">
            <h2>🛫 1. Selecciona tus Vuelos</h2>
            
            <?php if ($tipo_viaje !== 'solo_vuelta'): ?>
                <div class="bloque-vuelo">
                    <h4>Vuelo de Ida:</h4>
                    <?php foreach($vuelos as $index => $v): ?>
                        <label class="opcion-vuelo">
                            <input type="radio" name="id_vuelo_ida" value="<?=$v['id_vuelo']?>" data-precio="<?=$v['precio_base_vuelo']?>" class="selector-vuelo" <?= $index === 0 ? 'checked' : '' ?> required>
                            <strong><?=$v['numero_vuelo']?></strong> - Salida: <?=$v['fecha_salida']?> | Base: <strong>$<?=number_format($v['precio_base_vuelo'], 2)?></strong>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <input type="hidden" name="id_vuelo_ida" value="0" data-precio="0" class="selector-vuelo">
            <?php endif; ?>

            <?php if ($tipo_viaje !== 'solo_ida'): ?>
                <div class="bloque-vuelo">
                    <h4>Vuelo de Vuelta / Regreso:</h4>
                    <?php foreach($vuelos as $index => $v): ?>
                        <label class="opcion-vuelo">
                            <input type="radio" name="id_vuelo_vuelta" value="<?=$v['id_vuelo']?>" data-precio="<?=$v['precio_base_vuelo']?>" class="selector-vuelo" <?= ($index === 0) ? 'checked' : '' ?> required>
                            <strong><?=$v['numero_vuelo']?></strong> - Regreso: <?=$v['fecha_salida']?> | Base: <strong>$<?=number_format($v['precio_base_vuelo'], 2)?></strong>
                        </label>
                    <?php endforeach; ?>
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

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const cantidadPasajeros = <?=$cantidad_pasajeros?>;
        const tipoViajeInicial = "<?=$tipo_viaje?>";
        
        function calcularTotal() {
            let total = 0;
            let htmlDetalle = "";

            // 1. Obtener vuelos seleccionados u ocultos
            const radioIda = document.querySelector('input[name="id_vuelo_ida"]:checked') || document.querySelector('input[name="id_vuelo_ida"][type="hidden"]');
            const radioVuelta = document.querySelector('input[name="id_vuelo_vuelta"]:checked') || document.querySelector('input[name="id_vuelo_vuelta"][type="hidden"]');

            let precioIdaBase = radioIda ? parseFloat(radioIda.getAttribute('data-precio')) : 0;
            let precioVueltaBase = radioVuelta ? parseFloat(radioVuelta.getAttribute('data-precio')) : 0;

            // Sumar tramo Ida
            if(precioIdaBase > 0 && tipoViajeInicial !== 'solo_vuelta') {
                let subIda = precioIdaBase * cantidadPasajeros;
                total += subIda;
                htmlDetalle += `<p>• Vuelo Ida Base: $${precioIdaBase.toLocaleString()} x ${cantidadPasajeros} = <strong>$${subIda.toLocaleString()}</strong></p>`;
            }

            // Sumar tramo Vuelta
            if (precioVueltaBase > 0 && tipoViajeInicial !== 'solo_ida') {
                let subVuelta = precioVueltaBase * cantidadPasajeros;
                total += subVuelta;
                htmlDetalle += `<p>• Vuelo Vuelta Base: $${precioVueltaBase.toLocaleString()} x ${cantidadPasajeros} = <strong>$${subVuelta.toLocaleString()}</strong></p>`;
            }

            // 2. Sumar planes individuales por pasajero
            htmlDetalle += `<p style="border-top: 1px solid #ccc; padding-top:5px; margin-top:5px;"><strong>Planes seleccionados:</strong></p>`;
            
            for(let i = 1; i <= cantidadPasajeros; i++) {
                // Plan Ida
                if(tipoViajeInicial !== 'solo_vuelta') {
                    const planIda = document.querySelector(`input[name="planes[${i}][ida]"]:checked`);
                    let precioPlanIda = planIda ? parseFloat(planIda.getAttribute('data-precio')) : 0;
                    total += precioPlanIda;
                    htmlDetalle += `<div style="padding-left: 10px; font-size:12px;">Pasajero #${i} - Ida: +$${precioPlanIda.toLocaleString()}</div>`;
                }

                // Plan Vuelta
                if (tipoViajeInicial !== 'solo_ida') {
                    const planVuelta = document.querySelector(`input[name="planes[${i}][vuelta]"]:checked`);
                    let precioPlanVuelta = planVuelta ? parseFloat(planVuelta.getAttribute('data-precio')) : 0;
                    total += precioPlanVuelta;
                    htmlDetalle += `<div style="padding-left: 10px; font-size:12px; margin-bottom: 5px;">Pasajero #${i} - Vuelta: +$${precioPlanVuelta.toLocaleString()}</div>`;
                }
            }

            // Renderizar datos en la interfaz
            document.getElementById('detalle-precio-dinamico').innerHTML = htmlDetalle;
            document.getElementById('total-vista').textContent = '$' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        // Detectar cambios en los radio inputs
        document.querySelectorAll('.selector-vuelo, .selector-plan').forEach(elem => {
            elem.addEventListener('change', calcularTotal);
        });

        // Forzar cálculo inicial al cargar la página
        calcularTotal();
    });
</script>
</body>
</html>