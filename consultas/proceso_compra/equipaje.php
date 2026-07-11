<?php
require_once '../../config/conexion.php';
require_once 'flujo_helpers.php';
session_start();

$pageStyles = ['../../css/proceso_compra/equipaje.css'];

$equipajes = $pdo->query("SELECT id_tipo_equipaje, nombre_tipo, descripcion, precio_unitario FROM tipos_equipaje")->fetchAll(PDO::FETCH_ASSOC);
$promoEquipaje = !empty($_SESSION['promo_equipaje_gratis']);
$cantidadPasajeros = max(1, (int)($_SESSION['pasajeros'] ?? 1));
$errorEquipaje = '';
$seleccionesPorPasajero = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['id_vuelo'] = normalizarIdsVuelos($_POST['id_vuelo'] ?? $_SESSION['id_vuelo'] ?? []);
    $_SESSION['id_plan'] = $_POST['id_plan'] ?? $_SESSION['id_plan'] ?? null;
    $_SESSION['codigo_promo'] = strtoupper(trim($_POST['codigo_promo'] ?? ($_SESSION['codigo_promo'] ?? '')));
    $_SESSION['promo_equipaje_gratis'] = $_SESSION['codigo_promo'] === 'EQUIPAJEGRATIS';
    $_SESSION['reporte_equipaje_prohibido'] = trim($_POST['reporte_equipaje_prohibido'] ?? ($_SESSION['reporte_equipaje_prohibido'] ?? ''));
    $_SESSION['fecha_perdida_equipaje'] = trim($_POST['fecha_perdida_equipaje'] ?? ($_SESSION['fecha_perdida_equipaje'] ?? ''));
    $_SESSION['lugar_perdida_equipaje'] = trim($_POST['lugar_perdida_equipaje'] ?? ($_SESSION['lugar_perdida_equipaje'] ?? ''));

    for ($i = 1; $i <= $cantidadPasajeros; $i++) {
        $ids = array_map('intval', (array)($_POST['equipajes'][$i] ?? []));
        $ids = array_values(array_unique(array_filter($ids)));
        $seleccionesPorPasajero[$i] = $ids;
    }

    $tiposEquipajePorId = [];
    foreach ($equipajes as $eq) {
        $tiposEquipajePorId[(int)$eq['id_tipo_equipaje']] = $eq;
    }

    foreach ($seleccionesPorPasajero as $pasajero => $idsEquipaje) {
        $resultado = validarEquipaje($idsEquipaje, $tiposEquipajePorId, $cantidadPasajeros);
        if (!$resultado['ok']) {
            $errorEquipaje = $resultado['mensaje'];
            break;
        }
    }

    if ($errorEquipaje === '') {
        $_SESSION['equipajes_elegidos'] = $seleccionesPorPasajero;
        header('Location: servicios.php');
        exit();
    }
}

if (empty($seleccionesPorPasajero)) {
    $seleccionesPorPasajero = $_SESSION['equipajes_elegidos'] ?? [];
    if (!is_array($seleccionesPorPasajero)) {
        $seleccionesPorPasajero = [];
    }
}

