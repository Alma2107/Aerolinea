-- SQL combinado de aerolinea.sql y aerolinea (4).sql
-- Mantiene todas las tablas y datos de ambos dumps sin eliminar ninguna estructura

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `aerolinea` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `aerolinea`;

-- --------------------------------------------------------
-- Tabla aeropuertos
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `aeropuertos` (
  `codigo_iata` varchar(3) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `ciudad` varchar(100) NOT NULL,
  `pais` varchar(100) NOT NULL,
  PRIMARY KEY (`codigo_iata`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `aeropuertos` (`codigo_iata`, `nombre`, `ciudad`, `pais`) VALUES
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

INSERT IGNORE INTO `aeropuertos` (`codigo_iata`, `nombre`, `ciudad`, `pais`) VALUES
('ROS', 'Aeropuerto Internacional Rosario', 'Rosario', 'Argentina'),
('USH', 'Aeropuerto Internacional Malvinas Argentinas', 'Ushuaia', 'Argentina'),
('SLA', 'Aeropuerto Internacional Martin Miguel de Guemes', 'Salta', 'Argentina'),
('JUJ', 'Aeropuerto Internacional Gobernador Horacio Guzman', 'Jujuy', 'Argentina'),
('IGR', 'Aeropuerto Internacional Cataratas del Iguazu', 'Iguazu', 'Argentina'),
('FTE', 'Aeropuerto Internacional El Calafate', 'El Calafate', 'Argentina'),
('NQN', 'Aeropuerto Presidente Peron', 'Neuquen', 'Argentina'),
('MDQ', 'Aeropuerto Astor Piazzolla', 'Mar del Plata', 'Argentina'),
('PSS', 'Aeropuerto Libertador General San Martin', 'Posadas', 'Argentina'),
('REL', 'Aeropuerto Almirante Zar', 'Trelew', 'Argentina'),
('CRD', 'Aeropuerto General Mosconi', 'Comodoro Rivadavia', 'Argentina'),
('UAQ', 'Aeropuerto Domingo Faustino Sarmiento', 'San Juan', 'Argentina'),
('LUQ', 'Aeropuerto Brigadier Mayor Cesar Ojeda', 'San Luis', 'Argentina'),
('IRJ', 'Aeropuerto Capitan Vicente Almandos Almonacid', 'La Rioja', 'Argentina'),
('CTC', 'Aeropuerto Coronel Felipe Varela', 'Catamarca', 'Argentina'),
('RES', 'Aeropuerto Internacional Resistencia', 'Resistencia', 'Argentina'),
('CNQ', 'Aeropuerto Internacional Corrientes', 'Corrientes', 'Argentina'),
('SFN', 'Aeropuerto Sauce Viejo', 'Santa Fe', 'Argentina'),
('TUC', 'Aeropuerto Teniente Benjamin Matienzo', 'Tucuman', 'Argentina'),
('RGL', 'Aeropuerto Internacional Piloto Civil Norberto Fernandez', 'Rio Gallegos', 'Argentina'),
('RGA', 'Aeropuerto Internacional Gobernador Ramon Trejo Noel', 'Rio Grande', 'Argentina'),
('MVD', 'Aeropuerto Internacional de Carrasco', 'Montevideo', 'Uruguay'),
('ASU', 'Aeropuerto Internacional Silvio Pettirossi', 'Asuncion', 'Paraguay'),
('LIM', 'Aeropuerto Internacional Jorge Chavez', 'Lima', 'Peru'),
('FLN', 'Aeropuerto Internacional Hercilio Luz', 'Florianopolis', 'Brasil'),
('PUJ', 'Aeropuerto Internacional Punta Cana', 'Punta Cana', 'Republica Dominicana'),
('BCN', 'Aeropuerto Josep Tarradellas Barcelona-El Prat', 'Barcelona', 'Espana'),
('FCO', 'Aeropuerto Leonardo da Vinci-Fiumicino', 'Roma', 'Italia'),
('CDG', 'Aeropuerto Charles de Gaulle', 'Paris', 'Francia'),
('JFK', 'Aeropuerto Internacional John F. Kennedy', 'Nueva York', 'Estados Unidos'),
('CUN', 'Aeropuerto Internacional de Cancun', 'Cancun', 'Mexico'),
('MEX', 'Aeropuerto Internacional Benito Juarez', 'Mexico DF', 'Mexico'),
('BOG', 'Aeropuerto Internacional El Dorado', 'Bogota', 'Colombia'),
('PTY', 'Aeropuerto Internacional de Tocumen', 'Panama', 'Panama');

-- --------------------------------------------------------
-- Tabla aviones
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `aviones` (
  `id_avion` int(11) NOT NULL AUTO_INCREMENT,
  `modelo` varchar(50) NOT NULL,
  `capacidad` int(11) NOT NULL,
  `estado` varchar(20) NOT NULL,
  PRIMARY KEY (`id_avion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `aviones` (`id_avion`, `modelo`, `capacidad`, `estado`) VALUES
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
-- Tabla clientes
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `clientes` (
  `id_cliente` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `estado_cuenta` int(11) NOT NULL,
  PRIMARY KEY (`id_cliente`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `clientes` (`id_cliente`, `nombre`, `apellido`, `email`, `telefono`, `password_hash`, `estado_cuenta`) VALUES
(1, 'Alma', 'Carena', 'carenaalma2@gmail.com', '+541153392209', '$2y$10$p.rhHeHGlwjdaLXGwf9XquLSqxhFOlMt52xPCDbyrhs9/cwWyKnMG', 1),
(2, 'Ezequiel', 'Martínez', 'martinezequiel@gmail.com', '+541166667777', '$2y$10$XsKBb3SCSpCLJLVub9mFpe4gV1mtTkA0Uk.HQIXVb.bsncZg.3Eai', 1),
(3, 'Juan', 'Pérez', 'juan.perez@gmail.com', '+541122334455', 'hash_3', 1),
(4, 'María', 'Gómez', 'maria.gomez@hotmail.com', '+542614556677', 'hash_4', 1),
(5, 'Carlos', 'Rodríguez', 'carlos.rod@yahoo.com', '+543519876543', 'hash_5', 1),
(6, 'Lucía', 'Fernández', 'lucia.f@gmail.com', NULL, 'hash_6', 1),
(7, 'Santiago', 'López', 'santi.lopez@outlook.com', '+56988887777', 'hash_7', 1),
(8, 'Ana', 'Martínez', 'ana.mtnz@gmail.com', '+541134432332', 'hash_8', 1),
(9, 'Diego', 'Sánchez', 'dieguito@gmail.com', NULL, 'hash_9', 0),
(10, 'Laura', 'Álvarez', 'laura.alvarez@live.com.ar', '+542944552211', 'hash_10', 1),
(11, 'Lucaaa', 'Magali', 'popa@gmail.com', '+541153392209', '$2y$10$pCTVJU81uhNJ.j19uuqvHuVSpbfBoYpELYq1P.jMn0JEPGnsMEcU.', 1);

-- --------------------------------------------------------
-- Tabla empleados
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `empleados` (
  `id_empleado` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `usuario` varchar(50) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `modulo` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_empleado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Tabla metodos_pago
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `metodos_pago` (
  `id_metodo_pago` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_metodo` varchar(50) NOT NULL,
  `banco_proveedor` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_metodo_pago`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `metodos_pago` (`id_metodo_pago`, `nombre_metodo`, `banco_proveedor`) VALUES
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
-- Tabla compras_ordenes
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `compras_ordenes` (
  `id_orden` int(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` int(11) NOT NULL,
  `fecha_compra` datetime NOT NULL,
  `monto_total_pagado` decimal(10,2) NOT NULL,
  `id_metodo_pago` int(11) NOT NULL,
  PRIMARY KEY (`id_orden`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_metodo_pago` (`id_metodo_pago`),
  CONSTRAINT `fk_ordenes_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`),
  CONSTRAINT `fk_ordenes_pago` FOREIGN KEY (`id_metodo_pago`) REFERENCES `metodos_pago` (`id_metodo_pago`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `compras_ordenes` (`id_orden`, `id_cliente`, `fecha_compra`, `monto_total_pagado`, `id_metodo_pago`) VALUES
(1, 1, '2026-06-01 10:30:00', 50000.00, 1),
(2, 1, '2026-06-02 15:45:12', 62500.00, 5),
(3, 2, '2026-06-07 04:20:30', 75000.00, 1),
(4, 3, '2026-06-07 11:00:00', 147500.00, 2),
(5, 4, '2026-06-07 11:15:00', 195000.00, 3),
(6, 5, '2026-06-07 12:00:00', 16000.00, 7),
(7, 6, '2026-06-07 12:30:00', 45500.00, 1),
(8, 7, '2026-06-07 13:10:22', 230000.00, 9),
(9, 8, '2026-06-07 14:02:00', 74000.00, 5),
(10, 10, '2026-06-07 15:50:00', 59000.00, 2),
(11, 11, '2026-06-08 13:29:18', 79000.00, 9),
(12, 11, '2026-06-08 13:43:29', 68300.00, 10),
(13, 11, '2026-06-08 13:46:17', 96000.00, 4);

-- --------------------------------------------------------
-- Tabla comentarios
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `comentarios` (
  `id_comentario` int(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_comentario`),
  KEY `id_cliente` (`id_cliente`),
  CONSTRAINT `fk_comentarios_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `comentarios` (`id_cliente`, `comentario`) VALUES
(1, 'Muy buena experiencia. El vuelo salió a horario y el proceso de reserva fue claro.'),
(2, 'La atención fue excelente y la compra en pesos argentinos me resultó muy práctica.'),
(5, 'El sitio es muy fácil de usar y encontré un vuelo directo para mi viaje.');

-- --------------------------------------------------------
-- Tabla pasajeros
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pasajeros` (
  `id_pasajero` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_documento` varchar(20) NOT NULL,
  `numero_documento` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `asistencia_especial` tinyint(1) DEFAULT 0,
  `detalles_medicos` text DEFAULT NULL,
  PRIMARY KEY (`id_pasajero`),
  UNIQUE KEY `numero_documento` (`numero_documento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `pasajeros` (`id_pasajero`, `tipo_documento`, `numero_documento`, `nombre`, `apellido`, `fecha_nacimiento`, `asistencia_especial`, `detalles_medicos`) VALUES
(1, 'DNI', '45123456', 'Juan Carlos', 'Pérez', '1990-05-14', 0, NULL),
(2, 'DNI', '48987654', 'Martina', 'Gómez', '2002-11-23', 1, 'Silla de ruedas por esguince.'),
(3, 'Pasaporte', 'AAA111222', 'John', 'Smith', '1985-08-02', 0, NULL),
(4, 'DNI', '32456789', 'Alma', 'Carena', '1987-04-12', 0, NULL),
(5, 'DNI', '12345678', 'Roberto', 'Rodríguez', '1955-01-30', 1, 'Hipertenso con medicación.'),
(6, 'DNI', '52111222', 'Tomás', 'Fernández', '2010-09-05', 0, 'Menor no acompañado.'),
(7, 'Pasaporte', 'BBB444555', 'Emily', 'Watson', '1993-06-18', 0, NULL),
(8, 'DNI', '28999000', 'Diego', 'Sánchez', '1981-12-25', 0, NULL),
(9, 'DNI', '41000333', 'Laura', 'Álvarez', '1998-03-21', 0, NULL),
(10, 'DNI', '95444111', 'Pedro', 'González', '1974-07-07', 0, NULL),
(11, 'DNI', '7878', 'popo', 'melda', '6767-07-06', 1, NULL),
(12, 'DNI', '7788', 'Alma', 'puma', '2000-04-04', 0, NULL),
(13, 'DNI', '75775', 'mama', 'lali', '2000-03-02', 0, NULL);

-- --------------------------------------------------------
-- Tabla planes_tarifas
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `planes_tarifas` (
  `id_plan` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_plan` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `cargo_extra_plan` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id_plan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `planes_tarifas` (`id_plan`, `nombre_plan`, `descripcion`, `cargo_extra_plan`) VALUES
(1, 'BASIC', 'Bolso o mochila pequeña.', 0.00),
(2, 'LIGHT', 'Mochila + Equipaje de mano.', 4500.00),
(3, 'SMART', 'Mochila + Carry-on + Bodega 23kg + Asiento.', 12000.00),
(4, 'FULL FLEX', 'Cambios ilimitados, devolución y asientos top.', 25000.00);

-- --------------------------------------------------------
-- Tabla servicios_adicionales
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `servicios_adicionales` (
  `id_servicio` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_servicio` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_servicio` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_servicio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `servicios_adicionales` (`id_servicio`, `nombre_servicio`, `descripcion`, `precio_servicio`) VALUES
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
-- Tabla tipos_equipaje
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tipos_equipaje` (
  `id_tipo_equipaje` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_tipo` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_tipo_equipaje`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `tipos_equipaje` (`id_tipo_equipaje`, `nombre_tipo`, `descripcion`, `precio_unitario`) VALUES
(1, 'Bolso/Mochila Adicional', 'Pieza chica extra.', 3500.00),
(2, 'Equipaje de Mano (Carry-on 10kg)', 'Maleta compartimiento superior.', 7500.00),
(3, 'Maleta de Bodega Chica (15kg)', 'Bodega tramos cortos.', 9500.00),
(4, 'Maleta de Bodega Estándar (23kg)', 'Despacho reglamentario.', 14000.00),
(5, 'Maleta de Bodega Pesada (32kg)', 'Vuelos internacionales / pesados.', 22000.00),
(6, 'Equipaje Deportivo / Especial', 'Tablas de surf, instrumentos.', 18000.00);

-- --------------------------------------------------------
-- Tabla vuelos
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `vuelos` (
  `id_vuelo` int(11) NOT NULL AUTO_INCREMENT,
  `numero_vuelo` varchar(20) NOT NULL,
  `id_avion` int(11) NOT NULL,
  `origen_iata` varchar(3) NOT NULL,
  `destino_iata` varchar(3) NOT NULL,
  `fecha_salida` datetime NOT NULL,
  `fecha_llegada` datetime NOT NULL,
  `precio_base_vuelo` decimal(10,2) NOT NULL,
  `estado_vuelo` varchar(20) NOT NULL,
  PRIMARY KEY (`id_vuelo`),
  KEY `id_avion` (`id_avion`),
  KEY `origen_iata` (`origen_iata`),
  KEY `destino_iata` (`destino_iata`),
  CONSTRAINT `fk_vuelos_avion` FOREIGN KEY (`id_avion`) REFERENCES `aviones` (`id_avion`),
  CONSTRAINT `fk_vuelos_destino` FOREIGN KEY (`destino_iata`) REFERENCES `aeropuertos` (`codigo_iata`),
  CONSTRAINT `fk_vuelos_origen` FOREIGN KEY (`origen_iata`) REFERENCES `aeropuertos` (`codigo_iata`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `vuelos` (`id_vuelo`, `numero_vuelo`, `id_avion`, `origen_iata`, `destino_iata`, `fecha_salida`, `fecha_llegada`, `precio_base_vuelo`, `estado_vuelo`) VALUES
(1, 'JA1001', 1, 'AEP', 'BRC', '2026-07-10 08:00:00', '2026-07-10 10:15:00', 50000.00, 'Programado'),
(2, 'JA1002', 1, 'AEP', 'BRC', '2026-07-10 13:30:00', '2026-07-10 15:45:00', 55000.00, 'Programado'),
(3, 'JA1003', 1, 'AEP', 'BRC', '2026-07-10 22:00:00', '2026-07-11 00:15:00', 42000.00, 'Programado'),
(4, 'JA1004', 8, 'BRC', 'AEP', '2026-07-17 12:00:00', '2026-07-17 14:15:00', 48000.00, 'Programado'),
(5, 'JA1005', 2, 'EZE', 'MIA', '2026-08-01 22:00:00', '2026-08-02 07:15:00', 135000.00, 'Programado'),
(6, 'JA1006', 2, 'MIA', 'EZE', '2026-08-10 10:30:00', '2026-08-10 19:45:00', 140000.00, 'Programado'),
(7, 'JA1007', 3, 'EZE', 'MAD', '2026-08-15 13:00:00', '2026-08-16 06:15:00', 195000.00, 'Programado'),
(8, 'JA1008', 5, 'SCL', 'GRU', '2026-07-15 14:00:00', '2026-07-15 17:45:00', 38000.00, 'Programado'),
(9, 'JA1009', 8, 'AEP', 'COR', '2026-07-11 07:00:00', '2026-07-11 08:15:00', 16000.00, 'Programado'),
(10, 'JA1010', 8, 'COR', 'MDZ', '2026-07-12 09:00:00', '2026-07-12 10:05:00', 145000.00, 'Programado'),
(11, 'FS2001', 8, 'AEP', 'ROS', '2026-07-12 07:15:00', '2026-07-12 08:05:00', 28000.00, 'Programado'),
(12, 'FS2002', 8, 'ROS', 'AEP', '2026-07-12 19:40:00', '2026-07-12 20:30:00', 28000.00, 'Programado'),
(13, 'FS2003', 1, 'AEP', 'MDZ', '2026-07-13 09:10:00', '2026-07-13 11:05:00', 52000.00, 'Programado'),
(14, 'FS2004', 1, 'MDZ', 'AEP', '2026-07-18 18:25:00', '2026-07-18 20:15:00', 54000.00, 'Programado'),
(15, 'FS2005', 1, 'AEP', 'USH', '2026-07-14 06:50:00', '2026-07-14 10:35:00', 98000.00, 'Programado'),
(16, 'FS2006', 1, 'USH', 'AEP', '2026-07-21 15:30:00', '2026-07-21 19:10:00', 102000.00, 'Programado'),
(17, 'FS2007', 8, 'AEP', 'SLA', '2026-07-15 08:20:00', '2026-07-15 10:35:00', 62000.00, 'Programado'),
(18, 'FS2008', 8, 'SLA', 'JUJ', '2026-07-15 12:05:00', '2026-07-15 12:45:00', 26000.00, 'Programado'),
(19, 'FS2009', 8, 'AEP', 'IGR', '2026-07-16 10:00:00', '2026-07-16 11:50:00', 59000.00, 'Programado'),
(20, 'FS2010', 8, 'IGR', 'AEP', '2026-07-20 17:10:00', '2026-07-20 19:00:00', 61000.00, 'Programado'),
(21, 'FS2011', 1, 'AEP', 'FTE', '2026-07-17 05:55:00', '2026-07-17 09:10:00', 105000.00, 'Programado'),
(22, 'FS2012', 1, 'FTE', 'BRC', '2026-07-19 14:20:00', '2026-07-19 16:05:00', 69000.00, 'Programado'),
(23, 'FS2013', 8, 'AEP', 'NQN', '2026-07-18 11:15:00', '2026-07-18 13:10:00', 56000.00, 'Programado'),
(24, 'FS2014', 8, 'AEP', 'MDQ', '2026-07-19 08:00:00', '2026-07-19 08:55:00', 24000.00, 'Programado'),
(25, 'FS2015', 8, 'AEP', 'PSS', '2026-07-20 13:45:00', '2026-07-20 15:25:00', 48000.00, 'Programado'),
(26, 'FS2016', 1, 'AEP', 'REL', '2026-07-21 07:35:00', '2026-07-21 09:50:00', 76000.00, 'Programado'),
(27, 'FS2017', 1, 'REL', 'CRD', '2026-07-21 11:05:00', '2026-07-21 12:15:00', 42000.00, 'Programado'),
(28, 'FS2018', 8, 'AEP', 'UAQ', '2026-07-22 09:25:00', '2026-07-22 11:15:00', 52000.00, 'Programado'),
(29, 'FS2019', 8, 'AEP', 'LUQ', '2026-07-22 15:15:00', '2026-07-22 16:55:00', 50000.00, 'Programado'),
(30, 'FS2020', 8, 'AEP', 'IRJ', '2026-07-23 06:45:00', '2026-07-23 08:35:00', 51000.00, 'Programado'),
(31, 'FS2021', 8, 'AEP', 'CTC', '2026-07-23 12:20:00', '2026-07-23 14:05:00', 50000.00, 'Programado'),
(32, 'FS2022', 8, 'AEP', 'RES', '2026-07-24 07:10:00', '2026-07-24 08:45:00', 43000.00, 'Programado'),
(33, 'FS2023', 8, 'RES', 'CNQ', '2026-07-24 10:10:00', '2026-07-24 10:45:00', 22000.00, 'Programado'),
(34, 'FS2024', 8, 'AEP', 'SFN', '2026-07-25 08:35:00', '2026-07-25 09:35:00', 30000.00, 'Programado'),
(35, 'FS2025', 1, 'AEP', 'TUC', '2026-07-25 16:30:00', '2026-07-25 18:25:00', 57000.00, 'Programado'),
(36, 'FS2026', 1, 'AEP', 'RGL', '2026-07-26 06:15:00', '2026-07-26 09:35:00', 99000.00, 'Programado'),
(37, 'FS2027', 1, 'RGL', 'RGA', '2026-07-26 11:00:00', '2026-07-26 12:10:00', 45000.00, 'Programado'),
(38, 'FS3001', 5, 'AEP', 'MVD', '2026-07-27 09:00:00', '2026-07-27 09:55:00', 39000.00, 'Programado'),
(39, 'FS3002', 5, 'EZE', 'ASU', '2026-07-27 13:40:00', '2026-07-27 15:35:00', 68000.00, 'Programado'),
(40, 'FS3003', 5, 'EZE', 'LIM', '2026-07-28 07:30:00', '2026-07-28 12:15:00', 125000.00, 'Programado'),
(41, 'FS3004', 5, 'EZE', 'FLN', '2026-07-28 16:10:00', '2026-07-28 18:15:00', 82000.00, 'Programado'),
(42, 'FS3005', 3, 'EZE', 'PUJ', '2026-08-03 23:10:00', '2026-08-04 07:35:00', 210000.00, 'Programado'),
(43, 'FS3006', 3, 'EZE', 'BCN', '2026-08-05 12:15:00', '2026-08-06 05:55:00', 245000.00, 'Programado'),
(44, 'FS3007', 3, 'EZE', 'FCO', '2026-08-06 13:05:00', '2026-08-07 07:20:00', 252000.00, 'Programado'),
(45, 'FS3008', 3, 'EZE', 'CDG', '2026-08-07 14:45:00', '2026-08-08 08:10:00', 265000.00, 'Programado'),
(46, 'FS3009', 3, 'EZE', 'JFK', '2026-08-08 21:30:00', '2026-08-09 07:40:00', 235000.00, 'Programado'),
(47, 'FS3010', 3, 'EZE', 'CUN', '2026-08-09 08:20:00', '2026-08-09 17:30:00', 220000.00, 'Programado'),
(48, 'FS3011', 3, 'EZE', 'MEX', '2026-08-10 09:40:00', '2026-08-10 19:10:00', 230000.00, 'Programado'),
(49, 'FS3012', 5, 'EZE', 'BOG', '2026-08-11 10:15:00', '2026-08-11 16:35:00', 165000.00, 'Programado'),
(50, 'FS3013', 5, 'EZE', 'PTY', '2026-08-12 11:30:00', '2026-08-12 18:20:00', 175000.00, 'Programado');

-- --------------------------------------------------------
-- Tabla tickets_detalle
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tickets_detalle` (
  `id_ticket` int(11) NOT NULL AUTO_INCREMENT,
  `id_orden` int(11) NOT NULL,
  `id_vuelo` int(11) NOT NULL,
  `id_pasajero` int(11) NOT NULL,
  `numero_asiento` varchar(10) DEFAULT NULL,
  `id_plan` int(11) NOT NULL,
  `codigo_reserva_pnr` varchar(6) NOT NULL,
  `precio_tramo_pagado` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_ticket`),
  KEY `id_orden` (`id_orden`),
  KEY `id_vuelo` (`id_vuelo`),
  KEY `id_pasajero` (`id_pasajero`),
  KEY `id_plan` (`id_plan`),
  CONSTRAINT `fk_tickets_orden` FOREIGN KEY (`id_orden`) REFERENCES `compras_ordenes` (`id_orden`) ON DELETE CASCADE,
  CONSTRAINT `fk_tickets_pasajero` FOREIGN KEY (`id_pasajero`) REFERENCES `pasajeros` (`id_pasajero`),
  CONSTRAINT `fk_tickets_plan` FOREIGN KEY (`id_plan`) REFERENCES `planes_tarifas` (`id_plan`),
  CONSTRAINT `fk_tickets_vuelo` FOREIGN KEY (`id_vuelo`) REFERENCES `vuelos` (`id_vuelo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `tickets_detalle` (`id_ticket`, `id_orden`, `id_vuelo`, `id_pasajero`, `numero_asiento`, `id_plan`, `codigo_reserva_pnr`, `precio_tramo_pagado`) VALUES
(1, 1, 1, 1, '20A', 1, 'AX39FT', 50000.00),
(2, 2, 1, 2, '12A', 2, 'MZ99EE', 59500.00),
(3, 3, 1, 4, NULL, 3, 'PO92LL', 62000.00),
(4, 4, 5, 3, '1A', 2, 'QW12ER', 139500.00),
(5, 5, 7, 5, '2L', 1, 'TR77UI', 195000.00),
(6, 6, 9, 6, '10B', 1, 'KK88YY', 16000.00),
(7, 7, 2, 7, NULL, 1, 'BB22MM', 55000.00),
(8, 8, 5, 8, '5C', 4, 'AA11QQ', 160000.00),
(9, 9, 4, 9, '15F', 2, 'VV55XX', 52500.00),
(10, 10, 1, 10, '12B', 2, 'ZZ00PP', 54500.00),
(11, 11, 4, 11, NULL, 4, '89CQIA', 73000.00),
(12, 12, 4, 12, NULL, 3, '37ICG5', 60000.00),
(13, 13, 3, 13, NULL, 1, 'DYQJPZ', 42000.00),
(14, 13, 4, 13, NULL, 1, 'DYQJPZ', 48000.00);

-- --------------------------------------------------------
-- Tabla ticket_equipajes
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ticket_equipajes` (
  `id_ticket_equipaje` int(11) NOT NULL AUTO_INCREMENT,
  `id_ticket` int(11) NOT NULL,
  `id_tipo_equipaje` int(11) NOT NULL,
  `codigo_etiqueta` varchar(15) DEFAULT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_pagado` decimal(10,2) NOT NULL,
  `estado_equipaje` varchar(30) NOT NULL DEFAULT 'Registrado',
  PRIMARY KEY (`id_ticket_equipaje`),
  KEY `id_ticket` (`id_ticket`),
  KEY `id_tipo_equipaje` (`id_tipo_equipaje`),
  CONSTRAINT `fk_te_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tickets_detalle` (`id_ticket`) ON DELETE CASCADE,
  CONSTRAINT `fk_te_tipo` FOREIGN KEY (`id_tipo_equipaje`) REFERENCES `tipos_equipaje` (`id_tipo_equipaje`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `ticket_equipajes` (`id_ticket_equipaje`, `id_ticket`, `id_tipo_equipaje`, `cantidad`, `precio_pagado`) VALUES
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

INSERT IGNORE INTO `ticket_equipajes` (`id_ticket_equipaje`, `id_ticket`, `id_tipo_equipaje`, `codigo_etiqueta`, `cantidad`, `precio_pagado`, `estado_equipaje`) VALUES
(11, 11, 1, 'TAG-011', 1, 3500.00, 'Registrado'),
(12, 12, 1, 'TAG-012', 1, 3500.00, 'Registrado');

-- --------------------------------------------------------
-- Tabla ticket_servicios
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ticket_servicios` (
  `id_ticket_servicio` int(11) NOT NULL AUTO_INCREMENT,
  `id_ticket` int(11) NOT NULL,
  `id_servicio` int(11) NOT NULL,
  `precio_servicio_pagado` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_ticket_servicio`),
  KEY `id_ticket` (`id_ticket`),
  KEY `id_servicio` (`id_servicio`),
  CONSTRAINT `fk_ts_servicio` FOREIGN KEY (`id_servicio`) REFERENCES `servicios_adicionales` (`id_servicio`),
  CONSTRAINT `fk_ts_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tickets_detalle` (`id_ticket`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `ticket_servicios` (`id_ticket_servicio`, `id_ticket`, `id_servicio`, `precio_servicio_pagado`) VALUES
(1, 1, 1, 2500.00),
(2, 2, 5, 25000.00),
(3, 3, 3, 4500.00),
(4, 4, 2, 6000.00),
(5, 4, 9, 1800.00),
(6, 5, 4, 4800.00),
(7, 7, 6, 3500.00),
(8, 8, 8, 12000.00),
(9, 8, 2, 6000.00),
(10, 10, 9, 1800.00),
(11, 11, 1, 2500.00),
(12, 12, 4, 4800.00),
(13, 13, 2, 6000.00),
(14, 14, 2, 6000.00);

-- --------------------------------------------------------
-- Tabla notificaciones
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notificaciones` (
  `id_notificacion` int(11) NOT NULL AUTO_INCREMENT,
  `modulo_destino` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `mensaje` text NOT NULL,
  `leida` tinyint(1) NOT NULL DEFAULT 0,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_notificacion`),
  KEY `idx_notificaciones_modulo` (`modulo_destino`, `leida`, `fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Tabla promociones
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `promociones` (
  `id_promocion` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) NOT NULL,
  `titulo` varchar(120) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo_beneficio` varchar(40) NOT NULL,
  `valor_beneficio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `destino_iata` varchar(3) DEFAULT NULL,
  `min_pasajeros` int(11) NOT NULL DEFAULT 1,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_promocion`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `destino_iata` (`destino_iata`),
  CONSTRAINT `fk_promociones_destino` FOREIGN KEY (`destino_iata`) REFERENCES `aeropuertos` (`codigo_iata`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `promociones` (`codigo`, `titulo`, `descripcion`, `tipo_beneficio`, `valor_beneficio`, `destino_iata`, `min_pasajeros`, `activa`) VALUES
('BARILO20', '20% OFF en Bariloche', 'Aplica descuento a vuelos con destino Bariloche.', 'porcentaje', 20.00, 'BRC', 1, 1),
('EQUIPAJEGRATIS', 'Equipaje gratis', 'Agrega una valija promocional durante la compra.', 'equipaje_gratis', 1.00, NULL, 1, 1),
('CORDOBA2X1', '2x1 a Cordoba', 'Beneficio para dos pasajeros hacia Cordoba.', '2x1', 50.00, 'COR', 2, 1);

-- --------------------------------------------------------
-- Tabla asientos_avion
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `asientos_avion` (
  `id_asiento_avion` int(11) NOT NULL AUTO_INCREMENT,
  `id_avion` int(11) NOT NULL,
  `numero_asiento` varchar(10) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `cargo_extra` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id_asiento_avion`),
  UNIQUE KEY `avion_asiento` (`id_avion`, `numero_asiento`),
  CONSTRAINT `fk_asientos_avion` FOREIGN KEY (`id_avion`) REFERENCES `aviones` (`id_avion`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `asientos_avion` (`id_avion`, `numero_asiento`, `categoria`, `cargo_extra`) VALUES
(1, '1A', 'Business', 12000.00), (1, '1B', 'Business', 12000.00), (1, '1C', 'Business', 12000.00), (1, '2A', 'Premium Economy', 6500.00), (1, '2B', 'Premium Economy', 6500.00), (1, '2C', 'Premium Economy', 6500.00), (1, '10A', 'Estandar', 0.00), (1, '10B', 'Estandar', 0.00), (1, '10C', 'Estandar', 0.00), (1, '11A', 'Estandar', 0.00), (1, '11B', 'Estandar', 0.00), (1, '11C', 'Estandar', 0.00),
(2, '1A', 'Business', 12000.00), (2, '1B', 'Business', 12000.00), (2, '2A', 'Premium Economy', 6500.00), (2, '2B', 'Premium Economy', 6500.00), (2, '12A', 'Estandar', 0.00), (2, '12B', 'Estandar', 0.00), (2, '12C', 'Estandar', 0.00), (2, '15A', 'Estandar', 0.00), (2, '15B', 'Estandar', 0.00), (2, '15C', 'Estandar', 0.00),
(3, '1A', 'Business', 18000.00), (3, '1B', 'Business', 18000.00), (3, '5A', 'Premium Economy', 9000.00), (3, '5B', 'Premium Economy', 9000.00), (3, '20A', 'Estandar', 0.00), (3, '20B', 'Estandar', 0.00), (3, '20C', 'Estandar', 0.00),
(5, '1A', 'Business', 15000.00), (5, '2A', 'Premium Economy', 7500.00), (5, '9A', 'Estandar', 0.00), (5, '9B', 'Estandar', 0.00), (5, '9C', 'Estandar', 0.00),
(8, '1A', 'Premium Economy', 5000.00), (8, '1B', 'Premium Economy', 5000.00), (8, '8A', 'Estandar', 0.00), (8, '8B', 'Estandar', 0.00), (8, '8C', 'Estandar', 0.00);

-- --------------------------------------------------------
-- Tabla reservas_cancelaciones
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reservas_cancelaciones` (
  `id_cancelacion` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_reserva_pnr` varchar(6) NOT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `fecha_solicitud` datetime NOT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'Solicitada',
  `motivo` varchar(180) DEFAULT NULL,
  PRIMARY KEY (`id_cancelacion`),
  UNIQUE KEY `reserva_unica` (`codigo_reserva_pnr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
