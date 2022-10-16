SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE IF NOT EXISTS `auth_groups` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` char(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `auth_group_permissions` (
    `group_id` int(11) UNSIGNED NOT NULL,
    `permission` char(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (`group_id`,`permission`),
    KEY `group_id` (`group_id`),
    KEY `permission` (`permission`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `auth_permissions` (
    `name` char(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `description` char(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `auth_users` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` char(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `password` char(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `is_admin` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
    `is_delete` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `auth_user_permissions` (
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `name` char(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (`user_id`,`name`),
    KEY `user_id` (`user_id`),
    KEY `code` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_info` (
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `name` char(200) COLLATE utf8mb4_unicode_ci NOT NULL,
    `group_id` int(11) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (`user_id`),
    KEY `user_id` (`user_id`),
    KEY `group_id` (`group_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE `auth_group_permissions`
    ADD CONSTRAINT `auth_group_permissions_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `auth_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `auth_group_permissions_ibfk_2` FOREIGN KEY (`permission`) REFERENCES `auth_permissions` (`name`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `auth_user_permissions`
    ADD CONSTRAINT `auth_user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `auth_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `auth_user_permissions_ibfk_2` FOREIGN KEY (`name`) REFERENCES `auth_permissions` (`name`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `user_info`
    ADD CONSTRAINT `user_info_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `auth_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_info_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `auth_groups` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
