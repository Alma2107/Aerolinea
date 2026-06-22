<?php
require_once 'config/conexion.php';
include_once 'includes/header.php';

$aeropuertos = $pdo->query("SELECT codigo_iata, ciudad FROM aeropuertos")->fetchAll();
?>

<section class="hero-slider">

    <div class="slide active">
        <img src="img/car1.jpg" alt="">
        <div class="slide-text">
            <h1>Descubrí nuevos destinos</h1>
            <p>Vuelos nacionales e internacionales.</p>
        </div>
    </div>

    <div class="slide">
        <img src="img/sergiofly.jpg" alt="Destino 2">
        <div class="slide-text">
            <h1>Promociones exclusivas</h1>
            <p>Hasta 12 cuotas sin interés.</p>
        </div>
    </div>

    <div class="slide">
        <img src="img/car3.jpg" alt="Destino 3">
        <div class="slide-text">
            <h1>Volá con FlySmart</h1>
            <p>La mejor experiencia de viaje.</p>
        </div>
    </div>

    <button class="slider-btn prev">&#10094;</button>
    <button class="slider-btn next">&#10095;</button>

</section>
<br>
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

<div class="booking-layout">
    <div class="main-content">
        <div class="card">
            <p class="eyebrow">Buscador principal</p>
            <h2>¿A dónde viajamos?</h2>
            <p class="section-copy">Armá tu viaje en pocos pasos y compará horarios, tarifas y extras sin salir del mismo lugar.</p>
            <form action="consultas/proceso_compra/vuelos.php" method="GET">
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
                <button type="submit" class="btn-next">Buscar Horarios y Tarifas</button>
            </form>
        </div>
    </div>
</div>

<section class="travel-carousel-section" id="viajes">
    <div class="section-heading">
        <p class="eyebrow">Inspiración de viaje</p>
        <h2>Soña con tu proximo destino</h2>
        <p>Fotos, clima, vacaciones y mas en una experiencia inolvidable junto a nosotros.</p>
    </div>

    <div class="travel-carousel" aria-label="Carrusel de destinos">
        <article class="travel-card travel-card-large">
            <img src="img/vmar.jpg" alt="Aeropuerto y viaje">
            <div class="travel-card-overlay">
                <span class="travel-badge">Nuevo</span>
                <h3>Escapadas con vista al mar</h3>
                <p>Promos para vuelos de fin de semana, city breaks y escapadas cortas.</p>
            </div>
        </article>

        <article class="travel-card">
            <img src="img/si.jpg" alt="Viaje promocional">
            <div class="travel-card-overlay">
                <span class="travel-badge">Promo</span>
                <h3>Temporada de ofertas</h3>
                <p>Hasta 12 cuotas para reservar con tiempo y pagar más cómodo.</p>
            </div>
        </article>

        <article class="travel-card">
            <img src="img/vi.jpg" alt="Experiencia FlySmart">
            <div class="travel-card-overlay">
                <span class="travel-badge">FlySmart</span>
                <h3>Viajar con experiencia</h3>
                <p>Elegí vuelos, agregá equipaje y personalizá todo desde una sola pantalla.</p>
            </div>
        </article>
    </div>
</section>

<br> 
<br>


<section class="recommended-trips" id="recomendados">
    <div class="section-heading">
        <p class="eyebrow">Recomendados</p>
        <h2>Elegi tus proximas vacaciones</h2>
        <p>Tarjetas pensadas para mostrar lo que hace atractivo cada destino sin saturar la pantalla.</p>
    </div>

    <div class="trip-grid">
        <article class="trip-card trip-card-brasil">
            <span class="trip-tag">Brasil</span>
            <div class="offer-card">
            <span>Desde</span>
            <h3>$89.999</h3>
            <p>Buenos Aires → Río de Janeiro</p>
        </div>

            <img src="img/rdj.jpg" alt="Río de Janeiro">
            <h3>Río de Janeiro</h3>
            <p>Playas, ciudad y energía nocturna en una de las rutas más buscadas de la región.</p>
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
            <p>Buenos Aires → Bariloche</p>
        </div>

            <img src="img/bariloche.jpg" alt="Bariloche">
            <h3>Bariloche</h3>
            <p>Montaña, lago y clima de postal para viajes románticos o familiares.</p>
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
            <p>Buenos Aires → Uruguay</p>
        </div>
            <img src="img/pde.jpg" alt="Punta del Este">
            <h3>Punta del Este</h3>
            <p>Playa, relax y una estética elegante para quienes quieren desconectar rápido.</p>
            <ul>
                <li>Escapadas cortas</li>
                <li>Buena conexión regional</li>
                <li>Temporada alta y baja</li>
            </ul>
        </article>

        <article class="trip-card trip-card-chile">
            <span class="trip-tag">Chile</span>
         <div class="offer-card">
            <span>Desde</span>
            <h3>$59.999</h3>
            <p>Buenos Aires → Santiago</p>
        </div>
            <img src="img/sdc.jpg" alt="Santiago">
            <h3>Santiago</h3>
            <p>Una base ideal para negocios, conexiones y viajes urbanos con más movimiento.</p>
            <ul>
                <li>Conexiones rápidas</li>
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
            <h1>Llevá FlySmart en tu celular</h1>
            <p>Gestioná vuelos, check-in y reservas desde cualquier lugar.</p>
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
            <p>"El proceso fue rápido y muy claro."</p>
            <strong>María G.</strong>
        </div>
<br>
        <div class="testimonial">
            <p>"Excelente atención y muy buenos precios."</p>
            <strong>Carlos R.</strong>
        </div>
<br>
        <div class="testimonial">
            <p>"La compra online fue muy sencilla."</p>
            <strong>Sofía M.</strong>
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

/* automático cada 5 segundos */

setInterval(nextSlide, 5000);

</script>

<?php

include_once 'includes/footer.php';

?>
</body>
</html>