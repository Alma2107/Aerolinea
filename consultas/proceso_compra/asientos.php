<?php
require_once '../../config/conexion.php';
session_start();

$pageStyles = ['../../css/proceso_compra/asientos.css'];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['equipajes_elegidos'] = $_POST['servicios'] ?? [];
    $_SESSION['id_asiento'] = $_POST['id_asiento'] ?? null;
}

$asientos = $pdo->query("SELECT id_asiento_avion, numero_asiento, categoria, cargo_extra FROM asientos_avion")->fetchAll();
include_once '../../includes/header.php';
?>
<form action="pasajeros.php" method="POST">
    <div class="contenedor-asientos">
        <div class="main-content">
            <div class="card">
                <p class="eyebrow">Paso 3</p>
                <h2>Elegi tu asiento</h2>
                <p class="info-pasajeros">Selecciona una ubicacion que acompañe mejor tu viaje: adelante, emergencia o estandar.</p>
                <div class="leyenda-cabina">
                    <span class="leyenda-item"><span class="leyenda-color color-front"></span>Frente / premium</span>
                    <span class="leyenda-item"><span class="leyenda-color color-preferente"></span>Preferente</span>
                    <span class="leyenda-item"><span class="leyenda-color color-estandar"></span>Estandar</span>
                    <span class="leyenda-item"><span class="leyenda-color color-ocupado"></span>No disponible</span>
                </div>
                <div class="cabina-scroll">
                    <div class="cabina-techo">Cabina principal</div>
                    <div class="mapa-asientos">
                        <?php foreach($asientos as $as): ?>
                            <?php
                                $cargo = (float)$as['cargo_extra'];
                                $categoria = $cargo >= 12000 ? 'front' : ($cargo > 0 ? 'preferente' : 'estandar');
                            ?>
                            <label class="asiento-box asiento-disponible asiento-cat-<?=$categoria?>">
                                <input type="radio" name="id_asiento" value="<?=$as['id_asiento_avion']?>" required style="display:none;">
                                <strong><?=$as['numero_asiento']?></strong>
                                <span class="precio-tag">+$<?=number_format($cargo, 2)?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn-continuar">Datos de Pasajeros</button>
        </div>

        <aside class="sidebar-resumen">
            <h3>Resumen de cabina</h3>
            <p>Asiento seleccionado: <span class="destacado">pendiente</span></p>
            <hr class="separador">
        </aside>
    </div>
</form>
<script>
    document.querySelectorAll('.asiento-box').forEach(box => {
        box.addEventListener('click', function() {
            document.querySelectorAll('.asiento-box').forEach(b => b.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
</script>
<?php include_once '../../includes/footer.php'; ?>
</body>
</html>
