<?php
require_once '../../config/conexion.php';
session_start();
$pageStyles = ['../../css/proceso_compra/servicios.css'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['equipajes'] = [];
    // Procesamos y guardamos el equipaje de cada pasajero de forma organizada
    if (isset($_POST['equipaje'])) {
        foreach ($_POST['equipaje'] as $num_pasajero => $lista_cantidades) {
            foreach ($lista_cantidades as $id_tipo => $cantidad) {
                if ((int)$cantidad > 0) {
                    $_SESSION['equipajes'][$num_pasajero][$id_tipo] = (int)$cantidad;
                }
            }
        }
    }
}

// Controladores de respaldo primarios
$id_vuelo_ida       = $_SESSION['id_vuelo_ida'] ?? 0;
$id_vuelo_vuelta    = $_SESSION['id_vuelo_vuelta'] ?? 0;
$cantidad_pasajeros = $_SESSION['pasajeros'] ?? 1;
$planes_seleccionados = $_SESSION['planes_pasajeros'] ?? [];

// 1. Cargar catálogos completos indexados para evitar consultas redundantes en bucles
$planes_db = $pdo->query("SELECT id_plan, nombre_plan, cargo_extra_plan FROM planes_tarifas")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);
$equipajes_db = $pdo->query("SELECT id_tipo_equipaje, nombre_tipo, precio_unitario FROM tipos_equipaje")->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);

// 2. Obtener datos de los vuelos para el resumen visual
$vuelo_ida_num = "No asignado";
$precio_ida_base = 0.00;

if ($id_vuelo_ida > 0) {
    $stmtV = $pdo->prepare("SELECT numero_vuelo, precio_base_vuelo FROM vuelos WHERE id_vuelo = ?");
    $stmtV->execute([$id_vuelo_ida]);
    $vuelo_sel = $stmtV->fetch(PDO::FETCH_ASSOC);
    if ($vuelo_sel) {
        $vuelo_ida_num = $vuelo_sel['numero_vuelo'];
        $precio_ida_base = (float)$vuelo_sel['precio_base_vuelo'];
    }
}

$vuelo_vuelta_num = "";
$precio_vuelta_base = 0.00;

if ($id_vuelo_vuelta > 0) {
    $stmtVV = $pdo->prepare("SELECT numero_vuelo, precio_base_vuelo FROM vuelos WHERE id_vuelo = ?");
    $stmtVV->execute([$id_vuelo_vuelta]);
    $vuelo_v_data = $stmtVV->fetch(PDO::FETCH_ASSOC);
    if ($vuelo_v_data) {
        $vuelo_vuelta_num = $vuelo_v_data['numero_vuelo'];
        $precio_vuelta_base = (float)$vuelo_v_data['precio_base_vuelo'];
    }
}

// 3. RECALCULAR TOTAL ACUMULADO EXACTO (Vuelos + Planes personalizados)
$total_acumulado = 0;
for ($i = 1; $i <= $cantidad_pasajeros; $i++) {
    $total_acumulado += $precio_ida_base;
    $total_acumulado += $precio_vuelta_base;

    if ($id_vuelo_ida > 0 && isset($planes_seleccionados[$i]['ida'])) {
        $id_p_ida = $planes_seleccionados[$i]['ida'];
        $total_acumulado += (float)($planes_db[$id_p_ida]['cargo_extra_plan'] ?? 0);
    }

    if ($id_vuelo_vuelta > 0 && isset($planes_seleccionados[$i]['vuelta'])) {
        $id_p_vta = $planes_seleccionados[$i]['vuelta'];
        $total_acumulado += (float)($planes_db[$id_p_vta]['cargo_extra_plan'] ?? 0);
    }
}

// Obtener catálogo de servicios para renderizar las opciones del formulario
$servicios = $pdo->query("SELECT id_servicio, nombre_servicio, descripcion, precio_servicio FROM servicios_adicionales")->fetchAll(PDO::FETCH_ASSOC);
include_once '../../includes/header.php';
?>

<div class="contenedor-servicios">
    
    <form action="asientos.php" method="POST" id="form-servicios">
        
        <?php for($i = 1; $i <= $cantidad_pasajeros; $i++): ?>
            <div class="card">
                <h3>👤 Pasajero #<?=$i?>: Servicios adicionales a bordo</h3>
                <p class="subtitulo-card">Sumá extras opcionales exclusivos para este pasajero.</p>
                
                <div class="grid-servicios">
                    <?php foreach($servicios as $s): ?>
                        <div class="item-servicio">
                            <label class="label-servicio">
                                <input type="checkbox" name="servicios[<?=$i?>][]" value="<?=$s['id_servicio']?>" data-precio="<?=$s['precio_servicio']?>" data-nombre="P#<?=$i?> - <?=$s['nombre_servicio']?>" class="check-servicio"> 
                                <div>
                                    <strong><?=$s['nombre_servicio']?></strong> (+$<?=number_format($s['precio_servicio'], 2)?>)
                                    <p><?=$s['descripcion']?></p>
                                </div>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endfor; ?>

        <button type="submit" class="btn-continuar">Continuar a Elegir Asientos</button>
    </form>

    <div class="sidebar-resumen">
        <h3>Resumen de tu Viaje</h3>
        <p><strong>Vuelo Ida:</strong> <?=$vuelo_ida_num?></p>
        <?php if ($vuelo_vuelta_num !== ""): ?>
            <p><strong>Vuelo Vuelta:</strong> <?=$vuelo_vuelta_num?></p>
        <?php endif; ?>
        <p><strong>Pasajeros:</strong> x<?=$cantidad_pasajeros?></p>
        
        <?php 
        $subtotal_equipajes = 0;
        if(!empty($_SESSION['equipajes'])): 
        ?>
            <p class="titulo-seccion-resumen">Equipaje Extra Seleccionado:</p>
            <ul class="lista-resumen lista-equipaje-resumen">
                <?php foreach($_SESSION['equipajes'] as $num_p => $items): ?>
                    <?php foreach($items as $id => $cant): 
                        // Acceso veloz en memoria simulando la estructura limpia de la Base de Datos
                        $eq = $equipajes_db[$id] ?? null;
                        if(!$eq) continue;
                        
                        $monto_equipaje = ((float)$eq['precio_unitario'] * (int)$cant);
                        $subtotal_equipajes += $monto_equipaje;
                    ?>
                        <li>Pasajero #<?=$num_p?>: <?=$eq['nombre_tipo']?> (x<?=$cant?>) +$<?=number_format($monto_equipaje, 2)?></li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        <?php 
        endif; 
        $total_con_equipaje = $total_acumulado + $subtotal_equipajes;
        ?>
        
        <div id="contenedor-servicios-dinamico" class="bloque-dinamico">
            <p class="titulo-seccion-resumen">Servicios de a bordo:</p>
            <ul id="lista-servicios-vista" class="lista-resumen"></ul>
        </div>
        
        <hr class="separador">
        <h4 class="total-contenedor">
            <span>Total Acumulado:</span>
            <span class="total-precio" id="total-vista" data-base="<?=$total_con_equipaje?>">$<?=number_format($total_con_equipaje, 2)?></span>
        </h4>
    </div>
</div>

<script src="js/proceso_compra/servicios.js"></script>
<?php include_once '../../includes/footer.php'; ?>
</body>
</html>