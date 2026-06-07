<?php
session_start();
require_once '../../config/conexion.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['datos_pasajeros'] = $_POST['pasajero'];
}

$metodos = $pdo->query("SELECT id_metodo_pago, nombre_metodo, banco_proveedor FROM metodos_pago")->fetchAll(PDO::FETCH_ASSOC);
include_once '../../includes/header.php';
?>
<form action="confirmar.php" method="POST">
    <div class="booking-layout">
        <div class="main-content">
            <div class="card">
                <h2>Método de Pago</h2>
                <div style="margin: 15px 0; display:flex; flex-direction:column; gap:8px;">
                    <?php foreach($metodos as $index => $m): ?>
                        <label>
                            <input type="radio" name="id_metodo_pago" value="<?=$m['id_metodo_pago']?>" <?=$index === 0 ? 'checked' : ''?>> 
                            <?=$m['nombre_metodo']?> (<?=$m['banco_proveedor']?>)
                        </label>
                    <?php endforeach; ?>
                </div>
                <div style="display:flex; flex-direction:column; gap:10px; margin-top:15px;">
                    <input type="text" placeholder="Número de Tarjeta" required style="padding:10px;">
                    <input type="text" placeholder="Nombre del Titular" required style="padding:10px;">
                </div>
            </div>
            <button type="submit" class="btn-next">Finalizar Reserva y Pagar</button>
        </div>
    </div>
</form>
</body>
</html>