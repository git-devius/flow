-- Database: flowdb
-- ------------------------------------------------------
CREATE DATABASE IF NOT EXISTS flowdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE flowdb;
--
-- Table structure for table `approvals`
--

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `approvals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `approvals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `validator_id` int(11) unsigned NOT NULL,
  `level` tinyint(4) NOT NULL,
  `decision` enum('approved','rejected') NOT NULL,
  `comment` text DEFAULT NULL,
  `decision_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `investment_id` (`request_id`),
  KEY `validator_id` (`validator_id`),
  KEY `idx_request_id` (`request_id`),
  CONSTRAINT `approvals_ibfk_2` FOREIGN KEY (`validator_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_approvals_request_unique_v2` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approvals`
--

LOCK TABLES `approvals` WRITE;
/*!40000 ALTER TABLE `approvals` DISABLE KEYS */;
-- INSERT INTO `approvals` VALUES (31,35,6,1,'approved','okay !!','2025-12-22 18:36:58'),(32,35,6,2,'approved','yop yop','2025-12-22 18:40:53'),(33,35,6,3,'rejected','mm pas en rêve !','2025-12-22 19:02:45'),(34,34,6,1,'approved','','2025-12-22 19:28:51'),(35,34,6,2,'approved','','2025-12-22 19:28:53'),(36,34,6,3,'approved','','2025-12-22 19:28:55');
/*!40000 ALTER TABLE `approvals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `companies` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pole_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_company` (`pole_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `companies`
--

LOCK TABLES `companies` WRITE;
/*!40000 ALTER TABLE `companies` DISABLE KEYS */;
-- INSERT INTO `companies` VALUES (5,6,'SCAGEX','2025-12-19 18:05:01');
/*!40000 ALTER TABLE `companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `poles`
--

DROP TABLE IF EXISTS `poles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `poles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `poles`
--

LOCK TABLES `poles` WRITE;
/*!40000 ALTER TABLE `poles` DISABLE KEYS */;
-- INSERT INTO `poles` VALUES (6,'AGENCES DE MARQUE','2025-12-19 18:00:54'),(7,'AUTRE','2025-12-19 18:02:09'),(8,'BOUTIQUE','2025-12-19 18:02:16'),(9,'CASH & CARRY','2025-12-19 18:03:44'),(10,'IMMOBILIERE','2025-12-19 18:03:51'),(11,'METROPOLE','2025-12-19 18:04:00'),(12,'PLATEFORMES','2025-12-19 18:04:07'),(13,'RETAIL','2025-12-19 18:04:15'),(14,'SUPPORT','2025-12-19 18:04:21');
/*!40000 ALTER TABLE `poles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `requests`
--

DROP TABLE IF EXISTS `requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pole_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `workflow_type` varchar(50) NOT NULL DEFAULT 'investment',
  `type` varchar(255) NOT NULL,
  `budget_planned` tinyint(1) NOT NULL DEFAULT 0,
  `objective` text DEFAULT NULL,
  `start_date_duration` text DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `requester_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected','cancelled','draft') NOT NULL DEFAULT 'pending',
  `current_step` int(11) NOT NULL DEFAULT 1,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deletion_comment` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pole_id` (`pole_id`),
  KEY `company_id` (`company_id`),
  KEY `requester_id` (`requester_id`),
  KEY `idx_requests_status` (`status`),
  KEY `idx_requests_workflow_type` (`workflow_type`),
  KEY `idx_requests_created_at` (`created_at`),
  KEY `idx_requests_company_workflow` (`company_id`,`workflow_type`),
  CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`pole_id`) REFERENCES `poles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `requests`
--

LOCK TABLES `requests` WRITE;
/*!40000 ALTER TABLE `requests` DISABLE KEYS */;
-- INSERT INTO `requests` VALUES (34,6,5,'investment','test',0,'coucou','12 mois',50000.00,6,'approved',3,NULL,'2025-12-22 16:38:40','2025-12-22 19:28:55',NULL),(35,6,5,'investment','test2',1,'kikoo','2000',45000.00,6,'rejected',3,NULL,'2025-12-22 16:42:18','2025-12-22 19:02:45',NULL);
/*!40000 ALTER TABLE `requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `api_token` varchar(255) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `allowed_workflows` varchar(255) DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (6,'alexandre.villemaine@groupesafo.com','$2y$10$GMILrgRoRpqebjk5e4X9L.OyJXuPwGIXE5kvqyEi5zl1Ws2xGS5ru','833807056ce049ab9b50c59c6d9226a5','VILLEMAINE Alexandre','admin',1,'investment,vacation,expense','2025-10-08 14:40:21');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workflow_steps`
--

DROP TABLE IF EXISTS `workflow_steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workflow_steps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `workflow_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'investment',
  `step_order` int(11) NOT NULL,
  `validator_user_id` int(10) unsigned DEFAULT NULL,
  `validator_role` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_step` (`company_id`,`workflow_type`,`step_order`),
  KEY `fk_steps_user` (`validator_user_id`),
  CONSTRAINT `fk_steps_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_steps_user` FOREIGN KEY (`validator_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workflow_steps`
--

LOCK TABLES `workflow_steps` WRITE;
/*!40000 ALTER TABLE `workflow_steps` DISABLE KEYS */;
-- INSERT INTO `workflow_steps` VALUES (11,5,'vacation',1,6,NULL,'2025-12-22 16:35:21'),(12,5,'investment',1,6,NULL,'2025-12-22 16:38:03'),(13,5,'investment',2,6,NULL,'2025-12-22 16:38:03'),(14,5,'investment',3,6,NULL,'2025-12-22 16:38:03');
/*!40000 ALTER TABLE `workflow_steps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workflow_validators`
--

DROP TABLE IF EXISTS `workflow_validators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workflow_validators` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(11) unsigned NOT NULL,
  `workflow_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g., investment, vacation, expense',
  `validator_lv1_user_id` int(11) unsigned DEFAULT NULL,
  `validator_lv2_user_id` int(11) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_workflow_company` (`company_id`,`workflow_type`),
  KEY `idx_v1` (`validator_lv1_user_id`),
  KEY `idx_v2` (`validator_lv2_user_id`),
  CONSTRAINT `fk_wf_val_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wf_val_v1` FOREIGN KEY (`validator_lv1_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_wf_val_v2` FOREIGN KEY (`validator_lv2_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workflow_validators`
--

LOCK TABLES `workflow_validators` WRITE;
/*!40000 ALTER TABLE `workflow_validators` DISABLE KEYS */;
/*!40000 ALTER TABLE `workflow_validators` ENABLE KEYS */;
UNLOCK TABLES;