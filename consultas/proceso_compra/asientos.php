<?php
require_once '../../config/conexion.php';
require_once 'flujo_helpers.php';
session_start();

$pageStyles = ['../../css/proceso_compra/asientos.css'];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['servicios_elegidos'] = [];
    foreach ($_POST['servicios_elegidos'] ?? [] as $pasajero => $serviciosPasajero) {
        $_SESSION['servicios_elegidos'][(int)$pasajero] = array_map('intval', (array)$serviciosPasajero);
    }
    $_SESSION['id_asiento'] = $_POST['id_asiento'] ?? null;
}

$idsVuelos = normalizarIdsVuelos($_SESSION['id_vuelo'] ?? []);
$idVueloReferencia = $idsVuelos[0] ?? 0;

$columnasAsientos = $pdo->query("SHOW COLUMNS FROM asientos_avion")->fetchAll(PDO::FETCH_COLUMN);
$asientosTienenAvion = in_array('id_avion', $columnasAsientos, true);
$idAvion = 0;
if ($asientosTienenAvion && $idVueloReferencia > 0) {
    $stmtAvion = $pdo->prepare("SELECT id_avion FROM vuelos WHERE id_vuelo = ? LIMIT 1");
    $stmtAvion->execute([$idVueloReferencia]);
    $idAvion = (int)($stmtAvion->fetchColumn() ?: 0);
}

if ($asientosTienenAvion && $idAvion > 0) {
    $stmtAsientos = $pdo->prepare("SELECT id_asiento_avion, numero_asiento, categoria, cargo_extra FROM asientos_avion WHERE id_avion = ? ORDER BY id_asiento_avion ASC");
    $stmtAsientos->execute([$idAvion]);
    $asientos = $stmtAsientos->fetchAll();
} else {
    $asientos = $pdo->query("SELECT id_asiento_avion, numero_asiento, categoria, cargo_extra FROM asientos_avion ORDER BY id_asiento_avion ASC LIMIT 120")->fetchAll();
}

$asientosOcupados = [];
if (!empty($idsVuelos)) {
    $placeholders = implode(',', array_fill(0, count($idsVuelos), '?'));
    $stmtOcupados = $pdo->prepare("SELECT DISTINCT numero_asiento FROM tickets_detalle WHERE id_vuelo IN ($placeholders) AND numero_asiento IS NOT NULL");
    $stmtOcupados->execute($idsVuelos);
    $asientosOcupados = array_flip($stmtOcupados->fetchAll(PDO::FETCH_COLUMN));
}
include_once '../../includes/header.php';
?>
<form action="pasajeros.php" method="POST">
    <div class="contenedor-asientos">
        <div class="main-content">
            <div class="card">
                <p class="eyebrow">Paso 4</p>
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
                                $ocupado = isset($asientosOcupados[$as['numero_asiento']]);
                            ?>
                            <label class="asiento-box <?= $ocupado ? 'asiento-ocupado' : 'asiento-disponible' ?> asiento-cat-<?=$categoria?>">
                                <input type="radio" name="id_asiento" value="<?=$as['id_asiento_avion']?>" required style="display:none;" <?= $ocupado ? 'disabled' : '' ?>>
                                <strong><?=$as['numero_asiento']?></strong>
                                <span class="precio-tag"><?= $ocupado ? 'Ocupado' : '+$' . number_format($cargo, 2) ?></span>
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
            if (this.classList.contains('asiento-ocupado')) return;
            document.querySelectorAll('.asiento-box').forEach(b => b.classList.remove('selected'));
            this.classList.add('selected');
            const selected = this.querySelector('strong')?.textContent || 'seleccionado';
            const target = document.querySelector('.sidebar-resumen .destacado');
            if (target) target.textContent = selected;
        });
    });
</script>
<?php include_once '../../includes/footer.php'; ?>
</body>
</html>
