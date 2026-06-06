<?php
require_once '../../config/conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['id_vuelo'] = $_POST['id_vuelo'];
    $_SESSION['id_plan'] = $_POST['id_plan'];
}

$servicios = $pdo->query("SELECT id_servicio, nombre_servicio, descripcion, precio_servicio FROM servicios_adicionales")->fetchAll();
include_once '../../includes/header.php';
?>
<form action="asientos.php" method="POST">
    <div class="booking-layout">
        <div class="main-content">
            <div class="card">
                <h2>¿Llevás equipaje adicional?</h2>
                <p style="color:green; margin-bottom:15px;">Mochila personal incluida: $0</p>
                <div class="grid-equipaje">
                    <?php foreach($servicios as $s): ?>
                        <div class="equipaje-card">
                            <h4><?=$s['nombre_servicio']?></h4>
                            <p><strong>+$<?=$s['precio_servicio']?></strong></p>
                            <input type="checkbox" name="servicios[]" value="<?=$s['id_servicio']?>"> Agregar
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="submit" class="btn-next">Elegir Asientos</button>
        </div>
    </div>
</form>
</body>
</html>