document.addEventListener("DOMContentLoaded", function() {
    // Obtenemos los valores que dejó PHP en el contenedor HTML
    const configData = document.getElementById('js-variables-vuelo');
    const cantidadPasajeros = parseInt(configData.getAttribute('data-pasajeros'));
    const tipoViajeInicial = configData.getAttribute('data-tipo-viaje');
    
    function calcularTotal() {
        let total = 0;
        let htmlDetalle = "";

        // 1. Obtener vuelos seleccionados u ocultos
        const radioIda = document.querySelector('input[name="id_vuelo_ida"]:checked') || document.querySelector('input[name="id_vuelo_ida"][type="hidden"]');
        const radioVuelta = document.querySelector('input[name="id_vuelo_vuelta"]:checked') || document.querySelector('input[name="id_vuelo_vuelta"][type="hidden"]');

        let precioIdaBase = radioIda ? parseFloat(radioIda.getAttribute('data-precio')) : 0;
        let precioVueltaBase = radioVuelta ? parseFloat(radioVuelta.getAttribute('data-precio')) : 0;

        // Sumar tramo Ida
        if(precioIdaBase > 0 && tipoViajeInicial !== 'solo_vuelta') {
            let subIda = precioIdaBase * cantidadPasajeros;
            total += subIda;
            htmlDetalle += `<p>• Vuelo Ida Base: $${precioIdaBase.toLocaleString()} x ${cantidadPasajeros} = <strong>$${subIda.toLocaleString()}</strong></p>`;
        }

        // Sumar tramo Vuelta
        if (precioVueltaBase > 0 && tipoViajeInicial !== 'solo_ida') {
            let subVuelta = precioVueltaBase * cantidadPasajeros;
            total += subVuelta;
            htmlDetalle += `<p>• Vuelo Vuelta Base: $${precioVueltaBase.toLocaleString()} x ${cantidadPasajeros} = <strong>$${subVuelta.toLocaleString()}</strong></p>`;
        }

        // 2. Sumar planes individuales por pasajero
        htmlDetalle += `<p style="border-top: 1px solid #ccc; padding-top:5px; margin-top:5px;"><strong>Planes seleccionados:</strong></p>`;
        
        for(let i = 1; i <= cantidadPasajeros; i++) {
            // Plan Ida
            if(tipoViajeInicial !== 'solo_vuelta') {
                const planIda = document.querySelector(`input[name="planes[${i}][ida]"]:checked`);
                let precioPlanIda = planIda ? parseFloat(planIda.getAttribute('data-precio')) : 0;
                total += precioPlanIda;
                htmlDetalle += `<div style="padding-left: 10px; font-size:12px;">Pasajero #${i} - Ida: +$${precioPlanIda.toLocaleString()}</div>`;
            }

            // Plan Vuelta
            if (tipoViajeInicial !== 'solo_ida') {
                const planVuelta = document.querySelector(`input[name="planes[${i}][vuelta]"]:checked`);
                let precioPlanVuelta = planVuelta ? parseFloat(planVuelta.getAttribute('data-precio')) : 0;
                total += precioPlanVuelta;
                htmlDetalle += `<div style="padding-left: 10px; font-size:12px; margin-bottom: 5px;">Pasajero #${i} - Vuelta: +$${precioPlanVuelta.toLocaleString()}</div>`;
            }
        }

        // Renderizar datos en la interfaz
        document.getElementById('detalle-precio-dinamico').innerHTML = htmlDetalle;
        document.getElementById('total-vista').textContent = '$' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    // Detectar cambios en los radio inputs
    document.querySelectorAll('.selector-vuelo, .selector-plan').forEach(elem => {
        elem.addEventListener('change', calcularTotal);
    });

    // Forzar cálculo inicial al cargar la página
    calcularTotal();
});