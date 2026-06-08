<?php
session_start();
require_once '../../config/conexion.php';

// Si venís del paso anterior, capturamos los datos de los pasajeros
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Si en la pantalla anterior el name era 'asientos_seleccionados' se guarda en sesión
    if (isset($_POST['asientos_seleccionados'])) {
        $_SESSION['asientos_seleccionados'] = $_POST['asientos_seleccionados'];
    }
    if (isset($_POST['pasajero'])) {
        $_SESSION['datos_pasajeros'] = $_POST['pasajero'];
    }
}

// Variables de sesión blindadas
$id_vuelo_actual = $_SESSION['id_vuelo'] ?? 1;
$id_plan_actual  = $_SESSION['id_plan'] ?? 1;
$cantidad_pasajeros = $_SESSION['pasajeros'] ?? 1;

// Consultas Historial Final (Vuelo y Plan)
$stmtV = $pdo->prepare("SELECT numero_vuelo, precio_base_vuelo FROM vuelos WHERE id_vuelo = ?");
$stmtV->execute([$id_vuelo_actual]);
$vuelo_sel = $stmtV->fetch(PDO::FETCH_ASSOC);

// Control de contingencia si no encuentra el vuelo
if (!$vuelo_sel) {
    $vuelo_sel = ['numero_vuelo' => 'No definido', 'precio_base_vuelo' => 0.00];
}

$stmtP = $pdo->prepare("SELECT nombre_plan, cargo_extra_plan FROM planes_tarifas WHERE id_plan = ?");
$stmtP->execute([$id_plan_actual]);
$plan_sel = $stmtP->fetch(PDO::FETCH_ASSOC);

// Control de contingencia si no encuentra el plan
if (!$plan_sel) {
    $plan_sel = ['nombre_plan' => 'Estándar', 'cargo_extra_plan' => 0.00];
}

// Subtotal base multiplicado por la cantidad de pasajeros
$total_final = ((float)$vuelo_sel['precio_base_vuelo'] + (float)$plan_sel['cargo_extra_plan']) * $cantidad_pasajeros;

// Traer métodos de pago
$metodos = $pdo->query("SELECT id_metodo_pago, nombre_metodo, banco_proveedor FROM metodos_pago")->fetchAll(PDO::FETCH_ASSOC);

