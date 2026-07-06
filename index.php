<?php
require_once 'config/conexion.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function ensureComentariosTableExists(PDO $pdo): void {
    $stmt = $pdo->query("SHOW TABLES LIKE 'comentarios'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("CREATE TABLE comentarios (
            id_comentario INT(11) NOT NULL AUTO_INCREMENT,
            id_cliente INT(11) NOT NULL,
            comentario TEXT NOT NULL,
            fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id_comentario),
            KEY id_cliente (id_cliente),
            CONSTRAINT fk_comentarios_cliente FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    }
}

$comentarioError = '';
$comentarioSuccess = '';
$comentarioTexto = '';

try {
    ensureComentariosTableExists($pdo);
} catch (PDOException $e) {
    // Si no se puede crear la tabla, seguimos sin habilitar comentarios.
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'publicar_comentario') {
    if (empty($_SESSION['usuario_id'])) {
        $comentarioError = 'Debes estar registrado e iniciar sesión para enviar tu experiencia.';
    } else {
        $texto = trim($_POST['comentario'] ?? '');
        if ($texto === '') {
            $comentarioError = 'El comentario no puede quedar vacío.';
        } elseif (mb_strlen($texto) > 500) {
            $comentarioError = 'El comentario no puede exceder los 500 caracteres.';
        } else {
            $stmtInsert = $pdo->prepare("INSERT INTO comentarios (id_cliente, comentario, fecha_creacion) VALUES (:id_cliente, :comentario, NOW())");
            $stmtInsert->execute([
                'id_cliente' => $_SESSION['usuario_id'],
                'comentario' => $texto,
            ]);
            header('Location: index.php?comentario_publicado=1#nosotros');
            exit;
        }
        $comentarioTexto = htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
    }
}

if (isset($_GET['comentario_publicado'])) {
    $comentarioSuccess = 'Gracias por compartir tu experiencia, se publicó correctamente.';
}

try {
    $stmtComentarios = $pdo->prepare("SELECT c.comentario, c.fecha_creacion, cl.nombre, cl.apellido FROM comentarios c INNER JOIN clientes cl ON cl.id_cliente = c.id_cliente WHERE cl.estado_cuenta = 1 ORDER BY c.fecha_creacion DESC LIMIT 6");
    $stmtComentarios->execute();
    $comentarios = $stmtComentarios->fetchAll();
} catch (PDOException $e) {
    $comentarios = [];
}

$aeropuertos = $pdo->query("SELECT codigo_iata, ciudad FROM aeropuertos")->fetchAll();
$fechaRecomendada = date('Y-m-d', strtotime('+14 days'));

