<?php
require_once 'config/conexion.php';
$pageStyles = ['css/reservas.css'];
include_once 'includes/header.php';

$aeropuertos = $pdo->query("SELECT codigo_iata, ciudad FROM aeropuertos")->fetchAll();
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
    <div class="reservation-tool reservation-tool-status">
        <p class="eyebrow">Gestionar reserva</p>
        <h2>Mis viajes</h2>
        <p>Consulta una reserva con tu codigo PNR o entra a tu panel de viajes.</p>
        <form action="consultas/reservas/ver_reserva.php" method="GET" class="reservation-form">
            <input type="text" name="pnr" maxlength="6" placeholder="Ej: AX39FT" required>
            <button type="submit" class="btn-next btn-orange">Consultar PNR</button>
        </form>
    </div>

    <div class="reservation-tool reservation-tool-history">
        <p class="eyebrow">Panel del cliente</p>
        <h2>Historial y proximos vuelos</h2>
        <p>Revisa tus vuelos futuros, historial, check-in y tickets.</p>
        <a href="consultas/reservas/mis_viajes.php" class="btn-next">Entrar a Mis viajes</a>
    </div>
</section>

<div class="booking-layout" id="buscador">
    <div class="main-content">
        <div class="card">
            <p class="eyebrow">Buscador principal</p>
            <h2>A donde queres viajar</h2>
            <p class="section-copy">Busca vuelos, compara horarios y elegi la tarifa que mas te convenga.</p>
            <form action="consultas/proceso_compra/vuelos.php" method="GET" id="flight-search-form">
                <input type="hidden" name="codigo_promo" id="codigo_promo" value="">
                <div class="trip-type-selector">
                    <label><input type="radio" name="tipo_viaje" value="ida_vuelta" checked> Ida y Vuelta</label>
                    <label><input type="radio" name="tipo_viaje" value="solo_ida"> Solo Ida</label>
                </div>
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
                    <input type="date" name="fecha_vuelta">
                    <input type="number" name="pasajeros" min="1" max="5" value="1">
                </div>
                <button type="submit" class="btn-next">Buscar vuelos</button>
            </form>
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
        <h2>Elegi tu proximo destino</h2>
    </div>

    <div class="trip-grid">
        <article class="trip-card trip-card-brasil">
            <span class="trip-tag">Brasil</span>
            <div class="offer-card">
            <span>Desde</span>
            <h3>$89.999</h3>
            <p>Buenos Aires - Rio de Janeiro</p>
        </div>

            <img src="img/rdj.jpg" alt="Rio de Janeiro">
            <h3>Rio de Janeiro</h3>
            <p>Playas, ciudad y vuelos directos para una escapada completa.</p>
            <ul>
                <li>Playas urbanas</li>
                <li>Vuelos directos</li>
                <li>Ideal para escapadas largas</li>
            </ul>
            
        </article>

        <article class="trip-card trip-card-argentina">
            <span class="trip-tag">Argentina</span>
        <div class="offer-card">
            <span>Desde</span>
            <h3>$69.999</h3>
            <p>Buenos Aires - Bariloche</p>
        </div>

            <img src="img/bariloche.jpg" alt="Bariloche">
            <h3>Bariloche</h3>
            <p>Montana, lago y salidas para viajar en pareja o en familia.</p>
            <ul>
                <li>Paquetes de invierno</li>
                <li>Salida flexible</li>
                <li>Experiencia premium</li>
            </ul>
        </article>

        <article class="trip-card trip-card-uruguay">
            <span class="trip-tag">Uruguay</span>
        <div class="offer-card">
            <span>Desde</span>
            <h3>$39.999</h3>
            <p>Buenos Aires - Uruguay</p>
        </div>
            <img src="img/pde.jpg" alt="Punta del Este">
            <h3>Punta del Este</h3>
            <p>Playa y descanso para una escapada corta.</p>
            <ul>
                <li>Escapadas cortas</li>
                <li>Buena conexion regional</li>
                <li>Temporada alta y baja</li>
            </ul>
        </article>

        <article class="trip-card trip-card-chile">
            <span class="trip-tag">Chile</span>
         <div class="offer-card">
            <span>Desde</span>
            <h3>$59.999</h3>
            <p>Buenos Aires - Santiago</p>
        </div>
            <img src="img/sdc.jpg" alt="Santiago">
            <h3>Santiago</h3>
            <p>Una ruta practica para conexiones, trabajo o turismo urbano.</p>
            <ul>
                <li>Conexiones rapidas</li>
                <li>Turismo urbano</li>
                <li>Plan flexible</li>
            </ul>
        </article>
    </div>
</section>


<br>
<br>
<br>

<section class="recommended-trips" id="Mas Informacion">
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

    <div class="testimonial-grid">
        <div class="testimonial">
            <p>"La compra fue rapida y clara."</p>
            <strong>Maria G.</strong>
        </div>
<br>
        <div class="testimonial">
            <p>"Buena atencion y precios claros."</p>
            <strong>Carlos R.</strong>
        </div>
<br>
        <div class="testimonial">
            <p>"Pude reservar sin complicaciones."</p>
            <strong>Sofia M.</strong>
        </div>
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


