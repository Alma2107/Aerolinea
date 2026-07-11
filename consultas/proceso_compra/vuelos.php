<?php
require_once '../../config/conexion.php';
require_once 'flujo_helpers.php';
session_start();

$pageStyles = ['../../css/proceso_compra/vuelos.css'];

$tipoViaje = $_GET['tipo_viaje'] ?? 'ida_vuelta';
$origen = strtoupper(trim($_GET['origen'] ?? ''));
$destino = strtoupper(trim($_GET['destino'] ?? ''));
$fechaIda = trim($_GET['fecha_ida'] ?? '');
$fechaVuelta = trim($_GET['fecha_vuelta'] ?? '');
$pasajeros = max(1, (int)($_GET['pasajeros'] ?? 1));
$codigoPromo = strtoupper(trim($_GET['codigo_promo'] ?? $_GET['promo'] ?? ''));
$segmentosBusqueda = [];

if ($tipoViaje === 'solo_ida') {
    $origen = strtoupper(trim($_GET['origen_solo'] ?? $origen));
    $destino = strtoupper(trim($_GET['destino_solo'] ?? $destino));
    $fechaIda = trim($_GET['fecha_ida_solo'] ?? $fechaIda);
    $pasajeros = max(1, (int)($_GET['pasajeros_solo'] ?? $pasajeros));
} elseif ($tipoViaje === 'multitramos') {
    $segmentosBusqueda[] = [
        'origen' => strtoupper(trim($_GET['origen_multi_1'] ?? '')),
        'destino' => strtoupper(trim($_GET['destino_multi_1'] ?? '')),
        'fecha' => trim($_GET['fecha_multi_1'] ?? ''),
    ];
    $segmentosBusqueda[] = [
        'origen' => strtoupper(trim($_GET['origen_multi_2'] ?? '')),
        'destino' => strtoupper(trim($_GET['destino_multi_2'] ?? '')),
        'fecha' => trim($_GET['fecha_multi_2'] ?? ''),
    ];
    $pasajeros = max(1, (int)($_GET['pasajeros_multi'] ?? $pasajeros));
    $origen = $segmentosBusqueda[0]['origen'] ?? '';
    $destino = $segmentosBusqueda[0]['destino'] ?? '';
    $fechaIda = $segmentosBusqueda[0]['fecha'] ?? '';
}

$multitramos = $tipoViaje === 'multitramos' || !empty($_GET['multitramos']);
$erroresBusqueda = [];
$hoy = date('Y-m-d');

function fechaBusquedaValida(string $fecha): bool {
    return $fecha === '' || (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha);
}

if (!fechaBusquedaValida($fechaIda) || !fechaBusquedaValida($fechaVuelta)) {
    $erroresBusqueda[] = 'Revisa el formato de las fechas.';
}

if ($fechaIda !== '' && $fechaIda < $hoy) {
    $erroresBusqueda[] = 'La fecha de ida no puede ser anterior a hoy.';
}

if ($tipoViaje === 'ida_vuelta') {
    if ($fechaVuelta !== '' && $fechaVuelta < $hoy) {
        $erroresBusqueda[] = 'La fecha de vuelta no puede ser anterior a hoy.';
    }
    if ($fechaIda !== '' && $fechaVuelta !== '' && $fechaVuelta < $fechaIda) {
        $erroresBusqueda[] = 'La vuelta no puede ser anterior a la ida.';
    }
}

if ($multitramos && !empty($segmentosBusqueda)) {
    foreach ($segmentosBusqueda as $segmento) {
        if (!fechaBusquedaValida($segmento['fecha'])) {
            $erroresBusqueda[] = 'Revisa el formato de las fechas de los tramos.';
        } elseif ($segmento['fecha'] !== '' && $segmento['fecha'] < $hoy) {
            $erroresBusqueda[] = 'Ningun tramo puede tener una fecha anterior a hoy.';
        }
    }
    if (($segmentosBusqueda[0]['fecha'] ?? '') !== '' && ($segmentosBusqueda[1]['fecha'] ?? '') !== '' && $segmentosBusqueda[1]['fecha'] < $segmentosBusqueda[0]['fecha']) {
        $erroresBusqueda[] = 'El segundo tramo no puede salir antes del primero.';
    }
}

$promos = obtenerPromos();
$promoActiva = $promos[strtoupper(trim($codigoPromo))] ?? null;
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

$_SESSION['tipo_viaje'] = $tipoViaje;
$_SESSION['pasajeros'] = $pasajeros;
$_SESSION['codigo_promo'] = $codigoPromo;
$_SESSION['segmentos_busqueda'] = $segmentosBusqueda;

