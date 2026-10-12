-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 12-10-2026 a las 11:31:42
-- Versión del servidor: 8.0.30
-- Versión de PHP: 8.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `prodenya`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria_accesos_legajo`
--

CREATE TABLE `auditoria_accesos_legajo` (
  `id` bigint UNSIGNED NOT NULL,
  `legajo_id` int UNSIGNED NOT NULL,
  `usuario_id` int UNSIGNED NOT NULL,
  `tipo_accion` enum('Visualizacion','Descarga') COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_acceso` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_origen` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria_descargas`
--

CREATE TABLE `auditoria_descargas` (
  `id` bigint UNSIGNED NOT NULL,
  `documento_id` int UNSIGNED NOT NULL,
  `usuario_id` int UNSIGNED NOT NULL,
  `fecha_descarga` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_origen` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `barrios`
--

CREATE TABLE `barrios` (
  `id` int UNSIGNED NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `localidad_id` int UNSIGNED NOT NULL,
  `zona_barrio_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `barrios`
--

INSERT INTO `barrios` (`id`, `nombre`, `localidad_id`, `zona_barrio_id`, `created_at`, `updated_by`, `deleted_by`, `updated_at`, `deleted_at`) VALUES
(1, 'Santa Teresita', 5, 1, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(2, 'Las Mellizas', 5, 1, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(3, 'Azopardo', 5, 1, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(4, 'Yaguaron', 5, 1, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(5, 'San Jorge', 5, 1, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(6, 'Castelli', 5, 1, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(7, 'San Martin', 5, 1, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(8, 'Suizo', 5, 1, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(9, 'Parque Norte', 5, 1, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(10, 'Parque Sarmiento', 5, 1, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(11, '25 de Mayo', 5, 1, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(12, 'Santa Clara', 5, 1, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(13, 'Moreno', 5, 1, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(14, 'Bola de Oro', 5, 1, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(15, 'San Cayetano', 5, 1, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(16, 'Prado Español', 5, 1, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(17, 'Alto Verde', 5, 1, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(18, 'San Pablo', 5, 1, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(19, 'La Loma', 5, 1, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(20, 'Guena', 5, 1, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(21, 'Asonia', 5, 1, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(22, 'Fraga', 5, 1, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(23, 'Martinez', 5, 1, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(24, 'Lares', 5, 1, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(25, 'Jose Ingenieros', 5, 1, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(27, 'Urquiza', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(28, 'Don Bosco', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(29, 'Obrero', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(30, 'Cavalli', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(31, 'Padre D´Amico', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(32, 'Química', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(33, 'Belgrano', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(34, 'San Isidro', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(35, 'Ponce de León', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(36, 'Saavedra', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(37, 'Los Fresnos', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(38, 'Alcoholera', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(39, 'Astul Urquiaga', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(40, 'Super Usina', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(41, 'Golf Club', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(42, 'Oeste', 5, 3, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(43, 'Lanza', 5, 3, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(44, 'Garetto', 5, 3, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(45, 'Evita', 5, 3, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(46, 'Santa Cecilia', 5, 3, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(47, 'Ginés García', 5, 3, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(48, 'ITEC', 5, 3, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(49, 'San Francisco', 5, 3, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(50, 'La Florida', 5, 3, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(51, 'General Mosconi', 5, 3, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(52, 'Mitre', 5, 3, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(53, 'Irigoyen', 5, 3, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(54, 'Villa María', 5, 3, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(55, 'Los Viñedos', 5, 3, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(57, 'Don Americo', 5, 3, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(58, 'Parque Córdoba', 5, 3, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(59, 'Colombo', 5, 3, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(60, 'Malvinas', 5, 3, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(61, 'Virgen del Rosario', 5, 3, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(62, 'Colombini', 5, 3, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(63, '21 de Septiembre', 5, 3, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(64, 'Las Viñas', 5, 3, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(65, 'Pezzi', 5, 3, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(66, 'Las Flores', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(67, 'Juan XXIII', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(68, 'Del Carmen', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(69, 'Primavera', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(70, 'Ponte', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(71, '17 de Octubre', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(72, 'Los Pinos', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(73, 'Savio', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(74, 'Santa Rosa', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(75, '9 de Julio', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(76, 'Virgen de Luján', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(77, 'Trípoli', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(78, 'California', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(79, 'Parque Avamba´e', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(80, 'Avamba´e', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(81, '7 de Septiembre', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(82, 'San Eduardo', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(83, 'Guemes', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(84, 'Plastiversal', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(85, 'Somisa', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(86, 'Sironi', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(87, 'Ayres del Sur', 5, 2, '2024-10-23 17:25:50', NULL, NULL, NULL, NULL),
(88, 'CENTRO', 5, 4, '2026-01-20 05:28:01', NULL, NULL, NULL, NULL),
(89, 'La Emilia', 5, 5, '2026-01-20 05:28:01', NULL, NULL, NULL, NULL),
(90, 'Villa Riccio', 5, 5, '2026-01-20 05:28:01', NULL, NULL, NULL, NULL),
(91, 'Villa Canto', 5, 5, '2026-01-20 05:28:01', NULL, NULL, NULL, NULL),
(92, 'Villa Campi', 5, 5, '2026-01-20 05:28:01', NULL, NULL, NULL, NULL),
(93, 'Campos Salles', 5, 5, '2026-01-20 05:28:01', NULL, NULL, NULL, NULL),
(94, 'General Rojo', 5, 5, '2026-01-20 05:28:01', NULL, NULL, NULL, NULL),
(95, 'Conesa', 5, 5, '2026-01-20 05:28:01', NULL, NULL, NULL, NULL),
(96, 'Erezcano', 5, 5, '2026-01-20 05:28:01', NULL, NULL, NULL, NULL),
(98, '14 de Abril', 5, 1, NULL, NULL, NULL, NULL, NULL),
(99, '12 de Marzo', 5, 3, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cud`
--

CREATE TABLE `cud` (
  `id` int UNSIGNED NOT NULL,
  `persona_id` int UNSIGNED NOT NULL,
  `numero_cud` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_emision` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos`
--

CREATE TABLE `documentos` (
  `id` int UNSIGNED NOT NULL,
  `persona_id` int UNSIGNED DEFAULT NULL,
  `legajo_id` int UNSIGNED DEFAULT NULL,
  `tipo_documento_id` int UNSIGNED NOT NULL,
  `nombre_archivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ruta_archivo` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tamanio_bytes` bigint UNSIGNED DEFAULT NULL,
  `subido_por` int UNSIGNED NOT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `legajos`
--

CREATE TABLE `legajos` (
  `id` int UNSIGNED NOT NULL,
  `persona_id` int UNSIGNED NOT NULL,
  `numero_legajo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `motivo_ingreso` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `nivel_riesgo` enum('Bajo','Medio','Alto','Critico') COLLATE utf8mb4_unicode_ci NOT NULL,
  `origen_derivacion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profesional_id` int UNSIGNED DEFAULT NULL,
  `fecha_apertura` date NOT NULL DEFAULT (curdate()),
  `estado` enum('Abierto','Cerrado','Archivado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Abierto',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `creado_por` int UNSIGNED NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `legajos`
--

INSERT INTO `legajos` (`id`, `persona_id`, `numero_legajo`, `motivo_ingreso`, `nivel_riesgo`, `origen_derivacion`, `profesional_id`, `fecha_apertura`, `estado`, `observaciones`, `creado_por`, `creado_en`, `actualizado_en`) VALUES
(1, 1, 'VIL-HER-735', 'asd', 'Critico', 'asd', 1, '2026-10-08', 'Abierto', NULL, 1, '2026-10-08 10:45:17', '2026-10-08 10:45:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `legajo_problematica`
--

CREATE TABLE `legajo_problematica` (
  `legajo_id` int UNSIGNED NOT NULL,
  `problematica_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `legajo_problematica`
--

INSERT INTO `legajo_problematica` (`legajo_id`, `problematica_id`) VALUES
(1, 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `localidades`
--

CREATE TABLE `localidades` (
  `id` int UNSIGNED NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provincia_id` int UNSIGNED NOT NULL,
  `codigo_postal` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `localidades`
--

INSERT INTO `localidades` (`id`, `nombre`, `provincia_id`, `codigo_postal`, `created_at`, `updated_at`, `deleted_at`, `updated_by`, `deleted_by`) VALUES
(2, 'La Plata', 1, 1900, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(3, 'Mar del Plata', 1, 7601, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(4, 'Bahía Blanca', 1, 8000, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(5, 'San Nicolás de los Arroyos', 1, 2900, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(6, 'Tandil', 1, 7000, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(7, 'Olavarría', 1, 7400, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(8, 'Junín', 1, 6000, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(9, 'Pergamino', 1, 2700, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(10, 'San Isidro', 1, 1642, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(11, 'Luján', 1, 6700, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(12, 'Zárate', 1, 2800, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(13, 'Campana', 1, 2804, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(14, 'General Pueyrredón', 1, 7600, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(15, 'La Matanza', 1, 1702, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(16, 'San Fernando', 1, 1646, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(17, 'Tres de Febrero', 1, 1651, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(18, 'San Miguel', 1, 1663, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(19, 'Lomas de Zamora', 1, 1832, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(20, 'Quilmes', 1, 1879, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(21, 'Avellaneda', 1, 1870, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(22, 'Berazategui', 1, 1884, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(23, 'Ituzaingó', 1, 1714, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(24, 'Morón', 1, 1708, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(25, 'Florencio Varela', 1, 1888, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(26, 'San Martín', 1, 1650, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(27, 'Vicente López', 1, 1638, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(28, 'San Fernando del Valle de Catamarca', 2, 4700, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(29, 'Ramallo', 1, 2915, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(30, 'Andalgalá', 2, 4740, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(31, 'Belén', 2, 4750, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(32, 'Tinogasta', 2, 5340, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(33, 'Santa María', 2, 4742, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(34, 'Fiambalá', 2, 4733, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(35, 'La Merced', 2, 4753, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(36, 'Aconquija', 2, 4741, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(37, 'San José', 2, 4701, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(38, 'Saujil', 2, 5335, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(39, 'Pomán', 2, 4724, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(40, 'Capayán', 2, 4722, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(41, 'Valle Viejo', 2, 4718, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(42, 'Ancasti', 2, 4747, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(43, 'El Rodeo', 2, 4730, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(44, 'Los Altos', 2, 4709, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(45, 'Puerta de Corral Quemado', 2, 4745, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(46, 'La Puerta', 2, 4703, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(47, 'Resistencia', 3, 3500, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(48, 'Barranqueras', 3, 3503, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(49, 'Fontana', 3, 3506, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(50, 'Presidencia Roque Sáenz Peña', 3, 3700, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(51, 'Villa Ángela', 3, 3541, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(52, 'Charata', 3, 3730, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(53, 'Machagai', 3, 3708, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(54, 'Las Breñas', 3, 3722, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(55, 'Quitilipi', 3, 3540, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(56, 'Tres Isletas', 3, 3535, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(57, 'General Pinedo', 3, 3703, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(58, 'La Leonesa', 3, 3511, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(59, 'Puerto Tirol', 3, 3519, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(60, 'Colonia Benítez', 3, 3513, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(61, 'Puerto Vilelas', 3, 3507, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(62, 'Margarita Belén', 3, 3505, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(63, 'General San Martín', 3, 3542, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(64, 'San Bernardo', 3, 3706, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(65, 'Juan José Castelli', 3, 3705, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(66, 'Coronel Du Graty', 3, 3724, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(67, 'Comodoro Rivadavia', 4, 9000, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(68, 'Puerto Madryn', 4, 9120, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(69, 'Trelew', 4, 9100, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(70, 'Rawson', 4, 9103, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(71, 'Esquel', 4, 9200, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(72, 'Gaiman', 4, 9105, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(73, 'Trevelin', 4, 9203, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(74, 'Dolavon', 4, 9101, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(75, 'Rada Tilly', 4, 9001, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(76, 'Sarmiento', 4, 9005, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(77, 'Lago Puelo', 4, 9211, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(78, 'Río Mayo', 4, 9011, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(79, 'Río Senguer', 4, 9017, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(80, 'Gobernador Costa', 4, 9221, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(81, 'Paso de Indios', 4, 9023, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(82, 'Córdoba', 5, 5000, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(83, 'Villa María', 5, 5900, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(84, 'Río Cuarto', 5, 5800, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(85, 'San Francisco', 5, 2400, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(86, 'Jesús María', 5, 5220, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(87, 'Villa Carlos Paz', 5, 5152, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(88, 'La Falda', 5, 5172, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(89, 'Cosquín', 5, 5166, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(90, 'Alta Gracia', 5, 5186, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(91, 'Bell Ville', 5, 2550, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(92, 'Villa Allende', 5, 5105, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(93, 'Rio Tercero', 5, 5850, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(94, 'Villa Dolores', 5, 5870, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(95, 'Río Ceballos', 5, 5111, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(96, 'Arroyito', 5, 2440, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(97, 'Mina Clavero', 5, 5889, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(98, 'Villa General Belgrano', 5, 5194, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(99, 'Marcos Juárez', 5, 2580, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(100, 'Corrientes', 6, 3400, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(101, 'Goya', 6, 3450, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(102, 'Mercedes', 6, 3471, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(103, 'Ituzaingó', 6, 3302, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(104, 'Santo Tomé', 6, 3340, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(105, 'Curuzú Cuatiá', 6, 3470, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(106, 'Bella Vista', 6, 3452, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(107, 'Esquina', 6, 3229, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(108, 'Empedrado', 6, 3416, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(109, 'Monte Caseros', 6, 3220, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(110, 'Saladas', 6, 3451, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(111, 'Virasoro', 6, 3342, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(112, 'San Lorenzo', 6, 3425, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(113, 'La Cruz', 6, 3226, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(114, 'Paso de los Libres', 6, 3230, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(115, 'Alvear', 6, 3424, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(116, 'San Luis del Palmar', 6, 3407, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(117, 'Berón de Astrada', 6, 3414, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(118, 'Paraná', 7, 3100, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(119, 'Concordia', 7, 3200, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(120, 'Gualeguaychú', 7, 2821, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(121, 'Gualeguay', 7, 2820, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(122, 'Victoria', 7, 3153, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(123, 'Villaguay', 7, 3240, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(124, 'La Paz', 7, 3190, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(125, 'Colón', 7, 3280, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(126, 'Federal', 7, 3181, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(127, 'Chajarí', 7, 3228, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(128, 'Diamante', 7, 3105, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(129, 'San José', 7, 3283, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(130, 'Crespo', 7, 3116, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(131, 'Ramírez', 7, 3113, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(132, 'Rosario del Tala', 7, 3170, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(133, 'San Justo', 7, 3195, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(134, 'Pueblo Belgrano', 7, 3180, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(135, 'Pueblo General Alvear', 7, 3175, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(136, 'Salto', 7, 3224, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(137, 'Mendoza', 8, 5500, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(138, 'San Rafael', 8, 5600, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(139, 'Godoy Cruz', 8, 5501, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(140, 'Luján de Cuyo', 8, 5505, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(141, 'Guaymallén', 8, 5519, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(142, 'Las Heras', 8, 5502, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(143, 'Maipú', 8, 5515, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(144, 'Junín', 8, 5512, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(145, 'San Martín', 8, 5560, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(146, 'Tupungato', 8, 5565, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(147, 'Malargüe', 8, 5613, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(148, 'Rivadavia', 8, 5562, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(149, 'General Alvear', 8, 5623, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(150, 'Villa Nueva', 8, 5516, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(151, 'Las Compuertas', 8, 5547, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(152, 'San Carlos', 8, 5567, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(153, 'San José del Carril', 8, 5537, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(154, 'Rincón del Indio', 8, 5555, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(155, 'La Consulta', 8, 5568, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(156, 'Parque General San Martín', 8, 5518, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(157, 'El Carrizal', 8, 5520, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(158, 'Tunuyán', 8, 5561, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(159, 'Santa Rosa', 9, 6370, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(160, 'General Alvear', 9, 6070, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(161, 'Toay', 9, 6300, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(162, 'Catriló', 9, 6109, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(163, 'General Pico', 9, 6360, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(164, 'Intendente Alvear', 9, 6343, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(165, 'Macachín', 9, 6316, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(166, 'Realicó', 9, 6150, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(167, 'Santo Tomás', 9, 6332, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(168, 'Victorica', 9, 6338, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(169, 'Rancul', 9, 6307, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(170, 'Pico de Orizaba', 9, 6113, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(171, 'Puelches', 9, 6120, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(172, 'Ceballos', 9, 6351, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(173, 'Chamaico', 9, 6340, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(174, 'Lonquimay', 9, 6176, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(175, 'Rucanelo', 9, 6116, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(176, 'Bernasconi', 9, 6100, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(177, 'Chamanal', 9, 6152, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(178, 'Huantraico', 9, 6095, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(179, 'Rivadavia', 9, 6350, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(180, 'La Maruja', 9, 6118, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(182, 'Carhué', 10, 6465, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(183, 'Guaminí', 10, 6461, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(184, 'Daireaux', 10, 6460, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(185, 'Adolfo Alsina', 10, 6462, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(186, 'Salliqueló', 10, 6463, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(187, 'Pihué', 10, 6466, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(188, 'Pehuajó', 10, 6468, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(189, 'Trenque Lauquen', 10, 6469, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(190, 'General Villegas', 10, 6470, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(191, 'Intendente Alvear', 10, 6471, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(192, 'Trevino', 10, 6482, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(193, 'Banderaló', 10, 6483, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(195, 'Dorrego', 10, 6485, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(196, 'San Miguel del Monte', 10, 6486, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(197, 'General La Madrid', 10, 6487, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(198, 'Tornquist', 10, 6488, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(199, 'Laprida', 10, 6489, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(200, 'Las Flores', 10, 6490, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(201, 'Azul', 10, 6491, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(202, 'Tandil', 10, 6492, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(203, 'Berazategui', 10, 6493, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(204, 'Zárate', 10, 6494, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(205, 'General Alvear', 10, 6495, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL),
(207, 'Castelli', 10, 6497, '2026-01-31 13:22:19', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2024_01_01_000000_create_passkeys_table', 1),
(5, '2024_01_01_000001_create_roles_table', 1),
(6, '2024_01_01_000002_create_usuarios_table', 1),
(7, '2024_01_01_000003_create_provincias_table', 1),
(8, '2024_01_01_000004_create_localidades_table', 1),
(9, '2024_01_01_000005_create_barrios_table', 1),
(10, '2024_01_01_000006_create_personas_table', 1),
(11, '2024_01_01_000007_create_cud_table', 1),
(12, '2024_01_01_000008_create_legajos_table', 1),
(13, '2024_01_01_000009_create_auditoria_accesos_legajo_table', 1),
(14, '2024_01_01_000010_create_tipos_documento_table', 1),
(15, '2024_01_01_000011_create_documentos_table', 1),
(16, '2024_01_01_000012_create_auditoria_descargas_table', 1),
(17, '2024_01_01_000013_create_vista_personas_completa', 1),
(18, '2024_01_01_000014_add_motivo_ingreso_to_legajos_table', 1),
(19, '2025_08_14_170933_add_two_factor_columns_to_users_table', 1),
(20, '2026_01_27_000001_create_teams_table', 1),
(21, '2026_01_27_000002_add_current_team_id_to_users_table', 1),
(22, '2026_09_17_000001_add_dump_columns_to_geographic_tables', 1),
(23, '2026_10_01_000015_create_tipos_problematicas_and_legajo_fields', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `passkeys`
--

CREATE TABLE `passkeys` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `credential_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `credential` json NOT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personas`
--

CREATE TABLE `personas` (
  `id` int UNSIGNED NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dni` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `genero` enum('M','F','X','NO_ESPECIFICA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NO_ESPECIFICA',
  `direccion_calle` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion_numero` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion_piso_depto` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `barrio_id` int UNSIGNED DEFAULT NULL,
  `localidad_id` int UNSIGNED NOT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `personas`
--

INSERT INTO `personas` (`id`, `nombre`, `apellido`, `dni`, `fecha_nacimiento`, `genero`, `direccion_calle`, `direccion_numero`, `direccion_piso_depto`, `barrio_id`, `localidad_id`, `telefono`, `email`, `observaciones`, `activo`, `creado_en`, `actualizado_en`) VALUES
(1, 'hernan', 'vilchez', '41145735', '2019-05-02', 'M', 'paraguay 1', '384', NULL, 14, 5, '+543364367123', 'hernanvilchezdoc@gmail.com', 'asd', 1, '2026-10-08 10:45:17', '2026-10-08 10:45:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `problematica`
--

CREATE TABLE `problematica` (
  `id` bigint UNSIGNED NOT NULL,
  `nombre` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` int UNSIGNED DEFAULT NULL,
  `deleted_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `problematica`
--

INSERT INTO `problematica` (`id`, `nombre`, `created_at`, `updated_by`, `deleted_by`, `updated_at`, `deleted_at`) VALUES
(1, 'Ausencia del adulto responsable', '2024-10-24 07:59:55', NULL, NULL, NULL, NULL),
(3, 'Maltrato', '2024-10-24 07:59:55', NULL, NULL, NULL, NULL),
(4, 'Negligencia', '2024-10-24 07:59:55', NULL, NULL, NULL, NULL),
(5, 'COVID 19', '2024-10-24 07:59:55', NULL, NULL, NULL, NULL),
(6, 'Abuso sexual', '2024-10-24 07:59:55', NULL, NULL, NULL, NULL),
(7, 'Explotacion', '2024-10-24 07:59:55', NULL, NULL, NULL, NULL),
(8, 'Falta de acceso a servicios', '2024-10-24 07:59:55', NULL, NULL, NULL, NULL),
(9, 'Discriminacion/Violencia institucional', '2024-10-24 07:59:55', NULL, NULL, NULL, NULL),
(10, 'Identidad', '2024-10-24 07:59:55', NULL, NULL, NULL, NULL),
(11, 'Responsabilidad penal juvenil', '2024-10-24 07:59:55', NULL, NULL, NULL, NULL),
(12, 'Consulta juridicas', '2024-10-24 07:59:55', NULL, NULL, NULL, NULL),
(13, 'Salud', '2024-10-24 07:59:55', NULL, NULL, NULL, NULL),
(15, 'Medidas de abrigo', '2024-10-24 07:59:55', NULL, NULL, NULL, NULL),
(16, 'Otros', '2024-10-24 07:59:55', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `provincias`
--

CREATE TABLE `provincias` (
  `id` int UNSIGNED NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `provincias`
--

INSERT INTO `provincias` (`id`, `nombre`, `updated_at`, `updated_by`, `deleted_by`, `deleted_at`, `created_at`) VALUES
(1, 'Buenos Aires', NULL, NULL, NULL, NULL, '2024-10-23 17:25:50'),
(2, 'Catamarca', NULL, NULL, NULL, NULL, '2024-10-23 17:25:50'),
(3, 'Chaco', NULL, NULL, NULL, NULL, '2024-10-23 17:25:50'),
(4, 'Chubut', NULL, NULL, NULL, NULL, '2024-10-23 17:25:50'),
(5, 'Córdoba', NULL, NULL, NULL, NULL, '2024-10-23 17:25:50'),
(6, 'Corrientes', NULL, NULL, NULL, NULL, '2024-10-23 17:25:50'),
(7, 'Entre Ríos', NULL, NULL, NULL, NULL, '2024-10-23 17:25:50'),
(8, 'Formosa', NULL, NULL, NULL, NULL, '2024-10-23 17:25:50'),
(9, 'Jujuy', NULL, NULL, NULL, NULL, '2024-10-23 17:25:50'),
(10, 'La Pampa', NULL, NULL, NULL, NULL, '2024-10-23 17:25:50'),
(11, 'La Rioja', NULL, NULL, NULL, NULL, '2024-10-23 17:25:50'),
(12, 'Mendoza', NULL, NULL, NULL, NULL, '2024-10-23 17:25:50'),
(13, 'Misiones', NULL, NULL, NULL, NULL, '2024-10-23 17:25:50'),
(14, 'Neuquén', NULL, NULL, NULL, NULL, '2024-10-23 17:25:50'),
(15, 'Río Negro', NULL, NULL, NULL, NULL, '2024-10-23 17:25:50'),
(16, 'Salta', NULL, NULL, NULL, NULL, '2024-10-23 17:25:50'),
(17, 'San Juan', NULL, NULL, NULL, NULL, '2024-10-23 17:25:50'),
(18, 'San Luis', NULL, NULL, NULL, NULL, '2024-10-23 17:25:50'),
(19, 'Santa Cruz', NULL, NULL, NULL, NULL, '2024-10-23 17:25:50'),
(20, 'Santa Fe', NULL, NULL, NULL, NULL, '2024-10-23 17:25:50'),
(21, 'Santiago del Estero', NULL, NULL, NULL, NULL, '2024-10-23 17:25:50'),
(22, 'Tierra del Fuego', NULL, NULL, NULL, NULL, '2024-10-23 17:25:50'),
(23, 'Tucumán', NULL, NULL, NULL, NULL, '2024-10-23 17:25:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int UNSIGNED NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `descripcion`, `creado_en`) VALUES
(1, 'admin', 'Acceso total al sistema', '2026-10-08 07:42:33'),
(2, 'operador', 'Carga y edición de personas y documentos', '2026-10-08 07:42:33'),
(3, 'consulta', 'Solo lectura y descarga de documentos', '2026-10-08 07:42:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('ct2YXndsmeTH8wkqSoBKLHxr9M3J0Um4TPUcY6u9', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJ2NXVyR0dMMlJvSm5SMEpDcUdPMTlJUlRrUDBKaWRsWlNVN0xIRll4IiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL3BlcnNvbmFzXC9udWV2YSJ9LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2FwaVwvYmFycmlvcz9sb2NhbGlkYWRfaWQ9NSIsInJvdXRlIjoiYXBpLmJhcnJpb3MifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MX0=', 1791447578);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `teams`
--

CREATE TABLE `teams` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_personal` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `team_invitations`
--

CREATE TABLE `team_invitations` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `team_id` bigint UNSIGNED NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `invited_by` bigint UNSIGNED NOT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `team_members`
--

CREATE TABLE `team_members` (
  `id` bigint UNSIGNED NOT NULL,
  `team_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_documento`
--

CREATE TABLE `tipos_documento` (
  `id` int UNSIGNED NOT NULL,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipos_documento`
--

INSERT INTO `tipos_documento` (`id`, `nombre`) VALUES
(3, 'Certificado médico'),
(1, 'CUD'),
(2, 'DNI'),
(4, 'Otro');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_team_id` bigint UNSIGNED DEFAULT NULL,
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `current_team_id`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Administrador', 'admin@prodenya.local', NULL, '$2y$12$8/XuyEtB148i1LRmlU8HYeSNt/YK8lzbI3ZiMctHdhKuM8KlYhUfm', NULL, NULL, NULL, NULL, NULL, '2026-10-08 10:42:34', '2026-10-08 10:42:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int UNSIGNED NOT NULL,
  `nombre_usuario` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol_id` int UNSIGNED NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `ultimo_acceso` datetime DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_usuario`, `email`, `password_hash`, `rol_id`, `activo`, `ultimo_acceso`, `remember_token`, `creado_en`, `actualizado_en`) VALUES
(1, 'admin', 'admin@prodenya.local', '$2y$12$n9TWE.HX1ymTprVCI7054.y68wWmeOdbSUFOaNiwD50PTP9oOWsEu', 1, 1, NULL, NULL, '2026-10-08 10:42:34', '2026-10-08 10:42:34');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_personas_completa`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_personas_completa` (
`apellido` varchar(100)
,`barrio` varchar(100)
,`cud_vencimiento` date
,`dni` varchar(15)
,`edad` bigint
,`estado_cud` varchar(7)
,`id` int unsigned
,`localidad` varchar(100)
,`nombre` varchar(100)
,`numero_cud` varchar(50)
,`provincia` varchar(100)
);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `auditoria_accesos_legajo`
--
ALTER TABLE `auditoria_accesos_legajo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_auditlegajo_legajo` (`legajo_id`),
  ADD KEY `idx_auditlegajo_usuario` (`usuario_id`),
  ADD KEY `idx_auditlegajo_fecha` (`fecha_acceso`);

--
-- Indices de la tabla `auditoria_descargas`
--
ALTER TABLE `auditoria_descargas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_auditoria_documento` (`documento_id`),
  ADD KEY `idx_auditoria_usuario` (`usuario_id`),
  ADD KEY `idx_auditoria_fecha` (`fecha_descarga`);

--
-- Indices de la tabla `barrios`
--
ALTER TABLE `barrios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_barrio` (`nombre`,`localidad_id`),
  ADD KEY `barrios_localidad_id_foreign` (`localidad_id`);

--
-- Indices de la tabla `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indices de la tabla `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indices de la tabla `cud`
--
ALTER TABLE `cud`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cud_numero_cud_unique` (`numero_cud`),
  ADD KEY `idx_cud_persona` (`persona_id`),
  ADD KEY `idx_cud_vencimiento` (`fecha_vencimiento`);

--
-- Indices de la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documentos_tipo_documento_id_foreign` (`tipo_documento_id`),
  ADD KEY `documentos_subido_por_foreign` (`subido_por`),
  ADD KEY `idx_documento_persona` (`persona_id`),
  ADD KEY `idx_documento_legajo` (`legajo_id`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indices de la tabla `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indices de la tabla `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `legajos`
--
ALTER TABLE `legajos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `legajos_numero_legajo_unique` (`numero_legajo`),
  ADD KEY `legajos_creado_por_foreign` (`creado_por`),
  ADD KEY `idx_legajo_persona` (`persona_id`),
  ADD KEY `legajos_profesional_id_foreign` (`profesional_id`);

--
-- Indices de la tabla `legajo_problematica`
--
ALTER TABLE `legajo_problematica`
  ADD PRIMARY KEY (`legajo_id`,`problematica_id`),
  ADD KEY `legajo_problematica_problematica_id_foreign` (`problematica_id`);

--
-- Indices de la tabla `localidades`
--
ALTER TABLE `localidades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_localidad` (`nombre`,`provincia_id`),
  ADD KEY `localidades_provincia_id_foreign` (`provincia_id`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `passkeys`
--
ALTER TABLE `passkeys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `passkeys_credential_id_unique` (`credential_id`),
  ADD KEY `passkeys_user_id_index` (`user_id`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `personas`
--
ALTER TABLE `personas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personas_dni_unique` (`dni`),
  ADD KEY `personas_barrio_id_foreign` (`barrio_id`),
  ADD KEY `personas_localidad_id_foreign` (`localidad_id`),
  ADD KEY `idx_persona_dni` (`dni`),
  ADD KEY `idx_persona_apellido_nombre` (`apellido`,`nombre`);

--
-- Indices de la tabla `problematica`
--
ALTER TABLE `problematica`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `provincias`
--
ALTER TABLE `provincias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `provincias_nombre_unique` (`nombre`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_nombre_unique` (`nombre`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teams_slug_unique` (`slug`);

--
-- Indices de la tabla `team_invitations`
--
ALTER TABLE `team_invitations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `team_invitations_code_unique` (`code`),
  ADD KEY `team_invitations_team_id_foreign` (`team_id`),
  ADD KEY `team_invitations_invited_by_foreign` (`invited_by`);

--
-- Indices de la tabla `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `team_members_team_id_user_id_unique` (`team_id`,`user_id`),
  ADD KEY `team_members_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `tipos_documento`
--
ALTER TABLE `tipos_documento`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tipos_documento_nombre_unique` (`nombre`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_current_team_id_foreign` (`current_team_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuarios_nombre_usuario_unique` (`nombre_usuario`),
  ADD UNIQUE KEY `usuarios_email_unique` (`email`),
  ADD KEY `usuarios_rol_id_foreign` (`rol_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `auditoria_accesos_legajo`
--
ALTER TABLE `auditoria_accesos_legajo`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `auditoria_descargas`
--
ALTER TABLE `auditoria_descargas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `barrios`
--
ALTER TABLE `barrios`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT de la tabla `cud`
--
ALTER TABLE `cud`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `documentos`
--
ALTER TABLE `documentos`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `legajos`
--
ALTER TABLE `legajos`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `localidades`
--
ALTER TABLE `localidades`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=208;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `passkeys`
--
ALTER TABLE `passkeys`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `personas`
--
ALTER TABLE `personas`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `problematica`
--
ALTER TABLE `problematica`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `provincias`
--
ALTER TABLE `provincias`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `teams`
--
ALTER TABLE `teams`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `team_invitations`
--
ALTER TABLE `team_invitations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipos_documento`
--
ALTER TABLE `tipos_documento`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_personas_completa`
--
DROP TABLE IF EXISTS `vista_personas_completa`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_personas_completa`  AS SELECT `p`.`id` AS `id`, `p`.`nombre` AS `nombre`, `p`.`apellido` AS `apellido`, `p`.`dni` AS `dni`, timestampdiff(YEAR,`p`.`fecha_nacimiento`,curdate()) AS `edad`, `b`.`nombre` AS `barrio`, `l`.`nombre` AS `localidad`, `pr`.`nombre` AS `provincia`, `c`.`numero_cud` AS `numero_cud`, `c`.`fecha_vencimiento` AS `cud_vencimiento`, (case when (`c`.`fecha_vencimiento` >= curdate()) then 'Vigente' else 'Vencido' end) AS `estado_cud` FROM ((((`personas` `p` left join `barrios` `b` on((`p`.`barrio_id` = `b`.`id`))) join `localidades` `l` on((`p`.`localidad_id` = `l`.`id`))) join `provincias` `pr` on((`l`.`provincia_id` = `pr`.`id`))) left join `cud` `c` on((`c`.`persona_id` = `p`.`id`))) ;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `auditoria_accesos_legajo`
--
ALTER TABLE `auditoria_accesos_legajo`
  ADD CONSTRAINT `auditoria_accesos_legajo_legajo_id_foreign` FOREIGN KEY (`legajo_id`) REFERENCES `legajos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `auditoria_accesos_legajo_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Filtros para la tabla `auditoria_descargas`
--
ALTER TABLE `auditoria_descargas`
  ADD CONSTRAINT `auditoria_descargas_documento_id_foreign` FOREIGN KEY (`documento_id`) REFERENCES `documentos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `auditoria_descargas_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Filtros para la tabla `barrios`
--
ALTER TABLE `barrios`
  ADD CONSTRAINT `barrios_localidad_id_foreign` FOREIGN KEY (`localidad_id`) REFERENCES `localidades` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Filtros para la tabla `cud`
--
ALTER TABLE `cud`
  ADD CONSTRAINT `cud_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `personas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD CONSTRAINT `documentos_legajo_id_foreign` FOREIGN KEY (`legajo_id`) REFERENCES `legajos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `documentos_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `personas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `documentos_subido_por_foreign` FOREIGN KEY (`subido_por`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `documentos_tipo_documento_id_foreign` FOREIGN KEY (`tipo_documento_id`) REFERENCES `tipos_documento` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Filtros para la tabla `legajos`
--
ALTER TABLE `legajos`
  ADD CONSTRAINT `legajos_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `legajos_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `personas` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `legajos_profesional_id_foreign` FOREIGN KEY (`profesional_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `legajo_problematica`
--
ALTER TABLE `legajo_problematica`
  ADD CONSTRAINT `legajo_problematica_legajo_id_foreign` FOREIGN KEY (`legajo_id`) REFERENCES `legajos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `legajo_problematica_problematica_id_foreign` FOREIGN KEY (`problematica_id`) REFERENCES `problematica` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Filtros para la tabla `localidades`
--
ALTER TABLE `localidades`
  ADD CONSTRAINT `localidades_provincia_id_foreign` FOREIGN KEY (`provincia_id`) REFERENCES `provincias` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Filtros para la tabla `passkeys`
--
ALTER TABLE `passkeys`
  ADD CONSTRAINT `passkeys_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `personas`
--
ALTER TABLE `personas`
  ADD CONSTRAINT `personas_barrio_id_foreign` FOREIGN KEY (`barrio_id`) REFERENCES `barrios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `personas_localidad_id_foreign` FOREIGN KEY (`localidad_id`) REFERENCES `localidades` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Filtros para la tabla `team_invitations`
--
ALTER TABLE `team_invitations`
  ADD CONSTRAINT `team_invitations_invited_by_foreign` FOREIGN KEY (`invited_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_invitations_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `team_members`
--
ALTER TABLE `team_members`
  ADD CONSTRAINT `team_members_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_members_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_current_team_id_foreign` FOREIGN KEY (`current_team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_rol_id_foreign` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
