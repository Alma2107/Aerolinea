<?php
require_once '../../config/conexion.php';
session_start();

try {
    $pdo->beginTransaction();

    $id_cliente = $_SESSION['usuario_id'] ?? 1;

    // 1. Crear Orden de compra
    $stmtOrden = $pdo->prepare("INSERT INTO compras_ordenes (id_cliente, fecha_compra, monto_total_pagado, id_metodo_pago) VALUES (:cl, NOW(), 75000.00, 1)");
    $stmtOrden->execute(['cl' => $id_cliente]);
    $id_orden = $pdo->lastInsertId();

    // 2. Insertar Pasajeros y Tickets
    foreach($_SESSION['datos_pasajeros'] as $pas) {
        $stmtPas = $pdo->prepare("INSERT INTO pasajeros (tipo_documento, numero_documento, nombre, apellido, fecha_nacimiento, asistencia_especial) VALUES ('DNI', :doc, :nom, :ape, :fnac, :asist) ON DUPLICATE KEY UPDATE id_pasajero=LAST_INSERT_ID(id_pasajero)");
        $stmtPas->execute([
            'doc' => $pas['doc'], 'nom' => $pas['nombre'], 'ape' => $pas['apellido'], 'fnac' => $pas['fnac'], 'asist' => $pas['asistencia'] ?? 0
        ]);
        $id_pasajero = $pdo->lastInsertId();

        // Crear ticket por pasajero
        $pnr = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 6);
        $stmtTicket = $pdo->prepare("INSERT INTO tickets_detalle (id_orden, id_vuelo, id_pasajero, id_asiento_avion, id_plan, codigo_reserva_pnr, precio_tramo_pagado) VALUES (:id_orden, :id_vuelo, :id_pasajero, :id_asiento, :id_plan, :pnr, 50000.00)");
        $stmtTicket->execute([
            'id_orden' => $id_orden, 'id_vuelo' => $_SESSION['id_vuelo'], 'id_pasajero' => $id_pasajero, 'id_asiento' => $_SESSION['id_asiento'], 'id_plan' => $_SESSION['id_plan'], 'pnr' => $pnr
        ]);
        $id_ticket = $pdo->lastInsertId();

        // 3. Vincular Equipajes
        foreach($_SESSION['servicios_elegidos'] as $id_serv) {
            $stmtServ = $pdo->prepare("INSERT INTO ticket_servicios (id_ticket, id_servicio, precio_servicio_pagado) VALUES (:id_ticket, :id_serv, 15000.00)");
            $stmtServ->execute(['id_ticket' => $id_ticket, 'id_serv' => $id_serv]);
        }
    }

    $pdo->commit();
    
    // Limpiamos la compra pero dejamos activa la sesión del usuario
    unset($_SESSION['id_vuelo'], $_SESSION['id_plan'], $_SESSION['id_asiento'], $_SESSION['datos_pasajeros'], $_SESSION['servicios_elegidos']);

    echo "<html><head><link rel='stylesheet' href='../../css/estilos.css'></head><body>";
    echo "<div class='card' style='max-width:600px; margin:100px auto; text-align:center;'>";
    echo "<h1>🎉 ¡Reserva Completada!</h1><p style='margin:15px 0;'>Tu código PNR ha sido registrado con éxito.</p>";
    echo "<a href='../../index.php' class='btn-next' style='text-decoration:none;'>Volver al Inicio</a>";
    echo "</div></body></html>";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error en la transacción: " . $e->getMessage();
}
?>