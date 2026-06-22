<?php
require_once '../../config/conexion.php';
session_start();

$pageStyles = ['../../css/proceso_compra/pago.css'];

require_once '../../includes/header.php';

$datos_pasajeros = $_SESSION['datos_pasajeros'] ?? [];
$id_vuelo = (int)($_SESSION['id_vuelo'] ?? 0);
$id_plan = (int)($_SESSION['id_plan'] ?? 0);
$id_asiento = (int)($_SESSION['id_asiento'] ?? 0);
$servicios_elegidos = $_SESSION['servicios_elegidos'] ?? [];

if ($id_vuelo <= 0 || $id_plan <= 0 || $id_asiento <= 0 || empty($datos_pasajeros)) {
    echo '<div class="contenedor-pago"><div class="main-content"><div class="card" style="max-width:700px; margin:60px auto; text-align:center;">';
    echo '<p class="eyebrow">Error de acceso</p>';
    echo '<h2>Faltan datos para confirmar la reserva</h2>';
    echo '<p>Volvé al flujo de compra y completá vuelo, equipaje, asiento y pasajeros antes de finalizar el pago.</p>';
    echo '<a href="../../index.php" class="btn-pagar" style="display:inline-block; text-decoration:none; margin-top:20px;">Volver al inicio</a>';
    echo '</div></div></div>';
    include_once '../../includes/footer.php';
    echo '</body></html>';
    exit;
}

try {
    $pdo->beginTransaction();

    // ID de cliente default si no inició sesión, o el real
    $id_cliente = $_SESSION['usuario_id'] ?? 1;

    // 1. Crear Orden de compra
    $stmtOrden = $pdo->prepare("INSERT INTO compras_ordenes (id_cliente, fecha_compra, monto_total_pagado, id_metodo_pago) VALUES (:cl, NOW(), 75000.00, 1)");
    $stmtOrden->execute(['cl' => $id_cliente]);
    $id_orden = $pdo->lastInsertId();

    $stmtAsiento = $pdo->prepare("SELECT numero_asiento FROM asientos_avion WHERE id_asiento_avion = :id_asiento LIMIT 1");
    $stmtAsiento->execute(['id_asiento' => $id_asiento]);
    $asientoData = $stmtAsiento->fetch(PDO::FETCH_ASSOC);
    $numero_asiento = $asientoData['numero_asiento'] ?? null;

    // 2. Insertar Pasajeros y Tickets
    foreach ($datos_pasajeros as $pas) {
        $stmtPas = $pdo->prepare("INSERT INTO pasajeros (tipo_documento, numero_documento, nombre, apellido, fecha_nacimiento, asistencia_especial) VALUES ('DNI', :doc, :nom, :ape, :fnac, :asist) ON DUPLICATE KEY UPDATE id_pasajero=LAST_INSERT_ID(id_pasajero)");
        $stmtPas->execute([
            'doc' => $pas['doc'], 'nom' => $pas['nombre'], 'ape' => $pas['apellido'], 'fnac' => $pas['fnac'], 'asist' => $pas['asistencia'] ?? 0
        ]);
        $id_pasajero = $pdo->lastInsertId();

        // Crear ticket por pasajero
        $pnr = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 6);
        $stmtTicket = $pdo->prepare("INSERT INTO tickets_detalle (id_orden, id_vuelo, id_pasajero, numero_asiento, id_plan, codigo_reserva_pnr, precio_tramo_pagado) VALUES (:id_orden, :id_vuelo, :id_pasajero, :numero_asiento, :id_plan, :pnr, 50000.00)");
        $stmtTicket->execute([
            'id_orden' => $id_orden,
            'id_vuelo' => $id_vuelo,
            'id_pasajero' => $id_pasajero,
            'numero_asiento' => $numero_asiento,
            'id_plan' => $id_plan,
            'pnr' => $pnr
        ]);
        $id_ticket = $pdo->lastInsertId();

        // 3. Vincular Equipajes
        foreach ($servicios_elegidos as $id_serv) {
            $stmtServ = $pdo->prepare("INSERT INTO ticket_servicios (id_ticket, id_servicio, precio_servicio_pagado) VALUES (:id_ticket, :id_serv, 15000.00)");
            $stmtServ->execute(['id_ticket' => $id_ticket, 'id_serv' => $id_serv]);
        }
    }

    $pdo->commit();
    
    // Limpiamos la compra pero dejamos activa la sesión del usuario
    unset($_SESSION['id_vuelo'], $_SESSION['id_plan'], $_SESSION['id_asiento'], $_SESSION['datos_pasajeros'], $_SESSION['servicios_elegidos']);

    echo '<div class="contenedor-pago"><div class="main-content"><div class="card" style="max-width:700px; margin:60px auto; text-align:center;">';
    echo '<p class="eyebrow">Reserva completada</p>';
    echo '<h1>🎉 ¡Tu reserva fue confirmada!</h1>';
    echo '<p style="margin:15px 0;">Tu código PNR fue registrado con éxito. Ya podés volver al inicio o seguir navegando por la aerolínea.</p>';
    echo '<a href="../../index.php" class="btn-pagar" style="display:inline-block; text-decoration:none; margin-top:20px;">Volver al inicio</a>';
    echo '</div></div></div>';
    include_once '../../includes/footer.php';
    echo '</body></html>';

} catch (Exception $e) {
    $pdo->rollBack();
    echo '<div class="contenedor-pago"><div class="main-content"><div class="card" style="max-width:700px; margin:60px auto; text-align:center;">';
    echo '<p class="eyebrow">Error</p>';
    echo '<h2>No se pudo completar la reserva</h2>';
    echo '<p>Se produjo un error durante la confirmación. Revisá que el flujo anterior esté completo e intentá de nuevo.</p>';
    echo '<p style="margin-top:12px; color:#b91c1c;">' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    echo '<a href="../../index.php" class="btn-pagar" style="display:inline-block; text-decoration:none; margin-top:20px;">Volver al inicio</a>';
    echo '</div></div></div>';
    include_once '../../includes/footer.php';
    echo '</body></html>';
}
?>