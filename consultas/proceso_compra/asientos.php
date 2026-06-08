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

$id_vuelo_actual = $_SESSION['id_vuelo'] ?? 1;

// 1. Consultar datos del vuelo y avión
$stmtV = $pdo->prepare("
    SELECT v.numero_vuelo, v.precio_base_vuelo, a.capacidad, a.modelo 
    FROM vuelos v
    INNER JOIN aviones a ON v.id_avion = a.id_avion
    WHERE v.id_vuelo = ?
");
$stmtV->execute([$id_vuelo_actual]); 
$vuelo_sel = $stmtV->fetch(PDO::FETCH_ASSOC);

// 2. Consultar qué asientos YA ESTÁN OCUPADOS en ESTE vuelo específico
$stmtOcupados = $pdo->prepare("SELECT id_asiento_avion FROM tickets_detalle WHERE id_vuelo = ? AND id_asiento_avion IS NOT NULL");
$stmtOcupados->execute([$id_vuelo_actual]);
$asientos_ocupados = $stmtOcupados->fetchAll(PDO::FETCH_COLUMN);

// 3. Consultar datos del plan
$stmtP = $pdo->prepare("SELECT nombre_plan, cargo_extra_plan FROM planes_tarifas WHERE id_plan = ?");
$stmtP->execute([$_SESSION['id_plan'] ?? 1]);
$plan_sel = $stmtP->fetch(PDO::FETCH_ASSOC);

$cantidad_pasajeros = $_SESSION['pasajeros'] ?? 1;
$total_acumulado = ($vuelo_sel['precio_base_vuelo'] + $plan_sel['cargo_extra_plan']) * $cantidad_pasajeros;

$capacidad_avion = $vuelo_sel['capacidad'] ?? 186; 

// Configuración fija de letras (Distribución estándar 3 y 3 con pasillo en el medio)
$letras_izquierda = ['A', 'B', 'C'];
$letras_derecha   = ['D', 'E', 'F'];
$asientos_por_fila = 6;
$total_filas = ceil($capacidad_avion / $asientos_por_fila);

include_once '../../includes/header.php';
?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; max-width: 1200px; margin: 20px auto; padding: 0 20px; font-family: sans-serif;">
    
    <form action="pasajeros.php" method="POST">
        
        <div style="display: flex; gap: 15px; background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 20px; font-size: 12px; justify-content: center; flex-wrap: wrap;">
            <div style="display:flex; align-items:center; gap:5px;"><span style="display:inline-block; width:15px; height:15px; background:#b3e5fc; border:1px solid #0288d1; border-radius:3px;"></span> Primera Fila / Front Row</div>
            <div style="display:flex; align-items:center; gap:5px;"><span style="display:inline-block; width:15px; height:15px; background:#e1bee7; border:1px solid #7b1fa2; border-radius:3px;"></span> Business Class</div>
            <div style="display:flex; align-items:center; gap:5px;"><span style="display:inline-block; width:15px; height:15px; background:#c8e6c9; border:1px solid #388e3c; border-radius:3px;"></span> Espacio Extra / Legroom</div>
            <div style="display:flex; align-items:center; gap:5px;"><span style="display:inline-block; width:15px; height:15px; background:#f5f5f5; border:1px solid #9e9e9e; border-radius:3px;"></span> Económico / Estándar</div>
            <div style="display:flex; align-items:center; gap:5px;"><span style="display:inline-block; width:15px; height:15px; background:#ffe0b2; border:1px solid #f57c00; border-radius:3px;"></span> Ventanilla Preferencial</div>
            <div style="display:flex; align-items:center; gap:5px;"><span style="display:inline-block; width:15px; height:15px; background:#e0e0e0; border:1px solid #757575; border-radius:3px;"></span> Ocupado ❌</div>
        </div>

        <?php for($i = 1; $i <= $cantidad_pasajeros; $i++): ?>
            <div class="card" style="border: 1px solid #ddd; padding: 20px; border-radius: 8px; background:#fff; margin-bottom: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <h2 style="margin-top:0; color:#0056b3;">👤 Pasajero #<?=$i?>: Elegí tu asiento</h2>
                <p style="font-size: 13px; color: #666; margin-bottom: 15px;">
                    Vuelo: <strong><?=$vuelo_sel['numero_vuelo']?></strong> | Avión: <strong><?=$vuelo_sel['modelo']?></strong>
                </p>
                
                <div style="max-height: 550px; overflow-y: auto; padding: 20px; border: 1px solid #ccc; border-radius: 30px 30px 10px 10px; background: #f0f4f8; max-width: 500px; margin: 0 auto;">
                    
                    <div style="background: #0288d1; color: white; text-align: center; padding: 10px; border-radius: 20px 20px 0 0; font-weight: bold; font-size: 13px; margin-bottom: 15px; letter-spacing: 1px;">
                        🛩️ FRENTE DEL AVIÓN / CABINA
                    </div>

                    <div class="mapa-asientos" style="display: grid; grid-template-columns: repeat(3, 1fr) 40px repeat(3, 1fr); gap: 6px 4px; align-items: center;">
                        
                        <?php 
                        for($fila = 1; $fila <= $total_filas; $fila++): 
                            
                            // Inserción visual de Divisiones / Carteles como en la Imagen 1
                            if ($fila == 1) {
                                echo '<div style="grid-column: 1 / -1; text-align: center; font-size: 11px; font-weight: bold; color: #555; background: #fff; padding: 4px; border: 1px dashed #ccc; margin: 5px 0;">Front Row</div>';
                            } elseif ($fila == 2) {
                                echo '<div style="grid-column: 1 / -1; text-align: center; font-size: 11px; font-weight: bold; color: #555; background: #fff; padding: 4px; border: 1px dashed #ccc; margin: 5px 0;">Business Class</div>';
                            } elseif ($fila == 6) {
                                echo '<div style="grid-column: 1 / -1; text-align: center; font-size: 11px; font-weight: bold; color: #555; background: #fff; padding: 4px; border: 1px dashed #ccc; margin: 5px 0;">Standard Rows</div>';
                            } elseif ($fila == 15) {
                                echo '<div style="grid-column: 1 / -1; text-align: center; font-size: 11px; font-weight: bold; color: #555; background: #fff; padding: 4px; border: 1px dashed #ccc; margin: 5px 0;">⚠️ Extra Legroom (Salida de Emergencia)</div>';
                            }

                            // --- LADO IZQUIERDO (Asientos A, B, C) ---
                            foreach($letras_izquierda as $letra) {
                                imprimirAsiento($fila, $letra, $id_vuelo_actual, $asientos_ocupados, $i);
                            }

                            // --- EL PASILLO CENTRAL ---
                            echo '<div style="text-align: center; font-weight: bold; color: #777; font-size: 11px;">' . $fila . '</div>';

                            // --- LADO DERECHO (Asientos D, E, F) ---
                            foreach($letras_derecha as $letra) {
                                imprimirAsiento($fila, $letra, $id_vuelo_actual, $asientos_ocupados, $i);
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
        <?php endfor; ?>

        <button type="submit" style="background:#0056b3; color:#fff; padding:12px 25px; border:none; border-radius:4px; cursor:pointer; font-weight:bold; margin-top:10px; font-size:15px; width: 100%;">Continuar a Datos de Pasajeros</button>
    </form>

    <div style="border: 1px solid #0056b3; padding: 20px; border-radius: 8px; background: #f4f8ff; height: fit-content; position: sticky; top: 20px;">
        <h3 style="color:#0056b3; border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-top:0;">Resumen de tu Viaje</h3>
        <p><strong>Vuelo:</strong> <?=$vuelo_sel['numero_vuelo']?></p>
        <p><strong>Tarifa:</strong> <?=$plan_sel['nombre_plan']?></p>
        <p><strong>Pasajeros:</strong> x<?=$cantidad_pasajeros?></p>
        
        <hr style="border:0; border-top:1px dashed #ccc; margin-top:15px;">
        <h4 style="margin:10px 0; display:flex; justify-content:space-between; font-size: 16px;">
            <span>Total Acumulado:</span>
            <span style="color:green;" id="total-vista" data-base="<?=$total_acumulado?>">$<?=number_format($total_acumulado, 2)?></span>
        </h4>
    </div>
</div>

<?php
// FUNCIÓN AUXILIAR: Encargada de renderizar cada asiento individual aplicando las reglas de negocio
function imprimirAsiento($fila, $letra, $id_vuelo, $asientos_ocupados, $pasajero_index) {
    $nombre_asiento = $fila . $letra;
    $esta_ocupado = in_array($nombre_asiento, $asientos_ocupados);

    $cargo_extra = 0;
    $categoria = "Estándar";
    $bg_color = "#f5f5f5"; 
    $border_color = "#9e9e9e";
    $text_color = "#424242";

    if ($esta_ocupado) {
        $categoria = "Ocupado";
        $bg_color = "#e0e0e0"; 
        $border_color = "#757575";
        $text_color = "#9e9e9e";
    } else {
        // Regla 1: Fila 1 (Front Row)
        if ($fila == 1) {
            $categoria = "Front Row";
            $cargo_extra = 20000;
            $bg_color = "#b3e5fc";
            $border_color = "#0288d1";
            $text_color = "#01579b";
        }
        // Regla 2: Filas 2 a 5 (Business)
        elseif ($fila >= 2 && $fila <= 5) {
            $categoria = "Business";
            $cargo_extra = 14000;
            $bg_color = "#e1bee7";
            $border_color = "#7b1fa2";
            $text_color = "#4a148c";
        }
        // Regla 3: Fila 15 (Salida de emergencia / Extra Legroom)
        elseif ($fila == 15) {
            $categoria = "Extra Space";
            $cargo_extra = 9500;
            $bg_color = "#c8e6c9";
            $border_color = "#388e3c";
            $text_color = "#1b5e20";
        }
        // Regla 4: Ventanillas del resto del avión
        elseif ($letra === 'A' || $letra === 'F') {
            $categoria = "Ventanilla";
            $cargo_extra = 3500;
            $bg_color = "#ffe0b2";
            $border_color = "#f57c00";
            $text_color = "#e65100";
        }
    }
    ?>
    <label class="asiento-box asiento-p<?=$pasajero_index?> <?= $esta_ocupado ? 'asiento-ocupado' : '' ?>" 
           style="border: 1px solid <?=$border_color?>; padding: 6px 1px; text-align: center; display: block; border-radius: 6px; background: <?=$bg_color?>; color: <?=$text_color?>; font-size: 10px; transition: all 0.15s; user-select: none; <?= $esta_ocupado ? 'cursor: not-allowed; opacity: 0.5;' : 'cursor: pointer;' ?>"
           title="<?=$categoria?> - <?=$nombre_asiento?>">
        
        <input type="radio" name="id_asiento[<?=$pasajero_index?>]" value="<?=$nombre_asiento?>" <?= $esta_ocupado ? 'disabled' : 'required' ?> style="display:none;" class="radio-asiento">
        
        <strong><?=$letra?></strong>
        <span style="font-size: 8px; display:block; scale: 0.9; font-weight: bold;">
            <?= $esta_ocupado ? '❌' : ($cargo_extra > 0 ? '+$' . number_format($cargo_extra, 0, '', '') : '•') ?>
        </span>
    </label>
    <?php
}
?>

<script>
    // Control clicks e interactividad visual
    <?php for($i = 1; $i <= $cantidad_pasajeros; $i++): ?>
        document.querySelectorAll('.asiento-p<?=$i?>').forEach(box => {
            box.addEventListener('click', function() {
                if (this.classList.contains('asiento-ocupado')) return;

                document.querySelectorAll('.asiento-p<?=$i?>').forEach(b => {
                    if(!b.classList.contains('asiento-ocupado')){
                        b.style.filter = 'none';
                        b.style.boxShadow = 'none';
                        b.style.transform = 'none';
                    }
                });
                
                // Resaltado de selección sin romper los colores de fondo nativos de las imágenes
                this.style.filter = 'brightness(0.85)';
                this.style.boxShadow = '0 0 0 2px #000';
                this.style.transform = 'scale(0.95)';
                
                this.querySelector('.radio-asiento').checked = true;
                calcularTotalDinamico();
            });
        });
    <?php endfor; ?>

    function calcularTotalDinamico() {
        const totalVista = document.getElementById('total-vista');
        let subtotal = parseFloat(totalVista.getAttribute('data-base'));
        
        document.querySelectorAll('.radio-asiento:checked').forEach(radio => {
            const contenedor = radio.closest('.asiento-box');
            // Buscamos el valor numérico interno del string de cargo extra
            const textoPrecio = contenedor.querySelector('span').textContent;
            if(textoPrecio.includes('+$')) {
                const precioExtra = parseFloat(textoPrecio.replace('+$', '')) || 0;
                subtotal += precioExtra;
            }
        });
        
        totalVista.textContent = '$' + subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
</script>
</body>
</html>