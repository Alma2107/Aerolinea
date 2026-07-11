<?php
require_once '../../config/conexion.php';
require_once 'flujo_helpers.php';
session_start();

$pageStyles = ['../../css/proceso_compra/servicios.css'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['servicios_elegidos'] = [];
    foreach ($_POST['servicios_elegidos'] ?? [] as $pasajero => $serviciosPasajero) {
        $_SESSION['servicios_elegidos'][(int)$pasajero] = array_map('intval', (array)$serviciosPasajero);
    }
}

$cantidadPasajeros = max(1, (int)($_SESSION['pasajeros'] ?? 1));
$idsVuelos = normalizarIdsVuelos($_SESSION['id_vuelo'] ?? []);
$idPlan = (int)($_SESSION['id_plan'] ?? 0);
$codigoPromo = strtoupper(trim($_SESSION['codigo_promo'] ?? ''));

$vuelosResumen = [];
$totalBase = 0.00;

if (!empty($idsVuelos)) {
    $placeholders = implode(',', array_fill(0, count($idsVuelos), '?'));
    $stmtVuelos = $pdo->prepare("SELECT id_vuelo, numero_vuelo, precio_base_vuelo, destino_iata FROM vuelos WHERE id_vuelo IN ($placeholders)");
    $stmtVuelos->execute($idsVuelos);
    $vuelosResumen = $stmtVuelos->fetchAll(PDO::FETCH_ASSOC);

    foreach ($vuelosResumen as $vueloResumen) {
        $totalBase += calcularPrecioTotalVuelo((float)$vueloResumen['precio_base_vuelo'], $cantidadPasajeros, $codigoPromo, (string)$vueloResumen['destino_iata']);
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
$equipajesElegidos = $_SESSION['equipajes_elegidos'] ?? [];
if (!is_array($equipajesElegidos)) {
    $equipajesElegidos = [];
}
foreach ($equipajesElegidos as $pasajeroEquipajes) {
    foreach ((array)$pasajeroEquipajes as $idEquipaje) {
        $idEquipaje = (int)$idEquipaje;
        $precio = (float)($equipajes[$idEquipaje]['precio_unitario'] ?? 0);
        if ($codigoPromo === 'EQUIPAJEGRATIS') {
            $precio = 0;
        }
        $subtotalEquipajes += $precio;
    }
}

$totalConEquipaje = $totalBase + $subtotalEquipajes;
$serviciosElegidos = $_SESSION['servicios_elegidos'] ?? [];

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
                        <?php $serviciosPasajero = $serviciosElegidos[$i] ?? []; ?>
                        <div class="item-servicio">
                            <label class="label-servicio">
                                <input
                                    type="checkbox"
                                    name="servicios_elegidos[<?= $i ?>][]"
                                    value="<?= (int)$servicio['id_servicio'] ?>"
                                    data-precio="<?= (float)$servicio['precio_servicio'] ?>"
                                    data-nombre="P#<?= $i ?> - <?= htmlspecialchars($servicio['nombre_servicio'], ENT_QUOTES, 'UTF-8') ?>"
                                    class="check-servicio"
                                    <?= in_array((int)$servicio['id_servicio'], $serviciosPasajero, true) ? 'checked' : '' ?>
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

        <?php if (!empty($equipajesElegidos)): ?>
            <p class="titulo-seccion-resumen">Equipaje seleccionado:</p>
            <ul class="lista-resumen lista-equipaje-resumen">
                <?php foreach ($equipajesElegidos as $pasajero => $idsEquipaje): ?>
                    <?php $nombres = []; foreach ((array)$idsEquipaje as $idEquipaje) { if (isset($equipajes[$idEquipaje])) { $nombres[] = $equipajes[$idEquipaje]['nombre_tipo']; } } ?>
                    <?php if (empty($nombres)) continue; ?>
                    <li>
                        <strong>P#<?= (int)$pasajero ?>:</strong> <?= htmlspecialchars(implode(', ', $nombres), ENT_QUOTES, 'UTF-8') ?>
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
