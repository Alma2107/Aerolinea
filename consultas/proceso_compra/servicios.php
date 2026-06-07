<?php
require_once '../../config/conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['equipajes'] = [];
    if (isset($_POST['equipaje'])) {
        foreach ($_POST['equipaje'] as $id_tipo => $cantidad) {
            if ((int)$cantidad > 0) {
                $_SESSION['equipajes'][$id_tipo] = (int)$cantidad;
            }
        }
    }
}

$servicios = $pdo->query("SELECT id_servicio, nombre_servicio, descripcion, precio_servicio FROM servicios_adicionales")->fetchAll(PDO::FETCH_ASSOC);
include_once '../../includes/header.php';
?>
<form action="asientos.php" method="POST">
    <div class="booking-layout">
        <div class="main-content">
            <div class="card">
                <h2>¿Deseás agregar servicios a bordo?</h2>
                <p style="margin-bottom:15px; color:#666;">Hacé tu viaje más cómodo sumando extras opcionales.</p>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <?php foreach($servicios as $s): ?>
                        <div style="border: 1px solid #eee; padding: 10px; border-radius: 4px;">
                            <label>
                                <input type="checkbox" name="servicios[]" value="<?=$s['id_servicio']?>"> 
                                <strong><?=$s['nombre_servicio']?></strong> (+$<?=number_format($s['precio_servicio'], 2)?>)
                                <p style="font-size: 11px; color:#666; margin:0;"><?=$s['descripcion']?></p>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="submit" class="btn-next">Continuar a Elegir Asientos</button>
        </div>
    </div>
</form>
</body>
</html>