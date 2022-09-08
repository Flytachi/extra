
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `warframe`
--

-- --------------------------------------------------------

--
-- Структура таблицы `firmware_enterprises`
--

CREATE TABLE IF NOT EXISTS `firmware_enterprises` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` char(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact` char(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT 0,
  `create_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `firmware_licenses`
--

CREATE TABLE IF NOT EXISTS `firmware_licenses` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `enterprise_id` int(11) UNSIGNED NOT NULL,
  `series` char(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `enterprise_id` (`enterprise_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `firmware_webhooks`
--

CREATE TABLE IF NOT EXISTS `firmware_webhooks` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `enterprise_id` int(11) UNSIGNED NOT NULL,
  `unique_key` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_key` (`unique_key`),
  KEY `enterprise_id` (`enterprise_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `firmware_licenses`
--
ALTER TABLE `firmware_licenses`
  ADD CONSTRAINT `firmware_licenses_ibfk_1` FOREIGN KEY (`enterprise_id`) REFERENCES `firmware_enterprises` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `firmware_webhooks`
--
ALTER TABLE `firmware_webhooks`
  ADD CONSTRAINT `firmware_webhooks_ibfk_1` FOREIGN KEY (`enterprise_id`) REFERENCES `firmware_enterprises` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
