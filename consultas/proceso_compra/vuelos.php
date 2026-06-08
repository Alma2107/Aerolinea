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

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; max-width: 1200px; margin: 20px auto; padding: 0 20px; font-family: sans-serif;">
    
    <form action="equipaje.php" method="POST">
        
        <div class="card" style="border: 1px solid #ddd; padding: 20px; border-radius: 8px; margin-bottom: 20px; background:#fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h2 style="color: #0056b3; margin-top:0;">🛫 1. Selecciona tus Vuelos</h2>
            
            <?php if ($tipo_viaje !== 'solo_vuelta'): ?>
                <div style="margin-bottom: 20px;">
                    <h4 style="margin-bottom: 8px; color: #333;">Vuelo de Ida:</h4>
                    <?php foreach($vuelos as $index => $v): ?>
                        <label style="display: block; border: 1px solid #ccc; padding: 12px; margin-bottom: 8px; border-radius:6px; cursor: pointer; background: #fff; transition: background 0.2s;">
                            <input type="radio" name="id_vuelo_ida" value="<?=$v['id_vuelo']?>" data-precio="<?=$v['precio_base_vuelo']?>" class="selector-vuelo" <?= $index === 0 ? 'checked' : '' ?> required>
                            <strong><?=$v['numero_vuelo']?></strong> - Salida: <?=$v['fecha_salida']?> | Base: <strong>$<?=number_format($v['precio_base_vuelo'], 2)?></strong>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <input type="hidden" name="id_vuelo_ida" value="0" data-precio="0" class="selector-vuelo">
            <?php endif; ?>

            <?php if ($tipo_viaje !== 'solo_ida'): ?>
                <div>
                    <h4 style="margin-bottom: 8px; color: #333;">Vuelo de Vuelta / Regreso:</h4>
                    <?php foreach($vuelos as $index => $v): ?>
                        <label style="display: block; border: 1px solid #ccc; padding: 12px; margin-bottom: 8px; border-radius:6px; cursor: pointer; background: #fff; transition: background 0.2s;">
                            <input type="radio" name="id_vuelo_vuelta" value="<?=$v['id_vuelo']?>" data-precio="<?=$v['precio_base_vuelo']?>" class="selector-vuelo" <?= ($index === 0) ? 'checked' : '' ?> required>
                            <strong><?=$v['numero_vuelo']?></strong> - Regreso: <?=$v['fecha_salida']?> | Base: <strong>$<?=number_format($v['precio_base_vuelo'], 2)?></strong>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <input type="hidden" name="id_vuelo_vuelta" value="0" data-precio="0" class="selector-vuelo">
            <?php endif; ?>
        </div>

        <div class="card" style="border: 1px solid #ddd; padding: 20px; border-radius: 8px; margin-bottom: 20px; background:#fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h2 style="color: #0056b3; margin-top:0;">🎒 2. Personaliza las Tarifas por Pasajero</h2>
            
            <?php for($i = 1; $i <= $cantidad_pasajeros; $i++): ?>
                <div style="border: 1px solid #0056b3; padding: 15px; margin-bottom: 20px; border-radius: 8px; background: #fff;">
                    <h3 style="margin-top: 0; color: #0056b3; border-bottom: 1px solid #eee; padding-bottom: 5px;">👤 Pasajero #<?=$i?></h3>
                    
                    <?php if ($tipo_viaje !== 'solo_vuelta'): ?>
                        <div class="contenedor-plan-ida" style="margin-bottom: 15px;">
                            <h4 style="margin: 5px 0; color: #555; font-size: 14px;">✈️ Tarifa para la IDA:</h4>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                                <?php foreach($planes as $index => $p): ?>
                                    <label style="border: 1px solid #ddd; padding: 10px; border-radius:6px; background:#fafafa; display: block; cursor: pointer; font-size: 13px;">
                                        <input type="radio" name="planes[<?=$i?>][ida]" value="<?=$p['id_plan']?>" data-precio="<?=$p['cargo_extra_plan']?>" class="selector-plan" <?= $index === 0 ? 'checked' : '' ?> required>
                                        <strong><?=$p['nombre_plan']?></strong> (+$<?=number_format($p['cargo_extra_plan'], 0)?>)
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($tipo_viaje !== 'solo_ida'): ?>
                        <div class="contenedor-plan-vuelta">
                            <h4 style="margin: 5px 0; color: #555; font-size: 14px;">🛬 Tarifa para la VUELTA:</h4>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                                <?php foreach($planes as $index => $p): ?>
                                    <label style="border: 1px solid #ddd; padding: 10px; border-radius:6px; background:#fafafa; display: block; cursor: pointer; font-size: 13px;">
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

        <button type="submit" style="background:#0056b3; color:#fff; padding:14px 30px; border:none; border-radius:4px; cursor:pointer; font-weight:bold; font-size: 16px; width: 100%;">Continuar al Equipaje</button>
    </form>

    <div style="border: 1px solid #0056b3; padding: 20px; border-radius: 8px; background: #f4f8ff; height: fit-content; position: sticky; top: 20px;">
        <h3 style="color:#0056b3; border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-top:0;">Resumen de tu Viaje</h3>
        <p style="margin: 10px 0;"><strong>Pasajeros:</strong> x<?=$cantidad_pasajeros?></p>
        
        <div id="detalle-precio-dinamico" style="font-size: 13px; color: #444; line-height: 1.6;"></div>

        <hr style="border:0; border-top:1px dashed #0056b3; margin: 15px 0;">
        <h3 style="margin:0; display:flex; justify-content:space-between; font-size: 18px;">
            <span>Total Inicial:</span>
            <span style="color:green;" id="total-vista">$0.00</span>
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