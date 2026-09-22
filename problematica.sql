-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 22-09-2026 a las 16:10:49
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
-- Base de datos: `entramados`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `problematica`
--

CREATE TABLE `problematica` (
  `id` bigint UNSIGNED NOT NULL,
  `nombre` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` int UNSIGNED DEFAULT NULL,
  `deleted_by` int UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `problematica`
--

INSERT INTO `problematica` (`id`, `nombre`, `created_at`, `updated_by`, `deleted_by`, `updated_at`, `deleted_at`) VALUES
(1, 'Ausencia del adulto responsable', '2024-10-24 04:59:55', NULL, NULL, NULL, NULL),
(3, 'Maltrato', '2024-10-24 04:59:55', NULL, NULL, NULL, NULL),
(4, 'Negligencia', '2024-10-24 04:59:55', NULL, NULL, NULL, NULL),
(5, 'COVID 19', '2024-10-24 04:59:55', NULL, NULL, NULL, NULL),
(6, 'Abuso sexual', '2024-10-24 04:59:55', NULL, NULL, NULL, NULL),
(7, 'Explotacion', '2024-10-24 04:59:55', NULL, NULL, NULL, NULL),
(8, 'Falta de acceso a servicios', '2024-10-24 04:59:55', NULL, NULL, NULL, NULL),
(9, 'Discriminacion/Violencia institucional', '2024-10-24 04:59:55', NULL, NULL, NULL, NULL),
(10, 'Identidad', '2024-10-24 04:59:55', NULL, NULL, NULL, NULL),
(11, 'Responsabilidad penal juvenil', '2024-10-24 04:59:55', NULL, NULL, NULL, NULL),
(12, 'Consulta juridicas', '2024-10-24 04:59:55', NULL, NULL, NULL, NULL),
(13, 'Salud', '2024-10-24 04:59:55', NULL, NULL, NULL, NULL),
(15, 'Medidas de abrigo', '2024-10-24 04:59:55', NULL, NULL, NULL, NULL),
(16, 'Otros', '2024-10-24 04:59:55', NULL, NULL, NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `problematica`
--
ALTER TABLE `problematica`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `problematica`
--
ALTER TABLE `problematica`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
