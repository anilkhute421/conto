-- MySQL dump 10.13  Distrib 8.0.23, for Win64 (x86_64)
--
-- Host: beastmaindevdb.mysql.database.azure.com    Database: contoliodev
-- ------------------------------------------------------
-- Server version	5.6.47.0

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
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_title` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `buildings_management_create` tinyint(4) NOT NULL,
  `buildings_management_view` tinyint(4) NOT NULL,
  `buildings_management_edit` tinyint(4) NOT NULL,
  `buildings_management_delete` tinyint(4) NOT NULL,
  `contracts_management_create` tinyint(4) NOT NULL,
  `contracts_management_view` tinyint(4) NOT NULL,
  `contracts_management_edit` tinyint(4) NOT NULL,
  `contracts_management_delete` tinyint(4) NOT NULL,
  `payment_management_create` tinyint(4) NOT NULL,
  `payment_management_view` tinyint(4) NOT NULL,
  `payment_management_edit` tinyint(4) NOT NULL,
  `payment_management_delete` tinyint(4) NOT NULL,
  `tenant_management_create` tinyint(4) NOT NULL,
  `tenant_management_view` tinyint(4) NOT NULL,
  `tenant_management_edit` tinyint(4) NOT NULL,
  `tenant_management_delete` tinyint(4) NOT NULL,
  `units_management_create` tinyint(4) NOT NULL,
  `units_management_view` tinyint(4) NOT NULL,
  `units_management_edit` tinyint(4) NOT NULL,
  `units_management_delete` tinyint(4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `buildings_management_none` tinyint(4) NOT NULL DEFAULT '0',
  `contracts_management_none` tinyint(4) NOT NULL DEFAULT '0',
  `payment_management_none` tinyint(4) NOT NULL DEFAULT '0',
  `tenant_management_none` tinyint(4) NOT NULL DEFAULT '0',
  `units_management_none` tinyint(4) NOT NULL DEFAULT '0',
  `avail_unit_create` tinyint(4) NOT NULL DEFAULT '0',
  `avail_unit_view` tinyint(4) NOT NULL DEFAULT '0',
  `avail_unit_edit` tinyint(4) NOT NULL DEFAULT '0',
  `avail_unit_delete` tinyint(4) NOT NULL DEFAULT '0',
  `avail_unit_none` tinyint(4) NOT NULL DEFAULT '0',
  `maintenance_req_create` tinyint(4) NOT NULL DEFAULT '0',
  `maintenance_req_view` tinyint(4) NOT NULL DEFAULT '0',
  `maintenance_req_edit` tinyint(4) NOT NULL DEFAULT '0',
  `maintenance_req_delete` tinyint(4) NOT NULL DEFAULT '0',
  `maintenance_req_none` tinyint(4) NOT NULL DEFAULT '0',
  `expert_create` tinyint(4) NOT NULL DEFAULT '0',
  `expert_view` tinyint(4) NOT NULL DEFAULT '0',
  `expert_edit` tinyint(4) NOT NULL DEFAULT '0',
  `expert_delete` tinyint(4) NOT NULL DEFAULT '0',
  `expert_none` tinyint(4) NOT NULL DEFAULT '0',
  `expense_create` tinyint(4) NOT NULL DEFAULT '0',
  `expense_view` tinyint(4) NOT NULL DEFAULT '0',
  `expense_edit` tinyint(4) NOT NULL DEFAULT '0',
  `expense_delete` tinyint(4) NOT NULL DEFAULT '0',
  `expense_none` tinyint(4) NOT NULL DEFAULT '0',
  `owner_create` tinyint(4) NOT NULL DEFAULT '0',
  `owner_view` tinyint(4) NOT NULL DEFAULT '0',
  `owner_edit` tinyint(4) NOT NULL DEFAULT '0',
  `owner_delete` tinyint(4) NOT NULL DEFAULT '0',
  `owner_none` tinyint(4) NOT NULL DEFAULT '0',
  `amount_none` tinyint(4) NOT NULL DEFAULT '0',
  `amount_view` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-04-26 18:18:04
