<?php
require_once '../../config/conexion.php';
session_start();

// VALIDACIÓN DE SEGURIDAD
if (!isset($_SESSION['id_vuelo']) || !isset($_SESSION['id_plan']) || empty($_SESSION['datos_pasajeros'])) {
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

    // 2. Calcular costos globales para el monto total de la orden
    $total_equipajes_global = 0; 
    if (!empty($_SESSION['equipajes'])) {
        foreach ($_SESSION['equipajes'] as $num_p => $items) {
            foreach ($items as $id_tipo => $cantidad) {
                $stmtE = $pdo->prepare("SELECT precio_unitario FROM tipos_equipaje WHERE id_tipo_equipaje = ?");
                $stmtE->execute([$id_tipo]); 
                $total_equipajes_global += ($stmtE->fetchColumn() * $cantidad);
            }
        }
    }

    $total_servicios_global = 0; 
    if (!empty($_SESSION['servicios'])) {
        foreach ($_SESSION['servicios'] as $num_p => $servicios_p) {
            foreach ($servicios_p as $id_serv) {
                $stmtS = $pdo->prepare("SELECT precio_servicio FROM servicios_adicionales WHERE id_servicio = ?");
                $stmtS->execute([$id_serv]); 
                $total_servicios_global += $stmtS->fetchColumn();
            }
        }
    }

    $total_asientos_global = 0;
    if (!empty($_SESSION['id_asiento'])) {
        foreach ($_SESSION['id_asiento'] as $id_asiento_sel) {
            $stmtA = $pdo->prepare("SELECT cargo_extra FROM asientos_avion WHERE id_asiento_avion = ?");
            $stmtA->execute([$id_asiento_sel]);
            $total_asientos_global += ($stmtA->fetchColumn() ?: 0.00);
        }
    }

    $cant_pasajeros = count($_SESSION['datos_pasajeros']);
    $monto_total_orden = (($precio_vuelo + $precio_plan) * $cant_pasajeros) + $total_asientos_global + $total_equipajes_global + $total_servicios_global;

    // 3. Guardar Orden Maestra de Compra
    $stmtOrden = $pdo->prepare("INSERT INTO compras_ordenes (id_cliente, fecha_compra, monto_total_pagado, id_metodo_pago) VALUES (:cl, NOW(), :total, :metodo)");
    $stmtOrden->execute(['cl' => $id_cliente, 'total' => $monto_total_orden, 'metodo' => $id_metodo_pago]);
    $id_orden = $pdo->lastInsertId();

    // Generar PNR Único para la reserva
    $pnr = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 6);

    // 4. Procesar cada Pasajero y vincular sus compras independientes
    foreach($_SESSION['datos_pasajeros'] as $index => $pas) {
        
        // Insertar o actualizar Pasajero en la BD
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

        // Extraer el Asiento correspondiente a ESTE pasajero específico
        $id_asiento_pasajero = $_SESSION['id_asiento'][$index] ?? null;
        $precio_asiento_individual = 0;
        if ($id_asiento_pasajero) {
            $stmtAsiPrecio = $pdo->prepare("SELECT cargo_extra FROM asientos_avion WHERE id_asiento_avion = ?");
            $stmtAsiPrecio->execute([$id_asiento_pasajero]);
            $precio_asiento_individual = $stmtAsiPrecio->fetchColumn() ?: 0;
        }

        $precio_por_ticket_individual = $precio_vuelo + $precio_plan + $precio_asiento_individual;
        
        // Guardar Ticket Detalle individual
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

        // 5. Guardar Equipajes EXCLUSIVOS de este pasajero
        if (!empty($_SESSION['equipajes'][$index])) {
            foreach($_SESSION['equipajes'][$index] as $id_tipo => $cantidad) {
                if ($cantidad > 0) {
                    $stmtE = $pdo->prepare("SELECT precio_unitario FROM tipos_equipaje WHERE id_tipo_equipaje = ?");
                    $stmtE->execute([$id_tipo]);
                    $precio_u = $stmtE->fetchColumn();

                    $stmtEqIns = $pdo->prepare("INSERT INTO ticket_equipajes (id_ticket, id_tipo_equipaje, cantidad, precio_pagado) VALUES (?, ?, ?, ?)");
                    $stmtEqIns->execute([$id_ticket, $id_tipo, $cantidad, ($precio_u * $cantidad)]);
                }
            }
        }

        // 6. Guardar Servicios EXCLUSIVOS de este pasajero
        if (!empty($_SESSION['servicios'][$index])) {
            foreach($_SESSION['servicios'][$index] as $id_serv) {
                $stmtS = $pdo->prepare("SELECT precio_servicio FROM servicios_adicionales WHERE id_servicio = ?");
                $stmtS->execute([$id_serv]);
                $precio_s = $stmtS->fetchColumn();

                $stmtSrvIns = $pdo->prepare("INSERT INTO ticket_servicios (id_ticket, id_servicio, precio_servicio_pagado) VALUES (?, ?, ?)");
                $stmtSrvIns->execute([$id_ticket, $id_serv, $precio_s]);
            }
        }
    }

    $pdo->commit();
    
    // Limpieza total del carrito de compras de la sesión
    unset(
        $_SESSION['id_vuelo'], 
        $_SESSION['id_plan'], 
        $_SESSION['id_asiento'], 
        $_SESSION['datos_pasajeros'], 
        $_SESSION['equipajes'], 
        $_SESSION['servicios'], 
        $_SESSION['pasajeros']
    );

    include_once '../../includes/header.php';
    ?>
    <link rel="stylesheet" href="../../css/estilos-confirmar.css">

    <div class="contenedor-exito">
        <h1>🎉 ¡Pago Exitoso!</h1>
        <p>Tu orden de compra <strong>#<?=$id_orden?></strong> fue procesada perfectamente.</p>
        <p>El código PNR de tu reserva es:</p>
        <div class="pnr-resaltado"><?=$pnr?></div>
        <br>
        <a href="../../index.php" class="btn-home">Volver al Home</a>
    </div>
    </body>
    </html>

<?php
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    include_once '../../includes/header.php';
    ?>
    <link rel="stylesheet" href="../../css/proceso_compra/confirmar.css">
    <div class="error-servidor">
        Error crítico de procesamiento en el servidor: <?=htmlspecialchars($e->getMessage())?>
    </div>
    </body>
    </html>
<?php } ?>