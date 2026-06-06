<?php
session_start();
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['id_asiento'] = $_POST['id_asiento'];
}
$cantidad = $_SESSION['pasajeros'] ?? 1;
include_once '../../includes/header.php';
?>
<form action="pago.php" method="POST">
    <div class="booking-layout">
        <div class="main-content">
            <h2>Información de los Pasajeros</h2>
            <?php for($i = 1; $i <= $cantidad; $i++): ?>
                <div class="card">
                    <h3>Pasajero #<?=$i?></h3>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-top:10px;">
                        <input type="text" name="pasajero[<?=$i?>][nombre]" placeholder="Nombre" required style="padding:8px;">
                        <input type="text" name="pasajero[<?=$i?>][apellido]" placeholder="Apellido" required style="padding:8px;">
                        <input type="text" name="pasajero[<?=$i?>][doc]" placeholder="Documento" required style="padding:8px;">
                        <input type="date" name="pasajero[<?=$i?>][fnac]" required style="padding:8px;">
                    </div>
                    <div style="margin-top:10px;">
                        <label><input type="checkbox" name="pasajero[<?=$i?>][asistencia]" value="1"> Requiere asistencia especial</label>
                    </div>
                </div>
            <?php endfor; ?>
            <button type="submit" class="btn-next">Ir al Pago</button>
        </div>
    </div>
</form>
</body>
</html>