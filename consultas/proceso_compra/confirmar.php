<?php
require_once '../../config/conexion.php';
require_once 'flujo_helpers.php';
session_start();

$pageStyles = ['../../css/proceso_compra/pago.css', '../../css/reservas.css'];
require_once '../../includes/header.php';

$datosPasajeros = $_SESSION['datos_pasajeros'] ?? [];
$idsVuelos = normalizarIdsVuelos($_SESSION['id_vuelo'] ?? []);
$idPlan = (int)($_SESSION['id_plan'] ?? 0);
$idAsiento = (int)($_SESSION['id_asiento'] ?? 0);
$serviciosElegidos = $_SESSION['servicios_elegidos'] ?? [];
$equipajesElegidos = $_SESSION['equipajes_elegidos'] ?? [];
$codigoPromo = strtoupper(trim($_SESSION['codigo_promo'] ?? ''));

function mostrarErrorConfirmacion(string $titulo, string $mensaje): void {
    echo '<div class="contenedor-pago"><div class="main-content"><div class="card" style="max-width:700px; margin:60px auto; text-align:center;">';
    echo '<p class="eyebrow">Error de acceso</p>';
    echo '<h2>' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . '</h2>';
    echo '<p>' . htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') . '</p>';
    echo '<a href="../../index.php#buscador" class="btn-pagar" style="display:inline-block; text-decoration:none; margin-top:20px;">Volver al buscador</a>';
    echo '</div></div></div>';
}

function generarPnr(PDO $pdo): string {
    do {
        $pnr = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets_detalle WHERE codigo_reserva_pnr = ?");
        $stmt->execute([$pnr]);
    } while ((int)$stmt->fetchColumn() > 0);

    return $pnr;
}

if (empty($idsVuelos) || $idPlan <= 0 || $idAsiento <= 0 || empty($datosPasajeros)) {
    mostrarErrorConfirmacion('Faltan datos para confirmar la reserva', 'Completa vuelo, equipaje, servicios, asiento y pasajeros antes de finalizar el pago.');
    include_once '../../includes/footer.php';
    echo '</body></html>';
    exit;
}

