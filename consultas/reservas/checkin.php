<?php
require_once '../../config/conexion.php';
$pnr = strtoupper(trim($_GET['pnr'] ?? ''));
$pageStyles = ['../../css/reservas.css'];
include_once '../../includes/header.php';

$stmt = $pdo->prepare("SELECT td.codigo_reserva_pnr, v.numero_vuelo, v.fecha_salida, v.estado_vuelo, p.nombre, p.apellido FROM tickets_detalle td INNER JOIN vuelos v ON v.id_vuelo = td.id_vuelo INNER JOIN pasajeros p ON p.id_pasajero = td.id_pasajero WHERE td.codigo_reserva_pnr = :pnr LIMIT 1");
$stmt->execute(['pnr' => $pnr]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<main class="reservation-page">
    <section class="reservation-card reservation-empty">
        <?php if (!$ticket): ?>
            <h1>No encontramos la reserva</h1>
            <p>Verifica el codigo PNR.</p>
        <?php elseif (strtotime($ticket['fecha_salida']) < time()): ?>
            <h1>Check-in no disponible</h1>
            <p>El vuelo ya figura como pasado.</p>
        <?php else: ?>
            <p class="eyebrow">Check-in</p>
            <h1>Check-in realizado</h1>
            <p><?= htmlspecialchars($ticket['nombre'] . ' ' . $ticket['apellido'], ENT_QUOTES, 'UTF-8') ?>, tu check-in para el vuelo <?= htmlspecialchars($ticket['numero_vuelo'], ENT_QUOTES, 'UTF-8') ?> quedo registrado en esta demo.</p>
            <a href="pasaje.php?pnr=<?= urlencode($ticket['codigo_reserva_pnr']) ?>" class="btn-next btn-orange">Ver tarjeta de embarque</a>
        <?php endif; ?>
        <a href="mis_viajes.php" class="btn-next">Volver a Mis viajes</a>
    </section>
</main>
<?php include_once '../../includes/footer.php'; ?>
</body>
</html>
