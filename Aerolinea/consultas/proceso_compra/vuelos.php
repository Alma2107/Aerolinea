<?php
require_once '../../config/conexion.php';
session_start();

$pageStyles = ['../../css/proceso_compra/vuelos.css'];

if(isset($_GET['pasajeros'])) {
    $_SESSION['pasajeros'] = $_GET['pasajeros'];
}

$vuelos = $pdo->query("SELECT id_vuelo, numero_vuelo, fecha_salida, precio_base_vuelo FROM vuelos")->fetchAll();
$planes = $pdo->query("SELECT id_plan, nombre_plan, descripcion FROM planes_tarifas")->fetchAll();
$primerVuelo = $vuelos[0] ?? null;
$primerPlan = $planes[0] ?? null;

include_once '../../includes/header.php';
?>
<section class="contenedor-vuelos">
    <div class="main-content">
        <div class="card hero-step">
            <p class="eyebrow">Paso 1</p>
            <h2>Elegí horario y plan de viaje</h2>
            <p>Compará el vuelo y la tarifa en una interfaz más clara, con tarjetas visuales y menos texto compacto.</p>
        </div>

        <form action="equipaje.php" method="POST">
            <div class="card">
                <h2>Horarios disponibles</h2>
                <div class="grid-vuelos">
                    <?php foreach($vuelos as $v): ?>
                        <label class="opcion-vuelo">
                            <input type="radio" name="id_vuelo" value="<?=$v['id_vuelo']?>" required <?= ($primerVuelo && $v['id_vuelo'] === $primerVuelo['id_vuelo']) ? 'checked' : '' ?>>
                            <span class="badge-vuelo">Vuelo</span>
                            <strong><?=$v['numero_vuelo']?></strong>
                            <p>Salida: <?=$v['fecha_salida']?></p>
                            <span class="precio-vuelo">Base: $<?=number_format($v['precio_base_vuelo'], 2)?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card">
                <h2>Plan de viaje</h2>
                <div class="grid-planes">
                    <?php foreach($planes as $p): ?>
                        <label class="opcion-plan">
                            <input type="radio" name="id_plan" value="<?=$p['id_plan']?>" required <?= ($primerPlan && $p['id_plan'] === $primerPlan['id_plan']) ? 'checked' : '' ?>>
                            <strong><?=$p['nombre_plan']?></strong>
                            <p><?=$p['descripcion']?></p>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="btn-continuar">Continuar al Equipaje</button>
        </form>
    </div>

    <aside class="sidebar-resumen">
        <h3>Tu recorrido</h3>
        <p><strong>Origen:</strong> definido en el buscador</p>
        <p><strong>Destino:</strong> definido en el buscador</p>
        <p><strong>Pasajeros:</strong> <?= (int)($_SESSION['pasajeros'] ?? 1) ?></p>
        <hr class="separador">
        <p>En este paso elegís el vuelo base y la tarifa. El equipaje, asientos y datos de pasajeros se resuelven después con el mismo estilo visual.</p>
    </aside>
</section>
<?php include_once '../../includes/footer.php'; ?>
</body>
</html>