try {
    $pdo->beginTransaction();

    $placeholders = implode(',', array_fill(0, count($idsVuelos), '?'));
    $stmtVuelos = $pdo->prepare("SELECT id_vuelo, precio_base_vuelo, destino_iata FROM vuelos WHERE id_vuelo IN ($placeholders)");
    $stmtVuelos->execute($idsVuelos);
    $vuelos = $stmtVuelos->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

    if (count($vuelos) !== count($idsVuelos)) {
        throw new RuntimeException('Uno de los vuelos seleccionados ya no esta disponible.');
    }

    $stmtPlan = $pdo->prepare("SELECT cargo_extra_plan FROM planes_tarifas WHERE id_plan = ?");
    $stmtPlan->execute([$idPlan]);
    $cargoPlan = (float)($stmtPlan->fetchColumn() ?: 0);

    $stmtAsiento = $pdo->prepare("SELECT numero_asiento, cargo_extra FROM asientos_avion WHERE id_asiento_avion = ? LIMIT 1");
    $stmtAsiento->execute([$idAsiento]);
    $asientoData = $stmtAsiento->fetch(PDO::FETCH_ASSOC);
    $numeroAsiento = $asientoData['numero_asiento'] ?? null;
    $cargoAsiento = (float)($asientoData['cargo_extra'] ?? 0);

    $preciosEquipaje = [];
    $idsEquipajes = [];
    foreach ($equipajesElegidos as $pasajeroEquipajes) {
        foreach ((array)$pasajeroEquipajes as $idEquipaje) {
            $idEquipaje = (int)$idEquipaje;
            if ($idEquipaje > 0) {
                $idsEquipajes[] = $idEquipaje;
            }
        }
    }
    $idsEquipajes = array_values(array_unique($idsEquipajes));
    if (!empty($idsEquipajes)) {
        $eqPlaceholders = implode(',', array_fill(0, count($idsEquipajes), '?'));
        $stmtEquipajes = $pdo->prepare("SELECT id_tipo_equipaje, precio_unitario FROM tipos_equipaje WHERE id_tipo_equipaje IN ($eqPlaceholders)");
        $stmtEquipajes->execute($idsEquipajes);
        $preciosEquipaje = $stmtEquipajes->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    $idsServicios = [];
    foreach ($serviciosElegidos as $serviciosPasajero) {
        foreach ((array)$serviciosPasajero as $idServicio) {
            $idsServicios[] = (int)$idServicio;
        }
    }
    $idsServicios = array_values(array_unique(array_filter($idsServicios)));
    $preciosServicios = [];
    if (!empty($idsServicios)) {
        $servPlaceholders = implode(',', array_fill(0, count($idsServicios), '?'));
        $stmtServicios = $pdo->prepare("SELECT id_servicio, precio_servicio FROM servicios_adicionales WHERE id_servicio IN ($servPlaceholders)");
        $stmtServicios->execute($idsServicios);
        $preciosServicios = $stmtServicios->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    $montoTotal = 0.00;
    foreach ($datosPasajeros as $indicePasajero => $_pasajero) {
        foreach ($idsVuelos as $idVuelo) {
            $precioBase = (float)$vuelos[$idVuelo]['precio_base_vuelo'];
            $precioVuelo = precioTramoPorPasajero($precioBase, $indicePasajero, count($datosPasajeros), $codigoPromo, (string)$vuelos[$idVuelo]['destino_iata']);
            $montoTotal += $precioVuelo + ($precioVuelo > 0 ? $cargoPlan + $cargoAsiento : 0.0);
        }
        foreach ((array)($equipajesElegidos[$indicePasajero] ?? []) as $idEquipaje) {
            $montoTotal += $codigoPromo === 'EQUIPAJEGRATIS' ? 0 : (float)($preciosEquipaje[(int)$idEquipaje] ?? 0);
        }
        foreach (($serviciosElegidos[$indicePasajero] ?? []) as $idServicio) {
            $montoTotal += (float)($preciosServicios[$idServicio] ?? 0);
        }
    }

    $idCliente = (int)($_SESSION['usuario_id'] ?? 0);
    if ($idCliente <= 0) {
        throw new RuntimeException('Necesitas iniciar sesion para confirmar la compra.');
    }

    $stmtOrden = $pdo->prepare("INSERT INTO compras_ordenes (id_cliente, fecha_compra, monto_total_pagado, id_metodo_pago) VALUES (:cliente, NOW(), :monto, 1)");
    $stmtOrden->execute([
        'cliente' => $idCliente,
        'monto' => $montoTotal,
    ]);
    $idOrden = (int)$pdo->lastInsertId();

    $pnrsGenerados = [];

    foreach ($datosPasajeros as $indicePasajero => $pasajero) {
        $stmtPasajero = $pdo->prepare("
            INSERT INTO pasajeros (tipo_documento, numero_documento, nombre, apellido, fecha_nacimiento, asistencia_especial)
            VALUES ('DNI', :doc, :nombre, :apellido, :fecha_nacimiento, :asistencia)
            ON DUPLICATE KEY UPDATE id_pasajero = LAST_INSERT_ID(id_pasajero)
        ");
        $stmtPasajero->execute([
            'doc' => $pasajero['doc'],
            'nombre' => $pasajero['nombre'],
            'apellido' => $pasajero['apellido'],
            'fecha_nacimiento' => $pasajero['fnac'],
            'asistencia' => !empty($pasajero['asistencia']) ? 1 : 0,
        ]);
        $idPasajero = (int)$pdo->lastInsertId();

        foreach ($idsVuelos as $idVuelo) {
            $pnr = generarPnr($pdo);
            $precioBase = (float)$vuelos[$idVuelo]['precio_base_vuelo'];
            $precioTramo = precioTramoPorPasajero($precioBase, $indicePasajero, count($datosPasajeros), $codigoPromo, (string)$vuelos[$idVuelo]['destino_iata']);
            if ($precioTramo > 0) {
                $precioTramo += $cargoPlan + $cargoAsiento;
            }

            $stmtTicket = $pdo->prepare("
                INSERT INTO tickets_detalle (id_orden, id_vuelo, id_pasajero, numero_asiento, id_plan, codigo_reserva_pnr, precio_tramo_pagado)
                VALUES (:id_orden, :id_vuelo, :id_pasajero, :numero_asiento, :id_plan, :pnr, :precio)
            ");
            $stmtTicket->execute([
                'id_orden' => $idOrden,
                'id_vuelo' => $idVuelo,
                'id_pasajero' => $idPasajero,
                'numero_asiento' => $numeroAsiento,
                'id_plan' => $idPlan,
                'pnr' => $pnr,
                'precio' => $precioTramo,
            ]);
            $idTicket = (int)$pdo->lastInsertId();
            $pnrsGenerados[] = $pnr;

            foreach ((array)($equipajesElegidos[$indicePasajero] ?? []) as $idEquipaje) {
                $precioEquipaje = $codigoPromo === 'EQUIPAJEGRATIS' ? 0 : (float)($preciosEquipaje[(int)$idEquipaje] ?? 0);
                $stmtEquipaje = $pdo->prepare("INSERT INTO ticket_equipajes (id_ticket, id_tipo_equipaje, cantidad, precio_pagado) VALUES (:ticket, :equipaje, 1, :precio)");
                $stmtEquipaje->execute([
                    'ticket' => $idTicket,
                    'equipaje' => (int)$idEquipaje,
                    'precio' => $precioEquipaje,
                ]);
            }

            foreach (($serviciosElegidos[$indicePasajero] ?? []) as $idServicio) {
                $stmtServicio = $pdo->prepare("INSERT INTO ticket_servicios (id_ticket, id_servicio, precio_servicio_pagado) VALUES (:ticket, :servicio, :precio)");
                $stmtServicio->execute([
                    'ticket' => $idTicket,
                    'servicio' => (int)$idServicio,
                    'precio' => (float)($preciosServicios[$idServicio] ?? 0),
                ]);
            }
        }
    }

    $pdo->commit();

    unset(
        $_SESSION['id_vuelo'],
        $_SESSION['id_plan'],
        $_SESSION['id_asiento'],
        $_SESSION['datos_pasajeros'],
        $_SESSION['servicios_elegidos'],
        $_SESSION['equipajes_elegidos'],
        $_SESSION['promo_equipaje_gratis'],
        $_SESSION['codigo_promo']
    );

    $primerPnr = $pnrsGenerados[0] ?? '';

    echo '<div class="contenedor-pago"><div class="main-content"><div class="card" style="max-width:760px; margin:60px auto; text-align:center;">';
    echo '<p class="eyebrow">Reserva completada</p>';
    echo '<h1>Tu reserva fue confirmada</h1>';
    echo '<p style="margin:15px 0;">Codigo PNR principal: <strong>' . htmlspecialchars($primerPnr, ENT_QUOTES, 'UTF-8') . '</strong></p>';
    echo '<p style="margin:15px 0;">Total pagado: <strong>$' . number_format($montoTotal, 2, ',', '.') . '</strong></p>';
    echo '<div class="local-boarding-code" aria-label="Codigo de embarque">' . htmlspecialchars(chunk_split($primerPnr, 1, ' '), ENT_QUOTES, 'UTF-8') . '</div>';
    echo '<p style="margin:15px 0;">Usa este codigo para consultar la reserva, hacer check-in o abrir tu pasaje virtual.</p>';
    echo '<a href="../reservas/pasaje.php?pnr=' . urlencode($primerPnr) . '" class="btn-pagar" style="display:inline-block; text-decoration:none; margin-top:12px;">Ver pasaje virtual</a>';
    echo '<a href="../reservas/mis_viajes.php" class="btn-pagar" style="display:inline-block; text-decoration:none; margin-top:12px; background:#ff9f1c;">Ir a Mis viajes</a>';
    echo '</div></div></div>';
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo '<div class="contenedor-pago"><div class="main-content"><div class="card" style="max-width:700px; margin:60px auto; text-align:center;">';
    echo '<p class="eyebrow">Error</p>';
    echo '<h2>No se pudo completar la reserva</h2>';
    echo '<p>Revisa que el flujo anterior este completo e intenta de nuevo.</p>';
    echo '<p style="margin-top:12px; color:#b91c1c;">' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    echo '<a href="../../index.php#buscador" class="btn-pagar" style="display:inline-block; text-decoration:none; margin-top:20px;">Volver al buscador</a>';
    echo '</div></div></div>';
}
?>

<?php include_once '../../includes/footer.php'; ?>
</body>
</html>
