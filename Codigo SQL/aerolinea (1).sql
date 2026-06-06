-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-06-2026 a las 02:34:16
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
  `pais` varchar(100) NOT NULL,
  PRIMARY KEY (`codigo_iata`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `aeropuertos`
--

INSERT INTO `aeropuertos` (`codigo_iata`, `nombre`, `ciudad`, `pais`) VALUES
('AEP', 'Aeroparque Jorge Newbery', 'Buenos Aires', 'Argentina'),
('BRC', 'Aeropuerto de Bariloche', 'Bariloche', 'Argentina');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aviones`
--

CREATE TABLE `aviones` (
  `id_avion` int(11) NOT NULL AUTO_INCREMENT,
  `modelo` varchar(50) NOT NULL,
  `capacidad` int(11) NOT NULL,
  `estado` varchar(20) NOT NULL,
  PRIMARY KEY (`id_avion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `aviones`
--

INSERT INTO `aviones` (`id_avion`, `modelo`, `capacidad`, `estado`) VALUES
(1, 'Airbus A320', 186, 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asientos_avion`
--

CREATE TABLE `asientos_avion` (
  `id_asiento_avion` int(11) NOT NULL AUTO_INCREMENT,
  `id_avion` int(11) DEFAULT NULL,
  `numero_asiento` varchar(10) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `cargo_extra` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_asiento_avion`),
  KEY `id_avion` (`id_avion`),
  CONSTRAINT `asientos_avion_ibfk_1` FOREIGN KEY (`id_avion`) REFERENCES `aviones` (`id_avion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asientos_avion`
--

INSERT INTO `asientos_avion` (`id_asiento_avion`, `id_avion`, `numero_asiento`, `categoria`, `cargo_extra`) VALUES
(1, 1, '1A', 'Fila 1', 8000.00),
(2, 1, '12A', 'Salida Emergencia', 12000.00),
(3, 1, '14B', 'Estandar', 0.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `estado_cuenta` int(11) NOT NULL, -- Controla el borrado lógico (1=Activo, 0=Eliminado)
  PRIMARY KEY (`id_cliente`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metodos_pago`
--

CREATE TABLE `metodos_pago` (
  `id_metodo_pago` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_metodo` varchar(50) NOT NULL,
  `banco_proveedor` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_metodo_pago`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras_ordenes`
--

CREATE TABLE `compras_ordenes` (
  `id_orden` int(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` int(11) DEFAULT NULL,
  `fecha_compra` datetime NOT NULL,
  `monto_total_pagado` decimal(10,2) NOT NULL,
  `id_metodo_pago` int(11) NOT NULL, -- Corregido de VARCHAR a INT para la relación
  PRIMARY KEY (`id_orden`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_metodo_pago` (`id_metodo_pago`),
  CONSTRAINT `compras_ordenes_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`),
  CONSTRAINT `compras_ordenes_ibfk_2` FOREIGN KEY (`id_metodo_pago`) REFERENCES `metodos_pago` (`id_metodo_pago`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pasajeros`
--

CREATE TABLE `pasajeros` (
  `id_pasajero` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_documento` varchar(20) NOT NULL,
  `numero_documento` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `fecha_nacimiento` date NOT NULL, -- Nuevo campo para validar edad
  `asistencia_especial` tinyint(1) DEFAULT 0, -- Nuevo campo para discapacidades/asistencias (0=No, 1=Sí)
  PRIMARY KEY (`id_pasajero`),
  UNIQUE KEY `numero_documento` (`numero_documento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios_adicionales`
--

CREATE TABLE `servicios_adicionales` (
  `id_servicio` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_servicio` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_servicio` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_servicio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `servicios_adicionales`
--

INSERT INTO `servicios_adicionales` (`id_servicio`, `nombre_servicio`, `descripcion`, `precio_servicio`) VALUES
(1, 'Carri-on', 'Equipaje de mano en compartimiento superior', 15000.00),
(2, 'Bodega 23kg', 'Equipaje facturado pesado', 22000.00),
(3, 'Combo Snack Veggie', 'Café o Té + Sándwich de hummus y vegetales', 4500.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planes_tarifas`
--

CREATE TABLE `planes_tarifas` (
  `id_plan` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_plan` varchar(50) NOT NULL, -- BASIC, LIGHT, SMART, FULL FLEX
  `descripcion` text DEFAULT NULL,
  PRIMARY KEY (`id_plan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `planes_tarifas`
--

INSERT INTO `planes_tarifas` (`id_plan`, `nombre_plan`, `descripcion`) VALUES
(1, 'BASIC', 'Incluye bolso o mochila pequeña. Millas AAdvantage x0'),
(2, 'LIGHT', 'Incluye bolso o mochila pequeña. Millas AAdvantage x2'),
(3, 'SMART', 'Bolso + Equipaje de mano + Equipaje de bodega + Asiento estándar + Check-in aeropuerto. Millas x5'),
(4, 'FULL FLEX', 'Todo lo de SMART + Asiento donde quieras + Cambios ilimitados + Devolución 100%. Millas x5');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vuelos`
--

CREATE TABLE `vuelos` (
  `id_vuelo` int(11) NOT NULL AUTO_INCREMENT,
  `numero_vuelo` varchar(20) NOT NULL,
  `id_avion` int(11) DEFAULT NULL,
  `origen_iata` varchar(3) DEFAULT NULL,
  `destino_iata` varchar(3) DEFAULT NULL,
  `fecha_salida` datetime NOT NULL,
  `fecha_llegada` datetime NOT NULL,
  `precio_base_vuelo` decimal(10,2) NOT NULL,
  `estado_vuelo` varchar(20) NOT NULL,
  PRIMARY KEY (`id_vuelo`),
  KEY `id_avion` (`id_avion`),
  KEY `origen_iata` (`origen_iata`),
  KEY `destino_iata` (`destino_iata`),
  CONSTRAINT `vuelos_ibfk_1` FOREIGN KEY (`id_avion`) REFERENCES `aviones` (`id_avion`),
  CONSTRAINT `vuelos_ibfk_2` FOREIGN KEY (`origen_iata`) REFERENCES `aeropuertos` (`codigo_iata`),
  CONSTRAINT `vuelos_ibfk_3` FOREIGN KEY (`destino_iata`) REFERENCES `aeropuertos` (`codigo_iata`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vuelos`
--

INSERT INTO `vuelos` (`id_vuelo`, `numero_vuelo`, `id_avion`, `origen_iata`, `destino_iata`, `fecha_salida`, `fecha_llegada`, `precio_base_vuelo`, `estado_vuelo`) VALUES
(1, 'JA1640', 1, 'AEP', 'BRC', '2026-07-10 08:00:00', '2026-07-10 10:15:00', 50000.00, 'Programado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tickets_detalle`
--

CREATE TABLE `tickets_detalle` (
  `id_ticket` int(11) NOT NULL AUTO_INCREMENT,
  `id_orden` int(11) DEFAULT NULL,
  `id_vuelo` int(11) DEFAULT NULL,
  `id_pasajero` int(11) DEFAULT NULL,
  `id_asiento_avion` int(11) DEFAULT NULL,
  `id_plan` int(11) DEFAULT NULL, -- Nuevo campo para saber qué tarifa (Basic, Smart, etc.) seleccionó para este tramo
  `codigo_reserva_pnr` varchar(6) NOT NULL,
  `precio_tramo_pagado` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_ticket`),
  KEY `id_orden` (`id_orden`),
  KEY `id_vuelo` (`id_vuelo`),
  KEY `id_pasajero` (`id_pasajero`),
  KEY `id_asiento_avion` (`id_asiento_avion`),
  KEY `id_plan` (`id_plan`),
  CONSTRAINT `tickets_detalle_ibfk_1` FOREIGN KEY (`id_orden`) REFERENCES `compras_ordenes` (`id_orden`),
  CONSTRAINT `tickets_detalle_ibfk_2` FOREIGN KEY (`id_vuelo`) REFERENCES `vuelos` (`id_vuelo`),
  CONSTRAINT `tickets_detalle_ibfk_3` FOREIGN KEY (`id_pasajero`) REFERENCES `pasajeros` (`id_pasajero`),
  CONSTRAINT `tickets_detalle_ibfk_4` FOREIGN KEY (`id_asiento_avion`) REFERENCES `asientos_avion` (`id_asiento_avion`),
  CONSTRAINT `tickets_detalle_ibfk_5` FOREIGN KEY (`id_plan`) REFERENCES `planes_tarifas` (`id_plan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ticket_servicios`
--

CREATE TABLE `ticket_servicios` (
  `id_ticket_servicio` int(11) NOT NULL AUTO_INCREMENT,
  `id_ticket` int(11) DEFAULT NULL,
  `id_servicio` int(11) DEFAULT NULL,
  `precio_servicio_pagado` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_ticket_servicio`),
  KEY `id_ticket` (`id_ticket`),
  KEY `id_servicio` (`id_servicio`),
  CONSTRAINT `ticket_servicios_ibfk_1` FOREIGN KEY (`id_ticket`) REFERENCES `tickets_detalle` (`id_ticket`),
  CONSTRAINT `ticket_servicios_ibfk_2` FOREIGN KEY (`id_servicio`) REFERENCES `servicios_adicionales` (`id_servicio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;