<?php
require_once '../../config/conexion.php';
session_start();
$pageStyles = ['../../css/proceso_compra/equipaje.css'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['id_vuelo'] = $_POST['id_vuelo'];
    $_SESSION['id_plan'] = $_POST['id_plan'];
}

$equipajes = $pdo->query("SELECT id_tipo_equipaje, nombre_tipo, descripcion, precio_unitario FROM tipos_equipaje")->fetchAll();
include_once '../../includes/header.php';
?>
<form action="asientos.php" method="POST">
    <div class="contenedor-equipaje">
        <div class="main-content">
            <div class="card hero-equipaje">
                <p class="eyebrow">Paso 2</p>
                <h2>Elegí el equipaje que realmente necesitás</h2>
                <p class="text-inclusion">Mochila personal incluida: $0. Sumá el equipaje extra según tu tipo de viaje.</p>
            </div>

            <div class="card">
                <div class="grid-equipajes">
                    <?php foreach($equipajes as $index => $eq): ?>
                        <?php
                            $iconos = ['🎒', '🧳', '🧳', '🛄', '🛄', '🏄'];
                            $variantes = ['mochila', 'mano', 'bodega-chica', 'bodega-standar', 'bodega-pesada', 'especial'];
                            $icono = $iconos[$index] ?? '🧳';
                            $variante = $variantes[$index] ?? 'standard';
                        ?>
                        <label class="equipaje-card equipaje-<?=$variante?>">
                            <input type="checkbox" name="servicios[]" value="<?=$eq['id_tipo_equipaje']?>">
                            <div class="equipaje-thumb"><?=$icono?></div>
                            <h4><?=$eq['nombre_tipo']?></h4>
                            <p class="descripcion"><?=$eq['descripcion']?></p>
                            <p class="precio">+$<?=number_format($eq['precio_unitario'], 2)?></p>
                            <span class="equipaje-action">Agregar</span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="btn-continuar">Elegir Asientos</button>
        </div>

        <aside class="sidebar-resumen">
            <h3>Tu reserva</h3>
            <p><strong>Vuelo:</strong> <?=$_SESSION['id_vuelo']?></p>
            <p><strong>Plan:</strong> <?=$_SESSION['id_plan']?></p>
            <hr class="separador">
            <p>Acá se muestra el extra de equipaje como tarjetas para que se vea más claro y rápido de entender.</p>
        </aside>
    </div>
</form>
<?php include_once '../../includes/footer.php'; ?>
</body>
</html>