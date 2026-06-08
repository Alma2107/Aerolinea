<?php
require_once '../../config/conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['servicios'] = [];
    if (isset($_POST['servicios'])) {
        foreach ($_POST['servicios'] as $num_pasajero => $servicios_seleccionados) {
            $_SESSION['servicios'][$num_pasajero] = $servicios_seleccionados;
        }
    }
}

// Variables de sesión blindadas contra pérdidas momentáneas
$id_vuelo_actual = $_SESSION['id_vuelo'] ?? 1;
$id_plan_actual  = $_SESSION['id_plan'] ?? 1;
$cantidad_pasajeros = $_SESSION['pasajeros'] ?? 1;

// 1. Consultar datos del vuelo y el avión asignado
$stmtV = $pdo->prepare("
    SELECT v.numero_vuelo, v.precio_base_vuelo, a.id_avion, a.capacidad, a.modelo 
    FROM vuelos v
    INNER JOIN aviones a ON v.id_avion = a.id_avion
    WHERE v.id_vuelo = ?
");
$stmtV->execute([$id_vuelo_actual]); 
$vuelo_sel = $stmtV->fetch(PDO::FETCH_ASSOC);

if (!$vuelo_sel) {
    $vuelo_sel = [
        'numero_vuelo' => 'No definido', 
        'precio_base_vuelo' => 0.00, 
        'capacidad' => 174, 
        'modelo' => 'Avión Estándar'
    ];
}

// --- CÁLCULO DINÁMICO DE FILAS SEGÚN EL AVIÓN ASIGNADO ---
$capacidad_avion = (int)$vuelo_sel['capacidad']; 
$asientos_por_fila = 6; 
$total_filas = (int)ceil($capacidad_avion / $asientos_por_fila); 

// 2. Consultar qué asientos YA ESTÁN OCUPADOS
$stmtOcupados = $pdo->prepare("SELECT numero_asiento FROM tickets_detalle WHERE id_vuelo = ? AND numero_asiento IS NOT NULL");
$stmtOcupados->execute([$id_vuelo_actual]);
$asientos_ocupados = $stmtOcupados->fetchAll(PDO::FETCH_COLUMN);

// 3. Consultar datos del plan/tarifa
$stmtP = $pdo->prepare("SELECT nombre_plan, cargo_extra_plan FROM planes_tarifas WHERE id_plan = ?");
$stmtP->execute([$id_plan_actual]);
$plan_sel = $stmtP->fetch(PDO::FETCH_ASSOC);

if (!$plan_sel) {
    $plan_sel = ['nombre_plan' => 'Estándar', 'cargo_extra_plan' => 0.00];
}

$total_acumulado = ((float)$vuelo_sel['precio_base_vuelo'] + (float)$plan_sel['cargo_extra_plan']) * $cantidad_pasajeros;

include_once '../../includes/header.php';
?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; max-width: 1200px; margin: 20px auto; padding: 0 20px; font-family: sans-serif;">
    
    <form action="pasajeros.php" method="POST" id="form-asientos">
        
        <div style="display: flex; gap: 15px; background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 20px; font-size: 12px; justify-content: center; flex-wrap: wrap;">
            <div style="display:flex; align-items:center; gap:5px;"><span style="display:inline-block; width:15px; height:15px; background:#b3e5fc; border:1px solid #0288d1; border-radius:3px;"></span> Front Row</div>
            <div style="display:flex; align-items:center; gap:5px;"><span style="display:inline-block; width:15px; height:15px; background:#e1bee7; border:1px solid #7b1fa2; border-radius:3px;"></span> Business Class</div>
            <div style="display:flex; align-items:center; gap:5px;"><span style="display:inline-block; width:15px; height:15px; background:#c8e6c9; border:1px solid #388e3c; border-radius:3px;"></span> Espacio Extra</div>
            <div style="display:flex; align-items:center; gap:5px;"><span style="display:inline-block; width:15px; height:15px; background:#ffe0b2; border:1px solid #f57c00; border-radius:3px;"></span> Estándar Preferente</div>
            <div style="display:flex; align-items:center; gap:5px;"><span style="display:inline-block; width:15px; height:15px; background:#f5f5f5; border:1px solid #9e9e9e; border-radius:3px;"></span> Estándar / Económico</div>
            <div style="display:flex; align-items:center; gap:5px;"><span style="display:inline-block; width:15px; height:15px; background:#e0e0e0; border:1px solid #757575; border-radius:3px;"></span> Ocupado ❌</div>
        </div>

        <div class="card" style="border: 1px solid #ddd; padding: 20px; border-radius: 8px; background:#fff; margin-bottom: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h2 style="margin-top:0; color:#0056b3;">🗺️ Selección de Asientos</h2>
            <p style="font-size: 14px; color: #333; margin-bottom: 15px;">
                Estás comprando para <strong><?=$cantidad_pasajeros?> pasajero(s)</strong>. Por favor, seleccioná exactamente <strong><?=$cantidad_pasajeros?></strong> asiento(s) en el mapa.
            </p>
            <p style="font-size: 13px; color: #666; margin-bottom: 15px;">
                Vuelo: <strong><?=$vuelo_sel['numero_vuelo']?></strong> | Avión: <strong><?=$vuelo_sel['modelo']?> (<?=$vuelo_sel['capacidad']?> asientos)</strong>
            </p>
            
            <div style="max-height: 550px; overflow-y: auto; padding: 20px; border: 1px solid #ccc; border-radius: 30px 30px 10px 10px; background: #f0f4f8; max-width: 500px; margin: 0 auto;">
                
                <div style="background: #0288d1; color: white; text-align: center; padding: 10px; border-radius: 20px 20px 0 0; font-weight: bold; font-size: 13px; margin-bottom: 15px; letter-spacing: 1px;">
                    🛩️ FRENTE DEL AVIÓN / CABINA
                </div>

                <div class="mapa-asientos" style="display: grid; grid-template-columns: repeat(3, 1fr) 40px repeat(3, 1fr); gap: 6px 4px; align-items: center;">
                    
                    <?php 
                    for ($fila = 1; $fila <= $total_filas; $fila++): 
                        
                        // --- REGLAS DE CATEGORÍAS ---
                        if ($fila == 1) {
                            $categoria = "Front Row"; $cargo_extra = 5000.00;
                            echo '<div style="grid-column: 1 / -1; text-align: center; font-size: 11px; font-weight: bold; color: #0288d1; background: #fff; padding: 4px; border: 1px dashed #0288d1; margin: 5px 0; border-radius:4px;">Front Row (+$5,000.00)</div>';
                        } elseif ($fila >= 2 && $fila <= 5) {
                            $categoria = "Business"; $cargo_extra = 12000.00;
                            if ($fila == 2) echo '<div style="grid-column: 1 / -1; text-align: center; font-size: 11px; font-weight: bold; color: #7b1fa2; background: #fff; padding: 4px; border: 1px dashed #7b1fa2; margin: 5px 0; border-radius:4px;">Business Class (+$12,000.00)</div>';
                        } elseif ($fila == 6 || $fila == 7) {
                            $categoria = "Estándar Preferente"; $cargo_extra = 3000.00;
                            if ($fila == 6) echo '<div style="grid-column: 1 / -1; text-align: center; font-size: 11px; font-weight: bold; color: #f57c00; background: #fff; padding: 4px; border: 1px dashed #f57c00; margin: 5px 0; border-radius:4px;">Estándar Preferente (+$3,000.00)</div>';
                        } elseif ($fila == 12 || $fila == 13) { 
                            $categoria = "Espacio Extra"; $cargo_extra = 4500.00;
                            if ($fila == 12) echo '<div style="grid-column: 1 / -1; text-align: center; font-size: 11px; font-weight: bold; color: #2e7d32; background: #e8f5e9; padding: 4px; border: 1px dashed #388e3c; margin: 5px 0; border-radius:4px;">⚠️ Salida de Emergencia (+$4,500.00)</div>';
                        } else {
                            $categoria = "Estándar"; $cargo_extra = 0.00;
                            if ($fila == 8) echo '<div style="grid-column: 1 / -1; text-align: center; font-size: 11px; font-weight: bold; color: #555; background: #fff; padding: 4px; border: 1px dashed #ccc; margin: 5px 0; border-radius:4px;">Económico / Estándar</div>';
                        }

                        // --- LADO IZQUIERDO (A, B, C) ---
                        foreach(['A', 'B', 'C'] as $letra) {
                            $nombre_asiento = $fila . $letra;
                            imprimirAsientoAutomatico($nombre_asiento, $categoria, $cargo_extra, $asientos_ocupados);
                        }

                        // --- PASILLO ---
                        echo '<div style="text-align: center; font-weight: bold; color: #777; font-size: 11px; background:#fff; padding:3px 0; border-radius:4px;">' . $fila . '</div>';

                        // --- LADO DERECHO (D, E, F) ---
                        foreach(['D', 'E', 'F'] as $letra) {
                            $nombre_asiento = $fila . $letra;
                            imprimirAsientoAutomatico($nombre_asiento, $categoria, $cargo_extra, $asientos_ocupados);
                        }

                    endfor; 
                    ?>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 20px; border-top: 2px solid #ccc; padding-top: 15px;">
                    <div style="background: #cfd8dc; text-align: center; padding: 8px; font-size: 12px; font-weight: bold; color: #455a64; border-radius: 4px;">🚾 WC</div>
                    <div style="background: #cfd8dc; text-align: center; padding: 8px; font-size: 12px; font-weight: bold; color: #455a64; border-radius: 4px;">🚾 WC</div>
                </div>
            </div>
        </div>

        <button type="submit" style="background:#0056b3; color:#fff; padding:12px 25px; border:none; border-radius:4px; cursor:pointer; font-weight:bold; margin-top:10px; font-size:15px; width: 100%;">Continuar a Datos de Pasajeros</button>
    </form>

    <div style="border: 1px solid #0056b3; padding: 20px; border-radius: 8px; background: #f4f8ff; height: fit-content; position: sticky; top: 20px;">
        <h3 style="color:#0056b3; border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-top:0;">Resumen de tu Viaje</h3>
        <p><strong>Vuelo:</strong> <?=$vuelo_sel['numero_vuelo']?></p>
        <p><strong>Tarifa:</strong> <?=$plan_sel['nombre_plan']?></p>
        <p><strong>Pasajeros:</strong> x<?=$cantidad_pasajeros?></p>
        <p><strong>Asientos:</strong> <span id="asientos-lista" style="font-weight:bold; color:#0056b3;">Ninguno</span></p>
        
        <hr style="border:0; border-top:1px dashed #ccc; margin-top:15px;">
        <h4 style="margin:10px 0; display:flex; justify-content:space-between; font-size: 16px;">
            <span>Total Acumulado:</span>
            <span style="color:green;" id="total-vista" data-base="<?=$total_acumulado?>">$<?=number_format($total_acumulado, 2)?></span>
        </h4>
    </div>
</div>

<?php
// FUNCIÓN AUXILIAR: El input ahora es un CHECKBOX [] para permitir múltiples selecciones en el mismo mapa
function imprimirAsientoAutomatico($numero_asiento, $categoria, $cargo_extra, $asientos_ocupados) {
    $esta_ocupado = in_array($numero_asiento, $asientos_ocupados);

    $bg_color = "#f5f5f5"; 
    $border_color = "#9e9e9e";
    $text_color = "#424242";

    if ($esta_ocupado) {
        $bg_color = "#e0e0e0"; 
        $border_color = "#757575";
        $text_color = "#9e9e9e";
    } else {
        switch ($categoria) {
            case 'Front Row':
                $bg_color = "#b3e5fc"; $border_color = "#0288d1"; $text_color = "#01579b"; break;
            case 'Business':
                $bg_color = "#e1bee7"; $border_color = "#7b1fa2"; $text_color = "#4a148c"; break;
            case 'Estándar Preferente':
                $bg_color = "#ffe0b2"; $border_color = "#f57c00"; $text_color = "#e65100"; break;
            case 'Espacio Extra':
                $bg_color = "#c8e6c9"; $border_color = "#388e3c"; $text_color = "#1b5e20"; break;
        }
    }

    preg_match('/([A-Z])/', $numero_asiento, $matches);
    $letra_visual = $matches[1] ?? $numero_asiento;
    ?>
    <label class="asiento-box <?= $esta_ocupado ? 'asiento-ocupado' : 'asiento-disponible' ?>" 
           data-cargo="<?=$cargo_extra?>" 
           data-asiento="<?=$numero_asiento?>"
           style="border: 1px solid <?=$border_color?>; padding: 6px 1px; text-align: center; display: block; border-radius: 6px; background: <?=$bg_color?>; color: <?=$text_color?>; font-size: 10px; transition: all 0.15s; user-select: none; <?= $esta_ocupado ? 'cursor: not-allowed; opacity: 0.5;' : 'cursor: pointer;' ?>"
           title="<?=$categoria?> - Asiento <?=$numero_asiento?>">
        
        <input type="checkbox" name="asientos_seleccionados[]" value="<?=$numero_asiento?>" <?= $esta_ocupado ? 'disabled' : '' ?> style="display:none;" class="check-asiento">
        
        <strong><?=$letra_visual?></strong>
        <span style="font-size: 8px; display:block; scale: 0.9; font-weight: bold;">
            <?= $esta_ocupado ? '❌' : ($cargo_extra > 0 ? '+$' . number_format($cargo_extra, 0, '', '') : '•') ?>
        </span>
    </label>
    <?php
}
?>

<script>
    const LIMITE_PASAJEROS = <?=$cantidad_pasajeros?>;

    document.querySelectorAll('.asiento-disponible').forEach(box => {
        box.addEventListener('click', function(e) {
            e.preventDefault(); // Evitamos doble trigger del label/checkbox
            
            const checkbox = this.querySelector('.check-asiento');
            const seleccionados = document.querySelectorAll('.check-asiento:checked');

            if (checkbox.checked) {
                // Si ya estaba seleccionado, lo desmarcamos sin problemas
                checkbox.checked = false;
                restaurarEstilo(this);
            } else {
                // Si quiere seleccionar uno nuevo, validamos que no pase el límite de pasajeros
                if (seleccionados.length >= LIMITE_PASAJEROS) {
                    alert(`Ya seleccionaste los ${LIMITE_PASAJEROS} asientos correspondientes a tus pasajeros. Si querés cambiar, desmarcala uno primero.`);
                    return;
                }
                checkbox.checked = true;
                marcarEstilo(this);
            }

            actualizarResumenYTotal();
        });
    });

    function marcarEstilo(elemento) {
        elemento.style.filter = 'brightness(0.85)';
        elemento.style.boxShadow = '0 0 0 2px #000';
        elemento.style.transform = 'scale(0.95)';
    }

    function restaurarEstilo(elemento) {
        elemento.style.filter = 'none';
        elemento.style.boxShadow = 'none';
        elemento.style.transform = 'none';
    }

    function actualizarResumenYTotal() {
        const totalVista = document.getElementById('total-vista');
        const listaAsientos = document.getElementById('asientos-lista');
        let baseTotal = parseFloat(totalVista.getAttribute('data-base'));
        
        let nombresAsientos = [];
        let cargosExtras = 0;

        document.querySelectorAll('.check-asiento:checked').forEach(check => {
            const contenedor = check.closest('.asiento-box');
            nombresAsientos.push(contenedor.getAttribute('data-asiento'));
            cargosExtras += parseFloat(contenedor.getAttribute('data-cargo')) || 0;
        });

        // Actualizar texto lateral de asientos elegidos
        listaAsientos.textContent = nombresAsientos.length > 0 ? nombresAsientos.join(', ') : 'Ninguno';

        // Actualizar monto total en tiempo real
        let nuevoTotal = baseTotal + cargosExtras;
        totalVista.textContent = '$' + nuevoTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    // Validación antes de enviar el formulario
    document.getElementById('form-asientos').addEventListener('submit', function(e) {
        const seleccionados = document.querySelectorAll('.check-asiento:checked').length;
        if (seleccionados !== LIMITE_PASAJEROS) {
            e.preventDefault();
            alert(`Por favor, seleccioná exactamente ${LIMITE_PASAJEROS} asiento(s) antes de continuar.`);
        }
    });
</script>
</body>
</html>