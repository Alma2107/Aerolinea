document.addEventListener('DOMContentLoaded', () => {
    const contenedorTramos = document.getElementById('tramos-contenedor');
    const btnNuevoTramo = document.getElementById('btn-nuevo-tramo');
    const radioIdaVuelta = document.getElementById('radio-ida-vuelta');
    const radioSoloIda = document.getElementById('radio-solo-ida');
    const radioMultidestino = document.getElementById('radio-multidestino');

    let tramoCount = 0;
    let fechasPermitidas = [];

    // Llamada inicial para traer las fechas permitidas desde el backend
    fetch('index.php?action=obtener_fechas_vuelos')
        .then(res => res.json())
        .then(data => { 
            fechasPermitidas = data; 
            inicializarFormulario(); 
        })
        .catch(() => inicializarFormulario());

    function inicializarFormulario() {
        removerPasajerosMultidestino();
        if (radioMultidestino.checked) {
            btnNuevoTramo.style.display = 'inline-flex';
            contenedorTramos.innerHTML = '';
            agregarFilaTramo(true);
            agregarFilaTramo(true);
            agregarPasajerosMultidestino();
        } else {
            btnNuevoTramo.style.display = 'none';
            contenedorTramos.innerHTML = '';
            agregarFilaEstandar(radioIdaVuelta.checked);
        }
    }

    function agregarPasajerosMultidestino() {
        if (document.getElementById('pasajeros-multi')) return;
        const div = document.createElement('div');
        div.id = 'pasajeros-multi';
        div.className = 'input-group';
        div.style.cssText = 'width:120px;margin-bottom:20px;';
        div.innerHTML = `
            <label>Pasajeros</label>
            <input type="number" name="pasajeros" min="1" max="5" value="1" class="input-field" required>
        `;
        contenedorTramos.insertAdjacentElement('afterend', div);
    }

    function removerPasajerosMultidestino() {
        const el = document.getElementById('pasajeros-multi');
        if (el) el.remove();
    }

    function agregarFilaEstandar(incluirVuelta) {
        tramoCount = 1;
        const div = document.createElement('div');
        div.className = 'tramo-row standard-row' + (incluirVuelta ? '' : ' solo-ida');
        div.innerHTML = `
            <div class="input-group">
                <label>Origen</label>
                <input type="text" id="orig_input_1" class="input-field" required>
                <input type="hidden" name="origen" id="orig_hidden_1">
                <div id="orig_sug_1" class="autocomplete-suggestions" style="display:none;"></div>
            </div>
            <div class="input-group">
                <label>Destino</label>
                <input type="text" id="dest_input_1" class="input-field" required>
                <input type="hidden" name="destino" id="dest_hidden_1">
                <div id="dest_sug_1" class="autocomplete-suggestions" style="display:none;"></div>
            </div>
            <div class="input-group">
                <label>Partida</label>
                <input type="text" id="date_input_1" class="input-field" readonly required>
                <input type="hidden" name="fecha_ida" id="date_hidden_1">
                <div id="cal_box_1" class="custom-calendar-picker"></div>
            </div>
            ${incluirVuelta ? `
            <div class="input-group">
                <label>Regreso</label>
                <input type="text" id="date_input_ret" class="input-field" readonly required>
                <input type="hidden" name="fecha_vuelta" id="date_hidden_ret">
                <div id="cal_box_ret" class="custom-calendar-picker"></div>
            </div>` : ''}
            <div class="input-group">
                <label>Pasajeros</label>
                <input type="number" name="pasajeros" min="1" max="5" value="1" class="input-field" required>
            </div>
        `;
        contenedorTramos.appendChild(div);
        vincularAutocompletado(1);
        vincularCalendarioDB(1);
        if (incluirVuelta) vincularCalendarioDB('ret');
    }

    function agregarFilaTramo(esObligatorio = false) {
        tramoCount++;
        const idActual = tramoCount;

        const div = document.createElement('div');
        div.className = 'tramo-row multidestino-row';
        div.id = `tramo_fila_${idActual}`;
        div.innerHTML = `
            <div class="input-group">
                <label>Origen (Tramo ${idActual - 1})</label>
                <input type="text" id="orig_input_${idActual}" class="input-field" placeholder="Ciudad de Origen" required>
                <input type="hidden" name="origen_multidestino[]" id="orig_hidden_${idActual}">
                <div id="orig_sug_${idActual}" class="autocomplete-suggestions" style="display:none;"></div>
            </div>
            <div class="input-group">
                <label>Destino (Tramo ${idActual - 1})</label>
                <input type="text" id="dest_input_${idActual}" class="input-field" placeholder="Ciudad de Destino" required>
                <input type="hidden" name="destino_multidestino[]" id="dest_hidden_${idActual}">
                <div id="dest_sug_${idActual}" class="autocomplete-suggestions" style="display:none;"></div>
            </div>
            <div class="input-group">
                <label>Fecha Vuelo</label>
                <input type="text" id="date_input_${idActual}" class="input-field" placeholder="Seleccionar fecha" readonly required>
                <input type="hidden" name="fecha_multidestino[]" id="date_hidden_${idActual}">
                <div id="cal_box_${idActual}" class="custom-calendar-picker"></div>
            </div>
            ${!esObligatorio ? `<button type="button" class="btn-remove-tramo" data-id="${idActual}">✕</button>` : '<div></div>'}
        `;
        contenedorTramos.appendChild(div);
        vincularAutocompletado(idActual);
        vincularCalendarioDB(idActual);

        // Evento para el botón de eliminar tramo recién creado
        if(!esObligatorio) {
            div.querySelector('.btn-remove-tramo').addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const fila = document.getElementById(`tramo_fila_${id}`);
                if(fila) fila.remove();
            });
        }
    }

    // Escuchadores de eventos para los cambios en tipos de viaje
    radioIdaVuelta.addEventListener('change', inicializarFormulario);
    radioSoloIda.addEventListener('change', inicializarFormulario);
    radioMultidestino.addEventListener('change', inicializarFormulario);
    btnNuevoTramo.addEventListener('click', () => agregarFilaTramo(false));

    function vincularAutocompletado(id) {
        ['orig', 'dest'].forEach(tipo => {
            const input = document.getElementById(`${tipo}_input_${id}`);
            const hidden = document.getElementById(`${tipo}_hidden_${id}`);
            const sugBox = document.getElementById(`${tipo}_sug_${id}`);

            input.addEventListener('input', function() {
                const q = this.value.trim();
                if(q.length < 1) { sugBox.style.display = 'none'; return; }

                fetch(`index.php?action=buscar_aeropuerto&q=${encodeURIComponent(q)}`)
                    .then(res => res.json())
                    .then(data => {
                        sugBox.innerHTML = '';
                        if(data.length > 0) {
                            sugBox.style.display = 'block';
                            data.forEach(item => {
                                const opt = document.createElement('div');
                                opt.className = 'suggestion-item';
                                opt.innerText = `${item.ciudad} (${item.codigo_iata})`;
                                opt.addEventListener('click', () => {
                                    input.value = `${item.ciudad} (${item.codigo_iata})`;
                                    hidden.value = item.codigo_iata;
                                    sugBox.style.display = 'none';
                                });
                                sugBox.appendChild(opt);
                            });
                        } else { sugBox.style.display = 'none'; }
                    });
            });
            document.addEventListener('click', (e) => { if (e.target !== input) sugBox.style.display = 'none'; });
        });
    }

    function vincularCalendarioDB(id) {
        const input = document.getElementById(`date_input_${id}`);
        const hidden = document.getElementById(`date_hidden_${id}`);
        const calBox = document.getElementById(`cal_box_${id}`);
        let navDate = new Date();

        function renderMes() {
            calBox.innerHTML = '';
            const año = navDate.getFullYear();
            const mes = navDate.getMonth();
            const meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

            const header = document.createElement('div');
            header.className = 'calendar-header';
            header.innerHTML = `<button type="button" class="prev-m">◀</button><span>${meses[mes]} ${año}</span><button type="button" class="next-m">▶</button>`;
            calBox.appendChild(header);

            header.querySelector('.prev-m').addEventListener('click', (e) => { e.stopPropagation(); navDate.setMonth(navDate.getMonth() - 1); renderMes(); });
            header.querySelector('.next-m').addEventListener('click', (e) => { e.stopPropagation(); navDate.setMonth(navDate.getMonth() + 1); renderMes(); });

            const grid = document.createElement('div');
            grid.className = 'calendar-days-grid';
            ['Do','Lu','Ma','Mi','Ju','Vi','Sá'].forEach(d => { grid.innerHTML += `<div class="calendar-day-label">${d}</div>`; });

            const primerDia = new Date(año, mes, 1).getDay();
            const ultimoDia = new Date(año, mes + 1, 0).getDate();

            for(let i=0; i<primerDia; i++) { grid.appendChild(document.createElement('div')); }

            for(let d=1; d<=ultimoDia; d++) {
                const celda = document.createElement('div');
                celda.className = 'calendar-day';
                celda.innerText = d;

                const mStr = String(mes + 1).padStart(2, '0');
                const dStr = String(d).padStart(2, '0');
                const fFull = `${año}-${mStr}-${dStr}`;

                if(fechasPermitidas.includes(fFull)) {
                    celda.classList.add('available-flight');
                    celda.addEventListener('click', () => {
                        input.value = `${dStr}/${mStr}/${año}`;
                        hidden.value = fFull;
                        calBox.style.display = 'none';
                    });
                }
                grid.appendChild(celda);
            }
            calBox.appendChild(grid);
        }

        input.addEventListener('click', (e) => {
            e.stopPropagation();
            document.querySelectorAll('.custom-calendar-picker').forEach(c => { if(c !== calBox) c.style.display = 'none'; });
            calBox.style.display = calBox.style.display === 'block' ? 'none' : 'block';
            renderMes();
        });

        document.addEventListener('click', () => { calBox.style.display = 'none'; });
        calBox.addEventListener('click', (e) => e.stopPropagation());
    }
});