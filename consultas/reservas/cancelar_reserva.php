<?php
require_once '../../config/conexion.php';
session_start();

$pnr = strtoupper(trim($_GET['pnr'] ?? ''));
$pageStyles = ['../../css/reservas.css'];

$pdo->exec("
    CREATE TABLE IF NOT EXISTS reservas_cancelaciones (
        id_cancelacion INT AUTO_INCREMENT PRIMARY KEY,
        codigo_reserva_pnr VARCHAR(6) NOT NULL,
        id_cliente INT NULL,
        fecha_solicitud DATETIME NOT NULL,
        estado VARCHAR(30) NOT NULL DEFAULT 'Solicitada',
        motivo VARCHAR(180) DEFAULT NULL,
        UNIQUE KEY reserva_unica (codigo_reserva_pnr)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
");

$mensaje = 'No encontramos una reserva activa con ese codigo.';
$titulo = 'No se pudo cancelar';
$clase = 'Error';

if ($pnr !== '') {
    $stmtTicket = $pdo->prepare("
        SELECT td.codigo_reserva_pnr, v.fecha_salida, v.estado_vuelo
        FROM tickets_detalle td
        INNER JOIN vuelos v ON v.id_vuelo = td.id_vuelo
        WHERE td.codigo_reserva_pnr = :pnr
        LIMIT 1
    ");
    $stmtTicket->execute(['pnr' => $pnr]);
    $ticket = $stmtTicket->fetch(PDO::FETCH_ASSOC);

    if ($ticket && strtotime($ticket['fecha_salida']) > time() && strtolower($ticket['estado_vuelo']) !== 'cancelado') {
        $stmtCancelacion = $pdo->prepare("
            INSERT INTO reservas_cancelaciones (codigo_reserva_pnr, id_cliente, fecha_solicitud, estado, motivo)
            VALUES (:pnr, :cliente, NOW(), 'Solicitada', 'Cancelacion solicitada desde Mis viajes')
            ON DUPLICATE KEY UPDATE fecha_solicitud = NOW(), estado = 'Solicitada'
        ");
        $stmtCancelacion->execute([
            'pnr' => $pnr,
            'cliente' => $_SESSION['usuario_id'] ?? null,
        ]);

        $titulo = 'Solicitud registrada';
        $clase = 'Cancelar reserva';
        $mensaje = 'Registramos la solicitud de cancelacion para la reserva ' . $pnr . '. El ticket queda disponible para historial y seguimiento.';
    } elseif ($ticket) {
        $mensaje = 'La reserva existe, pero el vuelo ya paso o figura cancelado.';
    }
}

include_once '../../includes/header.php';
?>
<main class="reservation-page">
    <section class="reservation-card reservation-empty">
        <p class="eyebrow"><?= htmlspecialchars($clase, ENT_QUOTES, 'UTF-8') ?></p>
        <h1><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></p>
        <a href="mis_viajes.php" class="btn-next btn-orange">Volver a Mis viajes</a>
    </section>
</main>
<?php include_once '../../includes/footer.php'; ?>
</body>
</html>
