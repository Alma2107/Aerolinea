<?php
require_once '../../config/conexion.php';
session_start();

// VALIDACIÓN DE SEGURIDAD: Si no hay datos en la sesión, significa que la compra ya se procesó o expiró
if (!isset($_SESSION['id_vuelo']) || !isset($_SESSION['id_plan']) || empty($_SESSION['datos_pasajeros'])) {
    // Redirigimos al index o a una página de historial para que no explote con Warnings
    header("Location: ../../index.php");
    exit();
}

try {
    $pdo->beginTransaction();

    $id_vuelo = $_SESSION['id_vuelo'];
    $id_plan = $_SESSION['id_plan'];
    $id_metodo_pago = $_POST['id_metodo_pago'] ?? 1;
    $id_cliente = $_SESSION['usuario_id'] ?? 1;

    // 1. Obtener Precios Base del Vuelo y del Plan
    $stmtV = $pdo->prepare("SELECT precio_base_vuelo FROM vuelos WHERE id_vuelo = ?");
    $stmtV->execute([$id_vuelo]); 
    $precio_vuelo = $stmtV->fetchColumn();

    $stmtP = $pdo->prepare("SELECT cargo_extra_plan FROM planes_tarifas WHERE id_plan = ?");
    $stmtP->execute([$id_plan]); 
    $precio_plan = $stmtP->fetchColumn();

    // 2. Calcular Costos Totales de Equipajes desde la Sesión
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

    // 3. Calcular Costos Totales de Servicios desde la Sesión
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

    // 4. Calcular el Costo Total de todos los Asientos de los Pasajeros
    $total_asientos = 0;
    if (!empty($_SESSION['id_asiento'])) {
        foreach ($_SESSION['id_asiento'] as $id_asiento_sel) {
            $stmtA = $pdo->prepare("SELECT cargo_extra FROM asientos_avion WHERE id_asiento_avion = ?");
            $stmtA->execute([$id_asiento_sel]);
            $total_asientos += ($stmtA->fetchColumn() ?: 0.00);
        }
    }

    // Ahora estamos 100% seguros de que es un array contable
    $cant_pasajeros = count($_SESSION['datos_pasajeros']);
    
    // El monto total combina el (Vuelo + Plan) x Pasajeros + Asientos individuales + Equipajes + Servicios
    $monto_total_orden = (($precio_vuelo + $precio_plan) * $cant_pasajeros) + $total_asientos + $total_equipajes + $total_servicios;

    // 5. Guardar Orden Maestra de Compra
    $stmtOrden = $pdo->prepare("INSERT INTO compras_ordenes (id_cliente, fecha_compra, monto_total_pagado, id_metodo_pago) VALUES (:cl, NOW(), :total, :metodo)");
    $stmtOrden->execute(['cl' => $id_cliente, 'total' => $monto_total_orden, 'metodo' => $id_metodo_pago]);
    $id_orden = $pdo->lastInsertId();

    $pnr = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 6);

    // 6. Procesar cada Pasajero y vincular su asiento individual
    foreach($_SESSION['datos_pasajeros'] as $index => $pas) {
        
        // Insertar o verificar Pasajero
        $stmtPas = $pdo->prepare("INSERT INTO pasajeros (tipo_documento, numero_documento, nombre, apellido, fecha_nacimiento, asistencia_especial, detalles_medicos) 
            VALUES (:tipo_doc, :doc, :nom, :ape, :fnac, :asist, :detalles) ON DUPLICATE KEY UPDATE id_pasajero=LAST_INSERT_ID(id_pasajero)");
        $stmtPas->execute([
            'tipo_doc' => $pas['tipo_doc'], 
            'doc' => $pas['doc'], 
            'nom' => $pas['nombre'], 
            'ape' => $pas['apellido'], 
            'fnac' => $pas['fnac'], 
            'asist' => $pas['asistencia'] ?? 0, 
            'detalles' => !empty($pas['detalles_medicos']) ? $pas['detalles_medicos'] : NULL
        ]);
        $id_pasajero = $pdo->lastInsertId();

        // Extraemos el ID del asiento correspondiente a ESTE pasajero usando el $index del bucle
        $id_asiento_pasajero = $_SESSION['id_asiento'][$index] ?? null;

        // Buscamos si ese asiento tiene cargo extra para sumarlo al precio de este ticket individual
        $precio_asiento_individual = 0;
        if ($id_asiento_pasajero) {
            $stmtAsiPrecio = $pdo->prepare("SELECT cargo_extra FROM asientos_avion WHERE id_asiento_avion = ?");
            $stmtAsiPrecio->execute([$id_asiento_pasajero]);
            $precio_asiento_individual = $stmtAsiPrecio->fetchColumn() ?: 0;
        }

        $precio_por_ticket_individual = $precio_vuelo + $precio_plan + $precio_asiento_individual;
        
        // Guardar Ticket Detalle con su respectivo asiento asignado
        $stmtTicket = $pdo->prepare("INSERT INTO tickets_detalle (id_orden, id_vuelo, id_pasajero, id_asiento_avion, id_plan, codigo_reserva_pnr, precio_tramo_pagado) 
            VALUES (:id_orden, :id_vuelo, :id_pasajero, :id_asiento, :id_plan, :pnr, :precio_tramo)");
        $stmtTicket->execute([
            'id_orden' => $id_orden, 
            'id_vuelo' => $id_vuelo, 
            'id_pasajero' => $id_pasajero, 
            'id_asiento' => $id_asiento_pasajero, 
            'id_plan' => $id_plan, 
            'pnr' => $pnr, 
            'precio_tramo' => $precio_por_ticket_individual
        ]);
        $id_ticket = $pdo->lastInsertId();

        // Guardar Equipajes Asociados a este ticket
        foreach($lista_equipajes as $eq) {
            $stmtEqIns = $pdo->prepare("INSERT INTO ticket_equipajes (id_ticket, id_tipo_equipaje, cantidad, precio_pagado) VALUES (?, ?, ?, ?)");
            $stmtEqIns->execute([$id_ticket, $eq['id_tipo'], $eq['cantidad'], ($eq['precio'] * $eq['cantidad'])]);
        }

        // Guardar Servicios Asociados a este ticket
        foreach($lista_servicios as $srv) {
            $stmtSrvIns = $pdo->prepare("INSERT INTO ticket_servicios (id_ticket, id_servicio, precio_servicio_pagado) VALUES (?, ?, ?)");
            $stmtSrvIns->execute([$id_ticket, $srv['id_servicio'], $srv['precio']]);
        }
    }

    $pdo->commit();
    
    // Limpieza de datos temporales de la compra
    unset($_SESSION['id_vuelo'], $_SESSION['id_plan'], $_SESSION['id_asiento'], $_SESSION['datos_pasajeros'], $_SESSION['equipajes'], $_SESSION['servicios'], $_SESSION['pasajeros']);

    echo "<html><head><link rel='stylesheet' href='../../css/estilos.css'></head><body>";
    echo "<div class='card' style='max-width:600px; margin:100px auto; text-align:center; border: 1px solid #ddd; padding: 30px; border-radius: 8px; font-family: Arial, sans-serif; background: #fff;'>";
    echo "<h1 style='color: #28a745;'>🎉 ¡Pago Exitoso!</h1>";
    echo "<p>Tu orden de compra <strong>#$id_orden</strong> fue procesada perfectamente.</p>";
    echo "<p>El código PNR de tu reserva es: <strong style='font-size:20px; color:#0056b3;'>$pnr</strong></p>";
    echo "<a href='../../index.php' style='display:inline-block; margin-top:15px; padding:10px 20px; background:#28a745; color:#fff; text-decoration:none; border-radius:4px; font-weight: bold;'>Volver al Home</a>";
    echo "</div></body></html>";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error crítico de procesamiento en el servidor: " . $e->getMessage();
}
?>