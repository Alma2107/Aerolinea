document.addEventListener("DOMContentLoaded", function() {
    const totalVista = document.getElementById('total-vista');
    const contenedorServicios = document.getElementById('contenedor-servicios-dinamico');
    const listaServiciosVista = document.getElementById('lista-servicios-vista');
    const baseTotal = parseFloat(totalVista.getAttribute('data-base'));

    function actualizarServiciosYTotal() {
        let cargoServicios = 0;
        let htmlLista = "";
        const seleccionados = document.querySelectorAll('.check-servicio:checked');

        seleccionados.forEach(checkbox => {
            const precio = parseFloat(checkbox.getAttribute('data-precio')) || 0;
            const nombre = checkbox.getAttribute('data-nombre');
            cargoServicios += precio;
            htmlLista += `<li>${nombre} (+$${precio.toFixed(2)})</li>`;
        });

        // Mostrar u ocultar la sección de servicios en el resumen lateral
        if (seleccionados.length > 0) {
            contenedorServicios.style.display = "block";
            listaServiciosVista.innerHTML = htmlLista;
        } else {
            contenedorServicios.style.display = "none";
            listaServiciosVista.innerHTML = "";
        }

        // Modificar precio final reflejado
        let nuevoTotal = baseTotal + cargoServicios;
        totalVista.textContent = '$' + nuevoTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    document.querySelectorAll('.check-servicio').forEach(check => {
        check.addEventListener('change', actualizarServiciosYTotal);
    });

    // Ejecución inicial preventiva
    actualizarServiciosYTotal();
});