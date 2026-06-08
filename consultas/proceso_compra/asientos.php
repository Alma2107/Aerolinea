<?php
require_once '../../config/conexion.php';
session_start();

// 1. GUARDAR LOS SERVICIOS ADICIONALES EN LA SESIÓN (Vienen de servicios.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['servicios'])) {
    $_SESSION['servicios'] = $_POST['servicios'];
}

// Variables de control de tramos blindadas
$id_vuelo_ida_global = isset($_SESSION['id_vuelo_ida']) ? (int)$_SESSION['id_vuelo_ida'] : 0;
$id_vuelo_vta_global = isset($_SESSION['id_vuelo_vuelta']) ? (int)$_SESSION['id_vuelo_vuelta'] : 0;

// DETERMINAR EL TRAMO REAL ACTIVO
$tramo_solicitado = $_GET['tramo'] ?? 'ida';

// Si no hay vuelo de ida, el tramo por obligación tiene que ser vuelta
if ($id_vuelo_ida_global === 0) {
    $tramo_actual = 'vuelta';
} else {
    $tramo_actual = $tramo_solicitado;
}

// Asignar el ID del vuelo correspondiente al tramo actual
if ($tramo_actual === 'vuelta' && $id_vuelo_vta_global > 0) {
    $id_vuelo_actual = $id_vuelo_vta_global;
    $titulo_tramo = "Vuelo de Regreso / Vuelta";
} else {
    $id_vuelo_actual = $id_vuelo_ida_global;
    $titulo_tramo = "Vuelo de Ida";
}

// Si ocurre un error raro y no hay vuelos cargados, evitar errores fatales
if ($id_vuelo_actual === 0) {
    header("Location: ../../index.php");
    exit();
}

$cantidad_pasajeros   = $_SESSION['pasajeros'] ?? 1;
$planes_seleccionados = $_SESSION['planes_pasajeros'] ?? [];
$equipajes_sesion     = $_SESSION['equipajes'] ?? [];

// 3. CONSULTAR DATOS DEL VUELO Y AVIÓN
$stmtV = $pdo->prepare("
    SELECT v.numero_vuelo, v.precio_base_vuelo, a.id_avion, a.capacidad, a.modelo 
    FROM vuelos v
    INNER JOIN aviones a ON v.id_avion = a.id_avion
    WHERE v.id_vuelo = ?
");
$stmtV->execute([$id_vuelo_actual]); 
$vuelo_sel = $stmtV->fetch(PDO::FETCH_ASSOC);

if (!$vuelo_sel) {
    $vuelo_sel = ['numero_vuelo' => 'No definido', 'precio_base_vuelo' => 0.00, 'capacidad' => 180, 'modelo' => 'Airbus A320'];
}

// 4. CONFIGURACIÓN DINÁMICA DE CABINA
$capacidad_avion = (int)$vuelo_sel['capacidad'];

if ($capacidad_avion <= 120) {
    $asientos_por_fila = 4;
    $config_columnas = "repeat(2, 1fr) 40px repeat(2, 1fr)"; 
    $lado_izquierdo = ['A', 'B'];
    $lado_derecho   = ['C', 'D'];
} else {
    $asientos_por_fila = 6;
    $config_columnas = "repeat(3, 1fr) 40px repeat(3, 1fr)"; 
    $lado_izquierdo = ['A', 'B', 'C'];
    $lado_derecho   = ['D', 'E', 'F'];
}
$total_filas = (int)ceil($capacidad_avion / $asientos_por_fila);

// 5. CONSULTAR ASIENTOS OCUPADOS EN ESTE VUELO
$stmtOcupados = $pdo->prepare("SELECT numero_asiento FROM tickets_detalle WHERE id_vuelo = ? AND numero_asiento IS NOT NULL");
$stmtOcupados->execute([$id_vuelo_actual]);
$asientos_ocupados = $stmtOcupados->fetchAll(PDO::FETCH_COLUMN);


// --- 6. RECALCULO DE PRECIO EXACTO HASTA EL MOMENTO ---
$planes_db = $pdo->query("SELECT id_plan, cargo_extra_plan FROM planes_tarifas")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
$equipajes_db = $pdo->query("SELECT id_tipo_equipaje, precio_unitario FROM tipos_equipaje")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);

