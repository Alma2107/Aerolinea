<?php
session_start();
require_once '../../config/conexion.php';

// Si venís del paso anterior, capturamos los datos de los pasajeros
if($_SERVER['REQUEST_METHOD'] === 'POST') {
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

if (!$vuelo_sel) {
    $vuelo_sel = ['numero_vuelo' => 'No definido', 'precio_base_vuelo' => 0.00];
}

$stmtP = $pdo->prepare("SELECT nombre_plan, cargo_extra_plan FROM planes_tarifas WHERE id_plan = ?");
$stmtP->execute([$id_plan_actual]);
$plan_sel = $stmtP->fetch(PDO::FETCH_ASSOC);

if (!$plan_sel) {
    $plan_sel = ['nombre_plan' => 'Estándar', 'cargo_extra_plan' => 0.00];
}

// Subtotal base multiplicado por la cantidad de pasajeros
$total_final = ((float)$vuelo_sel['precio_base_vuelo'] + (float)$plan_sel['cargo_extra_plan']) * $cantidad_pasajeros;

// Traer métodos de pago
$metodos = $pdo->query("SELECT id_metodo_pago, nombre_metodo, banco_proveedor FROM metodos_pago")->fetchAll(PDO::FETCH_ASSOC);

include_once '../../includes/header.php';
?>

<link rel="stylesheet" href="css/proceso_compra/pago.css">

<div class="contenedor-pago">
    
    <form action="confirmar.php" method="POST">
        <div class="card">
            <h2>💳 Método de Pago</h2>
            
            <div class="grupo-metodos">
                <?php foreach($metodos as $index => $m): ?>
                    <label class="metodo-item">
                        <input type="radio" name="id_metodo_pago" value="<?=$m['id_metodo_pago']?>" <?=$index === 0 ? 'checked' : ''?>> 
                        <div class="metodo-info">
                            <strong><?=$m['nombre_metodo']?></strong> — <span><?=$m['banco_proveedor']?></span>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
            
            <div class="inputs-tarjeta">
                <input type="text" placeholder="Número de Tarjeta" required>
                <input type="text" placeholder="Nombre del Titular" required>
            </div>
        </div>
        
        <button type="submit" class="btn-pagar">Finalizar Reserva y Pagar</button>
    </form>

    <div class="sidebar-resumen-final">
        <h3>Detalle Final de Pago</h3>
        <p><strong>Vuelo:</strong> <?=$vuelo_sel['numero_vuelo']?> ($<?=number_format($vuelo_sel['precio_base_vuelo'], 2)?> c/u)</p>
        <p><strong>Tarifa:</strong> <?=$plan_sel['nombre_plan']?> (+$<?=number_format($plan_sel['cargo_extra_plan'], 2)?> c/u)</p>
        <p><strong>Cantidad de Pasajeros:</strong> x<?=$cantidad_pasajeros?></p>
        
        <p class="titulo-subseccion">Asientos Elegidos:</p>
        <ul class="lista-items">
            <?php 
            if (!empty($_SESSION['asientos_seleccionados']) && is_array($_SESSION['asientos_seleccionados'])) {
                foreach ($_SESSION['asientos_seleccionados'] as $index => $codigo_asiento) {
                    
                    intval(preg_match('/\d+/', $codigo_asiento, $matches));
                    $fila = isset($matches[0]) ? (int)$matches[0] : 8; 

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
        
        <hr class="separador-verde">
        <h3 class="total-final-contenedor">
            <span>TOTAL A PAGAR:</span>
            <span>$<?=number_format($total_final, 2)?></span>
        </h3>
    </div>
</div>
</body>
</html>