function buscarVuelos(PDO $pdo, string $origen, string $destino, string $fechaIda): array {
    $sql = "
        SELECT v.id_vuelo, v.numero_vuelo, v.fecha_salida, v.fecha_llegada, v.precio_base_vuelo, v.estado_vuelo,
               origen.ciudad AS origen_ciudad, origen.codigo_iata AS origen_iata,
               destino.ciudad AS destino_ciudad, destino.codigo_iata AS destino_iata,
               av.capacidad,
               COALESCE(COUNT(t.id_ticket), 0) AS asientos_vendidos,
               (av.capacidad - COALESCE(COUNT(t.id_ticket), 0)) AS asientos_disponibles
        FROM vuelos v
        INNER JOIN aeropuertos origen ON origen.codigo_iata = v.origen_iata
        INNER JOIN aeropuertos destino ON destino.codigo_iata = v.destino_iata
        INNER JOIN aviones av ON av.id_avion = v.id_avion
        LEFT JOIN tickets_detalle t ON t.id_vuelo = v.id_vuelo
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
    $sql .= " GROUP BY v.id_vuelo, v.numero_vuelo, v.fecha_salida, v.fecha_llegada, v.precio_base_vuelo, v.estado_vuelo,
               origen.ciudad, origen.codigo_iata, destino.ciudad, destino.codigo_iata, av.capacidad
              ORDER BY v.fecha_salida ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (!empty($erroresBusqueda)) {
    $vuelos = [];
} elseif ($multitramos && !empty($segmentosBusqueda)) {
    $vuelos = [];
    foreach ($segmentosBusqueda as $index => $segmento) {
        if ($segmento['origen'] === '' && $segmento['destino'] === '') {
            continue;
        }
        foreach (buscarVuelos($pdo, $segmento['origen'], $segmento['destino'], $segmento['fecha']) as $vuelo) {
            $vuelo['tramo_busqueda'] = $index + 1;
            $vuelos[] = $vuelo;
        }
    }
} else {
    $vuelos = buscarVuelos($pdo, $origen, $destino, $fechaIda);
    if (empty($vuelos) && $destino !== '') {
        $vuelos = buscarVuelos($pdo, '', $destino, $fechaIda);
    }
}

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
        <div class="card hero-step" id="vuelos-disponibles">
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
                <label class="multitramos-toggle">
                    <input type="checkbox" name="multitramos" value="1" <?= $multitramos ? 'checked' : '' ?>>
                    <span>Seleccionar varios tramos (multitramos)</span>
                </label>
                <?php if (empty($vuelos)): ?>
                    <div class="empty-state">
                        <h3><?= !empty($erroresBusqueda) ? 'Hay datos para corregir' : 'No encontramos vuelos para esos filtros' ?></h3>
                        <?php if (!empty($erroresBusqueda)): ?>
                            <?php foreach (array_unique($erroresBusqueda) as $errorBusqueda): ?>
                                <p><?= htmlspecialchars($errorBusqueda, ENT_QUOTES, 'UTF-8') ?></p>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Proba con otro origen, destino o fecha.</p>
                        <?php endif; ?>
                        <br>
                        <a href="../../index.php#buscador" class="btn-continuar empty-link">Volver al buscador</a>
                    </div>
                <?php else: ?>
                    <div class="grid-vuelos">
                        <?php foreach($vuelos as $v): ?>
                            <?php
                                $precioBase = (float)$v['precio_base_vuelo'];
                                $precioFinal = $promoActiva ? calcularPrecioTotalVuelo($precioBase, $pasajeros, $codigoPromo, (string)$v['destino_iata']) : $precioBase;
                                $asientosDisponibles = (int)($v['asientos_disponibles'] ?? 18);
                                $disponible = $asientosDisponibles > 0;
                                $selectorName = $multitramos ? 'id_vuelo[]' : 'id_vuelo';
                                $selectorType = $multitramos ? 'checkbox' : 'radio';
                            ?>
                            <label class="opcion-vuelo <?= $disponible ? '' : 'vuelo-no-disponible' ?>">
                                <input type="<?= $selectorType ?>" name="<?= $selectorName ?>" value="<?= htmlspecialchars((string)$v['id_vuelo'], ENT_QUOTES, 'UTF-8') ?>" <?= $multitramos ? '' : 'required' ?> <?= ($primerVuelo && $v['id_vuelo'] === $primerVuelo['id_vuelo']) ? 'checked' : '' ?> <?= $disponible ? '' : 'disabled' ?>>
                                <span class="badge-vuelo">Vuelo <?= htmlspecialchars($v['numero_vuelo'], ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="status-pill <?= estadoClaseVuelo($v['estado_vuelo']) ?>"><?= htmlspecialchars($v['estado_vuelo'], ENT_QUOTES, 'UTF-8') ?></span>
                                <strong><?= htmlspecialchars($v['origen_ciudad'] . ' -> ' . $v['destino_ciudad'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <p>Salida: <?= date('d/m/Y H:i', strtotime($v['fecha_salida'])) ?></p>
                                <p>Llegada: <?= date('d/m/Y H:i', strtotime($v['fecha_llegada'])) ?></p>
                                <?php if (!empty($v['fuente'])): ?><span class="badge-api">Fuente: <?= htmlspecialchars($v['fuente'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                                <span class="asientos-disponibles">Asientos disponibles: <?= $asientosDisponibles ?></span>
                                <?php if ($promoActiva && $promoActiva['descuento'] > 0): ?>
                                    <span class="precio-vuelo precio-anterior">Antes $<?= number_format($precioBase, 2, ',', '.') ?></span>
                                <?php endif; ?>
                                <span class="precio-vuelo">Total estimado para el grupo: $<?= number_format($precioFinal, 2, ',', '.') ?></span>
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
