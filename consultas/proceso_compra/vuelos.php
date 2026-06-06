<?php
require_once '../../config/conexion.php';
session_start();

if(isset($_GET['pasajeros'])) {
    $_SESSION['pasajeros'] = $_GET['pasajeros'];
}

$vuelos = $pdo->query("SELECT id_vuelo, numero_vuelo, fecha_salida, precio_base_vuelo FROM vuelos")->fetchAll();
$planes = $pdo->query("SELECT id_plan, nombre_plan, descripcion FROM planes_tarifas")->fetchAll();

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
                        <strong><?=$v['numero_vuelo']?></strong> - Salida: <?=$v['fecha_salida']?> | Base: $<?=$v['precio_base_vuelo']?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <h2>2. Selecciona tu Plan de Viaje</h2>
                <div class="grid-tarifas">
                    <?php foreach($planes as $p): ?>
                        <div class="tarifa-card">
                            <h3><?=$p['nombre_plan']?></h3>
                            <p style="font-size:12px; margin:10px 0; min-height:40px;"><?=$p['descripcion']?></p>
                            <input type="radio" name="id_plan" value="<?=$p['id_plan']?>" required>
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