-- --------------------------------------------------------
-- Anfitrião:                    127.0.0.1
-- Versão do servidor:           8.0.39 - MySQL Community Server - GPL
-- SO do servidor:               Win64
-- HeidiSQL Versão:              12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- A despejar estrutura da base de dados para estrelinha_login
CREATE DATABASE IF NOT EXISTS `estrelinha_login` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `estrelinha_login`;

-- A despejar estrutura para tabela estrelinha_login.registo_horas
CREATE TABLE IF NOT EXISTS `registo_horas` (
  `id_registo` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `Data` date NOT NULL,
  `Hora_Entrada` time NOT NULL,
  `Hora_Saida` time NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `finalizado` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_registo`),
  KEY `fk_id_user` (`id_user`),
  CONSTRAINT `fk_id_user` FOREIGN KEY (`id_user`) REFERENCES `users_login` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- A despejar dados para tabela estrelinha_login.registo_horas: ~12 rows (aproximadamente)
INSERT INTO `registo_horas` (`id_registo`, `id_user`, `Data`, `Hora_Entrada`, `Hora_Saida`, `created_at`, `updated_at`, `finalizado`) VALUES
	(1, 1, '2025-05-05', '00:00:00', '10:00:00', '2025-05-05 00:20:34', '2025-05-05 00:20:34', 0),
	(2, 1, '2025-05-05', '10:00:00', '00:00:00', '2025-05-05 00:20:55', '2025-05-05 00:20:55', 0),
	(3, 1, '2025-05-05', '10:00:00', '00:00:00', '2025-05-05 00:22:10', '2025-05-05 00:22:10', 0),
	(4, 1, '2025-05-05', '10:00:00', '00:00:00', '2025-05-05 00:22:13', '2025-05-05 00:22:13', 0),
	(5, 1, '2025-05-05', '02:00:00', '00:00:00', '2025-05-05 00:22:25', '2025-05-05 00:22:25', 0),
	(6, 1, '2025-05-05', '20:00:00', '00:00:00', '2025-05-05 00:23:05', '2025-05-05 00:23:05', 0),
	(7, 1, '2025-04-27', '05:00:00', '00:00:00', '2025-05-06 23:30:23', '2025-05-06 23:30:23', 0),
	(8, 1, '2025-04-27', '05:00:00', '00:00:00', '2025-05-06 23:33:29', '2025-05-06 23:33:29', 0),
	(9, 1, '2025-05-04', '05:00:00', '18:00:00', '2025-05-06 23:34:53', '2025-05-06 23:34:53', 0),
	(10, 1, '2025-05-18', '20:00:00', '00:00:00', '2025-05-06 23:35:28', '2025-05-06 23:35:28', 0),
	(11, 1, '2025-05-04', '15:00:00', '00:00:00', '2025-05-06 23:37:09', '2025-05-06 23:37:09', 0),
	(12, 1, '2025-05-05', '15:00:00', '00:00:00', '2025-05-06 23:37:30', '2025-05-06 23:37:30', 0);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