include_once '../../includes/header.php';
?>
<form action="equipaje.php" method="POST">
    <div class="contenedor-equipaje">
        <div class="main-content">
            <div class="card hero-equipaje">
                <p class="eyebrow">Paso 2</p>
                <h2>Elegi el equipaje que realmente necesitas</h2>
                <p class="text-inclusion">Mochila personal incluida: $0. Suma el equipaje extra segun tu tipo de viaje.</p>
            </div>

            <?php if ($promoEquipaje): ?>
                <div class="promo-applied-card">
                    <span class="promo-code">EQUIPAJEGRATIS</span>
                    <div>
                        <strong>Beneficio aplicado</strong>
                        <p>Te dejamos marcada una valija de bodega estandar sin cargo promocional.</p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($errorEquipaje !== ''): ?>
                <div class="promo-applied-card" style="border-color:#b91c1c; background:#fff7ed;">
                    <span class="promo-code">Atención</span>
                    <div>
                        <strong>El equipaje supera el límite permitido</strong>
                        <p><?= htmlspecialchars($errorEquipaje, ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php for ($pasajero = 1; $pasajero <= $cantidadPasajeros; $pasajero++): ?>
                <div class="card">
                    <h3>Pasajero #<?= (int)$pasajero ?></h3>
                    <div class="grid-equipajes">
                        <?php foreach($equipajes as $index => $eq): ?>
                            <?php
                                $iconos = ['MO', 'EM', 'BC', 'BE', 'BP', 'ES'];
                                $variantes = ['mochila', 'mano', 'bodega-chica', 'bodega-standar', 'bodega-pesada', 'especial'];
                                $icono = $iconos[$index] ?? 'EQ';
                                $variante = $variantes[$index] ?? 'standard';
                                $gratis = $promoEquipaje && stripos($eq['nombre_tipo'], 'Bodega') !== false && stripos($eq['nombre_tipo'], '23kg') !== false;
                                $seleccionesPasajero = $seleccionesPorPasajero[$pasajero] ?? [];
                                $seleccionado = in_array((int)$eq['id_tipo_equipaje'], $seleccionesPasajero, true) || ($gratis && empty($seleccionesPasajero));
                            ?>
                            <label class="equipaje-card equipaje-<?= $variante ?> <?= $gratis ? 'promo-free-bag' : '' ?>">
                                <input type="checkbox" name="equipajes[<?= (int)$pasajero ?>][]" value="<?= (int)$eq['id_tipo_equipaje'] ?>" <?= $seleccionado ? 'checked' : '' ?>>
                                <div class="equipaje-thumb"><?= htmlspecialchars($icono, ENT_QUOTES, 'UTF-8') ?></div>
                                <h4><?= htmlspecialchars($eq['nombre_tipo'], ENT_QUOTES, 'UTF-8') ?></h4>
                                <p class="descripcion"><?= htmlspecialchars($eq['descripcion'], ENT_QUOTES, 'UTF-8') ?></p>
                                <?php if ($gratis): ?>
                                    <p class="precio precio-promo">Gratis por promocion</p>
                                <?php else: ?>
                                    <p class="precio">+$<?= number_format($eq['precio_unitario'], 2, ',', '.') ?></p>
                                <?php endif; ?>
                                <span class="equipaje-action"><?= $gratis ? 'Aplicado' : 'Agregar' ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endfor; ?>

            <div class="card">
                <h3>Reporte de equipaje prohibido o perdido</h3>
                <p class="text-inclusion">Si tuviste algún artículo prohibido, extraviado o perdido en el trayecto, detallá dónde y cuándo ocurrió.</p>
                <div class="reporte-equipaje-grid">
                    <label>
                        <span>Fecha de pérdida</span>
                        <input type="date" name="fecha_perdida_equipaje" value="<?= htmlspecialchars($_SESSION['fecha_perdida_equipaje'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </label>
                    <label>
                        <span>Lugar donde ocurrió</span>
                        <input type="text" name="lugar_perdida_equipaje" placeholder="Ej: Sala de embarque AEP" value="<?= htmlspecialchars($_SESSION['lugar_perdida_equipaje'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </label>
                </div>
                <label class="reporte-equipaje-texto">
                    <span>Detalle del incidente</span>
                    <textarea name="reporte_equipaje_prohibido" rows="4" placeholder="Describe el objeto, el estado, si estaba prohibido o si se perdió durante el vuelo y cualquier dato adicional."><?= htmlspecialchars($_SESSION['reporte_equipaje_prohibido'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </label>
            </div>

            <button type="submit" class="btn-continuar">Continuar a Servicios</button>
        </div>

        <aside class="sidebar-resumen">
            <h3>Tu reserva</h3>
            <p><strong>Vuelo:</strong> <?= htmlspecialchars(is_array($_SESSION['id_vuelo'] ?? null) ? implode(', ', $_SESSION['id_vuelo']) : ($_SESSION['id_vuelo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Plan:</strong> <?= htmlspecialchars($_SESSION['id_plan'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            <?php if (!empty($_SESSION['codigo_promo'])): ?><p><strong>Promo:</strong> <?= htmlspecialchars($_SESSION['codigo_promo'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
            <?php if (!empty($_SESSION['reporte_equipaje_prohibido'])): ?><p><strong>Reporte:</strong> <?= htmlspecialchars($_SESSION['reporte_equipaje_prohibido'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
            <hr class="separador">
        </aside>
    </div>
</form>
<?php include_once '../../includes/footer.php'; ?>
</body>
</html>
