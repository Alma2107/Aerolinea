-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-06-2026 a las 17:15:53
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `aerolinea`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aeropuertos`
--

CREATE TABLE `aeropuertos` (
  `codigo_iata` varchar(3) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `ciudad` varchar(100) NOT NULL,
  `pais` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `aeropuertos`
--

INSERT INTO `aeropuertos` (`codigo_iata`, `nombre`, `ciudad`, `pais`) VALUES
('AEP', 'Aeroparque Jorge Newbery', 'Buenos Aires', 'Argentina'),
('BRC', 'Aeropuerto de Bariloche', 'Bariloche', 'Argentina'),
('COR', 'Aeropuerto de Córdoba', 'Córdoba', 'Argentina'),
('EZE', 'Aeropuerto Internacional Ministro Pistarini', 'Buenos Aires', 'Argentina'),
('GIG', 'Aeropuerto Internacional Galeão', 'Río de Janeiro', 'Brasil'),
('GRU', 'Aeropuerto Internacional de São Paulo-Guarulhos', 'São Paulo', 'Brasil'),
('MAD', 'Aeropuerto de Madrid-Barajas', 'Madrid', 'España'),
('MDZ', 'Aeropuerto de Mendoza', 'Mendoza', 'Argentina'),
('MIA', 'Aeropuerto Internacional de Miami', 'Miami', 'Estados Unidos'),
('SCL', 'Aeropuerto Internacional Arturo Merino Benítez', 'Santiago', 'Chile');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asientos_avion`
--

