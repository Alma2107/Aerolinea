<?php

function obtenerPromos(): array {
    return [
        'BARILO20' => [
            'titulo' => '20% OFF en Bariloche',
            'descripcion' => 'Descuento aplicado a vuelos con destino Bariloche.',
            'destino' => 'BRC',
            'descuento' => 0.20,
        ],
        'EQUIPAJEGRATIS' => [
            'titulo' => 'Equipaje gratis',
            'descripcion' => 'El beneficio se aplicara en el paso de equipaje.',
            'destino' => '',
            'descuento' => 0,
        ],
        'CORDOBA2X1' => [
            'titulo' => '2x1 a Cordoba',
            'descripcion' => 'Promocion preparada para dos pasajeros hacia Cordoba.',
            'destino' => 'COR',
            'descuento' => 0.50,
        ],
    ];
}

function obtenerPromoActiva(string $codigoPromo): ?array {
    $promos = obtenerPromos();
    return $promos[strtoupper(trim($codigoPromo))] ?? null;
}

function normalizarIdsVuelos($valor): array {
    if (is_array($valor)) {
        $ids = [];
        foreach ($valor as $item) {
            $id = (int)$item;
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        return array_values(array_unique($ids));
    }

    $id = (int)$valor;
    return $id > 0 ? [$id] : [];
}

function normalizarVuelosSeleccionados($valor, array $segmentosBusqueda = []): array {
    $seleccion = [];

    if (is_array($valor)) {
        foreach ($valor as $indiceTramo => $idVuelo) {
            $idVuelo = (int)$idVuelo;
            if ($idVuelo <= 0) {
                continue;
            }

            $tramo = is_numeric($indiceTramo) ? (int)$indiceTramo : 1;
            $segmento = $segmentosBusqueda[$tramo - 1] ?? [];
            $seleccion[] = [
                'id_vuelo' => $idVuelo,
                'tramo' => $tramo,
                'origen' => $segmento['origen'] ?? '',
                'destino' => $segmento['destino'] ?? '',
                'fecha' => $segmento['fecha'] ?? '',
            ];
        }
        return $seleccion;
    }

    $idVuelo = (int)$valor;
    if ($idVuelo <= 0) {
        return [];
    }

    $segmento = $segmentosBusqueda[0] ?? [];
    return [[
        'id_vuelo' => $idVuelo,
        'tramo' => 1,
        'origen' => $segmento['origen'] ?? '',
        'destino' => $segmento['destino'] ?? '',
        'fecha' => $segmento['fecha'] ?? '',
    ]];
}

function precioTramoPorPasajero(float $precioBase, int $indicePasajero, int $cantidadPasajeros, string $codigoPromo, string $destinoVuelo = ''): float {
    $codigoPromo = strtoupper(trim($codigoPromo));
    $destinoVuelo = strtoupper(trim($destinoVuelo));

    if ($codigoPromo === 'BARILO20' && $destinoVuelo === 'BRC') {
        return $precioBase * 0.80;
    }

    if ($codigoPromo === 'CORDOBA2X1' && $destinoVuelo === 'COR') {
        $pasajerosPagando = max(1, (int)ceil($cantidadPasajeros / 2));
        return $indicePasajero < $pasajerosPagando ? $precioBase : 0.0;
    }

    return $precioBase;
}

function calcularPrecioTotalVuelo(float $precioBase, int $cantidadPasajeros, string $codigoPromo, string $destinoVuelo = ''): float {
    $total = 0.0;
    for ($i = 0; $i < $cantidadPasajeros; $i++) {
        $total += precioTramoPorPasajero($precioBase, $i, $cantidadPasajeros, $codigoPromo, $destinoVuelo);
    }
    return $total;
}

function pesoDesdeNombre(string $nombreTipo): int {
    $nombreTipo = strtolower($nombreTipo);

    if (preg_match('/(\d+)kg/i', $nombreTipo, $coincidencia)) {
        return (int)$coincidencia[1];
    }

    if (str_contains($nombreTipo, 'carry-on')) {
        return 10;
    }

    if (str_contains($nombreTipo, 'mochila')) {
        return 2;
    }

    if (str_contains($nombreTipo, 'especial')) {
        return 20;
    }

    return 0;
}

function validarEquipaje(array $idsEquipaje, array $tiposEquipaje, int $cantidadPasajeros = 1): array {
    $pesoTotal = 0;
    foreach ($idsEquipaje as $idEquipaje) {
        $idEquipaje = (int)$idEquipaje;
        $nombreTipo = $tiposEquipaje[$idEquipaje]['nombre_tipo'] ?? '';
        $pesoTotal += pesoDesdeNombre($nombreTipo);
    }

    $limite = max(23, 23 * max(1, $cantidadPasajeros));
    return [
        'ok' => $pesoTotal <= $limite,
        'peso_total' => $pesoTotal,
        'limite' => $limite,
        'mensaje' => $pesoTotal > $limite
            ? 'El total de equipaje supera el límite de ' . $limite . 'kg para este grupo.'
            : '',
    ];
}
