/**
 * JetAway - Sistema de Animación y Desplazamiento Lateral Dinámico
 */

document.addEventListener("DOMContentLoaded", () => {
    const body = document.body;
    const eyebrow = document.getElementById('eyebrow');
    const mainTitle = document.getElementById('main-title');
    
    const btnToLogin = document.getElementById('go-to-login');
    const btnToRegister = document.getElementById('go-to-register');

    let currentMode = body.classList.contains('is-login') ? 'login' : 'register';
    let isAnimating = false;

    function switchMode(nextMode) {
    if (isAnimating || nextMode === currentMode) return;
    isAnimating = true;

    if (nextMode === 'login') {
        body.classList.add('is-login');
        eyebrow.textContent = 'bienvenido de vuelta';
        mainTitle.innerHTML = 'INICIAR SESIÓN'; // Modificado sin <br>
    } else {
        body.classList.remove('is-login');
        eyebrow.textContent = 'comenzá tu viaje';
        mainTitle.innerHTML = 'CREAR CUENTA'; // Modificado sin <br>
    }

    history.replaceState(null, '', '?mode=' + nextMode);
    currentMode = nextMode;

    setTimeout(() => { 
        isAnimating = false; 
    }, 1200);
}

    // Escuchadores de eventos directos sobre los enlaces (Adiós al onclick problemático)
    if (btnToLogin) {
        btnToLogin.addEventListener('click', (e) => {
            e.preventDefault(); // Evita que el '#' recargue o mueva la pantalla
            switchMode('login');
        });
    }

    if (btnToRegister) {
        btnToRegister.addEventListener('click', (e) => {
            e.preventDefault();
            switchMode('register');
        });
    }
});