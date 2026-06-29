<?php
require_once '../../config/conexion.php';
session_start();

$pageStyles = ['../../css/proceso_compra/equipaje.css'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['id_vuelo'] = $_POST['id_vuelo'];
    $_SESSION['id_plan'] = $_POST['id_plan'];
    $_SESSION['codigo_promo'] = strtoupper(trim($_POST['codigo_promo'] ?? ($_SESSION['codigo_promo'] ?? '')));
    $_SESSION['promo_equipaje_gratis'] = $_SESSION['codigo_promo'] === 'EQUIPAJEGRATIS';
}

$equipajes = $pdo->query("SELECT id_tipo_equipaje, nombre_tipo, descripcion, precio_unitario FROM tipos_equipaje")->fetchAll();
$promoEquipaje = !empty($_SESSION['promo_equipaje_gratis']);
include_once '../../includes/header.php';
?>
<form action="asientos.php" method="POST">
    <div class="contenedor-equipaje">
        <div class="main-content">
            <div class="card hero-equipaje">
                <p class="eyebrow">Paso 2</p>
                <h2>Elegi el equipaje que realmente necesitas</h2>
                <p class="text-inclusion">Mochila personal incluida: $0. Suma el equipaje extra segun tu tipo de viaje.</p>
            </div>

            <?php if ($promoEquipaje): ?>
                <div class="promo-applied-card">
                    <span class="promo-code">EQUIPAJEGRATIS</span>
                    <div>
                        <strong>Beneficio aplicado</strong>
                        <p>Te dejamos marcada una valija de bodega estandar sin cargo promocional.</p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="grid-equipajes">
                    <?php foreach($equipajes as $index => $eq): ?>
                        <?php
                            $iconos = ['MO', 'EM', 'BC', 'BE', 'BP', 'ES'];
                            $variantes = ['mochila', 'mano', 'bodega-chica', 'bodega-standar', 'bodega-pesada', 'especial'];
                            $icono = $iconos[$index] ?? 'EQ';
                            $variante = $variantes[$index] ?? 'standard';
                            $gratis = $promoEquipaje && stripos($eq['nombre_tipo'], 'Bodega') !== false && stripos($eq['nombre_tipo'], '23kg') !== false;
                        ?>
                        <label class="equipaje-card equipaje-<?= $variante ?> <?= $gratis ? 'promo-free-bag' : '' ?>">
                            <input type="checkbox" name="servicios[]" value="<?= (int)$eq['id_tipo_equipaje'] ?>" <?= $gratis ? 'checked' : '' ?>>
                            <div class="equipaje-thumb"><?= htmlspecialchars($icono, ENT_QUOTES, 'UTF-8') ?></div>
                            <h4><?= htmlspecialchars($eq['nombre_tipo'], ENT_QUOTES, 'UTF-8') ?></h4>
                            <p class="descripcion"><?= htmlspecialchars($eq['descripcion'], ENT_QUOTES, 'UTF-8') ?></p>
                            <?php if ($gratis): ?>
                                <p class="precio precio-promo">Gratis por promocion</p>
                            <?php else: ?>
                                <p class="precio">+$<?= number_format($eq['precio_unitario'], 2, ',', '.') ?></p>
                            <?php endif; ?>
                            <span class="equipaje-action"><?= $gratis ? 'Aplicado' : 'Agregar' ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="btn-continuar">Elegir Asientos</button>
        </div>

        <aside class="sidebar-resumen">
            <h3>Tu reserva</h3>
            <p><strong>Vuelo:</strong> <?= htmlspecialchars($_SESSION['id_vuelo'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Plan:</strong> <?= htmlspecialchars($_SESSION['id_plan'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            <?php if (!empty($_SESSION['codigo_promo'])): ?><p><strong>Promo:</strong> <?= htmlspecialchars($_SESSION['codigo_promo'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
            <hr class="separador">
        </aside>
    </div>
</form>
<?php include_once '../../includes/footer.php'; ?>
</body>
</html>
