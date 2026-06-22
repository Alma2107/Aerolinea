document.addEventListener("DOMContentLoaded", function() {
    const totalVista = document.getElementById('total-vista');
    const bloqueMaletas = document.getElementById('bloque-maletas');
    const listaMaletas = document.getElementById('lista-maletas-resumen');
    const baseDinamica = parseFloat(totalVista.getAttribute('data-base'));

    function recalcularMaletas() {
        let cargoTotalEquipajes = 0;
        let htmlResumen = "";
        const inputs = document.querySelectorAll('.input-equipaje');

        inputs.forEach(input => {
            const cantidad = parseInt(input.value) || 0;
            if (cantidad > 0) {
                const precioUnidad = parseFloat(input.getAttribute('data-precio')) || 0;
                const nombreDetalle = input.getAttribute('data-nombre');
                const subtotalItem = precioUnidad * cantidad;

                cargoTotalEquipajes += subtotalItem;
                htmlResumen += `<li>${nombreDetalle} (x${cantidad}) +$${subtotalItem.toFixed(2)}</li>`;
            }
        });

        if (cargoTotalEquipajes > 0) {
            bloqueMaletas.style.display = "block";
            listaMaletas.innerHTML = htmlResumen;
        } else {
            bloqueMaletas.style.display = "none";
            listaMaletas.innerHTML = "";
        }

        let nuevoTotalFinal = baseDinamica + cargoTotalEquipajes;
        totalVista.textContent = '$' + nuevoTotalFinal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    // Escuchar eventos de cambio de número o tipeo manual
    document.querySelectorAll('.input-equipaje').forEach(input => {
        input.addEventListener('input', recalcularMaletas);
        input.addEventListener('change', recalcularMaletas);
    });
});