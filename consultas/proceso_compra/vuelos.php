<?php
require_once '../../config/conexion.php';
session_start();

$pageStyles = ['../../css/proceso_compra/vuelos.css'];

$origen = strtoupper(trim($_GET['origen'] ?? ''));
$destino = strtoupper(trim($_GET['destino'] ?? ''));
$fechaIda = trim($_GET['fecha_ida'] ?? '');
$pasajeros = max(1, (int)($_GET['pasajeros'] ?? 1));
$codigoPromo = strtoupper(trim($_GET['codigo_promo'] ?? $_GET['promo'] ?? ''));

$promos = [
    'BARILO20' => [
        'titulo' => '20% OFF en Bariloche',
        'descripcion' => 'Descuento aplicado a vuelos con destino Bariloche.',
        'destino' => 'BRC',
        'descuento' => 0.20,
    ],
    'EQUIPAJEGRATIS' => [
        'titulo' => 'Equipaje gratis',
        'descripcion' => 'El beneficio se aplicara en el paso de equipaje.',
        'destino' => '',
        'descuento' => 0,
    ],
    'CORDOBA2X1' => [
        'titulo' => '2x1 a Cordoba',
        'descripcion' => 'Promocion preparada para dos pasajeros hacia Cordoba.',
        'destino' => 'COR',
        'descuento' => 0.50,
    ],
];

$promoActiva = $promos[$codigoPromo] ?? null;
if ($promoActiva) {
    $_SESSION['promo_activa'] = $codigoPromo;
    $_SESSION['promo_equipaje_gratis'] = $codigoPromo === 'EQUIPAJEGRATIS';
    if (!empty($promoActiva['destino'])) {
        $destino = $promoActiva['destino'];
    }
    if ($codigoPromo === 'CORDOBA2X1') {
        $pasajeros = max(2, $pasajeros);
    }
}

$_SESSION['pasajeros'] = $pasajeros;
$_SESSION['codigo_promo'] = $codigoPromo;

$sql = "
    SELECT v.id_vuelo, v.numero_vuelo, v.fecha_salida, v.fecha_llegada, v.precio_base_vuelo, v.estado_vuelo,
           origen.ciudad AS origen_ciudad, origen.codigo_iata AS origen_iata,
           destino.ciudad AS destino_ciudad, destino.codigo_iata AS destino_iata
    FROM vuelos v
    INNER JOIN aeropuertos origen ON origen.codigo_iata = v.origen_iata
    INNER JOIN aeropuertos destino ON destino.codigo_iata = v.destino_iata
    WHERE 1 = 1
";
$params = [];

if ($origen !== '') {
    $sql .= " AND v.origen_iata = :origen";
    $params['origen'] = $origen;
}
if ($destino !== '') {
    $sql .= " AND v.destino_iata = :destino";
    $params['destino'] = $destino;
}
if ($fechaIda !== '') {
    $sql .= " AND DATE(v.fecha_salida) >= :fecha_ida";
    $params['fecha_ida'] = $fechaIda;
}
$sql .= " ORDER BY v.fecha_salida ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vuelos = $stmt->fetchAll(PDO::FETCH_ASSOC);
$planes = $pdo->query("SELECT id_plan, nombre_plan, descripcion FROM planes_tarifas")->fetchAll();
$primerVuelo = $vuelos[0] ?? null;
$primerPlan = $planes[0] ?? null;

function estadoClaseVuelo(string $estado): string {
    $estado = strtolower($estado);
    if (str_contains($estado, 'horario')) return 'status-on-time';
    if (str_contains($estado, 'demorado')) return 'status-delayed';
    if (str_contains($estado, 'cancelado')) return 'status-cancelled';
    return 'status-scheduled';
}

