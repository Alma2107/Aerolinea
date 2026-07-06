<?php
require_once '../../config/conexion.php';
session_start();

$pageStyles = ['../../css/proceso_compra/servicios.css'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['equipajes_elegidos'] = array_map('intval', $_POST['equipajes'] ?? []);
}

$cantidadPasajeros = max(1, (int)($_SESSION['pasajeros'] ?? 1));
$idsVuelos = $_SESSION['id_vuelo'] ?? [];
$idsVuelos = is_array($idsVuelos) ? array_map('intval', $idsVuelos) : [(int)$idsVuelos];
$idsVuelos = array_values(array_filter($idsVuelos));
$idPlan = (int)($_SESSION['id_plan'] ?? 0);

$vuelosResumen = [];
$totalBase = 0.00;

if (!empty($idsVuelos)) {
    $placeholders = implode(',', array_fill(0, count($idsVuelos), '?'));
    $stmtVuelos = $pdo->prepare("SELECT id_vuelo, numero_vuelo, precio_base_vuelo FROM vuelos WHERE id_vuelo IN ($placeholders)");
    $stmtVuelos->execute($idsVuelos);
    $vuelosResumen = $stmtVuelos->fetchAll(PDO::FETCH_ASSOC);

    foreach ($vuelosResumen as $vueloResumen) {
        $totalBase += (float)$vueloResumen['precio_base_vuelo'] * $cantidadPasajeros;
    }
}

if ($idPlan > 0) {
    $stmtPlan = $pdo->prepare("SELECT cargo_extra_plan FROM planes_tarifas WHERE id_plan = ?");
    $stmtPlan->execute([$idPlan]);
    $totalBase += (float)($stmtPlan->fetchColumn() ?: 0) * $cantidadPasajeros;
}

$equipajes = $pdo->query("SELECT id_tipo_equipaje, nombre_tipo, precio_unitario FROM tipos_equipaje")->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
$servicios = $pdo->query("SELECT id_servicio, nombre_servicio, descripcion, precio_servicio FROM servicios_adicionales")->fetchAll(PDO::FETCH_ASSOC);

$subtotalEquipajes = 0.00;
foreach ($_SESSION['equipajes_elegidos'] ?? [] as $idEquipaje) {
    $precio = (float)($equipajes[$idEquipaje]['precio_unitario'] ?? 0);
    if (!empty($_SESSION['codigo_promo']) && $_SESSION['codigo_promo'] === 'EQUIPAJEGRATIS') {
        $precio = 0;
    }
    $subtotalEquipajes += $precio;
}

$totalConEquipaje = $totalBase + $subtotalEquipajes;

include_once '../../includes/header.php';
?>

<div class="contenedor-servicios">
    <form action="asientos.php" method="POST" id="form-servicios">
        <div class="card">
            <p class="eyebrow">Paso 3</p>
            <h2>Servicios adicionales</h2>
            <p class="subtitulo-card">Suma extras utiles para el viaje. Si no necesitas ninguno, podes continuar sin seleccionarlos.</p>
        </div>

        <?php for ($i = 1; $i <= $cantidadPasajeros; $i++): ?>
            <div class="card">
                <h3>Pasajero #<?= $i ?></h3>
                <p class="subtitulo-card">Extras opcionales para este pasajero.</p>

                <div class="grid-servicios">
                    <?php foreach ($servicios as $servicio): ?>
                        <div class="item-servicio">
                            <label class="label-servicio">
                                <input
                                    type="checkbox"
                                    name="servicios_elegidos[<?= $i ?>][]"
                                    value="<?= (int)$servicio['id_servicio'] ?>"
                                    data-precio="<?= (float)$servicio['precio_servicio'] ?>"
                                    data-nombre="P#<?= $i ?> - <?= htmlspecialchars($servicio['nombre_servicio'], ENT_QUOTES, 'UTF-8') ?>"
                                    class="check-servicio"
                                >
                                <div>
                                    <strong><?= htmlspecialchars($servicio['nombre_servicio'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    (+$<?= number_format((float)$servicio['precio_servicio'], 2, ',', '.') ?>)
                                    <p><?= htmlspecialchars($servicio['descripcion'], ENT_QUOTES, 'UTF-8') ?></p>
                                </div>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endfor; ?>

        <button type="submit" class="btn-continuar">Continuar a Elegir Asientos</button>
    </form>

    <div class="sidebar-resumen">
        <h3>Resumen de tu Viaje</h3>
        <p><strong>Vuelos:</strong> <?= htmlspecialchars(implode(', ', array_column($vuelosResumen, 'numero_vuelo')) ?: 'No asignado', ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Pasajeros:</strong> x<?= $cantidadPasajeros ?></p>

        <?php if (!empty($_SESSION['equipajes_elegidos'])): ?>
            <p class="titulo-seccion-resumen">Equipaje seleccionado:</p>
            <ul class="lista-resumen lista-equipaje-resumen">
                <?php foreach ($_SESSION['equipajes_elegidos'] as $idEquipaje): ?>
                    <?php if (!isset($equipajes[$idEquipaje])) continue; ?>
                    <li>
                        <?= htmlspecialchars($equipajes[$idEquipaje]['nombre_tipo'], ENT_QUOTES, 'UTF-8') ?>
                        +$<?= number_format((float)$equipajes[$idEquipaje]['precio_unitario'], 2, ',', '.') ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div id="contenedor-servicios-dinamico" class="bloque-dinamico">
            <p class="titulo-seccion-resumen">Servicios de a bordo:</p>
            <ul id="lista-servicios-vista" class="lista-resumen"></ul>
        </div>

        <hr class="separador">
        <h4 class="total-contenedor">
            <span>Total estimado:</span>
            <span class="total-precio" id="total-vista" data-base="<?= $totalConEquipaje ?>">$<?= number_format($totalConEquipaje, 2, ',', '.') ?></span>
        </h4>
    </div>
</div>

<script src="../../js/proceso_compra/servicios.js"></script>
<?php include_once '../../includes/footer.php'; ?>
</body>
</html>
