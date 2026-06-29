<?php
require_once '../../config/conexion.php';

$pnr = strtoupper(trim($_GET['pnr'] ?? ''));
$pageStyles = ['../../css/reservas.css'];
include_once '../../includes/header.php';

$stmt = $pdo->prepare("
    SELECT td.codigo_reserva_pnr, td.numero_asiento, td.precio_tramo_pagado,
           v.numero_vuelo, v.estado_vuelo, v.fecha_salida, v.fecha_llegada,
           origen.ciudad AS origen_ciudad, origen.codigo_iata AS origen_iata,
           destino.ciudad AS destino_ciudad, destino.codigo_iata AS destino_iata,
           p.nombre, p.apellido, p.numero_documento, pt.nombre_plan
    FROM tickets_detalle td
    INNER JOIN vuelos v ON v.id_vuelo = td.id_vuelo
    INNER JOIN aeropuertos origen ON origen.codigo_iata = v.origen_iata
    INNER JOIN aeropuertos destino ON destino.codigo_iata = v.destino_iata
    INNER JOIN pasajeros p ON p.id_pasajero = td.id_pasajero
    INNER JOIN planes_tarifas pt ON pt.id_plan = td.id_plan
    WHERE td.codigo_reserva_pnr = :pnr
    LIMIT 1
");
$stmt->execute(['pnr' => $pnr]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

$pasajeUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/Aerolinea/consultas/reservas/pasaje.php?pnr=' . urlencode($pnr);
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($pasajeUrl);
?>

<main class="reservation-page">
    <?php if (!$ticket): ?>
        <section class="reservation-card reservation-empty">
            <h1>Pasaje no encontrado</h1>
            <p>El código de reserva no existe o está incompleto.</p>
            <a href="../../index.php#reservas" class="btn-next btn-orange">Volver a consultar</a>
        </section>
    <?php else: ?>
        <section class="boarding-pass">
            <div class="boarding-info">
                <p class="eyebrow">Pasaje virtual</p>
                <h1>FlySmart</h1>
                <p class="ticket-label">PNR <?= htmlspecialchars($ticket['codigo_reserva_pnr'], ENT_QUOTES, 'UTF-8') ?></p>

                <div class="boarding-route">
                    <div>
                        <span>Origen</span>
                        <strong><?= htmlspecialchars($ticket['origen_iata'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <p><?= htmlspecialchars($ticket['origen_ciudad'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div>
                        <span>Destino</span>
                        <strong><?= htmlspecialchars($ticket['destino_iata'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <p><?= htmlspecialchars($ticket['destino_ciudad'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>

                <div class="boarding-grid">
                    <p><strong>Pasajero</strong><?= htmlspecialchars($ticket['nombre'] . ' ' . $ticket['apellido'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Documento</strong><?= htmlspecialchars($ticket['numero_documento'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Vuelo</strong><?= htmlspecialchars($ticket['numero_vuelo'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Salida</strong><?= date('d/m/Y H:i', strtotime($ticket['fecha_salida'])) ?></p>
                    <p><strong>Llegada</strong><?= date('d/m/Y H:i', strtotime($ticket['fecha_llegada'])) ?></p>
                    <p><strong>Asiento</strong><?= htmlspecialchars($ticket['numero_asiento'] ?? 'Sin asignar', ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Tarifa</strong><?= htmlspecialchars($ticket['nombre_plan'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Estado</strong><?= htmlspecialchars($ticket['estado_vuelo'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>

            <aside class="boarding-qr">
                <img src="<?= htmlspecialchars($qrUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Código QR del pasaje">
                <p>Escaneá para abrir este pasaje.</p>
                <button type="button" class="btn-next btn-orange" onclick="window.print()">Descargar / imprimir</button>
            </aside>
        </section>
    <?php endif; ?>
</main>

<?php include_once '../../includes/footer.php'; ?>
</body>
</html>
