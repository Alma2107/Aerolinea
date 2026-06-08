<?php
require_once 'config/conexion.php';
include_once 'includes/header.php';

$aeropuertos = $pdo->query("SELECT codigo_iata, ciudad FROM aeropuertos")->fetchAll();
?>

<section class="hero-slider">

    <div class="slide">
        <img src="img/slider1.jpg" alt="">
        <div class="slide-text">
            <h1>Descubrí nuevos destinos</h1>
            <p>Vuelos nacionales e internacionales.</p>
        </div>
    </div>

    <div class="slide">
        <img src="img/oktubre.jpg" alt="Destino 2">
        <div class="slide-text">
            <h1>Promociones exclusivas</h1>
            <p>Hasta 12 cuotas sin interés.</p>
        </div>
    </div>

    <div class="slide">
        <img src="img/UBPEOI.jpg" alt="Destino 3">
        <div class="slide-text">
            <h1>Volá con FlySmart</h1>
            <p>La mejor experiencia de viaje.</p>
        </div>
    </div>

    <button class="slider-btn prev">&#10094;</button>
    <button class="slider-btn next">&#10095;</button>

</section>

<section class="quick-services" id="servicios">

    <div class="service-card">
        <h3>🧳 Equipaje</h3>
        <p>Información sobre equipaje de mano y bodega.</p>
    </div>

    <div class="service-card">
        <h3>✅ Check-In</h3>
        <p>Realizá tu check-in online rápidamente.</p>
    </div>

    <div class="service-card">
        <h3>🛫 Estado de vuelo</h3>
        <p>Consultá horarios y retrasos.</p>
    </div>

    <div class="service-card">
        <h3>📞 Soporte</h3>
        <p>Atención al cliente las 24 horas.</p>
    </div>

</section>

<section class="destinos" id="destinos">

    <h2>Destinos Populares</h2>

    <div class="destinos-grid">

        <div class="destino-card">
            <img src="img/bariloche.jpg">
            <h3>Bariloche</h3>
        </div>

        <div class="destino-card">
            <img src="img/mendoza.jpg">
            <h3>Mendoza</h3>
        </div>

        <div class="destino-card">
            <img src="img/cordoba.jpg">
            <h3>Córdoba</h3>
        </div>

        <div class="destino-card">
            <img src="img/ushuaia.jpg">
            <h3>Ushuaia</h3>
        </div>

    </div>

</section>

<section class="promociones" id="promociones">

    <h2>Promociones</h2>

    <div class="promo-container">

        <div class="promo-card">
            <h3>20% OFF</h3>
            <p>Comprando ida y vuelta.</p>
        </div>

        <div class="promo-card">
            <h3>12 Cuotas</h3>
            <p>Con bancos adheridos.</p>
        </div>

        <div class="promo-card">
            <h3>Equipaje Bonificado</h3>
            <p>En vuelos seleccionados.</p>
        </div>

    </div>

</section>

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

/* automático cada 5 segundos */

setInterval(nextSlide, 5000);

</script>

</body>
</html>