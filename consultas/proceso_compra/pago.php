<?php
session_start();
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['datos_pasajeros'] = $_POST['pasajero'];
}
include_once '../../includes/header.php';
?>
<form action="confirmar.php" method="POST">
    <div class="booking-layout">
        <div class="main-content">
            <div class="card">
                <h2>Método de Pago</h2>
                <div style="margin: 15px 0;">
                    <label><input type="radio" name="metodo" checked> Tarjeta de Crédito</label>
                </div>
                <div style="display:flex; flex-direction:column; gap:10px;">
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