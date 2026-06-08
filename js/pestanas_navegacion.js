document.addEventListener("DOMContentLoaded", function () {
    const tabs = document.querySelectorAll(".tab-item");
    const contents = document.querySelectorAll(".tab-content");

    // Intercambio dinámico de Pestañas
    tabs.forEach(tab => {
        tab.addEventListener("click", function () {
            tabs.forEach(t => t.classList.remove("active"));
            contents.forEach(c => c.style.display = "none");

            this.classList.add("active");
            const targetTab = this.getAttribute("data-tab");
            
            if(targetTab === "vuelos") {
                document.getElementById("tab-vuelos").style.display = "block";
            } else if(targetTab === "estado") {
                document.getElementById("tab-estado").style.display = "block";
            } else if(targetTab === "reserva") {
                document.getElementById("tab-reserva").style.display = "block";
                cargarHistorialReservas(); // Carga las compras automáticamente al pulsar la pestaña
            }
        });
    });

    // --- LÓGICA PARA EL ESTADO DEL VUELO (PNR) ---
    const btnBuscarPnr = document.getElementById("btn-buscar-pnr");
    if (btnBuscarPnr) {
        btnBuscarPnr.addEventListener("click", function () {
            const pnrValue = document.getElementById("pnr-input").value.trim();
            const contenedorResultado = document.getElementById("resultado-pnr");

            if (pnrValue.length < 5) {
                contenedorResultado.innerHTML = `<p style="color:red; font-weight:600;">⚠️ Por favor, ingresa un código de reserva válido.</p>`;
                return;
            }

            contenedorResultado.innerHTML = `<p style="color:#666;">Buscando código ${pnrValue} en el sistema...</p>`;

            fetch(`index.php?action=consultar_pnr&pnr=${pnrValue}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        contenedorResultado.innerHTML = `<p style="color:red; font-weight:600;">❌ No se encontró ninguna reserva activa con el código "${pnrValue}".</p>`;
                        return;
                    }

                    // Armamos una tarjeta visual con el estado de los vuelos vinculados a ese PNR
                    let html = `<div style="background:#f4f6f9; border-left:5px solid #ffcc00; padding:15px; border-radius:4px; margin-top:10px;">`;
                    html += `<h4 style="margin-top:0;">📋 Reserva: <span style="color:#007bff;">${data[0].codigo_reserva_pnr}</span></h4>`;
                    
                    data.forEach(ticket => {
                        let badgeColor = ticket.estado_vuelo === 'Programado' ? '#28a745' : '#dc3545';
                        html += `
                            <div style="border-bottom: 1px solid #ddd; padding: 10px 0; font-size:14px;">
                                <strong>✈️ Vuelo:</strong> ${ticket.numero_vuelo} | 
                                <strong>Ruta:</strong> ${ticket.origen_iata} ➡️ ${ticket.destino_iata}<br>
                                <strong>Salida:</strong> ${ticket.fecha_salida} <br>
                                <strong>Pasajero:</strong> ${ticket.nombre} ${ticket.apellido} | 
                                <strong>Asiento:</strong> ${ticket.numero_asiento ? ticket.numero_asiento : 'Sin asignar'}<br>
                                <strong>Estado del Vuelo:</strong> <span style="background:${badgeColor}; color:white; padding:2px 8px; border-radius:10px; font-size:12px; font-weight:bold;">${ticket.estado_vuelo}</span>
                            </div>
                        `;
                    });
                    html += `</div>`;
                    contenedorResultado.innerHTML = html;
                })
                .catch(error => {
                    console.error("Error:", error);
                    contenedorResultado.innerHTML = `<p style="color:red;">Error al procesar la consulta externa.</p>`;
                });
        });
    }

    // --- LÓGICA PARA MI RESERVA (HISTORIAL) ---
    function cargarHistorialReservas() {
        const listaHistorial = document.getElementById("lista-historial");

        fetch(`index.php?action=historial_reservas`)
            .then(response => response.json())
            .then(data => {
                if (data.error && data.error === 'No iniciado') {
                    listaHistorial.innerHTML = `<p style="color:#dc3545; font-weight:600;">🔒 Por favor, inicia sesión en tu cuenta para ver tu historial de vuelos comprados.</p>`;
                    return;
                }

                if (data.length === 0) {
                    listaHistorial.innerHTML = `<p style="color:#666;">Aún no has realizado ninguna compra de pasajes con nosotros.</p>`;
                    return;
                }

                // Generamos la lista estructurada del historial
                let html = `<div style="max-height: 300px; overflow-y: auto; padding-right:5px;">`;
                
                // Agrupamos u organizamos las filas encontradas
                data.forEach(reserva => {
                    html += `
                        <div style="background: #ffffff; border: 1px solid #e0e0e0; padding: 12px; margin-bottom: 10px; border-radius: 6px; font-size: 13px;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                                <span><strong>Código PNR:</strong> <span style="color:#007bff; font-weight:bold;">${reserva.codigo_reserva_pnr}</span></span>
                                <span style="color:#666;">F. Compra: ${reserva.fecha_compra}</span>
                            </div>
                            <div style="color:#333;">
                                <strong>Vuelo:</strong> ${reserva.numero_vuelo} (${reserva.origen_iata} ➡️ ${reserva.destino_iata}) | 
                                <strong>Salida:</strong> ${reserva.fecha_salida}
                            </div>
                            <div style="margin-top:5px; text-align:right; font-weight:bold; color:#28a745;">
                                Total Orden: $${parseFloat(reserva.monto_total_pagado).toLocaleString('es-AR', {minimumFractionDigits: 2})}
                            </div>
                        </div>
                    `;
                });

                html += `</div>`;
                listaHistorial.innerHTML = html;
            })
            .catch(error => {
                console.error("Error:", error);
                listaHistorial.innerHTML = `<p style="color:red;">Error al recuperar el historial.</p>`;
            });
    }
});