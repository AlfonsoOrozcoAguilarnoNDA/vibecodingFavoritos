-- Estructura de la base de datos para vibecodingFavoritos
-- Servidor: cPanel / MySQL / Maria

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Tabla de Categorías
-- ----------------------------
DROP TABLE IF EXISTS `LINK_CATEGORIES`;
CREATE TABLE `LINK_CATEGORIES` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `category_icon` varchar(50) NOT NULL DEFAULT 'fa-link',
  `category_color` varchar(20) NOT NULL DEFAULT 'metro-blue',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Tabla de Enlaces (Favoritos)
-- ----------------------------
DROP TABLE IF EXISTS `LINKS`;
CREATE TABLE `LINKS` (
  `link_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `link_title` varchar(200) NOT NULL,
  `link_url` text NOT NULL,
  `link_comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`link_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `LINKS_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `LINK_CATEGORIES` (`category_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