include_once '../../includes/header.php';
?>
<section class="contenedor-vuelos">
    <div class="main-content">
        <div class="card hero-step">
            <p class="eyebrow">Paso 1</p>
            <h2>Elegi horario y plan de viaje</h2>
            <p>Comparamos solo los vuelos que coinciden con tu busqueda. Si entraste desde una promo, el beneficio queda aplicado durante la compra.</p>
        </div>

        <?php if ($promoActiva): ?>
            <div class="promo-applied-card">
                <span class="promo-code"><?= htmlspecialchars($codigoPromo, ENT_QUOTES, 'UTF-8') ?></span>
                <div>
                    <strong><?= htmlspecialchars($promoActiva['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <p><?= htmlspecialchars($promoActiva['descripcion'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
        <?php endif; ?>

        <form action="equipaje.php" method="POST">
            <input type="hidden" name="codigo_promo" value="<?= htmlspecialchars($codigoPromo, ENT_QUOTES, 'UTF-8') ?>">
            <div class="card">
                <h2>Horarios disponibles</h2>
                <?php if (empty($vuelos)): ?>
                    <div class="empty-state">
                        <h3>No encontramos vuelos para esos filtros</h3>
                        <p>Proba con otro origen, destino o fecha.</p>
                        <br>
                        <a href="../../index.php#buscador" class="btn-continuar empty-link">Volver al buscador</a>
                    </div>
                <?php else: ?>
                    <div class="grid-vuelos">
                        <?php foreach($vuelos as $v): ?>
                            <?php
                                $precioBase = (float)$v['precio_base_vuelo'];
                                $precioFinal = $promoActiva ? $precioBase * (1 - (float)$promoActiva['descuento']) : $precioBase;
                            ?>
                            <label class="opcion-vuelo">
                                <input type="radio" name="id_vuelo" value="<?= (int)$v['id_vuelo'] ?>" required <?= ($primerVuelo && $v['id_vuelo'] === $primerVuelo['id_vuelo']) ? 'checked' : '' ?>>
                                <span class="badge-vuelo">Vuelo <?= htmlspecialchars($v['numero_vuelo'], ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="status-pill <?= estadoClaseVuelo($v['estado_vuelo']) ?>"><?= htmlspecialchars($v['estado_vuelo'], ENT_QUOTES, 'UTF-8') ?></span>
                                <strong><?= htmlspecialchars($v['origen_ciudad'] . ' -> ' . $v['destino_ciudad'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <p>Salida: <?= date('d/m/Y H:i', strtotime($v['fecha_salida'])) ?></p>
                                <p>Llegada: <?= date('d/m/Y H:i', strtotime($v['fecha_llegada'])) ?></p>
                                <?php if ($promoActiva && $promoActiva['descuento'] > 0): ?>
                                    <span class="precio-vuelo precio-anterior">Antes $<?= number_format($precioBase, 2, ',', '.') ?></span>
                                <?php endif; ?>
                                <span class="precio-vuelo">Final: $<?= number_format($precioFinal, 2, ',', '.') ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2>Plan de viaje</h2>
                <div class="grid-planes">
                    <?php foreach($planes as $p): ?>
                        <label class="opcion-plan">
                            <input type="radio" name="id_plan" value="<?= (int)$p['id_plan'] ?>" required <?= ($primerPlan && $p['id_plan'] === $primerPlan['id_plan']) ? 'checked' : '' ?>>
                            <strong><?= htmlspecialchars($p['nombre_plan'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <p><?= htmlspecialchars($p['descripcion'], ENT_QUOTES, 'UTF-8') ?></p>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="btn-continuar" <?= empty($vuelos) ? 'disabled' : '' ?>>Continuar al Equipaje</button>
        </form>
    </div>

    <aside class="sidebar-resumen">
        <h3>Tu recorrido</h3>
        <p><strong>Origen:</strong> <?= htmlspecialchars($origen ?: 'Todos', ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Destino:</strong> <?= htmlspecialchars($destino ?: 'Todos', ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Pasajeros:</strong> <?= (int)$pasajeros ?></p>
        <?php if ($codigoPromo): ?><p><strong>Promo:</strong> <?= htmlspecialchars($codigoPromo, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
        <hr class="separador">
       
    </aside>
</section>
<?php include_once '../../includes/footer.php'; ?>
</body>
</html>
