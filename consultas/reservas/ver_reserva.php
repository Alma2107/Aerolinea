<?php
require_once '../../config/conexion.php';

$pnr = strtoupper(trim($_GET['pnr'] ?? ''));
$pageStyles = ['../../css/reservas.css'];
include_once '../../includes/header.php';

$reserva = null;

function estadoClaseReserva(string $estado): string {
    $estado = strtolower($estado);
    if (str_contains($estado, 'horario')) return 'status-on-time';
    if (str_contains($estado, 'demorado')) return 'status-delayed';
    if (str_contains($estado, 'cancelado')) return 'status-cancelled';
    return 'status-scheduled';
}

if ($pnr !== '') {
    $stmt = $pdo->prepare("
        SELECT td.id_ticket, td.codigo_reserva_pnr, td.numero_asiento, td.precio_tramo_pagado,
               v.numero_vuelo, v.estado_vuelo, v.fecha_salida, v.fecha_llegada,
               origen.ciudad AS origen_ciudad, origen.codigo_iata AS origen_iata,
               destino.ciudad AS destino_ciudad, destino.codigo_iata AS destino_iata,
               p.nombre, p.apellido, p.numero_documento, pt.nombre_plan, co.fecha_compra
        FROM tickets_detalle td
        INNER JOIN vuelos v ON v.id_vuelo = td.id_vuelo
        INNER JOIN aeropuertos origen ON origen.codigo_iata = v.origen_iata
        INNER JOIN aeropuertos destino ON destino.codigo_iata = v.destino_iata
        INNER JOIN pasajeros p ON p.id_pasajero = td.id_pasajero
        INNER JOIN planes_tarifas pt ON pt.id_plan = td.id_plan
        INNER JOIN compras_ordenes co ON co.id_orden = td.id_orden
        WHERE td.codigo_reserva_pnr = :pnr
        LIMIT 1
    ");
    $stmt->execute(['pnr' => $pnr]);
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<main class="reservation-page">
    <section class="reservation-card">
        <p class="eyebrow">Mis viajes</p>
        <h1>Consultar reserva</h1>
        <form action="ver_reserva.php" method="GET" class="reservation-search">
            <input type="text" name="pnr" maxlength="6" value="<?= htmlspecialchars($pnr, ENT_QUOTES, 'UTF-8') ?>" placeholder="Codigo de reserva" required>
            <button type="submit" class="btn-next btn-orange">Buscar</button>
        </form>
    </section>

    <?php if ($pnr !== '' && !$reserva): ?>
        <section class="reservation-card reservation-empty">
            <h2>No encontramos esa reserva</h2>
            <p>Revisa que el codigo PNR tenga 6 caracteres y volve a intentarlo.</p>
        </section>
    <?php elseif ($reserva): ?>
        <?php $cancelable = strtotime($reserva['fecha_salida']) > time() && strtolower($reserva['estado_vuelo']) !== 'cancelado'; ?>
        <section class="ticket-summary">
            <div class="ticket-main">
                <p class="ticket-label">Reserva <?= htmlspecialchars($reserva['codigo_reserva_pnr'], ENT_QUOTES, 'UTF-8') ?></p>
                <h2><?= htmlspecialchars($reserva['origen_ciudad'], ENT_QUOTES, 'UTF-8') ?> a <?= htmlspecialchars($reserva['destino_ciudad'], ENT_QUOTES, 'UTF-8') ?></h2>
                <div class="route-line">
                    <strong><?= htmlspecialchars($reserva['origen_iata'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <span></span>
                    <strong><?= htmlspecialchars($reserva['destino_iata'], ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
                <div class="status-pill <?= estadoClaseReserva($reserva['estado_vuelo']) ?>"><?= htmlspecialchars($reserva['estado_vuelo'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="ticket-details">
                <p><strong>Pasajero:</strong> <?= htmlspecialchars($reserva['nombre'] . ' ' . $reserva['apellido'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Vuelo:</strong> <?= htmlspecialchars($reserva['numero_vuelo'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Salida:</strong> <?= date('d/m/Y H:i', strtotime($reserva['fecha_salida'])) ?></p>
                <p><strong>Llegada:</strong> <?= date('d/m/Y H:i', strtotime($reserva['fecha_llegada'])) ?></p>
                <p><strong>Asiento:</strong> <?= htmlspecialchars($reserva['numero_asiento'] ?? 'Sin asignar', ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Tarifa:</strong> <?= htmlspecialchars($reserva['nombre_plan'], ENT_QUOTES, 'UTF-8') ?></p>
                <div class="action-grid">
                    <a href="pasaje.php?pnr=<?= urlencode($reserva['codigo_reserva_pnr']) ?>" class="btn-next">Descargar ticket</a>
                    <a href="checkin.php?pnr=<?= urlencode($reserva['codigo_reserva_pnr']) ?>" class="btn-next btn-orange">Realizar check-in</a>
                    <?php if ($cancelable): ?>
                        <a href="cancelar_reserva.php?pnr=<?= urlencode($reserva['codigo_reserva_pnr']) ?>" class="btn-next btn-danger-action">Cancelar reserva</a>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php include_once '../../includes/footer.php'; ?>
</body>
</html>
