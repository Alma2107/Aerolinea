<?php
require_once '../../config/conexion.php';
session_start();

try {
    $pdo->beginTransaction();

    $id_vuelo = $_SESSION['id_vuelo'];
    $id_plan = $_SESSION['id_plan'];
    $id_asiento = $_SESSION['id_asiento'];
    $id_metodo_pago = $_POST['id_metodo_pago'] ?? 1;
    $id_cliente = $_SESSION['usuario_id'] ?? 1;

    // Consultamos precios base
    $stmtV = $pdo->prepare("SELECT precio_base_vuelo FROM vuelos WHERE id_vuelo = ?");
    $stmtV->execute([$id_vuelo]);
    $precio_vuelo = $stmtV->fetchColumn();

    $stmtP = $pdo->prepare("SELECT cargo_extra_plan FROM planes_tarifas WHERE id_plan = ?");
    $stmtP->execute([$id_plan]);
    $precio_plan = $stmtP->fetchColumn();

    $stmtA = $pdo->prepare("SELECT cargo_extra FROM asientos_avion WHERE id_asiento_avion = ?");
    $stmtA->execute([$id_asiento]);
    $precio_asiento = $stmtA->fetchColumn() ?: 0.00;

    // Calcular Maletas Extras
    $total_equipajes = 0;
    $lista_equipajes = [];
    if (!empty($_SESSION['equipajes'])) {
        foreach ($_SESSION['equipajes'] as $id_tipo => $cantidad) {
            $stmtE = $pdo->prepare("SELECT precio_unitario FROM tipos_equipaje WHERE id_tipo_equipaje = ?");
            $stmtE->execute([$id_tipo]);
            $precio_u = $stmtE->fetchColumn();
            $total_equipajes += ($precio_u * $cantidad);
            $lista_equipajes[] = ['id_tipo' => $id_tipo, 'cantidad' => $cantidad, 'precio' => $precio_u];
        }
    }

    // Calcular Servicios a Bordo
    $total_servicios = 0;
    $lista_servicios = [];
    if (!empty($_SESSION['servicios'])) {
        foreach ($_SESSION['servicios'] as $id_serv) {
            $stmtS = $pdo->prepare("SELECT precio_servicio FROM servicios_adicionales WHERE id_servicio = ?");
            $stmtS->execute([$id_serv]);
            $precio_s = $stmtS->fetchColumn();
            $total_servicios += $precio_s;
            $lista_servicios[] = ['id_servicio' => $id_serv, 'precio' => $precio_s];
        }
    }

    // Costo total de la orden
    $cant_pasajeros = count($_SESSION['datos_pasajeros']);
    $precio_por_ticket = $precio_vuelo + $precio_plan + $precio_asiento;
    $monto_total_orden = ($precio_por_ticket * $cant_pasajeros) + $total_equipajes + $total_servicios;

    // 1. Guardar la orden de compra
    $stmtOrden = $pdo->prepare("INSERT INTO compras_ordenes (id_cliente, fecha_compra, monto_total_pagado, id_metodo_pago) VALUES (:cl, NOW(), :total, :metodo)");
    $stmtOrden->execute(['cl' => $id_cliente, 'total' => $monto_total_orden, 'metodo' => $id_metodo_pago]);
    $id_orden = $pdo->lastInsertId();

    // 2. Insertar cada pasajero y su ticket
    foreach($_SESSION['datos_pasajeros'] as $pas) {
        $stmtPas = $pdo->prepare("INSERT INTO pasajeros (tipo_documento, numero_documento, nombre, apellido, fecha_nacimiento, asistencia_especial, detalles_medicos) 
            VALUES (:tipo_doc, :doc, :nom, :ape, :fnac, :asist, :detalles) ON DUPLICATE KEY UPDATE id_pasajero=LAST_INSERT_ID(id_pasajero)");
        $stmtPas->execute([
            'tipo_doc' => $pas['tipo_doc'], 'doc' => $pas['doc'], 'nom' => $pas['nombre'], 'ape' => $pas['apellido'], 
            'fnac' => $pas['fnac'], 'asist' => $pas['asistencia'] ?? 0, 'detalles' => !empty($pas['detalles_medicos']) ? $pas['detalles_medicos'] : NULL
        ]);
        $id_pasajero = $pdo->lastInsertId();

        $pnr = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 6);
        
        $stmtTicket = $pdo->prepare("INSERT INTO tickets_detalle (id_orden, id_vuelo, id_pasajero, id_asiento_avion, id_plan, codigo_reserva_pnr, precio_tramo_pagado) 
            VALUES (:id_orden, :id_vuelo, :id_pasajero, :id_asiento, :id_plan, :pnr, :precio_tramo)");
        $stmtTicket->execute([
            'id_orden' => $id_orden, 'id_vuelo' => $id_vuelo, 'id_pasajero' => $id_pasajero, 'id_asiento' => $id_asiento, 'id_plan' => $id_plan, 'pnr' => $pnr, 'precio_tramo' => $precio_por_ticket
        ]);
        $id_ticket = $pdo->lastInsertId();

        // Guardar sus equipajes
        foreach($lista_equipajes as $eq) {
            $stmtEqIns = $pdo->prepare("INSERT INTO ticket_equipajes (id_ticket, id_tipo_equipaje, cantidad, precio_pagado) VALUES (?, ?, ?, ?)");
            $stmtEqIns->execute([$id_ticket, $eq['id_tipo'], $eq['cantidad'], ($eq['precio'] * $eq['cantidad'])]);
        }

        // Guardar sus servicios a bordo
        foreach($lista_servicios as $srv) {
            $stmtSrvIns = $pdo->prepare("INSERT INTO ticket_servicios (id_ticket, id_servicio, precio_servicio_pagado) VALUES (?, ?, ?)");
            $stmtSrvIns->execute([$id_ticket, $srv['id_servicio'], $srv['precio']]);
        }
    }

    $pdo->commit();
    unset($_SESSION['id_vuelo'], $_SESSION['id_plan'], $_SESSION['id_asiento'], $_SESSION['datos_pasajeros'], $_SESSION['equipajes'], $_SESSION['servicios'], $_SESSION['pasajeros']);

    echo "<html><head><link rel='stylesheet' href='../../css/estilos.css'></head><body>";
    echo "<div class='card' style='max-width:600px; margin:100px auto; text-align:center; border: 1px solid #ddd; padding: 30px; border-radius: 8px;'>";
    echo "<h1>🎉 ¡Reserva Completada!</h1>";
    echo "<p style='margin:15px 0; color: #444;'>Orden de compra #<strong>$id_orden</strong> creada con éxito.</p>";
    echo "<a href='../../index.php' class='btn-next' style='text-decoration:none;'>Volver al Inicio</a>";
    echo "</div></body></html>";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error crítico: " . $e->getMessage();
}
?>