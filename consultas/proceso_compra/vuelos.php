<?php
require_once '../../config/conexion.php';
session_start();

if (isset($_GET['pasajeros'])) {
    $_SESSION['pasajeros'] = (int)$_GET['pasajeros'];
} else if (!isset($_SESSION['pasajeros'])) {
    $_SESSION['pasajeros'] = 1;
}

// Limpieza para no arrastrar datos viejos
unset($_SESSION['id_vuelo'], $_SESSION['id_plan'], $_SESSION['equipajes'], $_SESSION['servicios'], $_SESSION['id_asiento'], $_SESSION['datos_pasajeros']);

$vuelos = $pdo->query("SELECT id_vuelo, numero_vuelo, fecha_salida, precio_base_vuelo FROM vuelos WHERE estado_vuelo = 'Programado'")->fetchAll(PDO::FETCH_ASSOC);
$planes = $pdo->query("SELECT id_plan, nombre_plan, descripcion, cargo_extra_plan FROM planes_tarifas")->fetchAll(PDO::FETCH_ASSOC);

include_once '../../includes/header.php';
?>
<form action="equipaje.php" method="POST">
    <div class="booking-layout">
        <div class="main-content">
            <div class="card">
                <h2>1. Selecciona tu Horario de Vuelo</h2>
                <?php foreach($vuelos as $v): ?>
                    <div style="border: 1px solid #ccc; padding: 15px; margin-bottom: 10px; border-radius:6px;">
                        <input type="radio" name="id_vuelo" value="<?=$v['id_vuelo']?>" required checked>
                        <strong><?=$v['numero_vuelo']?></strong> - Salida: <?=$v['fecha_salida']?> | Base: $<?=number_format($v['precio_base_vuelo'], 2)?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <h2>2. Selecciona tu Plan de Viaje</h2>
                <div class="grid-tarifas">
                    <?php foreach($planes as $p): ?>
                        <div class="tarifa-card" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; border-radius:6px;">
                            <h3><?=$p['nombre_plan']?> (+$<?=number_format($p['cargo_extra_plan'], 2)?>)</h3>
                            <p style="font-size:12px; margin:10px 0; min-height:40px;"><?=$p['descripcion']?></p>
                            <input type="radio" name="id_plan" value="<?=$p['id_plan']?>" required> Seleccionar
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="submit" class="btn-next">Continuar al Equipaje</button>
        </div>
    </div>
</form>
</body>
</html>