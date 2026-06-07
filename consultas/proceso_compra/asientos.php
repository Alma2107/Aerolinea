<?php
require_once '../../config/conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['servicios'] = $_POST['servicios'] ?? [];
}

$asientos = $pdo->query("SELECT id_asiento_avion, numero_asiento, categoria, cargo_extra FROM asientos_avion")->fetchAll(PDO::FETCH_ASSOC);
include_once '../../includes/header.php';
?>
<form action="pasajeros.php" method="POST">
    <div class="booking-layout">
        <div class="main-content">
            <div class="card">
                <h2>Elegí tu asiento</h2>
                <p style="font-size: 12px; color: #666; margin-bottom: 15px;">Seleccioná tu ubicación preferida en la aeronave.</p>
                <div class="mapa-asientos" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;">
                    <?php foreach($asientos as $as): ?>
                        <label class="asiento" style="border: 1px solid #ccc; padding: 10px; text-align: center; display: block; cursor: pointer; border-radius:4px;">
                            <input type="radio" name="id_asiento" value="<?=$as['id_asiento_avion']?>" required style="display:none;">
                            <strong><?=$as['numero_asiento']?></strong><br>
                            <span style="font-size: 10px; color: #888;"><?=$as['categoria']?></span><br>
                            <span style="font-size: 11px; color: green;">+$<?=$as['cargo_extra']?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="submit" class="btn-next" style="margin-top: 20px;">Datos de Pasajeros</button>
        </div>
    </div>
</form>

<script>
    document.querySelectorAll('.asiento').forEach(box => {
        box.addEventListener('click', function() {
            document.querySelectorAll('.asiento').forEach(b => {
                b.style.backgroundColor = '#fff';
                b.style.color = '#000';
            });
            this.style.backgroundColor = '#0056b3';
            this.style.color = '#fff';
        });
    });
</script>
</body>
</html>