include_once '../../includes/header.php';
?>
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; max-width: 1200px; margin: 20px auto; padding: 0 20px; font-family: sans-serif;">
    
    <form action="confirmar.php" method="POST">
        <div class="card" style="border: 1px solid #ddd; padding: 20px; border-radius: 8px; background:#fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h2>💳 Método de Pago</h2>
            <div style="margin: 15px 0; display:flex; flex-direction:column; gap:8px;">
                <?php foreach($metodos as $index => $m): ?>
                    <label style="cursor:pointer; padding: 6px; border: 1px solid #eee; border-radius: 4px; display: flex; align-items: center; gap: 8px;">
                        <input type="radio" name="id_metodo_pago" value="<?=$m['id_metodo_pago']?>" <?=$index === 0 ? 'checked' : ''?>> 
                        <div>
                            <strong><?=$m['nombre_metodo']?></strong> — <span style="color:#555; font-size:13px;"><?=$m['banco_proveedor']?></span>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
            <div style="display:flex; flex-direction:column; gap:10px; margin-top:15px;">
                <input type="text" placeholder="Número de Tarjeta" required style="padding:10px; border:1px solid #ccc; border-radius:4px;">
                <input type="text" placeholder="Nombre del Titular" required style="padding:10px; border:1px solid #ccc; border-radius:4px;">
            </div>
        </div>
        <button type="submit" style="background:#28a745; color:#fff; padding:12px 25px; border:none; border-radius:4px; cursor:pointer; font-weight:bold; margin-top:20px; font-size:16px; width: 100%;">Finalizar Reserva y Pagar</button>
    </form>

    <div style="border: 2px solid green; padding: 20px; border-radius: 8px; background: #f4fff4; height: fit-content; position: sticky; top: 20px;">
        <h3 style="color:green; border-bottom: 2px solid green; padding-bottom: 10px; margin-top:0;">Detalle Final de Pago</h3>
        <p style="font-size:14px;"><strong>Vuelo:</strong> <?=$vuelo_sel['numero_vuelo']?> ($<?=number_format($vuelo_sel['precio_base_vuelo'], 2)?> c/u)</p>
        <p style="font-size:14px;"><strong>Tarifa:</strong> <?=$plan_sel['nombre_plan']?> (+$<?=number_format($plan_sel['cargo_extra_plan'], 2)?> c/u)</p>
        <p style="font-size:14px;"><strong>Cantidad de Pasajeros:</strong> x<?=$cantidad_pasajeros?></p>
        
        <p style="margin-bottom:2px; font-weight:bold; margin-top:10px; font-size:14px;">Asientos Elegidos:</p>
        <ul style="margin:0; padding-left:20px; font-size:13px; color:#444;">
            <?php 
            // Procesamos los asientos guardados como texto ("1A", "12B")
            if (!empty($_SESSION['asientos_seleccionados']) && is_array($_SESSION['asientos_seleccionados'])) {
                foreach ($_SESSION['asientos_seleccionados'] as $index => $codigo_asiento) {
                    
                    // Extraer el número de fila para calcular el costo de forma dinámica
                    intval(preg_match('/\d+/', $codigo_asiento, $matches));
                    $fila = isset($matches[0]) ? (int)$matches[0] : 8; 

                    // Mismas reglas de negocio que declaramos en el mapa del avión
                    $cargo_asiento = 0.00;
                    if ($fila == 1) {
                        $cargo_asiento = 5000.00;
                    } elseif ($fila >= 2 && $fila <= 5) {
                        $cargo_asiento = 12000.00;
                    } elseif ($fila == 6 || $fila == 7) {
                        $cargo_asiento = 3000.00;
                    } elseif ($fila == 12 || $fila == 13) { 
                        $cargo_asiento = 4500.00;
                    }

                    $total_final += $cargo_asiento;
                    $num_pasajero = $index + 1;
                    ?>
                    <li>Pasajero #<?=$num_pasajero?>: Asiento <strong><?=$codigo_asiento?></strong> (+$<?=number_format($cargo_asiento, 2)?>)</li>
                    <?php
                }
            } else {
                echo "<li>No se seleccionaron asientos</li>";
            }
            ?>
        </ul>
        
        <?php 
        // Sumar Equipajes al total final
        if(!empty($_SESSION['equipajes']) && is_array($_SESSION['equipajes'])) {
            foreach($_SESSION['equipajes'] as $id => $cant) {
                if ($cant > 0) {
                    $stmtE = $pdo->prepare("SELECT precio_unitario FROM tipos_equipaje WHERE id_tipo_equipaje = ?");
                    $stmtE->execute([$id]); 
                    $precio_eq = $stmtE->fetchColumn();
                    if ($precio_eq) {
                        $total_final += ((float)$precio_eq * (int)$cant);
                    }
                }
            }
        }

        // Sumar Servicios al total final
        if(!empty($_SESSION['servicios']) && is_array($_SESSION['servicios'])) {
            foreach($_SESSION['servicios'] as $servicios_pasajero) {
                if (is_array($servicios_pasajero)) {
                    foreach ($servicios_pasajero as $id_serv) {
                        $stmtS = $pdo->prepare("SELECT precio_servicio FROM servicios_adicionales WHERE id_servicio = ?");
                        $stmtS->execute([$id_serv]); 
                        $precio_serv = $stmtS->fetchColumn();
                        if ($precio_serv) {
                            $total_final += (float)$precio_serv;
                        }
                    }
                }
            }
        }
        ?>
        
        <hr style="border:0; border-top:1px dashed green; margin-top:15px;">
        <h3 style="margin:10px 0; display:flex; justify-content:space-between; color:green;">
            <span>TOTAL A PAGAR:</span>
            <span>$<?=number_format($total_final, 2)?></span>
        </h3>
    </div>
</div>
</body>
</html>