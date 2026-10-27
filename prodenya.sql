-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 27-10-2026 a las 13:39:18
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
-- Estructura de tabla para la tabla `agenda_eventos`
--

CREATE TABLE `agenda_eventos` (
  `id` bigint UNSIGNED NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'reunion',
  `visibilidad` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  `lugar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enlace` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `participantes_externos` json DEFAULT NULL,
  `notas` text COLLATE utf8mb4_unicode_ci,
  `estado` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'programado',
  `creado_por` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `agenda_evento_legajo`
--

CREATE TABLE `agenda_evento_legajo` (
  `agenda_evento_id` bigint UNSIGNED NOT NULL,
  `legajo_id` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `agenda_evento_usuario`
--

CREATE TABLE `agenda_evento_usuario` (
  `agenda_evento_id` bigint UNSIGNED NOT NULL,
  `usuario_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria_accesos_legajo`
--

CREATE TABLE `auditoria_accesos_legajo` (
  `id` bigint UNSIGNED NOT NULL,
  `legajo_id` int UNSIGNED NOT NULL,
  `usuario_id` bigint UNSIGNED NOT NULL,
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
  `usuario_id` bigint UNSIGNED NOT NULL,
  `fecha_descarga` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_origen` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria_eventos`
--

CREATE TABLE `auditoria_eventos` (
  `id` bigint UNSIGNED NOT NULL,
  `usuario_id` bigint UNSIGNED DEFAULT NULL,
  `evento` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditable_type` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auditable_id` bigint UNSIGNED DEFAULT NULL,
  `datos_antes` json DEFAULT NULL,
  `datos_despues` json DEFAULT NULL,
  `ip_origen` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
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

--
-- Volcado de datos para la tabla `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-c525a5357e97fef8d3db25841c86da1a', 'i:1;', 1793055979),
('laravel-cache-c525a5357e97fef8d3db25841c86da1a:timer', 'i:1793055979;', 1793055979);

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
-- Estructura de tabla para la tabla `conversaciones`
--

CREATE TABLE `conversaciones` (
  `id` bigint UNSIGNED NOT NULL,
  `tipo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'directa',
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creado_por` bigint UNSIGNED NOT NULL,
  `ultimo_mensaje_en` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `conversacion_usuario`
--

CREATE TABLE `conversacion_usuario` (
  `conversacion_id` bigint UNSIGNED NOT NULL,
  `usuario_id` bigint UNSIGNED NOT NULL,
  `ultimo_leido_en` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
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
  `intervencion_id` int UNSIGNED DEFAULT NULL,
  `tipo_documento_id` int UNSIGNED NOT NULL,
  `nombre_archivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ruta_archivo` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tamanio_bytes` bigint UNSIGNED DEFAULT NULL,
  `subido_por` bigint UNSIGNED NOT NULL,
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
-- Estructura de tabla para la tabla `intervenciones`
--

CREATE TABLE `intervenciones` (
  `id` int UNSIGNED NOT NULL,
  `legajo_id` int UNSIGNED NOT NULL,
  `tipo_intervencion_id` int UNSIGNED NOT NULL,
  `profesional_id` bigint UNSIGNED NOT NULL,
  `fecha_intervencion` date NOT NULL,
  `hora_intervencion` time DEFAULT NULL,
  `detalle` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `firma_nombre` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firma_aclaracion` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firmada_en` timestamp NULL DEFAULT NULL,
  `firma_archivo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firma_entrevistado_nombre` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firma_entrevistado_aclaracion` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firma_entrevistado_archivo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firma_entrevistado_en` timestamp NULL DEFAULT NULL,
  `creado_por` bigint UNSIGNED DEFAULT NULL,
  `actualizado_por` bigint UNSIGNED DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
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
  `profesional_id` bigint UNSIGNED DEFAULT NULL,
  `fecha_apertura` date NOT NULL DEFAULT (curdate()),
  `fecha_cierre` date DEFAULT NULL,
  `estado` enum('Abierto','Cerrado','Archivado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Abierto',
  `retencion_hasta` date DEFAULT NULL,
  `archivado_legal_en` timestamp NULL DEFAULT NULL,
  `eliminacion_autorizada_en` timestamp NULL DEFAULT NULL,
  `eliminacion_autorizada_por` bigint UNSIGNED DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `creado_por` bigint UNSIGNED NOT NULL,
  `actualizado_por` bigint UNSIGNED DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `legajo_problematica`
--

CREATE TABLE `legajo_problematica` (
  `legajo_id` int UNSIGNED NOT NULL,
  `problematica_id` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Estructura de tabla para la tabla `mail_messages`
--

CREATE TABLE `mail_messages` (
  `id` bigint UNSIGNED NOT NULL,
  `message_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thread_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `to_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body_text` longtext COLLATE utf8mb4_unicode_ci,
  `body_html` longtext COLLATE utf8mb4_unicode_ci,
  `received_at` timestamp NULL DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `estado` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Nuevo',
  `clasificado_por` bigint UNSIGNED DEFAULT NULL,
  `clasificado_en` timestamp NULL DEFAULT NULL,
  `attachments` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mail_message_legajo`
--

CREATE TABLE `mail_message_legajo` (
  `mail_message_id` bigint UNSIGNED NOT NULL,
  `legajo_id` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes`
--

CREATE TABLE `mensajes` (
  `id` bigint UNSIGNED NOT NULL,
  `conversacion_id` bigint UNSIGNED NOT NULL,
  `usuario_id` bigint UNSIGNED NOT NULL,
  `cuerpo` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensaje_recursos`
--

CREATE TABLE `mensaje_recursos` (
  `id` bigint UNSIGNED NOT NULL,
  `mensaje_id` bigint UNSIGNED NOT NULL,
  `tipo_recurso` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recurso_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(23, '2026_10_01_000015_create_tipos_problematicas_and_legajo_fields', 1),
(24, '2026_10_12_000001_add_avatar_path_to_users_table', 1),
(25, '2026_10_12_000001_unify_usuarios_into_users', 1),
(26, '2026_10_12_000002_normalize_problematicas_table', 1),
(27, '2026_10_12_000003_add_audit_columns_to_personas_and_legajos', 1),
(28, '2026_10_12_000004_optimize_indexes_and_make_dni_nullable', 1),
(29, '2026_10_12_000005_fix_vista_personas_completa', 1),
(30, '2026_10_12_000007_create_mail_messages_table', 1),
(31, '2026_10_12_000008_integrate_legajos_into_personas', 1),
(32, '2026_10_15_000001_create_motivos_ingreso_table', 1),
(33, '2026_10_15_000002_add_motivo_ingreso_id_to_personas_table', 1),
(34, '2026_10_16_000001_create_tipos_intervencion_and_intervenciones_tables', 1),
(35, '2026_10_16_000002_add_intervencion_id_to_documentos', 1),
(36, '2026_10_19_000001_extend_intervenciones_and_seed_types', 1),
(37, '2026_10_19_000002_add_digital_signatures_to_intervenciones', 1),
(38, '2026_10_19_000003_create_internal_communication_tables', 1),
(39, '2026_10_20_000001_create_workflow_tables', 1),
(40, '2026_10_20_000002_extend_mailbox_workflow', 1),
(41, '2026_10_20_000003_create_auditoria_eventos_table', 1),
(42, '2026_10_20_000004_add_legal_retention_to_legajos', 1),
(43, '2026_10_20_000005_add_visibility_to_agenda_eventos', 1),
(44, '2026_10_20_000006_add_account_workflow_to_users_table', 1),
(45, '2026_10_20_000007_add_fecha_cierre_to_legajos', 1);

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
  `dni` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `creado_por` bigint UNSIGNED DEFAULT NULL,
  `actualizado_por` bigint UNSIGNED DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `numero_legajo` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `motivo_ingreso` text COLLATE utf8mb4_unicode_ci,
  `nivel_riesgo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `origen_derivacion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profesional_id` bigint UNSIGNED DEFAULT NULL,
  `fecha_apertura` date DEFAULT NULL,
  `fecha_cierre` date DEFAULT NULL,
  `estado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observaciones_legajo` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `persona_problematica`
--

CREATE TABLE `persona_problematica` (
  `persona_id` int UNSIGNED NOT NULL,
  `problematica_id` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `problematicas`
--

CREATE TABLE `problematicas` (
  `id` int UNSIGNED NOT NULL,
  `nombre` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_by` bigint UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `problematicas`
--

INSERT INTO `problematicas` (`id`, `nombre`, `created_at`, `updated_by`, `deleted_by`, `updated_at`, `deleted_at`) VALUES
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
(1, 'admin', 'Acceso total al sistema', '2026-08-14 06:08:47'),
(2, 'operador', 'Carga y edición de personas y documentos', '2026-08-14 06:08:47'),
(3, 'consulta', 'Solo lectura y descarga de documentos', '2026-08-14 06:08:47'),
(4, 'oficios', 'Clasificación, vinculación y derivación de comunicaciones.', '2026-10-20 06:00:00'),
(5, 'coordinador', 'Supervisión, agenda, tareas y avisos.', '2026-10-20 06:00:00'),
(6, 'profesional', 'Intervenciones, seguimiento y firmas profesionales.', '2026-10-20 06:00:00'),
(7, 'administrativo', 'Carga y organización de información operativa.', '2026-10-20 06:00:00');

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
('yDFX7MHbk8EPRnABIXbZ0ycedP6JTMhKDVXHsSoV', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJXTllsNThYMEN3Y2VmdlNvTWxlU0tSYzJRb05PQUMxQ2x1N0tpMEZKIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDAifSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1793106689);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas`
--

CREATE TABLE `tareas` (
  `id` bigint UNSIGNED NOT NULL,
  `tipo` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'derivacion',
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `prioridad` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `estado` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `fecha_limite` date DEFAULT NULL,
  `asignado_a` bigint UNSIGNED DEFAULT NULL,
  `creado_por` bigint UNSIGNED NOT NULL,
  `legajo_id` int UNSIGNED DEFAULT NULL,
  `intervencion_id` int UNSIGNED DEFAULT NULL,
  `mail_message_id` bigint UNSIGNED DEFAULT NULL,
  `completada_en` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarea_recursos`
--

CREATE TABLE `tarea_recursos` (
  `id` bigint UNSIGNED NOT NULL,
  `tarea_id` bigint UNSIGNED NOT NULL,
  `tipo_recurso` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recurso_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_intervencion`
--

CREATE TABLE `tipos_intervencion` (
  `id` int UNSIGNED NOT NULL,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipos_intervencion`
--

INSERT INTO `tipos_intervencion` (`id`, `nombre`, `activo`) VALUES
(1, 'Denuncia', 1),
(2, 'Oficio', 1),
(3, 'Acta', 1),
(4, 'Mail', 1),
(5, 'Intervención en calle', 1),
(6, 'Intervención domiciliaria', 1),
(7, 'Intervención en sede', 1),
(8, 'Sin intervención', 1),
(9, 'Respuesta oficio', 1),
(10, 'Recepcion oficio', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dni` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profesion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol_id` int UNSIGNED DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `estado_cuenta` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aprobado',
  `aprobado_en` datetime DEFAULT NULL,
  `aprobado_por` bigint UNSIGNED DEFAULT NULL,
  `rechazado_en` datetime DEFAULT NULL,
  `rechazado_por` bigint UNSIGNED DEFAULT NULL,
  `motivo_rechazo` text COLLATE utf8mb4_unicode_ci,
  `suspendido_en` datetime DEFAULT NULL,
  `suspendido_por` bigint UNSIGNED DEFAULT NULL,
  `motivo_suspension` text COLLATE utf8mb4_unicode_ci,
  `archivado_en` datetime DEFAULT NULL,
  `archivado_por` bigint UNSIGNED DEFAULT NULL,
  `ultimo_acceso` datetime DEFAULT NULL,
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

INSERT INTO `users` (`id`, `name`, `dni`, `telefono`, `profesion`, `email`, `avatar_path`, `email_verified_at`, `password`, `rol_id`, `activo`, `estado_cuenta`, `aprobado_en`, `aprobado_por`, `rechazado_en`, `rechazado_por`, `motivo_rechazo`, `suspendido_en`, `suspendido_por`, `motivo_suspension`, `archivado_en`, `archivado_por`, `ultimo_acceso`, `current_team_id`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Administrador', NULL, NULL, NULL, 'admin@gmail.com', NULL, '2026-10-24 05:21:19', '$2y$12$8fHwThf6/gi7VLpxPdnhreVP/HgdCCJnNn70d/fFGG/F6DI9Je6xK', 1, 1, 'aprobado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-24 05:19:31', '2026-10-24 05:21:19'),
(10, 'juan segundo cian', '45032239', '+543364318066', NULL, 'juansegundocian@gmail.com', NULL, '2026-10-27 04:22:02', '$2y$12$SNWcyVNCU3xfe11C2lnT5uDAwjwby2YmU6a8OzTlB2UXSBa7OvNf6', 6, 1, 'aprobado', '2026-10-26 22:25:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-27 04:19:55', '2026-10-27 04:25:00');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_personas_completa`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_personas_completa` (
`activo` tinyint(1)
,`apellido` varchar(100)
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
-- Indices de la tabla `agenda_eventos`
--
ALTER TABLE `agenda_eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agenda_eventos_creado_por_foreign` (`creado_por`),
  ADD KEY `agenda_eventos_fecha_inicio_index` (`fecha_inicio`),
  ADD KEY `agenda_eventos_visibilidad_index` (`visibilidad`);

--
-- Indices de la tabla `agenda_evento_legajo`
--
ALTER TABLE `agenda_evento_legajo`
  ADD PRIMARY KEY (`agenda_evento_id`,`legajo_id`),
  ADD KEY `agenda_evento_legajo_legajo_id_foreign` (`legajo_id`);

--
-- Indices de la tabla `agenda_evento_usuario`
--
ALTER TABLE `agenda_evento_usuario`
  ADD PRIMARY KEY (`agenda_evento_id`,`usuario_id`),
  ADD KEY `agenda_evento_usuario_usuario_id_foreign` (`usuario_id`);

--
-- Indices de la tabla `auditoria_accesos_legajo`
--
ALTER TABLE `auditoria_accesos_legajo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_auditlegajo_usuario` (`usuario_id`),
  ADD KEY `idx_auditlegajo_fecha` (`fecha_acceso`),
  ADD KEY `idx_auditlegajo_legajo_fecha` (`legajo_id`,`fecha_acceso`);

--
-- Indices de la tabla `auditoria_descargas`
--
ALTER TABLE `auditoria_descargas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_auditoria_usuario` (`usuario_id`),
  ADD KEY `idx_auditoria_fecha` (`fecha_descarga`),
  ADD KEY `idx_auditoria_documento_fecha` (`documento_id`,`fecha_descarga`);

--
-- Indices de la tabla `auditoria_eventos`
--
ALTER TABLE `auditoria_eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auditoria_eventos_usuario_id_foreign` (`usuario_id`),
  ADD KEY `auditoria_eventos_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
  ADD KEY `auditoria_eventos_evento_creado_en_index` (`evento`,`creado_en`);

--
-- Indices de la tabla `barrios`
--
ALTER TABLE `barrios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_barrio` (`nombre`,`localidad_id`),
  ADD KEY `barrios_localidad_id_foreign` (`localidad_id`),
  ADD KEY `barrios_updated_by_foreign` (`updated_by`),
  ADD KEY `barrios_deleted_by_foreign` (`deleted_by`);

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
-- Indices de la tabla `conversaciones`
--
ALTER TABLE `conversaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversaciones_creado_por_foreign` (`creado_por`),
  ADD KEY `conversaciones_ultimo_mensaje_en_index` (`ultimo_mensaje_en`);

--
-- Indices de la tabla `conversacion_usuario`
--
ALTER TABLE `conversacion_usuario`
  ADD PRIMARY KEY (`conversacion_id`,`usuario_id`),
  ADD KEY `conversacion_usuario_usuario_id_foreign` (`usuario_id`);

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
  ADD KEY `idx_documento_persona` (`persona_id`),
  ADD KEY `idx_documento_legajo` (`legajo_id`),
  ADD KEY `documentos_subido_por_foreign` (`subido_por`),
  ADD KEY `idx_documento_intervencion` (`intervencion_id`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indices de la tabla `intervenciones`
--
ALTER TABLE `intervenciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_intervencion_legajo_fecha` (`legajo_id`,`fecha_intervencion`),
  ADD KEY `intervenciones_tipo_intervencion_id_foreign` (`tipo_intervencion_id`),
  ADD KEY `intervenciones_profesional_id_foreign` (`profesional_id`),
  ADD KEY `intervenciones_creado_por_foreign` (`creado_por`),
  ADD KEY `intervenciones_actualizado_por_foreign` (`actualizado_por`);

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
  ADD KEY `legajos_profesional_id_foreign` (`profesional_id`),
  ADD KEY `legajos_actualizado_por_foreign` (`actualizado_por`),
  ADD KEY `idx_legajo_persona_estado` (`persona_id`,`estado`),
  ADD KEY `idx_legajo_estado_creado` (`estado`,`creado_en`),
  ADD KEY `legajos_eliminacion_autorizada_por_foreign` (`eliminacion_autorizada_por`),
  ADD KEY `legajos_retencion_hasta_index` (`retencion_hasta`),
  ADD KEY `idx_legajo_fecha_cierre` (`fecha_cierre`);

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
  ADD KEY `localidades_provincia_id_foreign` (`provincia_id`),
  ADD KEY `localidades_updated_by_foreign` (`updated_by`),
  ADD KEY `localidades_deleted_by_foreign` (`deleted_by`);

--
-- Indices de la tabla `mail_messages`
--
ALTER TABLE `mail_messages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mail_messages_message_id_unique` (`message_id`),
  ADD KEY `mail_messages_thread_id_index` (`thread_id`),
  ADD KEY `mail_messages_from_email_index` (`from_email`),
  ADD KEY `mail_messages_received_at_index` (`received_at`),
  ADD KEY `mail_messages_is_read_index` (`is_read`),
  ADD KEY `mail_messages_clasificado_por_foreign` (`clasificado_por`),
  ADD KEY `mail_messages_estado_index` (`estado`);

--
-- Indices de la tabla `mail_message_legajo`
--
ALTER TABLE `mail_message_legajo`
  ADD PRIMARY KEY (`mail_message_id`,`legajo_id`),
  ADD KEY `mail_message_legajo_legajo_id_foreign` (`legajo_id`);

--
-- Indices de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mensajes_usuario_id_foreign` (`usuario_id`),
  ADD KEY `mensajes_conversacion_id_created_at_index` (`conversacion_id`,`created_at`);

--
-- Indices de la tabla `mensaje_recursos`
--
ALTER TABLE `mensaje_recursos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mensaje_recursos_mensaje_id_tipo_recurso_recurso_id_unique` (`mensaje_id`,`tipo_recurso`,`recurso_id`);

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
  ADD UNIQUE KEY `personas_numero_legajo_unique` (`numero_legajo`),
  ADD KEY `personas_barrio_id_foreign` (`barrio_id`),
  ADD KEY `personas_localidad_id_foreign` (`localidad_id`),
  ADD KEY `personas_creado_por_foreign` (`creado_por`),
  ADD KEY `personas_actualizado_por_foreign` (`actualizado_por`),
  ADD KEY `idx_persona_activo_apellido_nombre` (`activo`,`apellido`,`nombre`),
  ADD KEY `idx_persona_fecha_cierre` (`fecha_cierre`);

--
-- Indices de la tabla `persona_problematica`
--
ALTER TABLE `persona_problematica`
  ADD PRIMARY KEY (`persona_id`,`problematica_id`),
  ADD KEY `persona_problematica_problematica_id_foreign` (`problematica_id`);

--
-- Indices de la tabla `problematicas`
--
ALTER TABLE `problematicas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `problematicas_updated_by_foreign` (`updated_by`),
  ADD KEY `problematicas_deleted_by_foreign` (`deleted_by`);

--
-- Indices de la tabla `provincias`
--
ALTER TABLE `provincias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `provincias_nombre_unique` (`nombre`),
  ADD KEY `provincias_updated_by_foreign` (`updated_by`),
  ADD KEY `provincias_deleted_by_foreign` (`deleted_by`);

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
-- Indices de la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tareas_creado_por_foreign` (`creado_por`),
  ADD KEY `tareas_mail_message_id_foreign` (`mail_message_id`),
  ADD KEY `tareas_asignado_a_estado_index` (`asignado_a`,`estado`),
  ADD KEY `tareas_legajo_id_foreign` (`legajo_id`),
  ADD KEY `tareas_intervencion_id_foreign` (`intervencion_id`),
  ADD KEY `tareas_fecha_limite_index` (`fecha_limite`);

--
-- Indices de la tabla `tarea_recursos`
--
ALTER TABLE `tarea_recursos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tarea_recursos_tarea_id_tipo_recurso_recurso_id_unique` (`tarea_id`,`tipo_recurso`,`recurso_id`);

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
-- Indices de la tabla `tipos_intervencion`
--
ALTER TABLE `tipos_intervencion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tipos_intervencion_nombre_unique` (`nombre`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_dni_unique` (`dni`),
  ADD KEY `users_current_team_id_foreign` (`current_team_id`),
  ADD KEY `users_rol_id_foreign` (`rol_id`),
  ADD KEY `users_aprobado_por_foreign` (`aprobado_por`),
  ADD KEY `users_rechazado_por_foreign` (`rechazado_por`),
  ADD KEY `users_suspendido_por_foreign` (`suspendido_por`),
  ADD KEY `users_archivado_por_foreign` (`archivado_por`),
  ADD KEY `users_estado_cuenta_index` (`estado_cuenta`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `agenda_eventos`
--
ALTER TABLE `agenda_eventos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT de la tabla `auditoria_eventos`
--
ALTER TABLE `auditoria_eventos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `barrios`
--
ALTER TABLE `barrios`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT de la tabla `conversaciones`
--
ALTER TABLE `conversaciones`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT de la tabla `intervenciones`
--
ALTER TABLE `intervenciones`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `legajos`
--
ALTER TABLE `legajos`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `localidades`
--
ALTER TABLE `localidades`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=208;

--
-- AUTO_INCREMENT de la tabla `mail_messages`
--
ALTER TABLE `mail_messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mensaje_recursos`
--
ALTER TABLE `mensaje_recursos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT de la tabla `passkeys`
--
ALTER TABLE `passkeys`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `personas`
--
ALTER TABLE `personas`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `problematicas`
--
ALTER TABLE `problematicas`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `provincias`
--
ALTER TABLE `provincias`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `tareas`
--
ALTER TABLE `tareas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tarea_recursos`
--
ALTER TABLE `tarea_recursos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipos_intervencion`
--
ALTER TABLE `tipos_intervencion`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_personas_completa`
--
DROP TABLE IF EXISTS `vista_personas_completa`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY INVOKER VIEW `vista_personas_completa`  AS SELECT `p`.`id` AS `id`, `p`.`nombre` AS `nombre`, `p`.`apellido` AS `apellido`, `p`.`dni` AS `dni`, `p`.`activo` AS `activo`, timestampdiff(YEAR,`p`.`fecha_nacimiento`,curdate()) AS `edad`, `b`.`nombre` AS `barrio`, `l`.`nombre` AS `localidad`, `pr`.`nombre` AS `provincia`, `c`.`numero_cud` AS `numero_cud`, `c`.`fecha_vencimiento` AS `cud_vencimiento`, (case when (`c`.`id` is null) then 'Sin CUD' when (`c`.`fecha_vencimiento` >= curdate()) then 'Vigente' else 'Vencido' end) AS `estado_cud` FROM ((((`personas` `p` left join `barrios` `b` on((`p`.`barrio_id` = `b`.`id`))) join `localidades` `l` on((`p`.`localidad_id` = `l`.`id`))) join `provincias` `pr` on((`l`.`provincia_id` = `pr`.`id`))) left join `cud` `c` on((`c`.`id` = (select `c2`.`id` from `cud` `c2` where (`c2`.`persona_id` = `p`.`id`) order by `c2`.`fecha_vencimiento` desc,`c2`.`id` desc limit 1)))) ;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `agenda_eventos`
--
ALTER TABLE `agenda_eventos`
  ADD CONSTRAINT `agenda_eventos_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `agenda_evento_legajo`
--
ALTER TABLE `agenda_evento_legajo`
  ADD CONSTRAINT `agenda_evento_legajo_agenda_evento_id_foreign` FOREIGN KEY (`agenda_evento_id`) REFERENCES `agenda_eventos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `agenda_evento_legajo_legajo_id_foreign` FOREIGN KEY (`legajo_id`) REFERENCES `legajos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `agenda_evento_usuario`
--
ALTER TABLE `agenda_evento_usuario`
  ADD CONSTRAINT `agenda_evento_usuario_agenda_evento_id_foreign` FOREIGN KEY (`agenda_evento_id`) REFERENCES `agenda_eventos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `agenda_evento_usuario_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `auditoria_accesos_legajo`
--
ALTER TABLE `auditoria_accesos_legajo`
  ADD CONSTRAINT `auditoria_accesos_legajo_legajo_id_foreign` FOREIGN KEY (`legajo_id`) REFERENCES `legajos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `auditoria_accesos_legajo_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Filtros para la tabla `auditoria_descargas`
--
ALTER TABLE `auditoria_descargas`
  ADD CONSTRAINT `auditoria_descargas_documento_id_foreign` FOREIGN KEY (`documento_id`) REFERENCES `documentos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `auditoria_descargas_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Filtros para la tabla `auditoria_eventos`
--
ALTER TABLE `auditoria_eventos`
  ADD CONSTRAINT `auditoria_eventos_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `barrios`
--
ALTER TABLE `barrios`
  ADD CONSTRAINT `barrios_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `barrios_localidad_id_foreign` FOREIGN KEY (`localidad_id`) REFERENCES `localidades` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `barrios_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `conversaciones`
--
ALTER TABLE `conversaciones`
  ADD CONSTRAINT `conversaciones_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `conversacion_usuario`
--
ALTER TABLE `conversacion_usuario`
  ADD CONSTRAINT `conversacion_usuario_conversacion_id_foreign` FOREIGN KEY (`conversacion_id`) REFERENCES `conversaciones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversacion_usuario_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `cud`
--
ALTER TABLE `cud`
  ADD CONSTRAINT `cud_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `personas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD CONSTRAINT `documentos_intervencion_id_foreign` FOREIGN KEY (`intervencion_id`) REFERENCES `intervenciones` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `documentos_legajo_id_foreign` FOREIGN KEY (`legajo_id`) REFERENCES `legajos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `documentos_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `personas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `documentos_subido_por_foreign` FOREIGN KEY (`subido_por`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `documentos_tipo_documento_id_foreign` FOREIGN KEY (`tipo_documento_id`) REFERENCES `tipos_documento` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Filtros para la tabla `intervenciones`
--
ALTER TABLE `intervenciones`
  ADD CONSTRAINT `intervenciones_actualizado_por_foreign` FOREIGN KEY (`actualizado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `intervenciones_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `intervenciones_legajo_id_foreign` FOREIGN KEY (`legajo_id`) REFERENCES `legajos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `intervenciones_profesional_id_foreign` FOREIGN KEY (`profesional_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `intervenciones_tipo_intervencion_id_foreign` FOREIGN KEY (`tipo_intervencion_id`) REFERENCES `tipos_intervencion` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Filtros para la tabla `legajos`
--
ALTER TABLE `legajos`
  ADD CONSTRAINT `legajos_actualizado_por_foreign` FOREIGN KEY (`actualizado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `legajos_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `legajos_eliminacion_autorizada_por_foreign` FOREIGN KEY (`eliminacion_autorizada_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `legajos_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `personas` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `legajos_profesional_id_foreign` FOREIGN KEY (`profesional_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `legajo_problematica`
--
ALTER TABLE `legajo_problematica`
  ADD CONSTRAINT `legajo_problematica_legajo_id_foreign` FOREIGN KEY (`legajo_id`) REFERENCES `legajos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `legajo_problematica_problematica_id_foreign` FOREIGN KEY (`problematica_id`) REFERENCES `problematicas` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Filtros para la tabla `localidades`
--
ALTER TABLE `localidades`
  ADD CONSTRAINT `localidades_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `localidades_provincia_id_foreign` FOREIGN KEY (`provincia_id`) REFERENCES `provincias` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `localidades_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `mail_messages`
--
ALTER TABLE `mail_messages`
  ADD CONSTRAINT `mail_messages_clasificado_por_foreign` FOREIGN KEY (`clasificado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `mail_message_legajo`
--
ALTER TABLE `mail_message_legajo`
  ADD CONSTRAINT `mail_message_legajo_legajo_id_foreign` FOREIGN KEY (`legajo_id`) REFERENCES `legajos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mail_message_legajo_mail_message_id_foreign` FOREIGN KEY (`mail_message_id`) REFERENCES `mail_messages` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD CONSTRAINT `mensajes_conversacion_id_foreign` FOREIGN KEY (`conversacion_id`) REFERENCES `conversaciones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mensajes_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `mensaje_recursos`
--
ALTER TABLE `mensaje_recursos`
  ADD CONSTRAINT `mensaje_recursos_mensaje_id_foreign` FOREIGN KEY (`mensaje_id`) REFERENCES `mensajes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `passkeys`
--
ALTER TABLE `passkeys`
  ADD CONSTRAINT `passkeys_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `personas`
--
ALTER TABLE `personas`
  ADD CONSTRAINT `personas_actualizado_por_foreign` FOREIGN KEY (`actualizado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `personas_barrio_id_foreign` FOREIGN KEY (`barrio_id`) REFERENCES `barrios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `personas_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `personas_localidad_id_foreign` FOREIGN KEY (`localidad_id`) REFERENCES `localidades` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Filtros para la tabla `persona_problematica`
--
ALTER TABLE `persona_problematica`
  ADD CONSTRAINT `persona_problematica_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `personas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `persona_problematica_problematica_id_foreign` FOREIGN KEY (`problematica_id`) REFERENCES `problematicas` (`id`) ON DELETE RESTRICT;

--
-- Filtros para la tabla `problematicas`
--
ALTER TABLE `problematicas`
  ADD CONSTRAINT `problematicas_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `problematicas_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `provincias`
--
ALTER TABLE `provincias`
  ADD CONSTRAINT `provincias_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `provincias_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD CONSTRAINT `tareas_asignado_a_foreign` FOREIGN KEY (`asignado_a`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tareas_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tareas_intervencion_id_foreign` FOREIGN KEY (`intervencion_id`) REFERENCES `intervenciones` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tareas_legajo_id_foreign` FOREIGN KEY (`legajo_id`) REFERENCES `legajos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tareas_mail_message_id_foreign` FOREIGN KEY (`mail_message_id`) REFERENCES `mail_messages` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `tarea_recursos`
--
ALTER TABLE `tarea_recursos`
  ADD CONSTRAINT `tarea_recursos_tarea_id_foreign` FOREIGN KEY (`tarea_id`) REFERENCES `tareas` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `users_aprobado_por_foreign` FOREIGN KEY (`aprobado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_archivado_por_foreign` FOREIGN KEY (`archivado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_current_team_id_foreign` FOREIGN KEY (`current_team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_rechazado_por_foreign` FOREIGN KEY (`rechazado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_rol_id_foreign` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `users_suspendido_por_foreign` FOREIGN KEY (`suspendido_por`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
