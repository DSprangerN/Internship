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
  `Hora_Entrada` timestamp NULL DEFAULT NULL,
  `Hora_Saida` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `finalizado` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_registo`),
  UNIQUE KEY `unique_user_date` (`id_user`,`Data`),
  CONSTRAINT `fk_id_user` FOREIGN KEY (`id_user`) REFERENCES `users_login` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- A despejar dados para tabela estrelinha_login.registo_horas: ~9 rows (aproximadamente)
INSERT INTO `registo_horas` (`id_registo`, `id_user`, `Data`, `Hora_Entrada`, `Hora_Saida`, `created_at`, `updated_at`, `finalizado`) VALUES
	(13, 1, '2025-05-04', '2025-06-01 23:00:00', '2025-06-02 17:00:00', '2025-05-07 22:48:58', '2025-05-07 23:12:03', 0),
	(14, 1, '2025-04-27', '2025-06-02 09:00:00', '2025-06-02 17:00:00', '2025-05-07 22:49:20', '2025-05-07 22:49:20', 0),
	(19, 1, '2025-05-11', '2025-06-01 23:00:00', '2025-06-02 17:00:00', '2025-05-07 23:12:38', '2025-05-07 23:12:38', 0),
	(20, 1, '2025-05-12', '2025-06-02 09:00:00', '2025-06-02 17:00:00', '2025-05-13 18:15:12', '2025-05-13 18:15:12', 0),
	(21, 8, '2025-05-13', '2025-06-02 09:00:00', '2025-06-02 17:00:00', '2025-05-19 22:46:44', '2025-05-19 22:46:44', 0),
	(22, 8, '2025-05-11', '2025-06-02 07:00:00', '2025-06-02 17:00:00', '2025-05-19 22:49:48', '2025-05-19 22:49:48', 0),
	(23, 8, '2025-05-14', '2025-06-02 07:00:00', '2025-06-02 17:00:00', '2025-05-19 22:50:00', '2025-05-19 22:50:00', 0),
	(24, 8, '2025-05-06', '2025-06-02 04:00:00', '2025-06-02 19:00:00', '2025-05-19 22:50:13', '2025-05-19 22:50:13', 0),
	(25, 1, '2025-05-30', '2025-06-02 06:00:00', '2025-06-02 14:00:00', '2025-05-30 18:23:43', '2025-05-30 18:23:43', 0);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
