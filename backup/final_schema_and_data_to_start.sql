-- MySQL dump 10.13  Distrib 8.0.28, for Win64 (x86_64)
--
-- Host: bt-findg-db.mysql.database.azure.com    Database: contolioprod
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
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verify_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admins_email_unique` (`email`),
  UNIQUE KEY `admins_phone_unique` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'Admin','Admin','mohammed@contolio.com','','9845686545',NULL,'$2y$10$12IeKhv1UANIZeU1ngPu3.YyH6/f70ysSGE2gouHKt76EtdT0oyES','1842','2021-12-30 13:27:14','2022-04-04 02:24:27');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `available_unit_image`
--

DROP TABLE IF EXISTS `available_unit_image`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `available_unit_image` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `unit_id` bigint(20) unsigned NOT NULL,
  `image_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `available_unit_image_unit_id_foreign` (`unit_id`),
  CONSTRAINT `available_unit_image_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `avaliable_units` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `available_unit_image`
--

LOCK TABLES `available_unit_image` WRITE;
/*!40000 ALTER TABLE `available_unit_image` DISABLE KEYS */;
/*!40000 ALTER TABLE `available_unit_image` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `avaliable_units`
--

DROP TABLE IF EXISTS `avaliable_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `avaliable_units` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pm_company_id` bigint(20) NOT NULL,
  `unit_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `unit_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rooms` int(4) NOT NULL,
  `address` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bathrooms` int(4) NOT NULL,
  `area_sqm` int(11) NOT NULL,
  `monthly_rent` int(11) NOT NULL,
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `building_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `avaliable_units_building_id_foreign` (`building_id`),
  CONSTRAINT `avaliable_units_building_id_foreign` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `avaliable_units`
--

