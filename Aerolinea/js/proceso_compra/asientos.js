document.addEventListener("DOMContentLoaded", function() {
    // El LIMITE_PASAJEROS se toma de forma dinámica desde el atributo asignado al script o una variable global.
    const LIMITE_PASAJEROS = parseInt(window.cantidadPasajeros) || 1;

    document.querySelectorAll('.asiento-disponible').forEach(box => {
        box.addEventListener('click', function(e) {
            e.preventDefault(); 
            
            const checkbox = this.querySelector('.check-asiento');
            const seleccionados = document.querySelectorAll('.check-asiento:checked');

            if (checkbox.checked) {
                checkbox.checked = false;
                restaurarEstilo(this);
            } else {
                if (seleccionados.length >= LIMITE_PASAJEROS) {
                    alert(`Ya seleccionaste los ${LIMITE_PASAJEROS} asientos correspondientes a tus pasajeros.`);
                    return;
                }
                checkbox.checked = true;
                marcarEstilo(this);
            }
            actualizarResumenYTotal();
        });
    });

    function marcarEstilo(elemento) {
        elemento.style.filter = 'brightness(0.85)';
        elemento.style.boxShadow = '0 0 0 2px #000';
        elemento.style.transform = 'scale(0.95)';
    }

    function restaurarEstilo(elemento) {
        elemento.style.filter = 'none';
        elemento.style.boxShadow = 'none';
        elemento.style.transform = 'none';
    }

    function actualizarResumenYTotal() {
        const totalVista = document.getElementById('total-vista');
        const listaAsientos = document.getElementById('asientos-lista');
        let baseTotal = parseFloat(totalVista.getAttribute('data-base'));
        
        let nombresAsientos = [];
        let cargosExtras = 0;

        document.querySelectorAll('.check-asiento:checked').forEach(check => {
            const contenedor = check.closest('.asiento-box');
            nombresAsientos.push(contenedor.getAttribute('data-asiento'));
            cargosExtras += parseFloat(contenedor.getAttribute('data-cargo')) || 0;
        });

        listaAsientos.textContent = nombresAsientos.length > 0 ? nombresAsientos.join(', ') : 'Ninguno';
        let nuevoTotal = baseTotal + cargosExtras;
        totalVista.textContent = '$' + nuevoTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    document.getElementById('form-asientos').addEventListener('submit', function(e) {
        const seleccionados = document.querySelectorAll('.check-asiento:checked').length;
        if (seleccionados !== LIMITE_PASAJEROS) {
            e.preventDefault();
            alert(`Por favor, seleccioná exactamente ${LIMITE_PASAJEROS} asiento(s) para continuar.`);
        }
    });
});