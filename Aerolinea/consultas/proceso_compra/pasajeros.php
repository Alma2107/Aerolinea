<?php
session_start();
$pageStyles = ['../../css/proceso_compra/pasajeros.css'];
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['id_asiento'] = $_POST['id_asiento'];
}
$cantidad = $_SESSION['pasajeros'] ?? 1;
include_once '../../includes/header.php';
?>
<form action="pago.php" method="POST">
    <div class="contenedor-pasajeros">
        <div class="main-content">
            <div class="card">
                <p class="eyebrow">Paso 4</p>
                <h2 class="titulo-seccion">Información de los pasajeros</h2>
                <p class="section-copy">Cada pasajero en su propia tarjeta, con los datos clave bien separados para completar más rápido.</p>
            </div>

            <?php for($i = 1; $i <= $cantidad; $i++): ?>
                <div class="card">
                    <h3>Pasajero #<?=$i?></h3>
                    <div class="grid-inputs">
                        <input type="text" name="pasajero[<?=$i?>][nombre]" placeholder="Nombre" required>
                        <input type="text" name="pasajero[<?=$i?>][apellido]" placeholder="Apellido" required>
                        <input type="text" name="pasajero[<?=$i?>][doc]" placeholder="Documento" required>
                        <input type="date" name="pasajero[<?=$i?>][fnac]" required>
                    </div>
                    <div class="bloque-asistencia">
                        <label><input type="checkbox" name="pasajero[<?=$i?>][asistencia]" value="1"> Requiere asistencia especial</label>
                    </div>
                </div>
            <?php endfor; ?>

            <button type="submit" class="btn-pago">Ir al Pago</button>
        </div>

        <aside class="sidebar-resumen">
            <h3>Tu grupo de viaje</h3>
            <p><strong>Pasajeros:</strong> x<?=$cantidad?></p>
            <hr class="separador">
            <p>El formato por tarjetas ayuda a leer mejor cada bloque y reduce el ruido visual de los campos.</p>
        </aside>
    </div>
</form>
<?php include_once '../../includes/footer.php'; ?>
</body>
</html>