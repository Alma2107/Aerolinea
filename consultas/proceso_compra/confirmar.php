<?php
require_once '../../config/conexion.php';
session_start();

// 1. VALIDACIÓN DE SEGURIDAD ESTRICTA
if (empty($_SESSION['id_vuelo_ida']) || empty($_SESSION['datos_pasajeros'])) {
    header("Location: ../../index.php");
    exit();
}

try {
    $pdo->beginTransaction();

    // Recuperación de variables de sesión consolidadas
    $id_vuelo_ida         = (int)$_SESSION['id_vuelo_ida'];
    $id_vuelo_vuelta      = isset($_SESSION['id_vuelo_vuelta']) ? (int)$_SESSION['id_vuelo_vuelta'] : 0;
    $id_metodo_pago       = (int)($_POST['id_metodo_pago'] ?? 1);
    $id_cliente           = (int)($_SESSION['usuario_id'] ?? 1);
    $cantidad_pasajeros   = (int)$_SESSION['pasajeros'];
    
    $planes_seleccionados = $_SESSION['planes_pasajeros'] ?? [];
    $equipajes_sesion     = $_SESSION['equipajes'] ?? [];
    $servicios_sesion     = $_SESSION['servicios'] ?? [];
    $asientos_ida         = $_SESSION['asientos_ida'] ?? [];
    $asientos_vuelta      = $_SESSION['asientos_vuelta'] ?? [];

    // 2. PRECARGAR TARIFAS EN MEMORIA
    $planes_db = $pdo->query("SELECT id_plan, cargo_extra_plan FROM planes_tarifas")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
    $equipajes_db = $pdo->query("SELECT id_tipo_equipaje, precio_unitario FROM tipos_equipaje")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
    $servicios_db = $pdo->query("SELECT id_servicio, precio_servicio FROM servicios_adicionales")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);

    // Obtener costos base de vuelos
    $precio_ida_base = 0.00;
    $stmtI = $pdo->prepare("SELECT precio_base_vuelo FROM vuelos WHERE id_vuelo = ?");
    $stmtI->execute([$id_vuelo_ida]);
    $precio_ida_base = (float)($stmtI->fetchColumn() ?: 0.00);

    $precio_vuelta_base = 0.00;
    if ($id_vuelo_vuelta > 0) {
        $stmtR = $pdo->prepare("SELECT precio_base_vuelo FROM vuelos WHERE id_vuelo = ?");
        $stmtR->execute([$id_vuelo_vuelta]);
        $precio_vuelta_base = (float)($stmtR->fetchColumn() ?: 0.00);
    }

    // Helper interno para calcular el cargo extra de los asientos
    function calcularCargoAsientoConfirmar($asiento_texto) {
        if (empty($asiento_texto)) return 0.00;
        preg_match('/(\d+)/', $asiento_texto, $matches);
        $fila = (int)($matches[1] ?? 1);
        
        if ($fila == 1) return 5000.00;
        if ($fila >= 2 && $fila <= 5) return 12000.00;
        if ($fila == 6 || $fila == 7) return 3000.00;
        if ($fila == 12 || $fila == 13) return 4500.00;
        return 0.00;
    }

    // 3. CÁLCULO INTEGRAL DEL MONTO TOTAL
    $monto_total_orden = 0;

    for ($i = 1; $i <= $cantidad_pasajeros; $i++) {
        $monto_total_orden += $precio_ida_base;
        $monto_total_orden += $precio_vuelta_base;

        if ($id_vuelo_ida > 0 && isset($planes_seleccionados[$i]['ida'])) {
            $monto_total_orden += (float)($planes_db[$planes_seleccionados[$i]['ida']]['cargo_extra_plan'] ?? 0);
        }
        if ($id_vuelo_vuelta > 0 && isset($planes_seleccionados[$i]['vuelta'])) {
            $monto_total_orden += (float)($planes_db[$planes_seleccionados[$i]['vuelta']]['cargo_extra_plan'] ?? 0);
        }
        if (isset($equipajes_sesion[$i])) {
            foreach ($equipajes_sesion[$i] as $id_tipo => $cant) {
                $monto_total_orden += ((float)($equipajes_db[$id_tipo]['precio_unitario'] ?? 0) * (int)$cant);
            }
        }
        if (isset($servicios_sesion[$i])) {
            foreach ($servicios_sesion[$i] as $id_servicio) {
                $monto_total_orden += (float)($servicios_db[$id_servicio]['precio_servicio'] ?? 0);
            }
        }
    }

    foreach ($asientos_ida as $asiento) { $monto_total_orden += calcularCargoAsientoConfirmar($asiento); }
    foreach ($asientos_vuelta as $asiento) { $monto_total_orden += calcularCargoAsientoConfirmar($asiento); }

    // 4. GUARDAR ORDEN MAESTRA DE COMPRA
    $stmtOrden = $pdo->prepare("INSERT INTO compras_ordenes (id_cliente, fecha_compra, monto_total_pagado, id_metodo_pago) VALUES (:cl, NOW(), :total, :metodo)");
    $stmtOrden->execute(['cl' => $id_cliente, 'total' => $monto_total_orden, 'metodo' => $id_metodo_pago]);
    $id_orden = $pdo->lastInsertId();

    // Generar un único PNR de reserva aérea
    $pnr = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 6);

    // Preparar sentencias
    $stmtPas = $pdo->prepare("INSERT INTO pasajeros (tipo_documento, numero_documento, nombre, apellido, fecha_nacimiento, asistencia_especial, detalles_medicos) 
        VALUES (:tipo_doc, :doc, :nom, :ape, :fnac, :asist, :detalles) ON DUPLICATE KEY UPDATE id_pasajero=LAST_INSERT_ID(id_pasajero)");

    $stmtTicket = $pdo->prepare("INSERT INTO tickets_detalle (id_orden, id_vuelo, id_pasajero, numero_asiento, id_plan, codigo_reserva_pnr, precio_tramo_pagado) 
        VALUES (:id_orden, :id_vuelo, :id_pasajero, :num_asiento, :id_plan, :pnr, :precio_tramo)");

    $stmtEqIns = $pdo->prepare("INSERT INTO ticket_equipajes (id_ticket, id_tipo_equipaje, cantidad, precio_pagado) VALUES (?, ?, ?, ?)");
    $stmtSrvIns = $pdo->prepare("INSERT INTO ticket_servicios (id_ticket, id_servicio, precio_servicio_pagado) VALUES (?, ?, ?)");

    // 5. PROCESAR PASAJEROS Y CREAR TICKETS
    foreach ($_SESSION['datos_pasajeros'] as $i => $pas) {
        
        $stmtPas->execute([
            'tipo_doc' => $pas['tipo_doc'], 
            'doc'      => $pas['doc'], 
            'nom'      => $pas['nombre'], 
            'ape'      => $pas['apellido'], 
            'fnac'     => $pas['fnac'], 
            'asist'    => $pas['asistencia'] ?? 0, 
            'detalles' => !empty($pas['detalles_medicos']) ? $pas['detalles_medicos'] : NULL
        ]);
        $id_pasajero = $pdo->lastInsertId();

        $tickets_del_pasajero = [];

        // --- TRAMO 1: IDA ---
        $asiento_i = $asientos_ida[$i - 1] ?? null;
        $plan_i    = $planes_seleccionados[$i]['ida'] ?? 1;
        $precio_ticket_ida = $precio_ida_base + (float)($planes_db[$plan_i]['cargo_extra_plan'] ?? 0) + calcularCargoAsientoConfirmar($asiento_i);

        $stmtTicket->execute([
            'id_orden'     => $id_orden, 
            'id_vuelo'     => $id_vuelo_ida, 
            'id_pasajero'  => $id_pasajero, 
            'num_asiento'  => $asiento_i, 
            'id_plan'      => $plan_i, 
            'pnr'          => $pnr, 
            'precio_tramo' => $precio_ticket_ida
        ]);
        $tickets_del_pasajero[] = $pdo->lastInsertId();

        // --- TRAMO 2: VUELTA ---
        if ($id_vuelo_vuelta > 0) {
            $asiento_v = $asientos_vuelta[$i - 1] ?? null;
            $plan_v    = $planes_seleccionados[$i]['vuelta'] ?? 1;
            $precio_ticket_vta = $precio_vuelta_base + (float)($planes_db[$plan_v]['cargo_extra_plan'] ?? 0) + calcularCargoAsientoConfirmar($asiento_v);

            $stmtTicket->execute([
                'id_orden'     => $id_orden, 
                'id_vuelo'     => $id_vuelo_vuelta, 
                'id_pasajero'  => $id_pasajero, 
                'num_asiento'  => $asiento_v, 
                'id_plan'      => $plan_v, 
                'pnr'          => $pnr, 
                'precio_tramo' => $precio_ticket_vta
            ]);
            $tickets_del_pasajero[] = $pdo->lastInsertId();
        }

        // 6. ASOCIAR ADICIONALES
        foreach ($tickets_del_pasajero as $id_ticket) {
            if (!empty($equipajes_sesion[$i])) {
                foreach ($equipajes_sesion[$i] as $id_tipo => $cantidad) {
                    if ($cantidad > 0) {
                        $precio_u = (float)($equipajes_db[$id_tipo]['precio_unitario'] ?? 0);
                        $stmtEqIns->execute([$id_ticket, $id_tipo, $cantidad, ($precio_u * $cantidad)]);
                    }
                }
            }

            if (!empty($servicios_sesion[$i])) {
                foreach ($servicios_sesion[$i] as $id_serv) {
                    $precio_s = (float)($servicios_db[$id_serv]['precio_servicio'] ?? 0);
                    $stmtSrvIns->execute([$id_ticket, $id_serv, $precio_s]);
                }
            }
        }
    }

    $pdo->commit();
    
    // 7. LIMPIEZA DE SESIÓN
    unset(
        $_SESSION['id_vuelo_ida'], 
        $_SESSION['id_vuelo_vuelta'], 
        $_SESSION['planes_pasajeros'], 
        $_SESSION['asientos_ida'], 
        $_SESSION['asientos_vuelta'], 
        $_SESSION['datos_pasajeros'], 
        $_SESSION['equipajes'], 
        $_SESSION['servicios'], 
        $_SESSION['pasajeros']
    );

    include_once '../../includes/header.php';
    ?>
    <link rel="stylesheet" href="css/proceso_compra/confirmar.css">

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
    <link rel="stylesheet" href="css/proceso_compra/confirmar.css">
    
    <div style="background: #fff5f5; color: #b91c1c; border: 2px solid #f87171; padding: 25px; margin: 30px auto; max-width: 900px; font-family: monospace; border-radius: 8px;">
        <h2 style="margin-top: 0; border-bottom: 2px solid #f87171; padding-bottom: 10px;">❌ Error Crítico en la Base de Datos</h2>
        <p><strong>Mensaje del Sistema (SQL):</strong></p>
        <div style="background: #7f1d1d; color: #fecaca; padding: 15px; border-radius: 5px;">
            <?= htmlspecialchars($e->getMessage()) ?>
        </div>
        <p style="margin-top: 20px;"><strong>Ubicación:</strong> Archivo <code><?= htmlspecialchars($e->getFile()) ?></code> en la línea <code><?= htmlspecialchars($e->getLine()) ?></code></p>
    </div>
    </body>
    </html>
    <?php
    exit();
}
?>