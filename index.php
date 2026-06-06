<?php
require_once 'config/conexion.php';
include_once 'includes/header.php';

$aeropuertos = $pdo->query("SELECT codigo_iata, ciudad FROM aeropuertos")->fetchAll();
?>
<div class="booking-layout">
    <div class="main-content">
        <div class="card">
            <h2>¿A dónde viajamos?</h2>
            <form action="consultas/proceso_compra/vuelos.php" method="GET">
                <div style="margin: 15px 0;">
                    <label><input type="radio" name="tipo_viaje" value="ida_vuelta" checked> Ida y Vuelta</label>
                    <label><input type="radio" name="tipo_viaje" value="solo_ida"> Solo Ida</label>
                </div>
                <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <select name="origen" required style="padding: 10px; flex: 1;">
                        <option value="">Origen</option>
                        <?php foreach($aeropuertos as $ap): ?>
                            <option value="<?=$ap['codigo_iata']?>"><?=$ap['ciudad']?> (<?=$ap['codigo_iata']?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <select name="destino" required style="padding: 10px; flex: 1;">
                        <option value="">Destino</option>
                        <?php foreach($aeropuertos as $ap): ?>
                            <option value="<?=$ap['codigo_iata']?>"><?=$ap['ciudad']?> (<?=$ap['codigo_iata']?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <input type="date" name="fecha_ida" required style="padding: 10px; flex: 1;">
                    <input type="date" name="fecha_vuelta" style="padding: 10px; flex: 1;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label>Pasajeros: </label>
                    <input type="number" name="pasajeros" min="1" max="5" value="1" style="padding: 8px;">
                </div>
                <button type="submit" class="btn-next">Buscar Horarios y Tarifas</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>