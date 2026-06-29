<?php
require_once '../../config/conexion.php';
session_start();

$pageStyles = ['../../css/reservas.css'];
include_once '../../includes/header.php';

$idCliente = $_SESSION['usuario_id'] ?? null;
$tickets = [];
$proximo = null;

function estadoClaseReserva(string $estado): string {
    $estado = strtolower($estado);
    if (str_contains($estado, 'horario')) return 'status-on-time';
    if (str_contains($estado, 'demorado')) return 'status-delayed';
    if (str_contains($estado, 'cancelado')) return 'status-cancelled';
    return 'status-scheduled';
}

if ($idCliente) {
    $stmt = $pdo->prepare("      
        SELECT td.codigo_reserva_pnr, td.numero_asiento, v.numero_vuelo, v.estado_vuelo, v.fecha_salida, v.fecha_llegada,
               origen.ciudad AS origen_ciudad, origen.codigo_iata AS origen_iata,
               destino.ciudad AS destino_ciudad, destino.codigo_iata AS destino_iata,
               p.nombre, p.apellido, co.fecha_compra
        FROM compras_ordenes co
        INNER JOIN tickets_detalle td ON td.id_orden = co.id_orden
        INNER JOIN vuelos v ON v.id_vuelo = td.id_vuelo
        INNER JOIN aeropuertos origen ON origen.codigo_iata = v.origen_iata
        INNER JOIN aeropuertos destino ON destino.codigo_iata = v.destino_iata
        INNER JOIN pasajeros p ON p.id_pasajero = td.id_pasajero
        WHERE co.id_cliente = :id_cliente
        ORDER BY v.fecha_salida ASC
    ");
    $stmt->execute(['id_cliente' => $idCliente]);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($tickets as $ticket) {
        if (strtotime($ticket['fecha_salida']) >= time() && strtolower($ticket['estado_vuelo']) !== 'cancelado') {
            $proximo = $ticket;
            break;
        }
    }
}
?>

<main class="reservation-page">
    <section class="client-panel-hero">
        <div>
            <p class="eyebrow">Panel del cliente</p>
            <h1>Mis viajes</h1>
            <p>Un solo lugar para ver detalles, estado del vuelo, check-in, ticket, QR e historial.</p>
        </div>
        <form action="ver_reserva.php" method="GET" class="reservation-search compact-search">
            <input type="text" name="pnr" maxlength="6" placeholder="Consultar PNR" required>
            <button type="submit" class="btn-next btn-orange">Consultar</button>
        </form>
    </section>

    <?php if (!$idCliente): ?>
        <section class="reservation-card reservation-empty">
            <h2>Inicia sesion para ver tus viajes</h2>
            <p>Sin iniciar sesion podes buscar vuelos, ver destinos, consultar un PNR y ver promociones. Para historial, check-in y tickets necesitas tu cuenta.</p>
            <a href="../login/login.php" class="btn-next btn-orange">Iniciar sesion</a>
        </section>
    <?php else: ?>
        <section class="dashboard-grid">
            <article class="dashboard-card next-flight-card">
                <p class="eyebrow">Proximo vuelo</p>
                <?php if ($proximo): ?>
                    <?php $faltan = max(0, strtotime($proximo['fecha_salida']) - time()); ?>
                    <h2><?= htmlspecialchars($proximo['origen_ciudad'] . ' -> ' . $proximo['destino_ciudad'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <p>Faltan <?= floor($faltan / 86400) ?> dias y <?= floor(($faltan % 86400) / 3600) ?> horas</p>
                    <span class="status-pill <?= estadoClaseReserva($proximo['estado_vuelo']) ?>"><?= htmlspecialchars($proximo['estado_vuelo'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php else: ?>
                    <h2>No tenes vuelos futuros</h2>
                    <p>Cuando compres un vuelo, va a aparecer aca.</p>
                <?php endif; ?>
            </article>

            <article class="dashboard-card">
                <p class="eyebrow">Promos disponibles</p>
                <h2>Ofertas para tu proximo viaje</h2>
                <div class="mini-promo-list">
                    <a href="../proceso_compra/vuelos.php?destino=BRC&pasajeros=1&codigo_promo=BARILO20">BARILO20</a>
                    <a href="../proceso_compra/vuelos.php?pasajeros=1&codigo_promo=EQUIPAJEGRATIS">EQUIPAJEGRATIS</a>
                    <a href="../proceso_compra/vuelos.php?destino=COR&pasajeros=2&codigo_promo=CORDOBA2X1">CORDOBA2X1</a>
                </div>
            </article>
        </section>

        <?php if (empty($tickets)): ?>
            <section class="reservation-card reservation-empty">
                <h2>Todavia no tenes vuelos comprados</h2>
                <p>Cuando finalices una reserva, va a aparecer en esta seccion.</p>
                <a href="../../index.php#buscador" class="btn-next">Buscar vuelos</a>
            </section>
        <?php else: ?>
            <section class="history-list">
                <?php foreach ($tickets as $ticket): ?>
                    <?php $cancelable = strtotime($ticket['fecha_salida']) > time() && strtolower($ticket['estado_vuelo']) !== 'cancelado'; ?>
                    <article class="history-item travel-action-card">
                        <div>
                            <p class="ticket-label"><?= htmlspecialchars($ticket['codigo_reserva_pnr'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($ticket['numero_vuelo'], ENT_QUOTES, 'UTF-8') ?></p>
                            <h2><?= htmlspecialchars($ticket['origen_ciudad'], ENT_QUOTES, 'UTF-8') ?> a <?= htmlspecialchars($ticket['destino_ciudad'], ENT_QUOTES, 'UTF-8') ?></h2>
                            <p><?= htmlspecialchars($ticket['nombre'] . ' ' . $ticket['apellido'], ENT_QUOTES, 'UTF-8') ?> · Salida <?= date('d/m/Y H:i', strtotime($ticket['fecha_salida'])) ?></p>
                            <span class="status-pill <?= estadoClaseReserva($ticket['estado_vuelo']) ?>"><?= htmlspecialchars($ticket['estado_vuelo'], ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div class="history-actions action-grid">
                            <a href="ver_reserva.php?pnr=<?= urlencode($ticket['codigo_reserva_pnr']) ?>" class="btn-next">Ver detalles</a>
                            <a href="checkin.php?pnr=<?= urlencode($ticket['codigo_reserva_pnr']) ?>" class="btn-next btn-orange">Check-in</a>
                            <a href="pasaje.php?pnr=<?= urlencode($ticket['codigo_reserva_pnr']) ?>" class="btn-next">Descargar ticket</a>
                            <?php if ($cancelable): ?>
                                <a href="cancelar_reserva.php?pnr=<?= urlencode($ticket['codigo_reserva_pnr']) ?>" class="btn-next btn-danger-action">Cancelar reserva</a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    <?php endif; ?>
</main>

<?php include_once '../../includes/footer.php'; ?>
</body>
</html>
