<?php
require_once '../../config/conexion.php';
session_start();

if (isset($_GET['pasajeros'])) {
    $_SESSION['pasajeros'] = (int)$_GET['pasajeros'];
} else if (!isset($_SESSION['pasajeros'])) {
    $_SESSION['pasajeros'] = 1;
}

// Limpieza de datos anteriores
unset($_SESSION['id_vuelo'], $_SESSION['id_plan'], $_SESSION['equipajes'], $_SESSION['servicios'], $_SESSION['id_asiento'], $_SESSION['datos_pasajeros']);

$vuelos = $pdo->query("SELECT id_vuelo, numero_vuelo, fecha_salida, precio_base_vuelo FROM vuelos WHERE estado_vuelo = 'Programado'")->fetchAll(PDO::FETCH_ASSOC);
$planes = $pdo->query("SELECT id_plan, nombre_plan, descripcion, cargo_extra_plan FROM planes_tarifas")->fetchAll(PDO::FETCH_ASSOC);

include_once '../../includes/header.php';
?>
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; max-width: 1200px; margin: 20px auto; padding: 0 20px;">
    
    <form action="equipaje.php" method="POST">
        <div class="card" style="border: 1px solid #ddd; padding: 20px; border-radius: 8px; margin-bottom: 20px; background:#fff;">
            <h2>1. Selecciona tu Horario de Vuelo</h2>
            <?php foreach($vuelos as $v): ?>
                <div style="border: 1px solid #ccc; padding: 15px; margin-bottom: 10px; border-radius:6px;">
                    <input type="radio" name="id_vuelo" value="<?=$v['id_vuelo']?>" required checked>
                    <strong><?=$v['numero_vuelo']?></strong> - Salida: <?=$v['fecha_salida']?> | Base: $<?=number_format($v['precio_base_vuelo'], 2)?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card" style="border: 1px solid #ddd; padding: 20px; border-radius: 8px; margin-bottom: 20px; background:#fff;">
            <h2>2. Selecciona tu Plan de Viaje</h2>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <?php foreach($planes as $p): ?>
                    <div style="border: 1px solid #ddd; padding: 15px; border-radius:6px; background:#fafafa;">
                        <h3><?=$p['nombre_plan']?></h3>
                        <p style="font-size:12px; margin:10px 0; color:#555;"><?=$p['descripcion']?></p>
                        <p><strong>+$<?=number_format($p['cargo_extra_plan'], 2)?></strong></p>
                        <label><input type="radio" name="id_plan" value="<?=$p['id_plan']?>" required> Seleccionar</label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <button type="submit" style="background:#0056b3; color:#fff; padding:12px 25px; border:none; border-radius:4px; cursor:pointer; font-weight:bold;">Continuar al Equipaje</button>
    </form>

    <div style="border: 1px solid #0056b3; padding: 20px; border-radius: 8px; background: #f4f8ff; height: fit-content; position: sticky; top: 20px;">
        <h3 style="color:#0056b3; border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-top:0;">Resumen de tu Viaje</h3>
        <p style="margin: 10px 0;"><strong>Pasajeros:</strong> <?=$_SESSION['pasajeros']?></p>
        <hr style="border:0; border-top:1px dashed #ccc;">
        <p style="color:#666; font-style:italic; font-size:13px; text-align:center;">Selecciona un vuelo y un plan para calcular los costos iniciales.</p>
    </div>
</div>
</body>
</html>