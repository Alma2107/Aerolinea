<?php
require_once '../../config/conexion.php';
$pnr = strtoupper(trim($_GET['pnr'] ?? ''));
$pageStyles = ['../../css/reservas.css'];
include_once '../../includes/header.php';
?>
<main class="reservation-page">
    <section class="reservation-card reservation-empty">
        <p class="eyebrow">Cancelar reserva</p>
        <h1>Solicitud registrada</h1>
        <p>Para no borrar datos historicos en esta version, dejamos registrada la intencion de cancelar la reserva <?= htmlspecialchars($pnr, ENT_QUOTES, 'UTF-8') ?>. El siguiente paso seria guardar un estado propio de reserva en una tabla adicional.</p>
        <a href="mis_viajes.php" class="btn-next btn-orange">Volver a Mis viajes</a>
    </section>
</main>
<?php include_once '../../includes/footer.php'; ?>
</body>
</html>
