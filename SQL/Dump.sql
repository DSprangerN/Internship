-- MySQL dump 10.13  Distrib 8.0.38, for Win64 (x86_64)
--
-- Host: localhost    Database: estrelinha_login
-- ------------------------------------------------------
-- Server version	8.0.39

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `horas_descontadas`
--

DROP TABLE IF EXISTS `horas_descontadas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `horas_descontadas` (
  `id_descontos` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `Data` date NOT NULL,
  `Horas` time NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_descontos`),
  KEY `fk_id_user_hd` (`id_user`),
  CONSTRAINT `fk_id_user_hd` FOREIGN KEY (`id_user`) REFERENCES `users_login` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `horas_descontadas`
--

LOCK TABLES `horas_descontadas` WRITE;
/*!40000 ALTER TABLE `horas_descontadas` DISABLE KEYS */;
INSERT INTO `horas_descontadas` VALUES (1,1,'2025-05-19','10:00:00','2025-05-18 22:23:51','2025-05-18 22:23:51');
/*!40000 ALTER TABLE `horas_descontadas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `registo_horas`
--

DROP TABLE IF EXISTS `registo_horas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `registo_horas` (
  `id_registo` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `Data` date NOT NULL,
  `Hora_Entrada` time NOT NULL,
  `Hora_Saida` time NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `finalizado` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_registo`),
  UNIQUE KEY `unique_user_date` (`id_user`,`Data`),
  CONSTRAINT `fk_id_user` FOREIGN KEY (`id_user`) REFERENCES `users_login` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registo_horas`
--

LOCK TABLES `registo_horas` WRITE;
/*!40000 ALTER TABLE `registo_horas` DISABLE KEYS */;
INSERT INTO `registo_horas` VALUES (13,1,'2025-05-04','00:00:00','18:00:00','2025-05-07 22:48:58','2025-05-07 23:12:03',0),(14,1,'2025-04-27','10:00:00','18:00:00','2025-05-07 22:49:20','2025-05-07 22:49:20',0),(19,1,'2025-05-11','00:00:00','18:00:00','2025-05-07 23:12:38','2025-05-07 23:12:38',0),(20,1,'2025-05-12','10:00:00','18:00:00','2025-05-13 18:15:12','2025-05-13 18:15:12',0),(21,8,'2025-05-13','10:00:00','18:00:00','2025-05-19 22:46:44','2025-05-19 22:46:44',0),(22,8,'2025-05-11','08:00:00','18:00:00','2025-05-19 22:49:48','2025-05-19 22:49:48',0),(23,8,'2025-05-14','08:00:00','18:00:00','2025-05-19 22:50:00','2025-05-19 22:50:00',0),(24,8,'2025-05-06','05:00:00','20:00:00','2025-05-19 22:50:13','2025-05-19 22:50:13',0);
/*!40000 ALTER TABLE `registo_horas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_login`
--

DROP TABLE IF EXISTS `users_login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_login` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `Nome` varchar(50) NOT NULL,
  `Sobrenome` varchar(50) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `Username` (`Username`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_login`
--

LOCK TABLES `users_login` WRITE;
/*!40000 ALTER TABLE `users_login` DISABLE KEYS */;
INSERT INTO `users_login` VALUES (1,'David','Neves','dasNeves','$2y$10$3zcycvP8cQb20D4JJKOyXuXB8sPV4yx29.XQwAIXXddTrHf4zIUqm','2025-04-30 23:54:19','2025-05-01 00:40:06'),(2,'Marcia','Vicente','mvicente','$2y$10$wfHY3DZHIbxgz7Rw/xFBP.KVnkIrd1xVg1sdD/2G0yKr96iN8xXJW','2025-05-13 20:01:12','2025-05-13 20:04:12'),(8,'Luis','Santos','santosL','$2y$10$nbhY/RK4muxXwJafo9D4LuGog57gJq1bPRXSfWfkayDMZA2D7ywQ6','2025-05-19 22:12:39','2025-05-19 22:12:39');
/*!40000 ALTER TABLE `users_login` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-21 23:40:36
