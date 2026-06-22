<?php
session_start();
$pageStyles = ['../../css/proceso_compra/pago.css'];
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pasajero'])) {
    $_SESSION['datos_pasajeros'] = $_POST['pasajero'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['usuario_id'])) {
        $_SESSION['redirect_after_auth'] = '../../consultas/proceso_compra/pago.php';
        header('Location: nocuenta.php');
        exit();
    }

    header('Location: confirmar.php');
    exit();
}

include_once '../../includes/header.php';
?>
<form action="pago.php" method="POST">
    <div class="contenedor-pago">
        <div class="main-content">
            <div class="card">
                <p class="eyebrow">Paso 5</p>
                <h2>Método de pago</h2>
                <div class="grupo-metodos">
                    <label class="metodo-item">
                        <input type="radio" name="metodo" checked>
                        <span class="metodo-info">
                            <div>Tarjeta de crédito</div>
                            <span>Pago inmediato y confirmación rápida</span>
                        </span>
                    </label>
                </div>
                <div class="inputs-tarjeta">
                    <input type="text" name="numero_tarjeta" placeholder="Número de tarjeta" required>
                    <input type="text" name="nombre_titular" placeholder="Nombre del titular" required>
                </div>
            </div>
            <button type="submit" class="btn-pagar">Finalizar reserva y pagar</button>
        </div>

        <aside class="sidebar-resumen-final">
            <h3>Resumen final</h3>
            <p>Antes de pagar, revisá que el pasajero, asiento y equipaje coincidan con lo que querés reservar.</p>
            <hr class="separador-verde">
            <p>La pantalla de confirmación usa el mismo bloque visual para que el cierre del flujo no se sienta cortado.</p>
        </aside>
    </div>
</form>
<?php include_once '../../includes/footer.php'; ?>
</body>
</html>