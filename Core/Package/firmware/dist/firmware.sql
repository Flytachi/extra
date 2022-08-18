
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Структура таблицы `firmware_enterprises`
--

CREATE TABLE `firmware_enterprises` (
  `id` bigint(20) NOT NULL,
  `name` char(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact` char(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT 0,
  `create_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `firmware_licenses`
--

CREATE TABLE `firmware_licenses` (
  `id` bigint(20) NOT NULL,
  `enterprise_id` bigint(20) NOT NULL,
  `series` char(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `firmware_webhooks`
--

CREATE TABLE `firmware_webhooks` (
  `id` bigint(20) NOT NULL,
  `enterprise_id` bigint(20) NOT NULL,
  `unique_key` char(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `firmware_enterprises`
--
ALTER TABLE `firmware_enterprises`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `firmware_licenses`
--
ALTER TABLE `firmware_licenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `enterprise_id` (`enterprise_id`);

--
-- Индексы таблицы `firmware_webhooks`
--
ALTER TABLE `firmware_webhooks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_key` (`unique_key`),
  ADD KEY `enterprise_id` (`enterprise_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `firmware_enterprises`
--
ALTER TABLE `firmware_enterprises`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `firmware_licenses`
--
ALTER TABLE `firmware_licenses`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `firmware_webhooks`
--
ALTER TABLE `firmware_webhooks`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

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