$precio_ida_base = 0.00;
if ($id_vuelo_ida_global > 0) {
    $stmtI = $pdo->prepare("SELECT precio_base_vuelo FROM vuelos WHERE id_vuelo = ?");
    $stmtI->execute([$id_vuelo_ida_global]);
    $precio_ida_base = (float)($stmtI->fetchColumn() ?: 0.00);
}

$precio_vuelta_base = 0.00;
if ($id_vuelo_vta_global > 0) {
    $stmtR = $pdo->prepare("SELECT precio_base_vuelo FROM vuelos WHERE id_vuelo = ?");
    $stmtR->execute([$id_vuelo_vta_global]);
    $precio_vuelta_base = (float)($stmtR->fetchColumn() ?: 0.00);
}

$total_acumulado = 0;

for ($i = 1; $i <= $cantidad_pasajeros; $i++) {
    $total_acumulado += $precio_ida_base;
    $total_acumulado += $precio_vuelta_base;

    if ($id_vuelo_ida_global > 0 && isset($planes_seleccionados[$i]['ida'])) {
        $id_p_ida = $planes_seleccionados[$i]['ida'];
        $total_acumulado += (float)($planes_db[$id_p_ida]['cargo_extra_plan'] ?? 0);
    }
    if ($id_vuelo_vta_global > 0 && isset($planes_seleccionados[$i]['vuelta'])) {
        $id_p_vta = $planes_seleccionados[$i]['vuelta'];
        $total_acumulado += (float)($planes_db[$id_p_vta]['cargo_extra_plan'] ?? 0);
    }

    if (isset($equipajes_sesion[$i])) {
        foreach ($equipajes_sesion[$i] as $id_tipo => $cant) {
            $precio_eq = (float)($equipajes_db[$id_tipo]['precio_unitario'] ?? 0);
            $total_acumulado += ($precio_eq * (int)$cant);
        }
    }
}

include_once '../../includes/header.php';
?>

<link class="dinamico-css" rel="stylesheet" href="css/proceso_compra/asientos.css">

<div class="contenedor-asientos">
    
    <form action="procesar_asientos.php" method="POST" id="form-asientos">
        <input type="hidden" name="tramo" value="<?=$tramo_actual?>">

        <div class="leyenda-cabina">
            <div class="leyenda-item"><span class="leyenda-color color-front"></span> Front Row</div>
            <div class="leyenda-item"><span class="leyenda-color color-business"></span> Business Class</div>
            <div class="leyenda-item"><span class="leyenda-color color-extra"></span> Espacio Extra</div>
            <div class="leyenda-item"><span class="leyenda-color color-preferente"></span> Estándar Preferente</div>
            <div class="leyenda-item"><span class="leyenda-color color-estandar"></span> Estándar / Económico</div>
            <div class="leyenda-item"><span class="leyenda-color color-ocupado"></span> Ocupado ❌</div>
        </div>

        <div class="card">
            <h2>🗺️ Selección de Asientos (<span><?=$titulo_tramo?></span>)</h2>
            <p class="info-pasajeros">
                Estás comprando para <strong><?=$cantidad_pasajeros?> pasajero(s)</strong>. Seleccioná exactamente <strong><?=$cantidad_pasajeros?></strong> asiento(s).
            </p>
            <p class="info-vuelo">
                Vuelo: <strong><?=$vuelo_sel['numero_vuelo']?></strong> | Avión: <strong><?=$vuelo_sel['modelo']?> (<?=$vuelo_sel['capacidad']?> asientos)</strong>
            </p>
            
            <div class="cabina-scroll">
                <div class="cabina-techo">🛩️ FRENTE DEL AVIÓN / CABINA</div>

                <div class="mapa-asientos" style="display: grid; grid-template-columns: <?=$config_columnas?>; gap: 6px 4px; align-items: center;">
                    
                    <?php 
                    for ($fila = 1; $fila <= $total_filas; $fila++): 
                        
                        if ($fila == 1) {
                            $categoria = "Front Row"; $cargo_extra = 5000.00;
                            echo '<div class="separador-categoria sep-front">Front Row (+$5,000.00)</div>';
                        } elseif ($fila >= 2 && $fila <= 5) {
                            $categoria = "Business"; $cargo_extra = 12000.00;
                            if ($fila == 2) echo '<div class="separador-categoria sep-business">Business Class (+$12,000.00)</div>';
                        } elseif ($fila == 6 || $fila == 7) {
                            $categoria = "Estándar Preferente"; $cargo_extra = 3000.00;
                            if ($fila == 6) echo '<div class="separador-categoria sep-preferente">Estándar Preferente (+$3,000.00)</div>';
                        } elseif ($fila == 12 || $fila == 13) { 
                            $categoria = "Espacio Extra"; $cargo_extra = 4500.00;
                            if ($fila == 12) echo '<div class="separador-categoria sep-extra">⚠️ Salida de Emergencia (+$4,500.00)</div>';
                        } else {
                            $categoria = "Estándar"; $cargo_extra = 0.00;
                            if ($fila == 8) echo '<div class="separador-categoria sep-estandar">Económico / Estándar</div>';
                        }

                        foreach($lado_izquierdo as $letra) {
                            $nombre_asiento = $fila . $letra;
                            imprimirAsientoAutomatico($nombre_asiento, $categoria, $cargo_extra, $asientos_ocupados);
                        }

                        echo '<div class="numero-fila">' . $fila . '</div>';

                        foreach($lado_derecho as $letra) {
                            $nombre_asiento = $fila . $letra;
                            imprimirAsientoAutomatico($nombre_asiento, $categoria, $cargo_extra, $asientos_ocupados);
                        }

                    endfor; 
                    ?>
                </div>

                <div class="baños-contenedor">
                    <div class="baño">🚾 WC</div>
                    <div class="baño">🚾 WC</div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-continuar">
            <?= ($tramo_actual === 'ida' && $id_vuelo_vta_global > 0) ? 'Siguiente: Elegir Asientos de Vuelta ➡️' : 'Continuar a Datos de Pasajeros ➡️' ?>
        </button>
    </form>

    <div class="sidebar-resumen">
        <h3>Resumen de tu Viaje</h3>
        <p><strong>Tramo actual:</strong> <span class="destacado" style="text-transform: uppercase; color: #d9534f;"><?=$tramo_actual?></span></p>
        <p><strong>Vuelo:</strong> <?=$vuelo_sel['numero_vuelo']?></p>
        <p><strong>Pasajeros:</strong> x<?=$cantidad_pasajeros?></p>
        <p><strong>Asientos tramo actual:</strong> <span id="asientos-lista" style="font-weight:bold; color:#0056b3;">Ninguno</span></p>
        
        <div id="contenedor-asientos-dinamicos-detalle"></div>

        <hr class="separador">
        <h4 class="total-contenedor">
            <span>Total Acumulado:</span>
            <span class="total-precio" id="total-vista" data-base="<?=$total_acumulado?>">$<?=number_format($total_acumulado, 2)?></span>
        </h4>
    </div>