CREATE TABLE `asientos_avion` (
  `id_asiento_avion` int(11) NOT NULL,
  `id_avion` int(11) NOT NULL,
  `numero_asiento` varchar(10) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `cargo_extra` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asientos_avion`
--

INSERT INTO `asientos_avion` (`id_asiento_avion`, `id_avion`, `numero_asiento`, `categoria`, `cargo_extra`) VALUES
(1, 1, '1A', 'Business', 8500.00),
(2, 1, '1B', 'Business', 8500.00),
(3, 1, '12A', 'Premium Economy', 4500.00),
(4, 1, '12B', 'Premium Economy', 4500.00),
(5, 1, '20A', 'Estándar', 0.00),
(6, 2, '1A', 'Business', 9000.00),
(7, 2, '5C', 'Estándar Preferente', 2000.00),
(8, 2, '15F', 'Estándar', 0.00),
(9, 3, '2L', 'First Class', 15000.00),
(10, 8, '10B', 'Estándar', 0.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aviones`
--

CREATE TABLE `aviones` (
  `id_avion` int(11) NOT NULL,
  `modelo` varchar(50) NOT NULL,
  `capacidad` int(11) NOT NULL,
  `estado` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `aviones`
--

INSERT INTO `aviones` (`id_avion`, `modelo`, `capacidad`, `estado`) VALUES
(1, 'Boeing 737 Max 8', 186, 'Activo'),
(2, 'Boeing 737-800', 174, 'Activo'),
(3, 'Boeing 787 Dreamliner', 246, 'Activo'),
(4, 'Airbus A350', 300, 'Activo'),
(5, 'Airbus A321neo', 220, 'Activo'),
(6, 'Airbus A330-200', 260, 'Activo'),
(7, 'Boeing 777-300ER', 396, 'Activo'),
(8, 'Embraer 190', 96, 'Activo'),
(9, 'Airbus A320ceo', 168, 'Mantenimiento'),
(10, 'Boeing 737-700', 138, 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `estado_cuenta` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre`, `apellido`, `email`, `telefono`, `password_hash`, `estado_cuenta`) VALUES
(1, 'Alma', 'Carena', 'carenaalma2@gmail.com', '+541153392209', '$2y$10$p.rhHeHGlwjdaLXGwf9XquLSqxhFOlMt52xPCDbyrhs9/cwWyKnMG', 1),
(2, 'Ezequiel', 'Martínez', 'martinezequiel@gmail.com', '+541166667777', '$2y$10$XsKBb3SCSpCLJLVub9mFpe4gV1mtTkA0Uk.HQIXVb.bsncZg.3Eai', 1),
(3, 'Juan', 'Pérez', 'juan.perez@gmail.com', '+541122334455', 'hash_3', 1),
(4, 'María', 'Gómez', 'maria.gomez@hotmail.com', '+542614556677', 'hash_4', 1),
(5, 'Carlos', 'Rodríguez', 'carlos.rod@yahoo.com', '+543519876543', 'hash_5', 1),
(6, 'Lucía', 'Fernández', 'lucia.f@gmail.com', NULL, 'hash_6', 1),
(7, 'Santiago', 'López', 'santi.lopez@outlook.com', '+56988887777', 'hash_7', 1),
(8, 'Ana', 'Martínez', 'ana.mtnz@gmail.com', '+541134432332', 'hash_8', 1),
(9, 'Diego', 'Sánchez', 'dieguito@gmail.com', NULL, 'hash_9', 0),
(10, 'Laura', 'Álvarez', 'laura.alvarez@live.com.ar', '+542944552211', 'hash_10', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras_ordenes`
--

CREATE TABLE `compras_ordenes` (
  `id_orden` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `fecha_compra` datetime NOT NULL,
  `monto_total_pagado` decimal(10,2) NOT NULL,
  `id_metodo_pago` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `compras_ordenes`
--

INSERT INTO `compras_ordenes` (`id_orden`, `id_cliente`, `fecha_compra`, `monto_total_pagado`, `id_metodo_pago`) VALUES
(1, 1, '2026-06-01 10:30:00', 50000.00, 1),
(2, 1, '2026-06-02 15:45:12', 62500.00, 5),
(3, 2, '2026-06-07 04:20:30', 75000.00, 1),
(4, 3, '2026-06-07 11:00:00', 147500.00, 2),
(5, 4, '2026-06-07 11:15:00', 195000.00, 3),
(6, 5, '2026-06-07 12:00:00', 16000.00, 7),
(7, 6, '2026-06-07 12:30:00', 45500.00, 1),
(8, 7, '2026-06-07 13:10:22', 230000.00, 9),
(9, 8, '2026-06-07 14:02:00', 74000.00, 5),
(10, 10, '2026-06-07 15:50:00', 59000.00, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metodos_pago`
--

CREATE TABLE `metodos_pago` (
  `id_metodo_pago` int(11) NOT NULL,
  `nombre_metodo` varchar(50) NOT NULL,
  `banco_proveedor` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `metodos_pago`
--

INSERT INTO `metodos_pago` (`id_metodo_pago`, `nombre_metodo`, `banco_proveedor`) VALUES
(1, 'Tarjeta de Crédito', 'Visa Global'),
(2, 'Tarjeta de Crédito', 'Mastercard Internacional'),
(3, 'Tarjeta de Débito', 'Visa Débito Santander'),
(4, 'Tarjeta de Débito', 'Maestro Banco Galicia'),
(5, 'Billetera Virtual', 'Mercado Pago'),
(6, 'Billetera Virtual', 'Modo'),
(7, 'Transferencia Bancaria', 'Red Link / DEBIN'),
(8, 'Transferencia Bancaria', 'Red Banelco'),
(9, 'Tarjeta de Crédito', 'American Express'),
(10, 'Criptomonedas', 'Binance Pay');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pasajeros`
--

CREATE TABLE `pasajeros` (
  `id_pasajero` int(11) NOT NULL,
  `tipo_documento` varchar(20) NOT NULL,
  `numero_documento` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `asistencia_especial` tinyint(1) DEFAULT 0,
  `detalles_medicos` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pasajeros`
--

INSERT INTO `pasajeros` (`id_pasajero`, `tipo_documento`, `numero_documento`, `nombre`, `apellido`, `fecha_nacimiento`, `asistencia_especial`, `detalles_medicos`) VALUES
(1, 'DNI', '45123456', 'Juan Carlos', 'Pérez', '1990-05-14', 0, NULL),
(2, 'DNI', '48987654', 'Martina', 'Gómez', '2002-11-23', 1, 'Silla de ruedas por esguince.'),
(3, 'Pasaporte', 'AAA111222', 'John', 'Smith', '1985-08-02', 0, NULL),
(4, 'DNI', '32456789', 'Alma', 'Carena', '1987-04-12', 0, NULL),
(5, 'DNI', '12345678', 'Roberto', 'Rodríguez', '1955-01-30', 1, 'Hipertenso con medicación.'),
(6, 'DNI', '52111222', 'Tomás', 'Fernández', '2010-09-05', 0, 'Menor no acompañado.'),
(7, 'Pasaporte', 'BBB444555', 'Emily', 'Watson', '1993-06-18', 0, NULL),
(8, 'DNI', '28999000', 'Diego', 'Sánchez', '1981-12-25', 0, NULL),
(9, 'DNI', '41000333', 'Laura', 'Álvarez', '1998-03-21', 0, NULL),
(10, 'DNI', '95444111', 'Pedro', 'González', '1974-07-07', 0, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planes_tarifas`
--

CREATE TABLE `planes_tarifas` (
  `id_plan` int(11) NOT NULL,
  `nombre_plan` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `cargo_extra_plan` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `planes_tarifas`
--

INSERT INTO `planes_tarifas` (`id_plan`, `nombre_plan`, `descripcion`, `cargo_extra_plan`) VALUES
(1, 'BASIC', 'Bolso o mochila pequeña.', 0.00),
(2, 'LIGHT', 'Mochila + Equipaje de mano.', 4500.00),
(3, 'SMART', 'Mochila + Carry-on + Bodega 23kg + Asiento.', 12000.00),
(4, 'FULL FLEX', 'Cambios ilimitados, devolución y asientos top.', 25000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios_adicionales`
--

CREATE TABLE `servicios_adicionales` (
  `id_servicio` int(11) NOT NULL,
  `nombre_servicio` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_servicio` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `servicios_adicionales`
--

INSERT INTO `servicios_adicionales` (`id_servicio`, `nombre_servicio`, `descripcion`, `precio_servicio`) VALUES
(1, 'Wi-Fi Mensajería Flota', 'WhatsApp ilimitado.', 2500.00),
(2, 'Wi-Fi Premium Streaming', 'Internet veloz para videos.', 6000.00),
(3, 'Menú Vegano Completo', 'Comida caliente sin carne.', 4500.00),
(4, 'Menú Celíaco (Sin TACC)', 'Plato caliente certificado.', 4800.00),
(5, 'Mascota en Cabina (PETC)', 'Perro/gato chico bajo asiento.', 25000.00),
(6, 'Embarque Prioritario', 'Acceso Grupo 1 sin filas.', 3500.00),
(7, 'Auriculares Premium ANC', 'Cancelación de ruido.', 1500.00),
(8, 'Acceso a Sala VIP', 'Ingreso al Lounge exclusivo.', 12000.00),
(9, 'Combo Snack & Cafetería', 'Café en grano y alfajor.', 1800.00),
(10, 'Seguro de Viaje Básico', 'Cobertura médica médica estándar.', 3000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tickets_detalle`
--

CREATE TABLE `tickets_detalle` (
  `id_ticket` int(11) NOT NULL,
  `id_orden` int(11) NOT NULL,
  `id_vuelo` int(11) NOT NULL,
  `id_pasajero` int(11) NOT NULL,
  `id_asiento_avion` int(11) DEFAULT NULL,
  `id_plan` int(11) NOT NULL,
  `codigo_reserva_pnr` varchar(6) NOT NULL,
  `precio_tramo_pagado` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tickets_detalle`
--

INSERT INTO `tickets_detalle` (`id_ticket`, `id_orden`, `id_vuelo`, `id_pasajero`, `id_asiento_avion`, `id_plan`, `codigo_reserva_pnr`, `precio_tramo_pagado`) VALUES
(1, 1, 1, 1, 5, 1, 'AX39FT', 50000.00),
(2, 2, 1, 2, 3, 2, 'MZ99EE', 59500.00),
(3, 3, 1, 4, NULL, 3, 'PO92LL', 62000.00),
(4, 4, 5, 3, 6, 2, 'QW12ER', 139500.00),
(5, 5, 7, 5, 9, 1, 'TR77UI', 195000.00),
(6, 6, 9, 6, 10, 1, 'KK88YY', 16000.00),
(7, 7, 2, 7, NULL, 1, 'BB22MM', 55000.00),
(8, 8, 5, 8, 7, 4, 'AA11QQ', 160000.00),
(9, 9, 4, 9, 8, 2, 'VV55XX', 52500.00),
(10, 10, 1, 10, 4, 2, 'ZZ00PP', 54500.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ticket_equipajes`
--

CREATE TABLE `ticket_equipajes` (
  `id_ticket_equipaje` int(11) NOT NULL,
  `id_ticket` int(11) NOT NULL,
  `id_tipo_equipaje` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_pagado` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ticket_equipajes`
--

INSERT INTO `ticket_equipajes` (`id_ticket_equipaje`, `id_ticket`, `id_tipo_equipaje`, `cantidad`, `precio_pagado`) VALUES
(1, 1, 4, 1, 14000.00),
(2, 2, 2, 1, 7500.00),
(3, 3, 1, 2, 7000.00),
(4, 4, 4, 2, 28000.00),
(5, 5, 5, 1, 22000.00),
(6, 7, 4, 1, 14000.00),
(7, 8, 6, 1, 18000.00),
(8, 9, 2, 1, 7500.00),
(9, 10, 4, 1, 14000.00),
(10, 4, 2, 1, 7500.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ticket_servicios`
--

CREATE TABLE `ticket_servicios` (
  `id_ticket_servicio` int(11) NOT NULL,
  `id_ticket` int(11) NOT NULL,
  `id_servicio` int(11) NOT NULL,
  `precio_servicio_pagado` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ticket_servicios`
--

INSERT INTO `ticket_servicios` (`id_ticket_servicio`, `id_ticket`, `id_servicio`, `precio_servicio_pagado`) VALUES
(1, 1, 1, 2500.00),
(2, 2, 5, 25000.00),
(3, 3, 3, 4500.00),
(4, 4, 2, 6000.00),
(5, 4, 9, 1800.00),
(6, 5, 4, 4800.00),
(7, 7, 6, 3500.00),
(8, 8, 8, 12000.00),
(9, 8, 2, 6000.00),
(10, 10, 9, 1800.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_equipaje`
--

CREATE TABLE `tipos_equipaje` (
  `id_tipo_equipaje` int(11) NOT NULL,
  `nombre_tipo` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipos_equipaje`
--

INSERT INTO `tipos_equipaje` (`id_tipo_equipaje`, `nombre_tipo`, `descripcion`, `precio_unitario`) VALUES
(1, 'Bolso/Mochila Adicional', 'Pieza chica extra.', 3500.00),
(2, 'Equipaje de Mano (Carry-on 10kg)', 'Maleta compartimiento superior.', 7500.00),
(3, 'Maleta de Bodega Chica (15kg)', 'Bodega tramos cortos.', 9500.00),
(4, 'Maleta de Bodega Estándar (23kg)', 'Despacho reglamentario.', 14000.00),
(5, 'Maleta de Bodega Pesada (32kg)', 'Vuelos internacionales / pesados.', 22000.00),
(6, 'Equipaje Deportivo / Especial', 'Tablas de surf, instrumentos.', 18000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vuelos`
--

CREATE TABLE `vuelos` (
  `id_vuelo` int(11) NOT NULL,
  `numero_vuelo` varchar(20) NOT NULL,
  `id_avion` int(11) NOT NULL,
  `origen_iata` varchar(3) NOT NULL,
  `destino_iata` varchar(3) NOT NULL,
  `fecha_salida` datetime NOT NULL,
  `fecha_llegada` datetime NOT NULL,
  `precio_base_vuelo` decimal(10,2) NOT NULL,
  `estado_vuelo` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vuelos`
--

INSERT INTO `vuelos` (`id_vuelo`, `numero_vuelo`, `id_avion`, `origen_iata`, `destino_iata`, `fecha_salida`, `fecha_llegada`, `precio_base_vuelo`, `estado_vuelo`) VALUES
(1, 'JA1001', 1, 'AEP', 'BRC', '2026-07-10 08:00:00', '2026-07-10 10:15:00', 50000.00, 'Programado'),
(2, 'JA1002', 1, 'AEP', 'BRC', '2026-07-10 13:30:00', '2026-07-10 15:45:00', 55000.00, 'Programado'),
(3, 'JA1003', 1, 'AEP', 'BRC', '2026-07-10 22:00:00', '2026-07-11 00:15:00', 42000.00, 'Programado'),
(4, 'JA1004', 8, 'BRC', 'AEP', '2026-07-17 12:00:00', '2026-07-17 14:15:00', 48000.00, 'Programado'),
(5, 'JA1005', 2, 'EZE', 'MIA', '2026-08-01 22:00:00', '2026-08-02 07:15:00', 135000.00, 'Programado'),
(6, 'JA1006', 2, 'MIA', 'EZE', '2026-08-10 10:30:00', '2026-08-10 19:45:00', 140000.00, 'Programado'),
(7, 'JA1007', 3, 'EZE', 'MAD', '2026-08-15 13:00:00', '2026-08-16 06:15:00', 195000.00, 'Programado'),
(8, 'JA1008', 5, 'SCL', 'GRU', '2026-07-15 14:00:00', '2026-07-15 17:45:00', 38000.00, 'Programado'),
(9, 'JA1009', 8, 'AEP', 'COR', '2026-07-11 07:00:00', '2026-07-11 08:15:00', 16000.00, 'Programado'),
(10, 'JA1010', 8, 'COR', 'MDZ', '2026-07-12 09:00:00', '2026-07-12 10:05:00', 145000.00, 'Programado');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `aeropuertos`
--
ALTER TABLE `aeropuertos`
  ADD PRIMARY KEY (`codigo_iata`);

--
-- Indices de la tabla `asientos_avion`
--
ALTER TABLE `asientos_avion`
  ADD PRIMARY KEY (`id_asiento_avion`),
  ADD KEY `id_avion` (`id_avion`);

--
-- Indices de la tabla `aviones`
--
ALTER TABLE `aviones`
  ADD PRIMARY KEY (`id_avion`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `compras_ordenes`
--
ALTER TABLE `compras_ordenes`
  ADD PRIMARY KEY (`id_orden`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_metodo_pago` (`id_metodo_pago`);

--
-- Indices de la tabla `metodos_pago`
--
ALTER TABLE `metodos_pago`
  ADD PRIMARY KEY (`id_metodo_pago`);

--
-- Indices de la tabla `pasajeros`
--
ALTER TABLE `pasajeros`
  ADD PRIMARY KEY (`id_pasajero`),
  ADD UNIQUE KEY `numero_documento` (`numero_documento`);

--
-- Indices de la tabla `planes_tarifas`
--
ALTER TABLE `planes_tarifas`
  ADD PRIMARY KEY (`id_plan`);

--
-- Indices de la tabla `servicios_adicionales`
--
ALTER TABLE `servicios_adicionales`
  ADD PRIMARY KEY (`id_servicio`);

--
-- Indices de la tabla `tickets_detalle`
--
ALTER TABLE `tickets_detalle`
  ADD PRIMARY KEY (`id_ticket`),
  ADD KEY `id_orden` (`id_orden`),
  ADD KEY `id_vuelo` (`id_vuelo`),
  ADD KEY `id_pasajero` (`id_pasajero`),
  ADD KEY `id_asiento_avion` (`id_asiento_avion`),
  ADD KEY `id_plan` (`id_plan`);

--
-- Indices de la tabla `ticket_equipajes`
--
ALTER TABLE `ticket_equipajes`
  ADD PRIMARY KEY (`id_ticket_equipaje`),
  ADD KEY `id_ticket` (`id_ticket`),
  ADD KEY `id_tipo_equipaje` (`id_tipo_equipaje`);

--
-- Indices de la tabla `ticket_servicios`
--
ALTER TABLE `ticket_servicios`
  ADD PRIMARY KEY (`id_ticket_servicio`),
  ADD KEY `id_ticket` (`id_ticket`),
  ADD KEY `id_servicio` (`id_servicio`);

--
-- Indices de la tabla `tipos_equipaje`
--
ALTER TABLE `tipos_equipaje`
  ADD PRIMARY KEY (`id_tipo_equipaje`);

--
-- Indices de la tabla `vuelos`
--
ALTER TABLE `vuelos`
  ADD PRIMARY KEY (`id_vuelo`),
  ADD KEY `id_avion` (`id_avion`),
  ADD KEY `origen_iata` (`origen_iata`),
  ADD KEY `destino_iata` (`destino_iata`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asientos_avion`
--
ALTER TABLE `asientos_avion`
  MODIFY `id_asiento_avion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `aviones`
--
ALTER TABLE `aviones`
  MODIFY `id_avion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `compras_ordenes`
--
ALTER TABLE `compras_ordenes`
  MODIFY `id_orden` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `metodos_pago`
--
ALTER TABLE `metodos_pago`
  MODIFY `id_metodo_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `pasajeros`
--
ALTER TABLE `pasajeros`
  MODIFY `id_pasajero` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `planes_tarifas`
--
ALTER TABLE `planes_tarifas`
  MODIFY `id_plan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `servicios_adicionales`
--
ALTER TABLE `servicios_adicionales`
  MODIFY `id_servicio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `tickets_detalle`
--
ALTER TABLE `tickets_detalle`
  MODIFY `id_ticket` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `ticket_equipajes`
--
ALTER TABLE `ticket_equipajes`
  MODIFY `id_ticket_equipaje` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `ticket_servicios`
--
ALTER TABLE `ticket_servicios`
  MODIFY `id_ticket_servicio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `tipos_equipaje`
--
ALTER TABLE `tipos_equipaje`
  MODIFY `id_tipo_equipaje` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `vuelos`
--
ALTER TABLE `vuelos`
  MODIFY `id_vuelo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asientos_avion`
--
ALTER TABLE `asientos_avion`
  ADD CONSTRAINT `fk_asientos_avion` FOREIGN KEY (`id_avion`) REFERENCES `aviones` (`id_avion`) ON DELETE CASCADE;

--
-- Filtros para la tabla `compras_ordenes`
--
ALTER TABLE `compras_ordenes`
  ADD CONSTRAINT `fk_ordenes_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`),
  ADD CONSTRAINT `fk_ordenes_pago` FOREIGN KEY (`id_metodo_pago`) REFERENCES `metodos_pago` (`id_metodo_pago`);

--
-- Filtros para la tabla `tickets_detalle`
--
ALTER TABLE `tickets_detalle`
  ADD CONSTRAINT `fk_tickets_asiento` FOREIGN KEY (`id_asiento_avion`) REFERENCES `asientos_avion` (`id_asiento_avion`),
  ADD CONSTRAINT `fk_tickets_orden` FOREIGN KEY (`id_orden`) REFERENCES `compras_ordenes` (`id_orden`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tickets_pasajero` FOREIGN KEY (`id_pasajero`) REFERENCES `pasajeros` (`id_pasajero`),
  ADD CONSTRAINT `fk_tickets_plan` FOREIGN KEY (`id_plan`) REFERENCES `planes_tarifas` (`id_plan`),
  ADD CONSTRAINT `fk_tickets_vuelo` FOREIGN KEY (`id_vuelo`) REFERENCES `vuelos` (`id_vuelo`);

--
-- Filtros para la tabla `ticket_equipajes`
--
ALTER TABLE `ticket_equipajes`
  ADD CONSTRAINT `fk_te_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tickets_detalle` (`id_ticket`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_te_tipo` FOREIGN KEY (`id_tipo_equipaje`) REFERENCES `tipos_equipaje` (`id_tipo_equipaje`);

--
-- Filtros para la tabla `ticket_servicios`
--
ALTER TABLE `ticket_servicios`
  ADD CONSTRAINT `fk_ts_servicio` FOREIGN KEY (`id_servicio`) REFERENCES `servicios_adicionales` (`id_servicio`),
  ADD CONSTRAINT `fk_ts_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tickets_detalle` (`id_ticket`) ON DELETE CASCADE;

--
-- Filtros para la tabla `vuelos`
--
ALTER TABLE `vuelos`
  ADD CONSTRAINT `fk_vuelos_avion` FOREIGN KEY (`id_avion`) REFERENCES `aviones` (`id_avion`),
  ADD CONSTRAINT `fk_vuelos_destino` FOREIGN KEY (`destino_iata`) REFERENCES `aeropuertos` (`codigo_iata`),
  ADD CONSTRAINT `fk_vuelos_origen` FOREIGN KEY (`origen_iata`) REFERENCES `aeropuertos` (`codigo_iata`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
