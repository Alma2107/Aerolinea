<?php
require_once '../../config/conexion.php';
session_start();

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['servicios_elegidos'] = $_POST['servicios'] ?? [];
}

$asientos = $pdo->query("SELECT id_asiento_avion, numero_asiento FROM asientos_avion")->fetchAll();
include_once '../../includes/header.php';
?>
<form action="pasajeros.php" method="POST">
    <div class="booking-layout">
        <div class="main-content">
            <div class="card">
                <h2>Elegí tu asiento</h2>
                <div class="mapa-asientos">
                    <?php foreach($asientos as $as): ?>
                        <label class="asiento">
                            <input type="radio" name="id_asiento" value="<?=$as['id_asiento_avion']?>" required style="display:none;">
                            <?=$as['numero_asiento']?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="submit" class="btn-next">Datos de Pasajeros</button>
        </div>
    </div>
</form>
<script>
    document.querySelectorAll('.asiento').forEach(box => {
        box.addEventListener('click', function() {
            document.querySelectorAll('.asiento').forEach(b => b.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
</script>
</body>
</html>