LOCK TABLES `avaliable_units` WRITE;
/*!40000 ALTER TABLE `avaliable_units` DISABLE KEYS */;
/*!40000 ALTER TABLE `avaliable_units` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `buildings`
--

DROP TABLE IF EXISTS `buildings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `buildings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pm_company_id` bigint(20) NOT NULL,
  `property_manager_id` bigint(20) NOT NULL,
  `building_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `building_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `units` int(11) NOT NULL DEFAULT '0',
  `location_link` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `buildings`
--

LOCK TABLES `buildings` WRITE;
/*!40000 ALTER TABLE `buildings` DISABLE KEYS */;
/*!40000 ALTER TABLE `buildings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cms_pages`
--

DROP TABLE IF EXISTS `cms_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cms_pages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_for` tinyint(4) NOT NULL,
  `page_language` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cms_pages`
--

LOCK TABLES `cms_pages` WRITE;
/*!40000 ALTER TABLE `cms_pages` DISABLE KEYS */;
INSERT INTO `cms_pages` VALUES (1,'Privacy Policy',1,'en','','2022-01-17 09:00:42','2022-01-17 09:00:42'),(2,'Privacy Policy',1,'ar','','2022-01-17 09:00:42','2022-01-17 09:00:42'),(3,'Privacy Policy',0,'en','','2022-01-17 09:00:42','2022-03-03 09:20:30'),(4,'Privacy Policy',0,'ar','','2022-01-17 09:00:42','2022-03-09 04:08:20'),(5,'Term & Conditions',1,'en','','2022-01-17 09:00:42','2022-03-02 04:37:15'),(6,'Term & Conditions',1,'ar','','2022-01-17 09:00:42','2022-01-17 09:00:42'),(7,'Term & Conditions',0,'en','','2022-01-17 09:00:42','2022-01-17 09:00:42'),(8,'Term & Conditions',0,'ar','','2022-01-17 09:00:42','2022-03-07 03:02:04');
/*!40000 ALTER TABLE `cms_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comment_media`
--

DROP TABLE IF EXISTS `comment_media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comment_media` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `maintenance_request_id` bigint(20) unsigned NOT NULL,
  `media_type` tinyint(3) unsigned NOT NULL,
  `media_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `thumbnail_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `upload_by` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `comment_media_maintenance_request_id_foreign` (`maintenance_request_id`),
  CONSTRAINT `comment_media_maintenance_request_id_foreign` FOREIGN KEY (`maintenance_request_id`) REFERENCES `maintance_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comment_media`
--

LOCK TABLES `comment_media` WRITE;
/*!40000 ALTER TABLE `comment_media` DISABLE KEYS */;
/*!40000 ALTER TABLE `comment_media` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_requests`
--

DROP TABLE IF EXISTS `contact_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `property_manager_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_requests`
--

LOCK TABLES `contact_requests` WRITE;
/*!40000 ALTER TABLE `contact_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contracts_files_tables`
--

DROP TABLE IF EXISTS `contracts_files_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contracts_files_tables` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `contract_id` bigint(20) unsigned NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contracts_files_tables`
--

LOCK TABLES `contracts_files_tables` WRITE;
/*!40000 ALTER TABLE `contracts_files_tables` DISABLE KEYS */;
/*!40000 ALTER TABLE `contracts_files_tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contracts_tables`
--

DROP TABLE IF EXISTS `contracts_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contracts_tables` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pm_company_id` bigint(20) unsigned NOT NULL,
  `Tenant_id` bigint(20) unsigned NOT NULL,
  `building_id` bigint(20) unsigned NOT NULL,
  `unit_id` bigint(20) unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contracts_tables`
--

LOCK TABLES `contracts_tables` WRITE;
/*!40000 ALTER TABLE `contracts_tables` DISABLE KEYS */;
/*!40000 ALTER TABLE `contracts_tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `countries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `country` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `country_code` smallint(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `countries`
--

LOCK TABLES `countries` WRITE;
/*!40000 ALTER TABLE `countries` DISABLE KEYS */;
INSERT INTO `countries` VALUES (1,'QATAR','2022-01-17 09:00:42','2022-01-17 09:00:42',974),(2,'USA','2022-01-17 09:00:42','2022-01-17 09:00:42',1);
/*!40000 ALTER TABLE `countries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `currencies`
--

DROP TABLE IF EXISTS `currencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `currencies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `currency` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `symbol` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `currencies`
--

LOCK TABLES `currencies` WRITE;
/*!40000 ALTER TABLE `currencies` DISABLE KEYS */;
INSERT INTO `currencies` VALUES (1,'QAR','QAR ','2022-01-17 09:00:42','2022-01-17 09:00:42'),(2,'USD','$','2022-01-17 09:00:42','2022-01-17 09:00:42');
/*!40000 ALTER TABLE `currencies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expense_files`
--

DROP TABLE IF EXISTS `expense_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expense_files` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expense_item_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `expense_files_expense_item_id_foreign` (`expense_item_id`),
  CONSTRAINT `expense_files_expense_item_id_foreign` FOREIGN KEY (`expense_item_id`) REFERENCES `expenses_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expense_files`
--

LOCK TABLES `expense_files` WRITE;
/*!40000 ALTER TABLE `expense_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `expense_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expenses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pm_company_id` bigint(20) unsigned NOT NULL,
  `request_id` bigint(20) unsigned NOT NULL,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `building_id` bigint(20) unsigned NOT NULL,
  `unit_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenses_items`
--

DROP TABLE IF EXISTS `expenses_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expenses_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `currency_id` bigint(20) unsigned NOT NULL,
  `cost` int(11) NOT NULL DEFAULT '0',
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expenses_lines_id` bigint(1) unsigned NOT NULL DEFAULT '0',
  `expenses_id` bigint(20) unsigned NOT NULL,
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `expenses_items_expenses_id_foreign` (`expenses_id`),
  CONSTRAINT `expenses_items_expenses_id_foreign` FOREIGN KEY (`expenses_id`) REFERENCES `expenses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses_items`
--

LOCK TABLES `expenses_items` WRITE;
/*!40000 ALTER TABLE `expenses_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `expenses_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenseslines`
--

DROP TABLE IF EXISTS `expenseslines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expenseslines` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `expenseslines_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `arabic_expenseslines_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenseslines`
--

LOCK TABLES `expenseslines` WRITE;
/*!40000 ALTER TABLE `expenseslines` DISABLE KEYS */;
INSERT INTO `expenseslines` VALUES (1,'AC','2022-02-14 21:00:00','2022-02-14 21:00:00','مكيف الهواء'),(2,'Walls and Paint','2022-02-14 21:00:00','2022-02-14 21:00:00','الجدران والطلاء'),(3,'Doors Equipments','2022-02-14 21:00:00','2022-02-14 21:00:00','معدات الأبواب'),(4,'Windows Equipments','2022-02-14 21:00:00','2022-02-14 21:00:00','معدات النوافذ'),(5,'Toilet Related','2022-02-14 21:00:00','2022-02-14 21:00:00','الأعمال ذات الصلة بالمرحاض'),(6,'Plugs and switches','2022-02-14 21:00:00','2022-02-14 21:00:00','المقابس والمفاتيح'),(7,'fire extinguishers and Alarms','2022-02-14 21:00:00','2022-02-14 21:00:00','طفايات الحريق وأجهزة الإنذار'),(8,'Key Replacement','2022-02-14 21:00:00','2022-02-14 21:00:00','استبدال المفتاح'),(9,'Floors and Tiles','2022-02-14 21:00:00','2022-02-14 21:00:00','الأرضيات والبلاط'),(10,'Oven related','2022-02-14 21:00:00','2022-02-14 21:00:00','الفرن'),(11,'Refrigerators related','2022-02-14 21:00:00','2022-02-14 21:00:00','الثلاجات'),(12,'Cabinets','2022-02-14 21:00:00','2022-02-14 21:00:00','خزائن'),(13,'Labor Work','2022-02-14 21:00:00','2022-02-14 21:00:00','العمل العمالي');
/*!40000 ALTER TABLE `expenseslines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `experts`
--

DROP TABLE IF EXISTS `experts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `experts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pm_company_id` bigint(20) unsigned NOT NULL,
  `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_code` int(11) NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remark` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `experts`
--

LOCK TABLES `experts` WRITE;
/*!40000 ALTER TABLE `experts` DISABLE KEYS */;
/*!40000 ALTER TABLE `experts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maintance_requests`
--

DROP TABLE IF EXISTS `maintance_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `maintance_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pm_company_id` bigint(20) unsigned NOT NULL,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `building_id` bigint(20) unsigned NOT NULL,
  `unit_id` bigint(20) unsigned NOT NULL,
  `status` tinyint(4) NOT NULL,
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `preferred_date_time` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `request_code` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `maintenance_request_id` bigint(20) NOT NULL,
  `property_manager_id` bigint(1) unsigned NOT NULL DEFAULT '0',
  `tenant_unread_count` int(11) NOT NULL DEFAULT '0',
  `tenant_unread_date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maintance_requests`
--

LOCK TABLES `maintance_requests` WRITE;
/*!40000 ALTER TABLE `maintance_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `maintance_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maintenance_experts`
--

DROP TABLE IF EXISTS `maintenance_experts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `maintenance_experts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `maintenance_id` bigint(20) unsigned NOT NULL,
  `expert_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `unique_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `visit_date_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maintenance_experts`
--

LOCK TABLES `maintenance_experts` WRITE;
/*!40000 ALTER TABLE `maintenance_experts` DISABLE KEYS */;
/*!40000 ALTER TABLE `maintenance_experts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maintenance_files`
--

DROP TABLE IF EXISTS `maintenance_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `maintenance_files` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `maintenance_request_id` bigint(20) unsigned NOT NULL,
  `file_type` tinyint(3) unsigned NOT NULL,
  `file_name` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `thumbnail_name` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `upload_by` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `maintenance_files_maintenance_request_id_foreign` (`maintenance_request_id`),
  CONSTRAINT `maintenance_files_maintenance_request_id_foreign` FOREIGN KEY (`maintenance_request_id`) REFERENCES `maintance_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maintenance_files`
--

LOCK TABLES `maintenance_files` WRITE;
/*!40000 ALTER TABLE `maintenance_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `maintenance_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maitinance_request_for`
--

DROP TABLE IF EXISTS `maitinance_request_for`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `maitinance_request_for` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `maitinance_request_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `arabic_maintenance_request_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maitinance_request_for`
--

LOCK TABLES `maitinance_request_for` WRITE;
/*!40000 ALTER TABLE `maitinance_request_for` DISABLE KEYS */;
INSERT INTO `maitinance_request_for` VALUES (1,'AC','2022-02-14 21:00:00','2022-02-14 21:00:00','مكيف الهواء'),(2,'Walls and Paint','2022-02-14 21:00:00','2022-02-14 21:00:00','الجدران والطلاء'),(3,'Doors Equipments','2022-02-14 21:00:00','2022-02-14 21:00:00','معدات الأبواب'),(4,'Windows Equipments','2022-02-14 21:00:00','2022-02-14 21:00:00','معدات النوافذ'),(5,'Toilet Related','2022-02-14 21:00:00','2022-02-14 21:00:00','الأعمال ذات الصلة بالمرحاض'),(6,'Plugs and switches','2022-02-14 21:00:00','2022-02-14 21:00:00','المقابس والمفاتيح'),(7,'fire extinguishers and Alarms','2022-02-14 21:00:00','2022-02-14 21:00:00','طفايات الحريق وأجهزة الإنذار'),(8,'Key Replacement','2022-02-14 21:00:00','2022-02-14 21:00:00','استبدال المفتاح'),(9,'Floors and Tails','2022-02-14 21:00:00','2022-02-14 21:00:00','الأرضيات والبلاط'),(10,'Oven related','2022-02-14 21:00:00','2022-02-14 21:00:00','الفرن'),(11,'Refrigerators related','2022-02-14 21:00:00','2022-02-14 21:00:00','الثلاجات'),(12,'Cabinets','2022-02-14 21:00:00','2022-02-14 21:00:00','خزائن');
/*!40000 ALTER TABLE `maitinance_request_for` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=123 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_100000_create_password_resets_table',1),(2,'2019_08_19_000000_create_failed_jobs_table',1),(3,'2019_12_14_000001_create_personal_access_tokens_table',1),(4,'2021_12_17_111742_create_admins_table',1),(5,'2021_12_27_091253_create_property_manager_companies_table',1),(6,'2021_12_27_095005_create_property_managers_table',1),(7,'2021_12_28_090919_create_country_currency_table',2),(8,'2021_12_28_125404_create_tenants_table',3),(9,'2021_12_28_125731_create_buildings_table',3),(10,'2021_12_29_050435_create_tenants_units_table',3),(11,'2021_12_29_051136_create_roles_table',3),(12,'2021_12_29_064748_create_avaliable_units_table',3),(13,'2021_12_29_112743_update_create_tenants_table',4),(14,'2021_12_29_112744_alter_tenants_table',4),(15,'2021_12_31_102006_add_otp_to_tenants_table',5),(16,'2021_12_31_112157_alter_tenets_add_unique_email_key',5),(17,'2022_01_03_051508_create_privacy_terms_table',5),(18,'2022_01_03_084608_alter_tenants_add_prdoct_manager_id_table',5),(19,'2022_01_03_110609_alter_tenants_status_table',6),(20,'2022_01_04_063029_create_tenants_temp_table',7),(21,'2022_01_04_113747_alter_tenants_temp_table',7),(22,'2022_01_05_042814_alter_tenants_add_is_email_verify_is_phone_verify_table',7),(23,'2022_01_06_045515_alter_property_managers_add_address_field_latitude_field_longitude_field_table',8),(24,'2022_01_06_065604_alter_property_managers_add_address_field_latitude_field_longitude_field_for_length_table',9),(25,'2022_01_06_094105_create_property_manager_feedback_table',10),(26,'2022_01_06_100400_add_email_url_key_to_tenants_table',11),(27,'2022_01_07_045626_alter_property_manager_table_add_email_verify_url',12),(28,'2022_01_07_050552_alter_property_manager_add_column_email_otp',12),(29,'2022_01_10_042137_alter_property_manager_companies_remove_unique_email_or_phone_table',13),(30,'2022_01_10_070710_create_owners_table',14),(31,'2022_01_10_114637_create_available_unit_image_table',15),(32,'2022_01_11_072156_add_pm_company_id_tenants_table',16),(33,'2022_01_11_100455_alter_pm_id_default_0_tenants_table',16),(34,'2022_01_11_110258_alter_tenant_unit_table',17),(35,'2022_01_11_115404_crate_tenant_units_table',17),(36,'2022_01_12_103927_alter_available_units_table_add_is_available_column',18),(37,'2022_01_12_102415_create_contracts_tables',19),(38,'2022_01_12_105605_create_contracts_files_tables',19),(39,'2022_01_13_062555_create_payments__tables',20),(40,'2022_01_17_060850_remove_phone_unique_pm_user',21),(41,'2022_01_17_101958_create_countries_table',22),(42,'2022_01_17_102111_create_currencies_table',22),(43,'2022_01_17_105510_drop_country_courency_table',22),(44,'2022_01_17_112757_alter_currency_table_add_symbol_column',22),(45,'2022_01_17_125805_alter_table_add_available_units',23),(46,'2022_01_18_043115_create_contact_requests_table',23),(47,'2022_01_18_051623_alter_available_unit_table',23),(48,'2022_01_18_073153_alter_owners_table',23),(49,'2022_01_18_121634_alter_remove_owner_id_available_unit_table',24),(50,'2022_01_18_121635_remove_country_id_prop_manager_table',25),(51,'2022_01_19_121636_remove_is_available_in_avaliable_units_table',26),(52,'2022_01_19_121637_alter_tenants_units_table_final',27),(53,'2022_01_19_121638_alter_two_tables',28),(54,'2022_01_20_101907_create_cms_pages_table',29),(55,'2022_01_20_112435_drop_tenant_temp_table',30),(56,'2022_01_21_095750_alter_cms_table',31),(57,'2022_01_24_064727_alter_contracts_table',32),(58,'2022_01_24_090016_alter_contracts_add_status_table',33),(59,'2022_01_25_110345_drop_privacy_terms_table',34),(60,'2022_01_27_045737_drop_payment_table',35),(61,'2022_02_02_061015_add_country_code_in_country_table',36),(62,'2022_02_03_114222_add_tenant_code_in_tenant_table',37),(63,'2022_02_09_130246_create_maintance_requests_table',38),(64,'2022_02_09_131235_create_maintance_images_table',38),(65,'2022_02_11_051914_create_experts_table',39),(66,'2022_02_11_052736_create_specialities_table',39),(67,'2022_02_14_091012_create_specialisties_expert_id_table',40),(68,'2022_02_15_044202_create_expenseslines_table',41),(69,'2022_02_15_060933_create_expenses_table',42),(70,'2022_02_15_071215_create_maitinance_request_for_table',42),(71,'2022_02_16_053123_drop_maintance_images_table',43),(72,'2022_02_16_053302_create_maintenance_files_table',43),(73,'2022_02_16_062609_alter_experts_table',43),(74,'2022_02_16_062925_create_maintenance_experts_table',43),(75,'2022_02_16_064442_alter_maintenance_requests_table',43),(76,'2022_02_16_074106_alter_maintenance_requests_add_request_code_table',44),(77,'2022_02_16_101702_alter_maintenance_requests_add_pm_id_table',45),(78,'2022_02_17_065729_create_expenses_items_table',46),(79,'2022_02_17_070155_alter_expneses_table',46),(80,'2022_02_17_071345_alter_expense_item_add_files_name_table',46),(81,'2022_02_17_092219_alter_expense_item_delete_files_name_table',47),(82,'2022_02_17_092517_create_expense_files_table',47),(83,'2022_02_18_062607_alter_maintace_request_cascade_table',48),(84,'2022_02_21_104548_drop_maintenance_files_table',49),(85,'2022_02_21_105913_create_maintenance_files_new_table',49),(86,'2022_02_21_124115_alter_maintenance_files_drop_uploded_table',49),(87,'2022_02_22_064916_create_comment_media_table',50),(88,'2022_02_22_070952_alter_comment_media_drop_uploded_table',50),(89,'2022_02_25_045215_alter_maitenanace_expert_add_new_coloum_table',51),(90,'2022_03_08_060754_create_tenant_notifications_table',52),(91,'2022_03_08_061054_create_pm_notifications_table',52),(92,'2022_03_08_123952_alter_roles_table_final',53),(93,'2022_03_14_065951_alter_tenant_notofication_table',54),(94,'2022_03_14_072757_alter_tenant_notofications_table',55),(95,'2022_03_14_073238_alter_pm_notofication_table',56),(96,'2022_03_14_113830_property_manager_logs',57),(97,'2022_03_14_133556_alter_pm_noti_table',58),(98,'2022_03_14_133616_alter_tenant_noti_table',58),(99,'2022_03_16_045055_alter_property_manager_logs_add_new_coloum_table',59),(100,'2022_03_16_074452_alter_property_managers_logs_add_new_coloum_table',60),(101,'2022_03_24_060630_alter_nstore_procedure__total_closed_requests12m',61),(102,'2022_03_25_103837_alter_maintanace_files_table_add_length',62),(103,'2022_03_31_115937_alter_maintanace_request_for_table_add_new_colum',63),(104,'2022_03_31_120841_alter_expenseslines_table_add_new_colum',64),(105,'2022_03_31_125734_alter_specialities_table_add_new_colum',65),(106,'2022_04_01_091014_alter_experts_table_add_new_colum',66),(107,'2022_04_05_063125_alter_avail_tenant_unit',67),(108,'2022_04_05_105509_alter_expense_item_add_desc',68),(109,'2022_04_06_071520_alter_maintenance_experts_add_datetime',69),(110,'2022_04_07_041407_create_tenant_temp_table',70),(111,'2022_04_07_105220_create_app_version_check_table',71),(112,'2022_04_08_065138_alter_app_version_check_table',72),(113,'2022_04_08_065801_alter_apps_version_check_table',73),(114,'2022_04_08_070445_alter_app_version_s_check_table',73),(115,'2022_04_08_090243_alter_mobiles_apps_versions_table',74),(116,'2022_04_12_065647_alter_maintenance_request_table',75),(117,'2022_04_12_113930_alter_expert_table',76),(118,'2022_04_13_071648_alter_tenant_unit_add_checkbox_table',77),(119,'2022_04_14_052154_alter_roles_table',78),(120,'2022_04_26_053105_add_count_main_req_table',79),(121,'2022_04_28_051307_alter_maintenance_request_table_add_tenant_unread_date_table',80),(122,'2022_04_28_062140_alter_maintenance_request_table_add_tenant_unread_date_only_table',80);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mobile_app_versions`
--

DROP TABLE IF EXISTS `mobile_app_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mobile_app_versions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `android` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ios` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mobile_app_versions`
--

LOCK TABLES `mobile_app_versions` WRITE;
/*!40000 ALTER TABLE `mobile_app_versions` DISABLE KEYS */;
INSERT INTO `mobile_app_versions` VALUES (1,'1.0','1.0','2022-03-31 10:37:44','2022-03-31 10:37:44');
/*!40000 ALTER TABLE `mobile_app_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `owners`
--

DROP TABLE IF EXISTS `owners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `owners` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(4) NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_code` int(11) NOT NULL DEFAULT '0',
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pm_company_id` bigint(20) unsigned NOT NULL,
  `property_manager_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `owners`
--

LOCK TABLES `owners` WRITE;
/*!40000 ALTER TABLE `owners` DISABLE KEYS */;
/*!40000 ALTER TABLE `owners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pm_company_id` bigint(20) unsigned NOT NULL,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `building_id` bigint(20) unsigned NOT NULL,
  `unit_id` bigint(20) unsigned NOT NULL,
  `payment_type` tinyint(4) NOT NULL,
  `cheque_no` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `payment_date` date DEFAULT NULL,
  `amount` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL,
  `remark` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=765 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (764,'App\\Models\\AdminModel',1,'web','cba2a81f69731ff2f02a88d53330f6d562ce2195355d669ab8049c19bde92124','[\"admin\"]','2022-04-28 13:09:20','2022-04-28 11:46:42','2022-04-28 13:09:20');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pm_notifications`
--

DROP TABLE IF EXISTS `pm_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pm_notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `property_manager_id` bigint(20) unsigned NOT NULL,
  `message_language` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `seen_by` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pm_notifications`
--

LOCK TABLES `pm_notifications` WRITE;
/*!40000 ALTER TABLE `pm_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `pm_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `property_manager_companies`
--

DROP TABLE IF EXISTS `property_manager_companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `property_manager_companies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_code` int(11) NOT NULL DEFAULT '0',
  `office_contact_no` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_id` int(10) unsigned NOT NULL,
  `currency_id` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `property_manager_companies`
--

LOCK TABLES `property_manager_companies` WRITE;
/*!40000 ALTER TABLE `property_manager_companies` DISABLE KEYS */;
/*!40000 ALTER TABLE `property_manager_companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `property_manager_feedback`
--

DROP TABLE IF EXISTS `property_manager_feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `property_manager_feedback` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `pm_id` bigint(20) unsigned NOT NULL,
  `feedback_message` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `property_manager_feedback_pm_id_foreign` (`pm_id`),
  CONSTRAINT `property_manager_feedback_pm_id_foreign` FOREIGN KEY (`pm_id`) REFERENCES `property_managers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `property_manager_feedback`
--

LOCK TABLES `property_manager_feedback` WRITE;
/*!40000 ALTER TABLE `property_manager_feedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `property_manager_feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `property_manager_logs`
--

DROP TABLE IF EXISTS `property_manager_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `property_manager_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `module` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `affected_record_id` bigint(20) unsigned NOT NULL,
  `record_name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `pm_company_id` bigint(20) unsigned NOT NULL,
  `property_manager_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `property_manager_logs`
--

LOCK TABLES `property_manager_logs` WRITE;
/*!40000 ALTER TABLE `property_manager_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `property_manager_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `property_managers`
--

DROP TABLE IF EXISTS `property_managers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `property_managers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pm_company_id` bigint(20) unsigned NOT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_code` int(11) NOT NULL DEFAULT '0',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verify_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_otp` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `email_verify_url` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `office_contact_no` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` tinyint(3) unsigned NOT NULL,
  `status` tinyint(4) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `address` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `latitude` decimal(10,8) unsigned NOT NULL,
  `longitude` decimal(11,8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `property_managers_email_unique` (`email`),
  KEY `property_managers_pm_company_id_foreign` (`pm_company_id`),
  CONSTRAINT `property_managers_pm_company_id_foreign` FOREIGN KEY (`pm_company_id`) REFERENCES `property_manager_companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `property_managers`
--

LOCK TABLES `property_managers` WRITE;
/*!40000 ALTER TABLE `property_managers` DISABLE KEYS */;
/*!40000 ALTER TABLE `property_managers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_title` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Admin',1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,'2022-03-30 09:21:56','2022-03-31 03:48:50',0,0,0,0,0,1,1,1,1,0,1,1,1,1,0,1,1,1,1,0,1,1,1,1,0,1,1,1,1,0,0,1);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `specialisties_expert_id`
--

DROP TABLE IF EXISTS `specialisties_expert_id`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `specialisties_expert_id` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `expert_id` bigint(20) unsigned NOT NULL,
  `speciality_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `specialisties_expert_id`
--

LOCK TABLES `specialisties_expert_id` WRITE;
/*!40000 ALTER TABLE `specialisties_expert_id` DISABLE KEYS */;
/*!40000 ALTER TABLE `specialisties_expert_id` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `specialities`
--

DROP TABLE IF EXISTS `specialities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `specialities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `arabic_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `specialities`
--

LOCK TABLES `specialities` WRITE;
/*!40000 ALTER TABLE `specialities` DISABLE KEYS */;
INSERT INTO `specialities` VALUES (1,'Carpenter','2022-02-10 21:00:00','2022-02-10 21:00:00','نجار'),(2,'Plumber','2022-02-10 21:00:00','2022-02-10 21:00:00','سباك'),(3,'AC','2022-02-10 21:00:00','2022-02-10 21:00:00','أخصائي تكييف'),(4,'Electrician','2022-02-10 21:00:00','2022-02-10 21:00:00','كهربائي');
/*!40000 ALTER TABLE `specialities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenant_notifications`
--

DROP TABLE IF EXISTS `tenant_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenant_notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `tenant_id` bigint(20) unsigned NOT NULL,
  `pm_company_id` bigint(1) unsigned NOT NULL DEFAULT '0',
  `property_manager_id` bigint(1) unsigned NOT NULL DEFAULT '0',
  `message_language` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `seen_by` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_notifications`
--

LOCK TABLES `tenant_notifications` WRITE;
/*!40000 ALTER TABLE `tenant_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `tenant_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenant_temp`
--

DROP TABLE IF EXISTS `tenant_temp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenant_temp` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `otp` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenant_temp_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_temp`
--

LOCK TABLES `tenant_temp` WRITE;
/*!40000 ALTER TABLE `tenant_temp` DISABLE KEYS */;
/*!40000 ALTER TABLE `tenant_temp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenants`
--

DROP TABLE IF EXISTS `tenants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `property_manager_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `building_id` bigint(20) unsigned NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `unique_email_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `email_key_expire` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `unit_id` bigint(20) unsigned NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_code` int(11) NOT NULL DEFAULT '0',
  `country_id` int(10) unsigned NOT NULL,
  `language` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `os_type` tinyint(4) NOT NULL,
  `os_version` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `app_version` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `otp` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `is_email_verify` tinyint(4) NOT NULL DEFAULT '0',
  `is_phone_verify` tinyint(4) NOT NULL DEFAULT '0',
  `email_url_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pm_company_id` bigint(20) unsigned NOT NULL,
  `tenant_code` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenants_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenants`
--

LOCK TABLES `tenants` WRITE;
/*!40000 ALTER TABLE `tenants` DISABLE KEYS */;
/*!40000 ALTER TABLE `tenants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenants_units`
--

DROP TABLE IF EXISTS `tenants_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenants_units` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(4) NOT NULL,
  `maintenance_included` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `pm_company_id` bigint(20) NOT NULL,
  `building_id` bigint(20) unsigned NOT NULL,
  `owner_id` bigint(20) NOT NULL,
  `unit_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `unit_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rooms` int(4) NOT NULL,
  `address` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bathrooms` int(4) NOT NULL,
  `area_sqm` int(11) NOT NULL,
  `monthly_rent` int(11) NOT NULL,
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tenants_units_building_id_foreign` (`building_id`),
  CONSTRAINT `tenants_units_building_id_foreign` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenants_units`
--

LOCK TABLES `tenants_units` WRITE;
/*!40000 ALTER TABLE `tenants_units` DISABLE KEYS */;
/*!40000 ALTER TABLE `tenants_units` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-04-28 14:58:34