$destinosRecomendados = $pdo->prepare("SELECT v.destino_iata, MIN(v.precio_base_vuelo) AS precio_desde, v.origen_iata, o.ciudad AS origen_ciudad, a.ciudad AS destino_ciudad, MIN(v.fecha_salida) AS proxima_salida
    FROM vuelos v
    INNER JOIN aeropuertos a ON a.codigo_iata = v.destino_iata
    INNER JOIN aeropuertos o ON o.codigo_iata = v.origen_iata
    INNER JOIN aviones av ON av.id_avion = v.id_avion
    LEFT JOIN (
        SELECT id_vuelo, COUNT(*) AS vendidos
        FROM tickets_detalle
        GROUP BY id_vuelo
    ) t ON t.id_vuelo = v.id_vuelo
    WHERE COALESCE(av.capacidad, 0) > COALESCE(t.vendidos, 0)
      AND DATE(v.fecha_salida) >= CURDATE()
      AND LOWER(v.estado_vuelo) != 'cancelado'
      AND LOWER(av.estado) = 'activo'
    GROUP BY v.origen_iata, v.destino_iata, o.ciudad, a.ciudad
    ORDER BY MIN(v.fecha_salida) ASC
    LIMIT 4");
$destinosRecomendados->execute();
$destinosRecomendados = $destinosRecomendados->fetchAll();

$imagenesDestino = [
    'BRC' => 'img/bariloche.jpg',
    'COR' => 'img/car2.jpg',
    'MIA' => 'img/sergiofly.jpg',
    'MAD' => 'img/rdj.jpg',
];

$descripcionDestino = [
    'BRC' => 'Montaña, lago y turismo de aventura con tarifas reales en pesos argentinos.',
    'COR' => 'Sierras cordobesas, cultura y precios basados en vuelos reales.',
    'MIA' => 'Playas de Miami con conexión desde Ezeiza y precios en pesos argentinos.',
    'MAD' => 'Madrid directo desde Ezeiza para una experiencia internacional confiable.',
];

$pageStyles = ['css/reservas.css'];
include_once 'includes/header.php';
?>

<section class="hero-slider">

    <div class="slide active">
        <img src="img/car1.jpg" alt="">
        <div class="slide-text">
            <h1>Descubri nuevos destinos</h1>
            <p>Vuelos nacionales e internacionales.</p>
        </div>
    </div>

    <div class="slide">
        <img src="img/sergiofly.jpg" alt="Destino 2">
        <div class="slide-text">
            <h1>Promociones exclusivas</h1>
            <p>Hasta 12 cuotas sin interes.</p>
        </div>
    </div>

    <div class="slide">
        <img src="img/car3.jpg" alt="Destino 3">
        <div class="slide-text">
            <h1>Vola con FlySmart</h1>
            <p>Compra tu vuelo de forma simple.</p>
        </div>
    </div>

    <button class="slider-btn prev">&#10094;</button>
    <button class="slider-btn next">&#10095;</button>

</section>
<br>
<section class="quick-services" id="servicios">

    <div class="service-card">
        <h3>Equipaje</h3>
        <p>Revisa que podes llevar y suma valijas a tu reserva.</p>
    </div>

    <a class="service-card service-card-link" href="consultas/reservas/mis_viajes.php">
        <h3>Mis viajes</h3>
        <p>Consulta tus vuelos, hace check-in y descarga tus tickets.</p>
    </a>

    <a class="service-card service-card-link" href="#promociones-funcionales">
        <h3>Promociones</h3>
        <p>Encontra beneficios para tu proximo viaje.</p>
    </a>

    <div class="service-card">
        <h3>Soporte</h3>
        <p>Contactanos por cambios, pagos o consultas de tu viaje.</p>
    </div>

</section>

<section class="reservation-tools unified-trips" id="reservas">
    <div class="reservation-tool reservation-tool-status compact-reservation-panel">
        <p class="eyebrow">Gestionar reserva</p>
        <h2>Mis viajes</h2>
        <p>Consulta una reserva con tu codigo PNR o entra a tu panel de viajes.</p>
        <form action="consultas/reservas/ver_reserva.php" method="GET" class="reservation-form">
            <input type="text" name="pnr" maxlength="6" placeholder="Ej: AX39FT" required>
            <button type="submit" class="btn-next btn-orange">Consultar PNR</button>
        </form>
    </div>
</section>

<div class="booking-layout full-width-search" id="buscador">
    <div class="main-content full-width-main-content">
        <div class="card search-card-full">
            <p class="eyebrow">Buscador principal</p>
            <h2>A donde queres viajar</h2>
            <p class="section-copy">Busca vuelos, compara horarios y elegi la tarifa que mas te convenga.</p>
            <form action="consultas/proceso_compra/vuelos.php" method="GET" id="flight-search-form">
                <input type="hidden" name="codigo_promo" id="codigo_promo" value="">
                <div class="trip-type-selector">
                    <label class="trip-type-option"><input type="radio" name="tipo_viaje" value="ida_vuelta" checked> <span>Ida y vuelta</span></label>
                    <label class="trip-type-option"><input type="radio" name="tipo_viaje" value="solo_ida"> <span>Ida</span></label>
                    <label class="trip-type-option"><input type="radio" name="tipo_viaje" value="multitramos"> <span>Multitramos</span></label>
                </div>

                <div class="travel-mode-panels">
                    <div class="travel-mode-panel active" data-panel="ida_vuelta">
                        <div class="search-grid">
                            <select name="origen" required>
                                <option value="">Origen</option>
                                <?php foreach($aeropuertos as $ap): ?>
                                    <option value="<?=$ap['codigo_iata']?>"><?=$ap['ciudad']?> (<?=$ap['codigo_iata']?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <select name="destino" required>
                                <option value="">Destino</option>
                                <?php foreach($aeropuertos as $ap): ?>
                                    <option value="<?=$ap['codigo_iata']?>"><?=$ap['ciudad']?> (<?=$ap['codigo_iata']?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <input type="date" name="fecha_ida" required>
                            <input type="date" name="fecha_vuelta" required>
                            <input type="number" name="pasajeros" min="1" max="5" value="1">
                        </div>
                    </div>

                    <div class="travel-mode-panel" data-panel="solo_ida">
                        <div class="search-grid">
                            <select name="origen_solo" required>
                                <option value="">Origen</option>
                                <?php foreach($aeropuertos as $ap): ?>
                                    <option value="<?=$ap['codigo_iata']?>"><?=$ap['ciudad']?> (<?=$ap['codigo_iata']?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <select name="destino_solo" required>
                                <option value="">Destino</option>
                                <?php foreach($aeropuertos as $ap): ?>
                                    <option value="<?=$ap['codigo_iata']?>"><?=$ap['ciudad']?> (<?=$ap['codigo_iata']?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <input type="date" name="fecha_ida_solo" required>
                            <input type="number" name="pasajeros_solo" min="1" max="5" value="1">
                        </div>
                    </div>

                    <div class="travel-mode-panel" data-panel="multitramos">
                        <div class="search-grid multi-segment-grid">
                            <select name="origen_multi_1" required>
                                <option value="">Tramo 1 • Origen</option>
                                <?php foreach($aeropuertos as $ap): ?>
                                    <option value="<?=$ap['codigo_iata']?>"><?=$ap['ciudad']?> (<?=$ap['codigo_iata']?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <select name="destino_multi_1" required>
                                <option value="">Tramo 1 • Destino</option>
                                <?php foreach($aeropuertos as $ap): ?>
                                    <option value="<?=$ap['codigo_iata']?>"><?=$ap['ciudad']?> (<?=$ap['codigo_iata']?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <input type="date" name="fecha_multi_1" required>
                            <select name="origen_multi_2" required>
                                <option value="">Tramo 2 • Origen</option>
                                <?php foreach($aeropuertos as $ap): ?>
                                    <option value="<?=$ap['codigo_iata']?>"><?=$ap['ciudad']?> (<?=$ap['codigo_iata']?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <select name="destino_multi_2" required>
                                <option value="">Tramo 2 • Destino</option>
                                <?php foreach($aeropuertos as $ap): ?>
                                    <option value="<?=$ap['codigo_iata']?>"><?=$ap['ciudad']?> (<?=$ap['codigo_iata']?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <input type="date" name="fecha_multi_2" required>
                            <input type="number" name="pasajeros_multi" min="1" max="5" value="1">
                        </div>
                        <p class="helper-text">Combina varios trayectos en una sola reserva para visitar más de una ciudad sin volver al punto de inicio.</p>
                    </div>
                </div>

                <button type="submit" class="btn-next">Buscar vuelos</button>
            </form>

            <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('flight-search-form');
                if (!form) return;
                const radios = document.querySelectorAll('input[name="tipo_viaje"]');
                const panels = document.querySelectorAll('.travel-mode-panel');
                const today = new Date().toISOString().split('T')[0];
                const dateFields = form.querySelectorAll('input[type="date"]');
                const ida = form.elements['fecha_ida'];
                const vuelta = form.elements['fecha_vuelta'];
                const soloIda = form.elements['fecha_ida_solo'];
                const multi1 = form.elements['fecha_multi_1'];
                const multi2 = form.elements['fecha_multi_2'];

                dateFields.forEach(field => {
                    field.min = today;
                });

                function sincronizarFechas() {
                    if (ida && vuelta) {
                        vuelta.min = ida.value || today;
                        if (ida.value && vuelta.value && vuelta.value < ida.value) {
                            vuelta.value = ida.value;
                        }
                    }

                    if (soloIda) {
                        soloIda.min = today;
                    }

                    if (multi1 && multi2) {
                        multi2.min = multi1.value || today;
                        if (multi1.value && multi2.value && multi2.value < multi1.value) {
                            multi2.value = multi1.value;
                        }
                    }
                }

                function actualizarPanel() {
                    const selected = document.querySelector('input[name="tipo_viaje"]:checked')?.value || 'ida_vuelta';
                    panels.forEach(panel => {
                        const active = panel.dataset.panel === selected;
                        panel.classList.toggle('active', active);
                        panel.querySelectorAll('input, select').forEach(field => {
                            field.disabled = !active;
                        });
                    });
                    sincronizarFechas();
                }

                radios.forEach(radio => radio.addEventListener('change', actualizarPanel));
                dateFields.forEach(field => field.addEventListener('change', sincronizarFechas));
                actualizarPanel();
            });
            </script>

        </div>
    </div>
</div>

<section class="travel-carousel-section" id="promociones-funcionales">
    <div class="section-heading">
        <p class="eyebrow">Promociones</p>
        <h2>Aprovecha beneficios para tu viaje</h2>
        <p>Elegi una promo y busca vuelos con el beneficio aplicado.</p>
    </div>

    <div class="travel-carousel promo-carousel" aria-label="Promociones de vuelos">
        <a class="travel-card travel-card-large promo-card-link" href="consultas/proceso_compra/vuelos.php?destino=BRC&pasajeros=1&codigo_promo=BARILO20">
            <img src="img/Bariloche.jpg" alt="20% OFF en Bariloche">
            <div class="travel-card-overlay">
                <span class="travel-badge">BARILO20</span>
                <h3>20% OFF en Bariloche</h3>
                <p>Busca vuelos a Bariloche con descuento.</p>
            </div>
        </a>

        <a class="travel-card promo-card-link" href="consultas/proceso_compra/vuelos.php?pasajeros=1&codigo_promo=EQUIPAJEGRATIS">
            <img src="img/si.jpg" alt="Equipaje gratis">
            <div class="travel-card-overlay">
                <span class="travel-badge">EQUIPAJEGRATIS</span>
                <h3>Equipaje gratis</h3>
                <p>Suma una valija sin cargo en tu compra.</p>
            </div>
        </a>

        <a class="travel-card promo-card-link" href="consultas/proceso_compra/vuelos.php?destino=COR&pasajeros=2&codigo_promo=CORDOBA2X1">
            <img src="img/car2.jpg" alt="2x1 a Cordoba">
            <div class="travel-card-overlay">
                <span class="travel-badge">CORDOBA2X1</span>
                <h3>2x1 a Cordoba</h3>
                <p>Busca vuelos para dos pasajeros con beneficio aplicado.</p>
            </div>
        </a>
    </div>
</section>

<br> 
<br>


<section class="recommended-trips" id="recomendados">
    <div class="section-heading">
        <p class="eyebrow">Recomendados</p>
        <h2>Destinos con vuelos disponibles</h2>
    </div>

    <div class="trip-grid">
        <?php if (!empty($destinosRecomendados)): ?>
            <?php foreach ($destinosRecomendados as $dest): ?>
                <?php
                    $imagen = $imagenesDestino[$dest['destino_iata']] ?? 'img/car1.jpg';
                    $descripcion = $descripcionDestino[$dest['destino_iata']] ?? 'Tarifa base en pesos argentinos para este destino.';
                    $rutaTexto = htmlspecialchars($dest['origen_ciudad'] . ' - ' . $dest['destino_ciudad'], ENT_QUOTES, 'UTF-8');
                    $fechaVuelo = isset($dest['proxima_salida']) ? date('Y-m-d', strtotime($dest['proxima_salida'])) : $fechaRecomendada;
                    $urlDestino = 'consultas/proceso_compra/vuelos.php?tipo_viaje=solo_ida&origen_solo=' . urlencode($dest['origen_iata']) . '&destino_solo=' . urlencode($dest['destino_iata']) . '&fecha_ida_solo=' . urlencode($fechaVuelo) . '&pasajeros_solo=1#vuelos-disponibles';
                ?>
                <a class="trip-card" href="<?= htmlspecialchars($urlDestino, ENT_QUOTES, 'UTF-8') ?>">
                    <span class="trip-tag"><?= htmlspecialchars($dest['destino_ciudad'], ENT_QUOTES, 'UTF-8') ?></span>
                    <div class="offer-card">
                        <span>Desde</span>
                        <h3>$<?= number_format((float)$dest['precio_desde'], 0, ',', '.') ?></h3>
                        <p><?= $rutaTexto ?></p>
                    </div>
                    <img src="<?= htmlspecialchars($imagen, ENT_QUOTES, 'UTF-8') ?>" alt="<?= $rutaTexto ?>">
                    <h3><?= htmlspecialchars($dest['destino_ciudad'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p><?= htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8') ?></p>
                    <ul>
                        <li>Precio en pesos argentinos</li>
                        <li>Vuelos con disponibilidad real</li>
                        <li>Reserva directa desde el buscador</li>
                    </ul>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay recomendaciones cargadas en este momento. Vuelve a consultar más tarde.</p>
        <?php endif; ?>
    </div>
</section> 


<br>
<br>
<br>

<section class="recommended-trips" id="nosotros">
    <p class="eyebrow">Mas Informacion</p>
<br>

<section class="about-us" id="app">
    <div class="about-panel">
            <p class="eyebrow">APP movil</p>
            <h1>Lleva FlySmart en tu celular</h1>
            <p>Gestiona vuelos, check-in y reservas desde cualquier lugar.</p>
        <div class="about-stats">
            <h2>500K+</h2>
            <p>Pasajeros</p>
        </div>

        <div class="about-stats">
            <h2>40+</h2>
            <p>Destinos</p>
        </div>

        <div class="about-stats">
            <h2>98%</h2>
            <p>Puntualidad</p>
        </div>

        <div class="about-stats">
            <h2>24/7</h2>
            <p>Soporte</p>
        </div>
      
    </div>

    
    <div class="about-panel">
       <section class="testimonials">
        <p class="eyebrow">Opiniones</p>
    <div class="section-heading">
        <h2>Lo que dicen nuestros pasajeros</h2>
    </div>

    <?php if ($comentarioSuccess): ?>
        <div class="comment-message success"><?= htmlspecialchars($comentarioSuccess, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if ($comentarioError): ?>
        <div class="comment-message error"><?= htmlspecialchars($comentarioError, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="testimonial-grid">
        <?php if (!empty($comentarios)): ?>
            <?php foreach ($comentarios as $comentario): ?>
                <div class="testimonial">
                    <p>"<?= nl2br(htmlspecialchars($comentario['comentario'], ENT_QUOTES, 'UTF-8')) ?>"</p>
                    <strong><?= htmlspecialchars($comentario['nombre'] . ' ' . $comentario['apellido'], ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="testimonial">
                <p>Aun no hay opiniones publicadas. Sé el primero en compartir tu experiencia.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="comment-panel">
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <form class="comment-form" action="index.php#nosotros" method="POST">
                <input type="hidden" name="accion" value="publicar_comentario">
                <label for="comentario">Deja tu experiencia</label>
                <textarea id="comentario" name="comentario" rows="5" maxlength="500" placeholder="Contanos cómo fue tu viaje"><?= $comentarioTexto ?></textarea>
                <button type="submit" class="btn-next btn-comment-submit">Publicar comentario</button>
                <p class="comment-help">Solo los usuarios registrados pueden publicar opiniones. Tu nombre aparecerá junto al comentario.</p>
            </form>
        <?php else: ?>
            <div class="auth-prompt">
                <p>Debes iniciar sesión para dejar un comentario. El espacio está siempre visible para que leas experiencias reales.</p>
                <div class="auth-buttons">
                    <a href="consultas/login/login.php" class="btn-next btn-orange">Iniciar Sesión</a>
                    <a href="consultas/login/registro.php" class="btn-next">Registrarse</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
    </div>
    
</section>
</section>

<script>

const slides = document.querySelectorAll('.slide');
const nextBtn = document.querySelector('.next');
const prevBtn = document.querySelector('.prev');

let current = 0;

function showSlide(index){

    slides.forEach(slide => {
        slide.classList.remove('active');
    });

    slides[index].classList.add('active');
}

function nextSlide(){
    current++;

    if(current >= slides.length){
        current = 0;
    }

    showSlide(current);
}

function prevSlide(){
    current--;

    if(current < 0){
        current = slides.length - 1;
    }

    showSlide(current);
}

nextBtn.addEventListener('click', nextSlide);
prevBtn.addEventListener('click', prevSlide);

/* automatico cada 5 segundos */

setInterval(nextSlide, 5000);

</script>
<script>
const searchForm = document.getElementById('flight-search-form');
if (searchForm) {
    const fields = ['origen', 'destino', 'fecha_ida', 'fecha_vuelta', 'pasajeros'];
    fields.forEach(name => {
        const field = searchForm.elements[name];
        const saved = localStorage.getItem('flysmart_' + name);
        if (field && saved && !field.value) field.value = saved;
    });
    searchForm.addEventListener('submit', () => {
        fields.forEach(name => {
            const field = searchForm.elements[name];
            if (field) localStorage.setItem('flysmart_' + name, field.value);
        });
    });
}
</script>
<?php

include_once 'includes/footer.php';

?>
</body>
</html>