</div>

<?php
function imprimirAsientoAutomatico($numero_asiento, $categoria, $cargo_extra, $asientos_ocupados) {
    $esta_ocupado = in_array($numero_asiento, $asientos_ocupados);

    $clase_categoria = "asiento-cat-estandar";
    if (!$esta_ocupado) {
        switch ($categoria) {
            case 'Front Row': $clase_categoria = "asiento-cat-front"; break;
            case 'Business': $clase_categoria = "asiento-cat-business"; break;
            case 'Estándar Preferente': $clase_categoria = "asiento-cat-preferente"; break;
            case 'Espacio Extra': $clase_categoria = "asiento-cat-extra"; break;
        }
    }

    $clase_estado = $esta_ocupado ? 'asiento-ocupado' : 'asiento-disponible';

    preg_match('/([A-Z])/', $numero_asiento, $matches);
    $letra_visual = $matches[1] ?? $numero_asiento;
    ?>
    <label class="asiento-box <?= $clase_categoria ?> <?= $clase_estado ?>" 
            data-cargo="<?=$cargo_extra?>" 
            data-asiento="<?=$numero_asiento?>"
            title="<?=$categoria?> - Asiento <?=$numero_asiento?>">
        
        <input type="checkbox" name="asientos_seleccionados[]" value="<?=$numero_asiento?>" <?= $esta_ocupado ? 'disabled' : '' ?> style="display:none;" class="check-asiento">
        
        <strong><?=$letra_visual?></strong>
        <span class="precio-tag">
            <?= $esta_ocupado ? '❌' : ($cargo_extra > 0 ? '+$' . number_format($cargo_extra, 0, '', '') : '•') ?>
        </span>
    </label>
    <?php
}
?>

<script>
    window.cantidadPasajeros = <?=$cantidad_pasajeros?>;
</script>
<script src="js/proceso_compra/asientos.js"></script>
</body>
</html>