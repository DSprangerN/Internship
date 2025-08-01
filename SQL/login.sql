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

-- A despejar estrutura para tabela estrelinha_login.users_login
DROP TABLE IF EXISTS `users_login`;
CREATE TABLE IF NOT EXISTS `users_login` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `Nome` varchar(50) NOT NULL,
  `Sobrenome` varchar(50) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Ativo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `Username` (`Username`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A despejar dados para tabela estrelinha_login.users_login: ~3 rows (aproximadamente)
INSERT INTO `users_login` (`id_user`, `Nome`, `Sobrenome`, `Username`, `Password`, `created_at`, `updated_at`, `Ativo`) VALUES
	(1, 'David', 'Neves', 'dasNeves', '$2y$10$3zcycvP8cQb20D4JJKOyXuXB8sPV4yx29.XQwAIXXddTrHf4zIUqm', '2025-04-30 23:54:19', '2025-05-28 20:33:13', 1),
	(2, 'Marcia', 'Vicente', 'mvicente', '$2y$10$wfHY3DZHIbxgz7Rw/xFBP.KVnkIrd1xVg1sdD/2G0yKr96iN8xXJW', '2025-05-13 20:01:12', '2025-05-28 20:33:32', 1),
	(8, 'Luis', 'Santos', 'santosL', '$2y$10$nbhY/RK4muxXwJafo9D4LuGog57gJq1bPRXSfWfkayDMZA2D7ywQ6', '2025-05-19 22:12:39', '2025-05-28 20:33:26', 1);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
