<?php
require_once '../../config/conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['servicios'] = $_POST['servicios'] ?? [];
}

// Consultas Historial
$stmtV = $pdo->prepare("SELECT numero_vuelo, precio_base_vuelo FROM vuelos WHERE id_vuelo = ?");
$stmtV->execute([$_SESSION['id_vuelo']]);
$vuelo_sel = $stmtV->fetch(PDO::FETCH_ASSOC);

$stmtP = $pdo->prepare("SELECT nombre_plan, cargo_extra_plan FROM planes_tarifas WHERE id_plan = ?");
$stmtP->execute([$_SESSION['id_plan']]);
$plan_sel = $stmtP->fetch(PDO::FETCH_ASSOC);

$total_acumulado = ($vuelo_sel['precio_base_vuelo'] + $plan_sel['cargo_extra_plan']) * $_SESSION['pasajeros'];

$asientos = $pdo->query("SELECT id_asiento_avion, numero_asiento, categoria, cargo_extra FROM asientos_avion")->fetchAll(PDO::FETCH_ASSOC);
$cantidad_pasajeros = $_SESSION['pasajeros'] ?? 1;

include_once '../../includes/header.php';
?>
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; max-width: 1200px; margin: 20px auto; padding: 0 20px;">
    
    <form action="pasajeros.php" method="POST">
        
        <?php for($i = 1; $i <= $cantidad_pasajeros; $i++): ?>
            <div class="card" style="border: 1px solid #ddd; padding: 20px; border-radius: 8px; background:#fff; margin-bottom: 25px;">
                <h2 style="margin-top:0; color:#0056b3;">👤 Pasajero #<?=$i?>: Elegí tu asiento</h2>
                <p style="font-size: 13px; color: #666; margin-bottom: 15px;">Selecciona una ubicación exclusiva para el pasajero número <?=$i?>.</p>
                
                <div class="mapa-asientos" data-pasajero="<?=$i?>" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;">
                    <?php foreach($asientos as $as): ?>
                        <label class="asiento-box asiento-p<?=$i?>" style="border: 1px solid #ccc; padding: 10px; text-align: center; display: block; cursor: pointer; border-radius:4px; background:#fff;">
                            <input type="radio" name="id_asiento[<?=$i?>]" value="<?=$as['id_asiento_avion']?>" required style="display:none;" class="radio-asiento">
                            <strong><?=$as['numero_asiento']?></strong><br>
                            <span style="font-size: 10px; color: #888;"><?=$as['categoria']?></span><br>
                            <span style="font-size: 11px; color: green;" data-precio="<?=$as['cargo_extra']?>">+$<?=$as['cargo_extra']?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endfor; ?>

        <button type="submit" style="background:#0056b3; color:#fff; padding:12px 25px; border:none; border-radius:4px; cursor:pointer; font-weight:bold; margin-top:10px; font-size:15px;">Continuar a Datos de Pasajeros</button>
    </form>

    <div style="border: 1px solid #0056b3; padding: 20px; border-radius: 8px; background: #f4f8ff; height: fit-content; position: sticky; top: 20px;">
        <h3 style="color:#0056b3; border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-top:0;">Resumen de tu Viaje</h3>
        <p><strong>Pasajeros:</strong> x<?=$cantidad_pasajeros?></p>
        
        <?php if(!empty($_SESSION['equipajes'])): ?>
            <?php foreach($_SESSION['equipajes'] as $id => $cant): 
                $stmtE = $pdo->prepare("SELECT precio_unitario FROM tipos_equipaje WHERE id_tipo_equipaje = ?");
                $stmtE->execute([$id]);
                $total_acumulado += ($stmtE->fetchColumn() * $cant);
            endforeach; ?>
        <?php endif; ?>

        <?php if(!empty($_SESSION['servicios'])): ?>
            <p style="margin-bottom:2px; font-weight:bold;">Servicios adicionales:</p>
            <ul style="margin:0; padding-left:20px; font-size:13px; color:#444;">
                <?php foreach($_SESSION['servicios'] as $id_serv): 
                    $stmtS = $pdo->prepare("SELECT nombre_servicio, precio_servicio FROM servicios_adicionales WHERE id_servicio = ?");
                    $stmtS->execute([$id_serv]);
                    $srv = $stmtS->fetch();
                    $total_acumulado += $srv['precio_servicio'];
                ?>
                    <li><?=$srv['nombre_servicio']?>: +$<?=number_format($srv['precio_servicio'], 2)?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <hr style="border:0; border-top:1px dashed #ccc; margin-top:15px;">
        <h4 style="margin:10px 0; display:flex; justify-content:space-between;">
            <span>Total Acumulado:</span>
            <span style="color:green;" id="total-vista" data-base="<?=$total_acumulado?>">$<?=number_format($total_acumulado, 2)?></span>
        </h4>
    </div>
</div>

<script>
    // Script interactivo mejorado para manejar múltiples mapas independientes
    <?php for($i = 1; $i <= $cantidad_pasajeros; $i++): ?>
        document.querySelectorAll('.asiento-p<?=$i?>').forEach(box => {
            box.addEventListener('click', function() {
                // Desmarcar sólo los asientos pertenecientes al mapa de este pasajero específico
                document.querySelectorAll('.asiento-p<?=$i?>').forEach(b => {
                    b.style.backgroundColor = '#fff'; 
                    b.style.color = '#000';
                });
                // Marcar el asiento seleccionado
                this.style.backgroundColor = '#0056b3'; 
                this.style.color = '#fff';
                
                calcularTotalDinamico();
            });
        });
    <?php endfor; ?>

    // Función opcional que va sumando los cargos de los asientos en tiempo real en la barra lateral
    function calcularTotalDinamico() {
        const totalVista = document.getElementById('total-vista');
        let subtotal = parseFloat(totalVista.getAttribute('data-base'));
        
        // Sumamos el valor extra de cada radio que esté chequeado
        document.querySelectorAll('.radio-asiento:checked').forEach(radio => {
            const contenedor = radio.closest('.asiento-box');
            const precioExtra = parseFloat(contenedor.querySelector('[data-precio]').getAttribute('data-precio')) || 0;
            subtotal += precioExtra;
        });
        
        totalVista.textContent = '$' + subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
</script>
</body>
</html>