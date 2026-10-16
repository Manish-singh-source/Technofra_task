-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: laravel
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `laravel`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `laravel` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `laravel`;

--
-- Table structure for table `amc_service_details`
--

DROP TABLE IF EXISTS `amc_service_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amc_service_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `amc_service_id` bigint(20) unsigned NOT NULL,
  `visit_number` int(10) unsigned NOT NULL,
  `visit_date` date NOT NULL,
  `status` enum('pending','completed') NOT NULL DEFAULT 'pending',
  `details` text DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `before_visit_reminder_sent_at` timestamp NULL DEFAULT NULL,
  `same_day_reminder_sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `amc_service_details_amc_service_id_foreign` (`amc_service_id`),
  CONSTRAINT `amc_service_details_amc_service_id_foreign` FOREIGN KEY (`amc_service_id`) REFERENCES `amc_services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amc_service_details`
--

LOCK TABLES `amc_service_details` WRITE;
/*!40000 ALTER TABLE `amc_service_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `amc_service_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `amc_services`
--

DROP TABLE IF EXISTS `amc_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amc_services` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` bigint(20) unsigned NOT NULL,
  `total_visits` int(10) unsigned NOT NULL,
  `amc_start_date` date NOT NULL,
  `amc_end_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `amc_services_service_id_unique` (`service_id`),
  CONSTRAINT `amc_services_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amc_services`
--

LOCK TABLES `amc_services` WRITE;
/*!40000 ALTER TABLE `amc_services` DISABLE KEYS */;
/*!40000 ALTER TABLE `amc_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assigned_leads`
--

DROP TABLE IF EXISTS `assigned_leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assigned_leads` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lead_model` varchar(50) NOT NULL,
  `lead_id` bigint(20) unsigned NOT NULL,
  `staff_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`staff_ids`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `assigned_leads_lead_model_lead_id_unique` (`lead_model`,`lead_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assigned_leads`
--

LOCK TABLES `assigned_leads` WRITE;
/*!40000 ALTER TABLE `assigned_leads` DISABLE KEYS */;
/*!40000 ALTER TABLE `assigned_leads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookcall`
--

DROP TABLE IF EXISTS `bookcall`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookcall` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `meeting_agenda` text DEFAULT NULL,
  `meet_link` text DEFAULT NULL,
  `google_event_id` varchar(255) DEFAULT NULL,
  `meet_duration_minutes` int(10) unsigned DEFAULT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `booking_datetime` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookcall`
--

LOCK TABLES `bookcall` WRITE;
/*!40000 ALTER TABLE `bookcall` DISABLE KEYS */;
/*!40000 ALTER TABLE `bookcall` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendar_events`
--

DROP TABLE IF EXISTS `calendar_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `event_time` time DEFAULT NULL,
  `email_recipients` varchar(255) DEFAULT NULL,
  `whatsapp_recipients` varchar(255) DEFAULT NULL,
  `notification_channels` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`notification_channels`)),
  `reminder_delivery_log` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`reminder_delivery_log`)),
  `notification_sent` tinyint(1) NOT NULL DEFAULT 0,
  `reminder_10min_sent` tinyint(1) NOT NULL DEFAULT 0,
  `reminder_10min_sent_at` datetime DEFAULT NULL,
  `event_time_notification_sent` tinyint(1) NOT NULL DEFAULT 0,
  `event_time_notification_sent_at` datetime DEFAULT NULL,
  `notification_sent_at` datetime DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `calendar_events_event_date_index` (`event_date`),
  KEY `calendar_events_notification_sent_index` (`notification_sent`),
  KEY `calendar_events_created_by_index` (`created_by`),
  CONSTRAINT `calendar_events_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar_events`
--

LOCK TABLES `calendar_events` WRITE;
/*!40000 ALTER TABLE `calendar_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `calendar_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `client_business_details`
--

DROP TABLE IF EXISTS `client_business_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_business_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `client_type` varchar(255) NOT NULL,
  `industry` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_business_details_user_id_foreign` (`user_id`),
  CONSTRAINT `client_business_details_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_business_details`
--

LOCK TABLES `client_business_details` WRITE;
/*!40000 ALTER TABLE `client_business_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `client_business_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `client_issue_tasks`
--

DROP TABLE IF EXISTS `client_issue_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_issue_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `client_issue_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('todo','in_progress','review','done') NOT NULL DEFAULT 'todo',
  `priority` varchar(255) NOT NULL DEFAULT 'medium',
  `assigned_to` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `due_time` time DEFAULT NULL,
  `reminder_date` date DEFAULT NULL,
  `reminder_time` time DEFAULT NULL,
  `checklist_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`checklist_data`)),
  `labels_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`labels_data`)),
  `attachment` varchar(255) DEFAULT NULL,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attachments`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_issue_tasks_client_issue_id_foreign` (`client_issue_id`),
  CONSTRAINT `client_issue_tasks_client_issue_id_foreign` FOREIGN KEY (`client_issue_id`) REFERENCES `client_issues` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_issue_tasks`
--

LOCK TABLES `client_issue_tasks` WRITE;
/*!40000 ALTER TABLE `client_issue_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `client_issue_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `client_issue_team_assignments`
--

DROP TABLE IF EXISTS `client_issue_team_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_issue_team_assignments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `client_issue_id` bigint(20) unsigned NOT NULL,
  `team_name` varchar(255) NOT NULL,
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `note` text DEFAULT NULL,
  `assigned_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_issue_team_assignments_client_issue_id_foreign` (`client_issue_id`),
  KEY `client_issue_team_assignments_assigned_by_foreign` (`assigned_by`),
  KEY `client_issue_team_assignments_assigned_to_foreign` (`assigned_to`),
  CONSTRAINT `client_issue_team_assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `client_issue_team_assignments_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `client_issue_team_assignments_client_issue_id_foreign` FOREIGN KEY (`client_issue_id`) REFERENCES `client_issues` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_issue_team_assignments`
--

LOCK TABLES `client_issue_team_assignments` WRITE;
/*!40000 ALTER TABLE `client_issue_team_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `client_issue_team_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `client_issues`
--

DROP TABLE IF EXISTS `client_issues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_issues` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `issue_description` text NOT NULL,
  `priority` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `status` enum('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_issues_project_id_foreign` (`project_id`),
  KEY `client_issues_customer_id_foreign` (`customer_id`),
  CONSTRAINT `client_issues_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `client_issues_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_issues`
--

LOCK TABLES `client_issues` WRITE;
/*!40000 ALTER TABLE `client_issues` DISABLE KEYS */;
/*!40000 ALTER TABLE `client_issues` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clients` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cname` varchar(255) NOT NULL,
  `coname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contactform`
--

DROP TABLE IF EXISTS `contactform`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contactform` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fname` varchar(150) NOT NULL,
  `lname` varchar(150) NOT NULL,
  `contact` varchar(25) NOT NULL,
  `email` varchar(150) NOT NULL,
  `massage` text NOT NULL,
  `source_page` varchar(120) NOT NULL DEFAULT 'contact.php',
  `status` varchar(50) NOT NULL DEFAULT 'new',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contactform`
--

LOCK TABLES `contactform` WRITE;
/*!40000 ALTER TABLE `contactform` DISABLE KEYS */;
/*!40000 ALTER TABLE `contactform` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `client_name` varchar(255) NOT NULL,
  `contact_person` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `postal_code` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `client_type` enum('Individual','Company','Organization') NOT NULL,
  `industry` varchar(255) NOT NULL,
  `status` enum('Active','Inactive','Suspended') NOT NULL,
  `priority_level` enum('Low','Medium','High') DEFAULT NULL,
  `assigned_manager_id` bigint(20) unsigned DEFAULT NULL,
  `default_due_days` int(11) DEFAULT NULL,
  `billing_type` enum('Hourly','Fixed','Retainer') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_email_unique` (`email`),
  KEY `customers_user_id_foreign` (`user_id`),
  CONSTRAINT `customers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `departments_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `digital_marketing_leads`
--

DROP TABLE IF EXISTS `digital_marketing_leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `digital_marketing_leads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `company` varchar(150) NOT NULL DEFAULT '',
  `website` varchar(255) NOT NULL DEFAULT '',
  `source_page` varchar(120) NOT NULL DEFAULT 'digitalmarketingad.php',
  `status` enum('new','attempted_contact','contacted','qualified','demo_scheduled','proposal_sent','negotiation','won','lost','junk','converted') NOT NULL DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `digital_marketing_leads`
--

LOCK TABLES `digital_marketing_leads` WRITE;
/*!40000 ALTER TABLE `digital_marketing_leads` DISABLE KEYS */;
/*!40000 ALTER TABLE `digital_marketing_leads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enquiry_forms`
--

DROP TABLE IF EXISTS `enquiry_forms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enquiry_forms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `company` varchar(180) NOT NULL,
  `contact` varchar(25) NOT NULL,
  `designation` varchar(180) NOT NULL,
  `delivery_time` varchar(120) NOT NULL,
  `nature_of_project` varchar(120) NOT NULL,
  `message` text NOT NULL,
  `source_page` varchar(120) NOT NULL DEFAULT 'enquirynow.php',
  `status` varchar(50) NOT NULL DEFAULT 'new',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enquiry_forms`
--

LOCK TABLES `enquiry_forms` WRITE;
/*!40000 ALTER TABLE `enquiry_forms` DISABLE KEYS */;
/*!40000 ALTER TABLE `enquiry_forms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
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
-- Table structure for table `fcm_tokens`
--

DROP TABLE IF EXISTS `fcm_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcm_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `token` varchar(191) NOT NULL,
  `device_id` varchar(255) DEFAULT NULL,
  `platform` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fcm_tokens_user_id_token_unique` (`user_id`,`token`),
  KEY `fcm_tokens_user_id_is_active_index` (`user_id`,`is_active`),
  CONSTRAINT `fcm_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fcm_tokens`
--

LOCK TABLES `fcm_tokens` WRITE;
/*!40000 ALTER TABLE `fcm_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `fcm_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `google_leads`
--

DROP TABLE IF EXISTS `google_leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `google_leads` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lead_id` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `form_id` bigint(20) DEFAULT NULL,
  `campaign_id` bigint(20) DEFAULT NULL,
  `gcl_id` varchar(255) DEFAULT NULL,
  `is_test` tinyint(1) NOT NULL DEFAULT 0,
  `lead_stage` varchar(255) DEFAULT NULL,
  `status` enum('new','attempted_contact','contacted','qualified','demo_scheduled','proposal_sent','negotiation','won','lost','junk','converted') NOT NULL DEFAULT 'new',
  `lead_submit_time` timestamp NULL DEFAULT NULL,
  `raw_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`raw_payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `google_leads_lead_id_unique` (`lead_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `google_leads`
--

LOCK TABLES `google_leads` WRITE;
/*!40000 ALTER TABLE `google_leads` DISABLE KEYS */;
/*!40000 ALTER TABLE `google_leads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobapplication`
--

DROP TABLE IF EXISTS `jobapplication`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobapplication` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fname` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `contact` varchar(25) NOT NULL,
  `role` varchar(150) NOT NULL,
  `applicant_type` varchar(30) NOT NULL DEFAULT '',
  `experience` varchar(100) NOT NULL DEFAULT 'N/A',
  `ctc` varchar(100) NOT NULL DEFAULT 'N/A',
  `ectc` varchar(100) NOT NULL DEFAULT 'N/A',
  `location` varchar(150) NOT NULL,
  `skills_text` text NOT NULL,
  `skills_json` longtext DEFAULT NULL,
  `ai_tools_text` text NOT NULL,
  `ai_tools_json` longtext DEFAULT NULL,
  `notice` varchar(120) NOT NULL,
  `rn` varchar(150) NOT NULL DEFAULT '',
  `refrence` varchar(150) NOT NULL,
  `resume_file` varchar(255) NOT NULL,
  `portfolio_link` varchar(255) NOT NULL DEFAULT '',
  `source_page` varchar(120) NOT NULL DEFAULT 'job-application.php',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobapplication`
--

LOCK TABLES `jobapplication` WRITE;
/*!40000 ALTER TABLE `jobapplication` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobapplication` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lead_activities`
--

DROP TABLE IF EXISTS `lead_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lead_activities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lead_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `activity_type` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lead_activities_lead_id_index` (`lead_id`),
  KEY `lead_activities_user_id_index` (`user_id`),
  KEY `lead_activities_activity_type_index` (`activity_type`),
  KEY `lead_activities_user_id_idx2` (`user_id`),
  KEY `lead_activities_created_at_idx2` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lead_activities`
--

LOCK TABLES `lead_activities` WRITE;
/*!40000 ALTER TABLE `lead_activities` DISABLE KEYS */;
/*!40000 ALTER TABLE `lead_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lead_assignments`
--

DROP TABLE IF EXISTS `lead_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lead_assignments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lead_id` bigint(20) unsigned NOT NULL,
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `assigned_by` bigint(20) unsigned DEFAULT NULL,
  `assignment_note` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `assigned_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lead_assignments_lead_id_index` (`lead_id`),
  KEY `lead_assignments_assigned_to_index` (`assigned_to`),
  KEY `lead_assignments_lead_id_active_index` (`lead_id`,`active`),
  KEY `lead_assignments_assigned_to_idx2` (`assigned_to`),
  KEY `lead_assignments_created_at_idx2` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lead_assignments`
--

LOCK TABLES `lead_assignments` WRITE;
/*!40000 ALTER TABLE `lead_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `lead_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lead_conversions`
--

DROP TABLE IF EXISTS `lead_conversions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lead_conversions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lead_id` bigint(20) unsigned NOT NULL,
  `client_id` bigint(20) unsigned DEFAULT NULL,
  `converted_by` bigint(20) unsigned DEFAULT NULL,
  `conversion_value` decimal(12,2) DEFAULT NULL,
  `converted_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lead_conversions_lead_id_index` (`lead_id`),
  KEY `lead_conversions_converted_by_index` (`converted_by`),
  KEY `lead_conversions_converted_at_index` (`converted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lead_conversions`
--

LOCK TABLES `lead_conversions` WRITE;
/*!40000 ALTER TABLE `lead_conversions` DISABLE KEYS */;
/*!40000 ALTER TABLE `lead_conversions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lead_escalations`
--

DROP TABLE IF EXISTS `lead_escalations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lead_escalations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lead_id` bigint(20) unsigned NOT NULL,
  `escalated_from` bigint(20) unsigned DEFAULT NULL,
  `escalated_to` bigint(20) unsigned DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `escalated_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lead_escalations_lead_id_index` (`lead_id`),
  KEY `lead_escalations_escalated_to_index` (`escalated_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lead_escalations`
--

LOCK TABLES `lead_escalations` WRITE;
/*!40000 ALTER TABLE `lead_escalations` DISABLE KEYS */;
/*!40000 ALTER TABLE `lead_escalations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lead_followups`
--

DROP TABLE IF EXISTS `lead_followups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lead_followups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lead_id` bigint(20) unsigned NOT NULL,
  `source_type` varchar(50) DEFAULT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `staff_id` bigint(20) unsigned DEFAULT NULL,
  `followup_date` datetime NOT NULL,
  `followup_type` varchar(255) DEFAULT NULL,
  `outcome` varchar(255) DEFAULT NULL,
  `discussion_notes` longtext DEFAULT NULL,
  `next_followup_date` datetime DEFAULT NULL,
  `lead_status_after_followup` varchar(255) DEFAULT NULL,
  `reminder_sent` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lead_followups_lead_id_index` (`lead_id`),
  KEY `lead_followups_staff_id_index` (`staff_id`),
  KEY `lead_followups_followup_date_index` (`followup_date`),
  KEY `lead_followups_next_followup_date_index` (`next_followup_date`),
  KEY `lead_followups_followup_date_idx2` (`followup_date`),
  KEY `lead_followups_staff_id_idx2` (`staff_id`),
  KEY `lead_followups_created_at_idx2` (`created_at`),
  KEY `lead_followups_source_idx` (`source_type`,`source_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lead_followups`
--

LOCK TABLES `lead_followups` WRITE;
/*!40000 ALTER TABLE `lead_followups` DISABLE KEYS */;
/*!40000 ALTER TABLE `lead_followups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lead_notes`
--

DROP TABLE IF EXISTS `lead_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lead_notes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lead_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `note` longtext NOT NULL,
  `is_private` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lead_notes_lead_id_index` (`lead_id`),
  KEY `lead_notes_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lead_notes`
--

LOCK TABLES `lead_notes` WRITE;
/*!40000 ALTER TABLE `lead_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `lead_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lead_pipeline_stages`
--

DROP TABLE IF EXISTS `lead_pipeline_stages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lead_pipeline_stages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `color` varchar(255) NOT NULL DEFAULT 'secondary',
  `stage_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `is_closed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lead_pipeline_stages_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lead_pipeline_stages`
--

LOCK TABLES `lead_pipeline_stages` WRITE;
/*!40000 ALTER TABLE `lead_pipeline_stages` DISABLE KEYS */;
/*!40000 ALTER TABLE `lead_pipeline_stages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lead_reminders`
--

DROP TABLE IF EXISTS `lead_reminders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lead_reminders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lead_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `remind_at` datetime NOT NULL,
  `reminder_type` varchar(255) NOT NULL DEFAULT 'dashboard',
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `sent_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lead_reminders_lead_id_index` (`lead_id`),
  KEY `lead_reminders_user_id_index` (`user_id`),
  KEY `lead_reminders_remind_at_index` (`remind_at`),
  KEY `lead_reminders_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lead_reminders`
--

LOCK TABLES `lead_reminders` WRITE;
/*!40000 ALTER TABLE `lead_reminders` DISABLE KEYS */;
/*!40000 ALTER TABLE `lead_reminders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lead_status_histories`
--

DROP TABLE IF EXISTS `lead_status_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lead_status_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lead_id` bigint(20) unsigned NOT NULL,
  `old_status` varchar(255) DEFAULT NULL,
  `new_status` varchar(255) NOT NULL,
  `changed_by` bigint(20) unsigned DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `changed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lead_status_histories_lead_id_index` (`lead_id`),
  KEY `lead_status_histories_changed_by_index` (`changed_by`),
  KEY `lead_status_histories_new_status_index` (`new_status`),
  KEY `lead_status_histories_changed_at_idx` (`changed_at`),
  KEY `lead_status_histories_lead_id_idx2` (`lead_id`),
  KEY `lead_status_histories_created_at_idx2` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lead_status_histories`
--

LOCK TABLES `lead_status_histories` WRITE;
/*!40000 ALTER TABLE `lead_status_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `lead_status_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leads`
--

DROP TABLE IF EXISTS `leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leads` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lead_code` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `zipCode` varchar(255) DEFAULT NULL,
  `lead_value` decimal(10,2) DEFAULT NULL,
  `source` varchar(255) DEFAULT NULL,
  `industry` varchar(255) DEFAULT NULL,
  `priority` varchar(255) DEFAULT NULL,
  `expected_value` decimal(12,2) DEFAULT NULL,
  `next_followup_at` datetime DEFAULT NULL,
  `requirements` longtext DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `converted_at` datetime DEFAULT NULL,
  `lost_at` datetime DEFAULT NULL,
  `lost_reason` text DEFAULT NULL,
  `won_value` decimal(12,2) DEFAULT NULL,
  `pipeline_stage_order` int(10) unsigned DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `description` text DEFAULT NULL,
  `status` enum('new','attempted_contact','contacted','qualified','demo_scheduled','proposal_sent','negotiation','won','lost','junk','converted') NOT NULL DEFAULT 'new',
  `previous_status` varchar(255) DEFAULT NULL,
  `status_updated_at` datetime DEFAULT NULL,
  `status_updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leads_next_followup_at_idx` (`next_followup_at`),
  KEY `leads_status_idx` (`status`),
  KEY `leads_created_at_idx` (`created_at`),
  KEY `leads_status_pipeline_idx` (`status`),
  KEY `leads_status_updated_at_idx` (`status_updated_at`),
  KEY `leads_converted_at_idx` (`converted_at`),
  KEY `leads_lost_at_idx` (`lost_at`),
  KEY `leads_status_idx2` (`status`),
  KEY `leads_next_followup_at_idx2` (`next_followup_at`),
  KEY `leads_created_at_idx2` (`created_at`),
  KEY `leads_converted_at_idx2` (`converted_at`),
  KEY `leads_created_by_idx` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leads`
--

LOCK TABLES `leads` WRITE;
/*!40000 ALTER TABLE `leads` DISABLE KEYS */;
/*!40000 ALTER TABLE `leads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meta_lead_user`
--

DROP TABLE IF EXISTS `meta_lead_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meta_lead_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `meta_lead_id` bigint(20) unsigned NOT NULL,
  `page_id` varchar(255) DEFAULT NULL,
  `form_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `meta_lead_user_unique` (`user_id`,`meta_lead_id`),
  KEY `meta_lead_user_meta_lead_id_foreign` (`meta_lead_id`),
  KEY `meta_lead_user_scope_idx` (`user_id`,`page_id`,`form_id`),
  CONSTRAINT `meta_lead_user_meta_lead_id_foreign` FOREIGN KEY (`meta_lead_id`) REFERENCES `meta_leads` (`id`) ON DELETE CASCADE,
  CONSTRAINT `meta_lead_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meta_lead_user`
--

LOCK TABLES `meta_lead_user` WRITE;
/*!40000 ALTER TABLE `meta_lead_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `meta_lead_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meta_lead_whatsapp_message_logs`
--

DROP TABLE IF EXISTS `meta_lead_whatsapp_message_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meta_lead_whatsapp_message_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `meta_lead_id` bigint(20) unsigned NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `template_name` varchar(255) DEFAULT NULL,
  `template_parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`template_parameters`)),
  `status` varchar(20) NOT NULL DEFAULT 'queued',
  `provider_status_code` smallint(5) unsigned DEFAULT NULL,
  `provider_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`provider_response`)),
  `error_message` text DEFAULT NULL,
  `attempted_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `failed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `meta_lead_whatsapp_logs_lead_status_idx` (`meta_lead_id`,`status`),
  CONSTRAINT `meta_lead_whatsapp_message_logs_meta_lead_id_foreign` FOREIGN KEY (`meta_lead_id`) REFERENCES `meta_leads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meta_lead_whatsapp_message_logs`
--

LOCK TABLES `meta_lead_whatsapp_message_logs` WRITE;
/*!40000 ALTER TABLE `meta_lead_whatsapp_message_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `meta_lead_whatsapp_message_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meta_leads`
--

DROP TABLE IF EXISTS `meta_leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meta_leads` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lead_id` varchar(255) NOT NULL,
  `form_id` varchar(255) DEFAULT NULL,
  `page_id` varchar(255) DEFAULT NULL,
  `ad_id` varchar(255) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `field_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`field_data`)),
  `status` enum('new','attempted_contact','contacted','qualified','demo_scheduled','proposal_sent','negotiation','won','lost','junk','converted') NOT NULL DEFAULT 'new',
  `welcome_whatsapp_status` varchar(20) NOT NULL DEFAULT 'pending',
  `welcome_whatsapp_sent_at` timestamp NULL DEFAULT NULL,
  `welcome_whatsapp_last_log_id` bigint(20) unsigned DEFAULT NULL,
  `welcome_whatsapp_attempts` int(10) unsigned NOT NULL DEFAULT 0,
  `created_time` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `meta_leads_lead_id_unique` (`lead_id`),
  KEY `meta_leads_welcome_whatsapp_last_log_id_foreign` (`welcome_whatsapp_last_log_id`),
  KEY `meta_leads_welcome_whatsapp_status_idx` (`welcome_whatsapp_status`),
  CONSTRAINT `meta_leads_welcome_whatsapp_last_log_id_foreign` FOREIGN KEY (`welcome_whatsapp_last_log_id`) REFERENCES `meta_lead_whatsapp_message_logs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meta_leads`
--

LOCK TABLES `meta_leads` WRITE;
/*!40000 ALTER TABLE `meta_leads` DISABLE KEYS */;
/*!40000 ALTER TABLE `meta_leads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_resets_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2024_01_01_000001_create_settings_table',1),(6,'2024_01_01_000002_create_tags_table',1),(7,'2025_08_19_062727_create_clients_table',1),(8,'2025_08_20_045136_create_vendors_table',1),(9,'2025_08_20_052845_create_services_table',1),(10,'2025_08_20_061133_add_service_details_to_services_table',1),(11,'2025_08_21_072958_create_notification_reads_table',1),(12,'2025_08_23_101747_make_address_nullable_in_clients_table',1),(13,'2025_09_05_092200_update_vendors_table',1),(14,'2025_09_05_093541_update_users_table',1),(15,'2025_09_05_095319_update_clients_table',1),(16,'2025_09_05_101613_update_services_table',1),(17,'2025_09_26_050249_replace_amount_with_billing_date_in_services_table',1),(18,'2025_12_04_061429_make_client_id_nullable_in_services_table',1),(19,'2025_12_04_065045_create_vendor_services_table',1),(20,'2025_12_04_102317_add_plan_type_to_vendor_services_table',1),(21,'2025_12_04_103935_make_billing_date_nullable_in_vendor_services_table',1),(22,'2026_01_16_061137_create_staff_table',1),(23,'2026_01_16_061425_remove_fields_from_staff_table',1),(24,'2026_01_17_063036_create_calendar_events_table',1),(25,'2026_01_17_063053_create_jobs_table',1),(26,'2026_01_17_100000_add_whatsapp_fields_to_calendar_events',1),(27,'2026_01_29_041246_create_customers_table',1),(28,'2026_01_29_060408_create_projects_table',1),(29,'2026_01_29_073311_add_priority_and_technologies_to_projects_table',1),(30,'2026_01_29_100223_create_tasks_table',1),(31,'2026_01_29_104904_create_task_attachments_table',1),(32,'2026_01_29_113447_create_permission_tables',1),(33,'2026_01_31_120000_create_leads_table',1),(34,'2026_01_31_120001_add_role_password_to_customers_table',1),(35,'2026_02_02_070000_add_user_id_to_staff_and_customers_tables',1),(36,'2026_02_03_070000_create_client_issues_table',1),(37,'2026_02_04_053732_create_client_issue_tasks_table',1),(38,'2026_02_05_111200_add_due_date_to_client_issue_tasks_table',1),(39,'2026_02_07_120000_add_attachments_to_client_issue_tasks_table',1),(40,'2026_02_09_120000_add_team_to_staff_table',1),(41,'2026_02_09_130000_create_client_issue_team_assignments_table',1),(42,'2026_02_09_140000_add_assigned_to_to_client_issue_team_assignments_table',1),(43,'2026_02_11_101500_create_project_status_logs_table',1),(44,'2026_02_11_120000_create_project_files_table',1),(45,'2026_02_11_150000_create_project_milestones_table',1),(46,'2026_02_12_130709_create_task_comments_table',1),(47,'2026_02_13_120000_create_project_comments_table',1),(48,'2026_02_17_000000_create_teams_table',1),(49,'2026_02_17_010000_add_description_and_icon_to_teams_table',1),(50,'2026_03_06_000000_create_project_issues_table',1),(51,'2026_03_09_120000_make_project_id_nullable_in_tasks_table',1),(52,'2026_03_10_130000_make_customer_id_nullable_in_projects_table',1),(53,'2026_03_12_120000_add_remark_fields_to_services_table',1),(54,'2026_03_12_130000_add_view_calendar_permission',1),(55,'2026_03_12_140000_add_view_dashboard_welcome_permission',1),(56,'2026_03_14_140000_create_todos_table',1),(57,'2026_03_26_000001_create_departments_table',1),(58,'2026_03_26_120000_add_deleted_at_to_staff_table',1),(59,'2026_03_27_090000_create_bookcall_table',1),(60,'2026_03_27_090100_add_book_call_permission',1),(61,'2026_04_02_000000_add_meeting_agenda_to_bookcall_table',1),(62,'2026_04_07_000000_add_deleted_at_to_customers_table',1),(63,'2026_04_10_000000_create_digital_marketing_leads_table',1),(64,'2026_04_11_000000_add_digital_marketing_leads_permissions',1),(65,'2026_04_13_113818_update_roles_table',1),(66,'2026_04_14_000001_add_attachments_to_todos_table',1),(67,'2026_04_15_112122_add_delete_at_to_projects_table',1),(68,'2026_04_15_164200_add_deleted_at_to_tasks_table',1),(69,'2026_04_16_155401_add_delete_at_service_table',1),(70,'2026_04_16_172325_add_soft_deletes_to_vendor_services_table',1),(71,'2026_04_18_000001_add_profile_image_to_users_table',1),(72,'2026_04_18_111150_update_users_table_add_missing_fields',1),(73,'2026_04_18_111211_create_user_address_table',1),(74,'2026_04_18_111229_create_staff_team_table',1),(75,'2026_04_18_111246_create_staff_department_table',1),(76,'2026_04_18_111303_create_client_business_details_table',1),(77,'2026_04_18_171108_update_projects_table',1),(78,'2026_04_18_200000_migrate_legacy_staff_and_client_relationships_to_users',1),(79,'2026_04_20_163000_add_missing_profile_image_to_users_table',1),(80,'2026_04_21_130947_update_client_business_details_table',1),(81,'2026_04_23_000000_create_webapp_leads_table',1),(82,'2026_04_24_112314_update_vendors_table',1),(83,'2026_05_02_120000_create_fcm_tokens_table',1),(84,'2026_05_04_000000_create_notifications_table',1),(85,'2026_05_05_000000_create_meta_leads_table',1),(86,'2026_05_06_000001_create_google_leads_table',1),(87,'2026_05_15_000002_add_client_business_detail_id_to_services_table',1),(88,'2026_05_15_000003_make_optional_client_contact_fields_nullable',1),(89,'2026_05_19_000000_create_jobapplication_table',1),(90,'2026_05_19_000001_create_contactform_table',1),(91,'2026_05_19_000002_add_deleted_at_to_web_enquiry_tables',1),(92,'2026_05_19_000003_update_jobapplication_add_applicant_type_and_defaults',1),(93,'2026_05_21_000001_make_vendor_contact_fields_nullable',1),(94,'2026_05_21_000002_fix_personal_access_tokens_id_auto_increment',1),(95,'2026_05_22_000003_fix_permission_and_role_id_auto_increment',1),(96,'2026_05_23_000001_add_remark_to_vendor_services_table',1),(97,'2026_05_25_000000_create_assigned_leads_table',1),(98,'2026_05_25_000001_add_status_to_lead_source_tables',1),(99,'2026_05_25_000010_add_reminder_channels_to_todos_table',1),(100,'2026_05_26_000100_add_enterprise_columns_to_existing_leads_table',1),(101,'2026_05_26_000200_create_enterprise_lead_pipeline_tables',1),(102,'2026_05_26_000300_add_status_pipeline_fields_to_leads_table',1),(103,'2026_05_26_000400_add_changed_at_and_pipeline_stages',1),(104,'2026_05_26_000500_expand_lead_status_enum_values',1),(105,'2026_05_26_000600_add_staff_analytics_indexes',1),(106,'2026_05_26_020000_drop_assigned_columns_from_leads_table',1),(107,'2026_05_26_020100_add_source_fields_to_lead_followups_table',1),(108,'2026_05_27_162200_add_enterprise_columns_to_projects_table',1),(109,'2026_05_27_162300_add_enterprise_columns_to_tasks_table',1),(110,'2026_05_27_162400_create_missing_enterprise_project_task_tables',1),(111,'2026_05_27_163000_add_workflow_stage_columns_to_projects_and_tasks',1),(112,'2026_05_27_164000_add_progress_percentage_to_project_milestones_table',1),(113,'2026_05_27_170000_add_enterprise_comment_fields_and_attachments',1),(114,'2026_05_28_000001_add_project_dashboard_indexes',1),(115,'2026_05_29_000001_add_status_to_contactform_table',1),(116,'2026_06_01_123812_update_calendar_events_table',1),(117,'2026_06_02_000000_add_notification_channels_to_calendar_events',1),(118,'2026_06_02_010000_add_reminder_delivery_log_to_calendar_events_and_todos_table',1),(119,'2026_06_03_121942_update_calendar_events_table',1),(120,'2026_06_03_161709_update_user_address_table',1),(121,'2026_06_17_000001_create_amc_services_table',1),(122,'2026_06_17_000002_create_amc_service_details_table',1),(123,'2026_06_17_131439_update_vendor_services_table',1),(124,'2026_06_17_132222_update_services_table',1),(125,'2026_06_18_000001_add_reminder_tracking_to_amc_service_details_table',1),(126,'2026_07_20_000001_create_meta_lead_whatsapp_message_logs_table',1),(127,'2026_07_20_000002_add_welcome_whatsapp_tracking_to_meta_leads_table',1),(128,'2026_07_21_000001_add_user_scope_to_settings_and_leads',1),(129,'2026_07_21_000002_create_meta_lead_user_table',1),(130,'2026_07_21_000003_add_meta_credentials_and_client_lead_management_permissions',1),(131,'2026_07_30_103404_create_enquiry_forms_table',1),(132,'2026_07_30_110000_add_status_to_enquiry_forms_table',1),(133,'2026_08_04_000000_add_meet_fields_to_bookcall_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_reads`
--

DROP TABLE IF EXISTS `notification_reads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification_reads` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `service_id` bigint(20) unsigned NOT NULL,
  `notification_type` varchar(255) NOT NULL,
  `read_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notification_reads_user_id_service_id_notification_type_unique` (`user_id`,`service_id`,`notification_type`),
  KEY `notification_reads_service_id_foreign` (`service_id`),
  KEY `notification_reads_user_id_service_id_index` (`user_id`,`service_id`),
  CONSTRAINT `notification_reads_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notification_reads_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_reads`
--

LOCK TABLES `notification_reads` WRITE;
/*!40000 ALTER TABLE `notification_reads` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification_reads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
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
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'view_calendar','web','2026-10-10 05:25:03','2026-10-10 05:25:03'),(2,'view_dashboard_welcome','web','2026-10-10 05:25:03','2026-10-10 05:25:03'),(3,'view_book_calls','web','2026-10-10 05:25:03','2026-10-10 05:25:03'),(4,'view_digital_marketing_leads','web',NULL,NULL),(5,'create_digital_marketing_leads','web',NULL,NULL),(6,'edit_digital_marketing_leads','web',NULL,NULL),(7,'delete_digital_marketing_leads','web',NULL,NULL),(8,'create_book_calls','web',NULL,NULL),(9,'edit_book_calls','web',NULL,NULL),(10,'delete_book_calls','web',NULL,NULL),(11,'manage_meta_credentials','web','2026-10-10 05:25:10','2026-10-10 05:25:10'),(12,'view_leads_management','web','2026-10-10 05:25:10','2026-10-10 05:25:10'),(13,'view_leads','web','2026-10-10 05:25:10','2026-10-10 05:25:10');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_activities`
--

DROP TABLE IF EXISTS `project_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_activities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `task_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `activity_type` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `activity_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_activities_task_id_foreign` (`task_id`),
  KEY `project_activities_user_id_foreign` (`user_id`),
  KEY `project_activities_project_id_activity_at_index` (`project_id`,`activity_at`),
  KEY `project_activities_activity_type_index` (`activity_type`),
  KEY `project_activities_project_activity_at_idx` (`project_id`,`activity_at`),
  CONSTRAINT `project_activities_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_activities_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `project_activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_activities`
--

LOCK TABLES `project_activities` WRITE;
/*!40000 ALTER TABLE `project_activities` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_approvals`
--

DROP TABLE IF EXISTS `project_approvals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_approvals` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `requested_by` bigint(20) unsigned DEFAULT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approval_type` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `requested_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_approvals_requested_by_foreign` (`requested_by`),
  KEY `project_approvals_approved_by_foreign` (`approved_by`),
  KEY `project_approvals_project_id_status_index` (`project_id`,`status`),
  CONSTRAINT `project_approvals_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `project_approvals_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_approvals_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_approvals`
--

LOCK TABLES `project_approvals` WRITE;
/*!40000 ALTER TABLE `project_approvals` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_approvals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_change_requests`
--

DROP TABLE IF EXISTS `project_change_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_change_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `requested_by` bigint(20) unsigned DEFAULT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `impact_level` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'requested',
  `requested_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_change_requests_requested_by_foreign` (`requested_by`),
  KEY `project_change_requests_approved_by_foreign` (`approved_by`),
  KEY `project_change_requests_project_id_status_index` (`project_id`,`status`),
  CONSTRAINT `project_change_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `project_change_requests_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_change_requests_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_change_requests`
--

LOCK TABLES `project_change_requests` WRITE;
/*!40000 ALTER TABLE `project_change_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_change_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_comments`
--

DROP TABLE IF EXISTS `project_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_comments_project_id_foreign` (`project_id`),
  KEY `project_comments_user_id_foreign` (`user_id`),
  CONSTRAINT `project_comments_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_comments`
--

LOCK TABLES `project_comments` WRITE;
/*!40000 ALTER TABLE `project_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_deployments`
--

DROP TABLE IF EXISTS `project_deployments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_deployments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `deployed_by` bigint(20) unsigned DEFAULT NULL,
  `environment` varchar(255) DEFAULT NULL,
  `version` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'scheduled',
  `deployed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_deployments_deployed_by_foreign` (`deployed_by`),
  KEY `project_deployments_project_id_deployed_at_index` (`project_id`,`deployed_at`),
  CONSTRAINT `project_deployments_deployed_by_foreign` FOREIGN KEY (`deployed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `project_deployments_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_deployments`
--

LOCK TABLES `project_deployments` WRITE;
/*!40000 ALTER TABLE `project_deployments` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_deployments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_files`
--

DROP TABLE IF EXISTS `project_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_files` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_type` varchar(255) NOT NULL,
  `file_size` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `uploaded_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_files_project_id_foreign` (`project_id`),
  KEY `project_files_uploaded_by_foreign` (`uploaded_by`),
  CONSTRAINT `project_files_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_files_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_files`
--

LOCK TABLES `project_files` WRITE;
/*!40000 ALTER TABLE `project_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_issues`
--

DROP TABLE IF EXISTS `project_issues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_issues` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `issue_description` text NOT NULL,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `status` enum('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_issues_project_id_foreign` (`project_id`),
  KEY `project_issues_customer_id_foreign` (`customer_id`),
  CONSTRAINT `project_issues_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `project_issues_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_issues`
--

LOCK TABLES `project_issues` WRITE;
/*!40000 ALTER TABLE `project_issues` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_issues` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_milestones`
--

DROP TABLE IF EXISTS `project_milestones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_milestones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','in_progress','completed') NOT NULL DEFAULT 'pending',
  `due_date` date DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `progress_percentage` tinyint(3) unsigned DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_milestones_progress_percentage_index` (`progress_percentage`),
  KEY `project_milestones_project_status_idx` (`project_id`,`status`),
  CONSTRAINT `project_milestones_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_milestones`
--

LOCK TABLES `project_milestones` WRITE;
/*!40000 ALTER TABLE `project_milestones` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_milestones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_status_histories`
--

DROP TABLE IF EXISTS `project_status_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_status_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `from_status` varchar(255) DEFAULT NULL,
  `to_status` varchar(255) NOT NULL,
  `changed_by` bigint(20) unsigned DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `changed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_status_histories_changed_by_foreign` (`changed_by`),
  KEY `project_status_histories_project_id_changed_at_index` (`project_id`,`changed_at`),
  CONSTRAINT `project_status_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `project_status_histories_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_status_histories`
--

LOCK TABLES `project_status_histories` WRITE;
/*!40000 ALTER TABLE `project_status_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_status_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_status_logs`
--

DROP TABLE IF EXISTS `project_status_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_status_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `status` enum('not_started','in_progress','on_hold','completed','cancelled') NOT NULL,
  `started_at` datetime NOT NULL,
  `ended_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_status_logs_project_id_foreign` (`project_id`),
  CONSTRAINT `project_status_logs_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_status_logs`
--

LOCK TABLES `project_status_logs` WRITE;
/*!40000 ALTER TABLE `project_status_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_status_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_tags`
--

DROP TABLE IF EXISTS `project_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `color` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_tags_project_id_name_index` (`project_id`,`name`),
  CONSTRAINT `project_tags_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_tags`
--

LOCK TABLES `project_tags` WRITE;
/*!40000 ALTER TABLE `project_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_name` varchar(255) NOT NULL,
  `project_code` varchar(255) DEFAULT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('not_started','in_progress','on_hold','completed','cancelled') NOT NULL DEFAULT 'not_started',
  `lifecycle_stage` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `billing_type` enum('fixed_rate','hourly_rate') DEFAULT NULL,
  `total_rate` decimal(10,2) DEFAULT NULL,
  `estimated_hours` int(11) DEFAULT NULL,
  `actual_hours` decimal(10,2) DEFAULT NULL,
  `progress_percentage` tinyint(3) unsigned DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `members` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`members`)),
  `project_manager_id` bigint(20) unsigned DEFAULT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `deployment_date` date DEFAULT NULL,
  `maintenance_expiry` date DEFAULT NULL,
  `health_status` varchar(255) DEFAULT NULL,
  `last_activity_at` timestamp NULL DEFAULT NULL,
  `description` text DEFAULT NULL,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `project_type` varchar(255) DEFAULT NULL,
  `technologies` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`technologies`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `projects_customer_id_foreign` (`customer_id`),
  KEY `projects_project_code_index` (`project_code`),
  KEY `projects_project_type_index` (`project_type`),
  KEY `projects_project_manager_id_index` (`project_manager_id`),
  KEY `projects_approved_by_index` (`approved_by`),
  KEY `projects_health_status_index` (`health_status`),
  KEY `projects_last_activity_at_index` (`last_activity_at`),
  KEY `projects_lifecycle_stage_index` (`lifecycle_stage`),
  CONSTRAINT `projects_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `projects_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `projects_project_manager_id_foreign` FOREIGN KEY (`project_manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (11,1),(11,2),(11,3),(11,4),(11,5),(12,1),(12,2),(12,3),(12,4),(12,5),(13,1),(13,2),(13,3),(13,4),(13,5);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'super_admin','web','active','2026-10-10 05:25:10','2026-10-10 05:25:10'),(2,'super-admin','web','active','2026-10-10 05:25:10','2026-10-10 05:25:10'),(3,'admin','web','active','2026-10-10 05:25:10','2026-10-10 05:25:10'),(4,'client','web','active','2026-10-10 05:25:10','2026-10-10 05:25:10'),(5,'customer','web','active','2026-10-10 05:25:10','2026-10-10 05:25:10');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `services` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint(20) unsigned DEFAULT NULL,
  `client_business_detail_id` bigint(20) unsigned DEFAULT NULL,
  `vendor_id` bigint(20) unsigned NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `service_details` text DEFAULT NULL,
  `plan_type` enum('monthly','yearly','quarterly','half_year') NOT NULL,
  `remark_text` varchar(255) DEFAULT NULL,
  `remark_color` varchar(20) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `billing_date` date NOT NULL,
  `status` enum('active','inactive','expired','pending') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `services_vendor_id_foreign` (`vendor_id`),
  KEY `services_client_id_foreign` (`client_id`),
  KEY `services_client_business_detail_id_foreign` (`client_business_detail_id`),
  CONSTRAINT `services_client_business_detail_id_foreign` FOREIGN KEY (`client_business_detail_id`) REFERENCES `client_business_details` (`id`) ON DELETE SET NULL,
  CONSTRAINT `services_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `services_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'text',
  `group` varchar(255) NOT NULL DEFAULT 'general',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_user_id_key_unique` (`user_id`,`key`),
  KEY `settings_user_id_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sprints`
--

DROP TABLE IF EXISTS `sprints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sprints` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'planned',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `velocity` tinyint(3) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sprints_project_id_status_index` (`project_id`,`status`),
  CONSTRAINT `sprints_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sprints`
--

LOCK TABLES `sprints` WRITE;
/*!40000 ALTER TABLE `sprints` DISABLE KEYS */;
/*!40000 ALTER TABLE `sprints` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `departments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`departments`)),
  `team` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_email_unique` (`email`),
  KEY `staff_user_id_foreign` (`user_id`),
  CONSTRAINT `staff_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff`
--

LOCK TABLES `staff` WRITE;
/*!40000 ALTER TABLE `staff` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_department`
--

DROP TABLE IF EXISTS `staff_department`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_department` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `department_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `staff_department_user_id_foreign` (`user_id`),
  KEY `staff_department_department_id_foreign` (`department_id`),
  CONSTRAINT `staff_department_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `staff_department_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_department`
--

LOCK TABLES `staff_department` WRITE;
/*!40000 ALTER TABLE `staff_department` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff_department` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_lead_stats`
--

DROP TABLE IF EXISTS `staff_lead_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_lead_stats` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `staff_id` bigint(20) unsigned NOT NULL,
  `total_leads` int(10) unsigned NOT NULL DEFAULT 0,
  `converted_leads` int(10) unsigned NOT NULL DEFAULT 0,
  `lost_leads` int(10) unsigned NOT NULL DEFAULT 0,
  `pending_followups` int(10) unsigned NOT NULL DEFAULT 0,
  `total_calls` int(10) unsigned NOT NULL DEFAULT 0,
  `total_meetings` int(10) unsigned NOT NULL DEFAULT 0,
  `conversion_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_lead_stats_staff_id_unique` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_lead_stats`
--

LOCK TABLES `staff_lead_stats` WRITE;
/*!40000 ALTER TABLE `staff_lead_stats` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff_lead_stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_team`
--

DROP TABLE IF EXISTS `staff_team`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_team` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `team_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `staff_team_user_id_foreign` (`user_id`),
  KEY `staff_team_team_id_foreign` (`team_id`),
  CONSTRAINT `staff_team_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `staff_team_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_team`
--

LOCK TABLES `staff_team` WRITE;
/*!40000 ALTER TABLE `staff_team` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff_team` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#3498db',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tags_name_unique` (`name`),
  UNIQUE KEY `tags_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags`
--

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_attachments`
--

DROP TABLE IF EXISTS `task_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_attachments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint(20) unsigned NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_attachments_task_id_foreign` (`task_id`),
  CONSTRAINT `task_attachments_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_attachments`
--

LOCK TABLES `task_attachments` WRITE;
/*!40000 ALTER TABLE `task_attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_checklists`
--

DROP TABLE IF EXISTS `task_checklists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_checklists` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint(20) unsigned NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT 0,
  `completed_by` bigint(20) unsigned DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_checklists_parent_id_foreign` (`parent_id`),
  KEY `task_checklists_completed_by_foreign` (`completed_by`),
  KEY `task_checklists_task_id_is_completed_index` (`task_id`,`is_completed`),
  CONSTRAINT `task_checklists_completed_by_foreign` FOREIGN KEY (`completed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `task_checklists_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `task_checklists` (`id`) ON DELETE SET NULL,
  CONSTRAINT `task_checklists_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_checklists`
--

LOCK TABLES `task_checklists` WRITE;
/*!40000 ALTER TABLE `task_checklists` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_checklists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_comment_attachments`
--

DROP TABLE IF EXISTS `task_comment_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_comment_attachments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `task_comment_id` bigint(20) unsigned NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(255) DEFAULT NULL,
  `file_size` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_comment_attachments_task_comment_id_index` (`task_comment_id`),
  CONSTRAINT `task_comment_attachments_task_comment_id_foreign` FOREIGN KEY (`task_comment_id`) REFERENCES `task_comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_comment_attachments`
--

LOCK TABLES `task_comment_attachments` WRITE;
/*!40000 ALTER TABLE `task_comment_attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_comment_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_comments`
--

DROP TABLE IF EXISTS `task_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint(20) unsigned NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `comment` text NOT NULL,
  `mentions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`mentions`)),
  `edited_at` timestamp NULL DEFAULT NULL,
  `edit_history` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`edit_history`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_comments_task_id_foreign` (`task_id`),
  KEY `task_comments_user_id_foreign` (`user_id`),
  KEY `task_comments_parent_id_index` (`parent_id`),
  CONSTRAINT `task_comments_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `task_comments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `task_comments_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_comments`
--

LOCK TABLES `task_comments` WRITE;
/*!40000 ALTER TABLE `task_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_dependencies`
--

DROP TABLE IF EXISTS `task_dependencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_dependencies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint(20) unsigned NOT NULL,
  `depends_on_task_id` bigint(20) unsigned NOT NULL,
  `dependency_type` enum('blocks','depends_on','related_to') NOT NULL DEFAULT 'depends_on',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_dependency_unique` (`task_id`,`depends_on_task_id`,`dependency_type`),
  KEY `task_dependencies_depends_on_task_id_foreign` (`depends_on_task_id`),
  KEY `task_dependencies_created_by_foreign` (`created_by`),
  KEY `task_dependencies_task_id_depends_on_task_id_index` (`task_id`,`depends_on_task_id`),
  CONSTRAINT `task_dependencies_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `task_dependencies_depends_on_task_id_foreign` FOREIGN KEY (`depends_on_task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_dependencies_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_dependencies`
--

LOCK TABLES `task_dependencies` WRITE;
/*!40000 ALTER TABLE `task_dependencies` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_dependencies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_followers`
--

DROP TABLE IF EXISTS `task_followers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_followers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_followers_task_id_user_id_unique` (`task_id`,`user_id`),
  KEY `task_followers_user_id_foreign` (`user_id`),
  CONSTRAINT `task_followers_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_followers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_followers`
--

LOCK TABLES `task_followers` WRITE;
/*!40000 ALTER TABLE `task_followers` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_followers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_labels`
--

DROP TABLE IF EXISTS `task_labels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_labels` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint(20) unsigned NOT NULL,
  `label` varchar(255) NOT NULL,
  `color` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_labels_task_id_label_index` (`task_id`,`label`),
  CONSTRAINT `task_labels_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_labels`
--

LOCK TABLES `task_labels` WRITE;
/*!40000 ALTER TABLE `task_labels` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_labels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_status_histories`
--

DROP TABLE IF EXISTS `task_status_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_status_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint(20) unsigned NOT NULL,
  `from_status` varchar(255) DEFAULT NULL,
  `to_status` varchar(255) NOT NULL,
  `changed_by` bigint(20) unsigned DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `changed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_status_histories_changed_by_foreign` (`changed_by`),
  KEY `task_status_histories_task_id_changed_at_index` (`task_id`,`changed_at`),
  CONSTRAINT `task_status_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `task_status_histories_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_status_histories`
--

LOCK TABLES `task_status_histories` WRITE;
/*!40000 ALTER TABLE `task_status_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_status_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_tags`
--

DROP TABLE IF EXISTS `task_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `color` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_tags_task_id_name_index` (`task_id`,`name`),
  CONSTRAINT `task_tags_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_tags`
--

LOCK TABLES `task_tags` WRITE;
/*!40000 ALTER TABLE `task_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_time_logs`
--

DROP TABLE IF EXISTS `task_time_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_time_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `duration_minutes` decimal(10,2) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `log_type` enum('timer','manual') NOT NULL DEFAULT 'manual',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_time_logs_user_id_foreign` (`user_id`),
  KEY `task_time_logs_task_id_user_id_index` (`task_id`,`user_id`),
  KEY `task_time_logs_started_at_ended_at_index` (`started_at`,`ended_at`),
  CONSTRAINT `task_time_logs_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_time_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_time_logs`
--

LOCK TABLES `task_time_logs` WRITE;
/*!40000 ALTER TABLE `task_time_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_time_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `task_code` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `project_id` bigint(20) unsigned DEFAULT NULL,
  `milestone_id` bigint(20) unsigned DEFAULT NULL,
  `parent_task_id` bigint(20) unsigned DEFAULT NULL,
  `followers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`followers`)),
  `assignees` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`assignees`)),
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `status` enum('not_started','in_progress','on_hold','completed','cancelled') NOT NULL DEFAULT 'not_started',
  `workflow_status` varchar(255) DEFAULT NULL,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `task_type` varchar(255) DEFAULT NULL,
  `estimated_hours` decimal(10,2) DEFAULT NULL,
  `actual_hours` decimal(10,2) DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `qa_status` varchar(255) DEFAULT NULL,
  `blocked_reason` text DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `deployed_at` timestamp NULL DEFAULT NULL,
  `sequence_order` int(10) unsigned DEFAULT NULL,
  `sprint_id` bigint(20) unsigned DEFAULT NULL,
  `severity` varchar(255) DEFAULT NULL,
  `story_points` tinyint(3) unsigned DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_task_code_index` (`task_code`),
  KEY `tasks_milestone_id_index` (`milestone_id`),
  KEY `tasks_parent_task_id_index` (`parent_task_id`),
  KEY `tasks_task_type_index` (`task_type`),
  KEY `tasks_reviewed_by_index` (`reviewed_by`),
  KEY `tasks_qa_status_index` (`qa_status`),
  KEY `tasks_sequence_order_index` (`sequence_order`),
  KEY `tasks_sprint_id_index` (`sprint_id`),
  KEY `tasks_severity_index` (`severity`),
  KEY `tasks_workflow_status_index` (`workflow_status`),
  KEY `tasks_project_status_priority_idx` (`project_id`,`status`,`priority`),
  KEY `tasks_project_workflow_status_idx` (`project_id`,`workflow_status`),
  CONSTRAINT `tasks_milestone_id_foreign` FOREIGN KEY (`milestone_id`) REFERENCES `project_milestones` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_parent_task_id_foreign` FOREIGN KEY (`parent_task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teams`
--

DROP TABLE IF EXISTS `teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teams` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `teams_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teams`
--

LOCK TABLES `teams` WRITE;
/*!40000 ALTER TABLE `teams` DISABLE KEYS */;
/*!40000 ALTER TABLE `teams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `todos`
--

DROP TABLE IF EXISTS `todos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `todos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attachments`)),
  `task_date` date NOT NULL,
  `task_time` time DEFAULT NULL,
  `repeat_interval` int(10) unsigned NOT NULL DEFAULT 1,
  `repeat_unit` enum('day','week','month','year') NOT NULL DEFAULT 'day',
  `repeat_days` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`repeat_days`)),
  `reminder_time` time DEFAULT NULL,
  `reminder_email` tinyint(1) NOT NULL DEFAULT 1,
  `reminder_whatsapp` tinyint(1) NOT NULL DEFAULT 0,
  `starts_on` date NOT NULL,
  `ends_type` enum('never','on','after') NOT NULL DEFAULT 'never',
  `ends_on` date DEFAULT NULL,
  `ends_after_occurrences` int(10) unsigned DEFAULT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL,
  `last_reminded_occurrence_on` date DEFAULT NULL,
  `last_reminder_sent_at` timestamp NULL DEFAULT NULL,
  `reminder_delivery_log` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`reminder_delivery_log`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `todos_user_id_foreign` (`user_id`),
  CONSTRAINT `todos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `todos`
--

LOCK TABLES `todos` WRITE;
/*!40000 ALTER TABLE `todos` DISABLE KEYS */;
/*!40000 ALTER TABLE `todos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_address`
--

DROP TABLE IF EXISTS `user_address`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_address` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `address_line_1` varchar(255) NOT NULL,
  `address_line_2` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `pincode` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_address_user_id_foreign` (`user_id`),
  CONSTRAINT `user_address_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_address`
--

LOCK TABLES `user_address` WRITE;
/*!40000 ALTER TABLE `user_address` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_address` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `role` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendor_services`
--

DROP TABLE IF EXISTS `vendor_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vendor_services` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint(20) unsigned NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `service_details` text DEFAULT NULL,
  `remark_text` varchar(100) DEFAULT NULL,
  `remark_color` varchar(20) DEFAULT NULL,
  `plan_type` enum('monthly','yearly','quarterly','half_year') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `billing_date` date DEFAULT NULL,
  `status` enum('active','inactive','expired','pending') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vendor_services_vendor_id_foreign` (`vendor_id`),
  CONSTRAINT `vendor_services_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendor_services`
--

LOCK TABLES `vendor_services` WRITE;
/*!40000 ALTER TABLE `vendor_services` DISABLE KEYS */;
/*!40000 ALTER TABLE `vendor_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendors`
--

DROP TABLE IF EXISTS `vendors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vendors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vendors_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendors`
--

LOCK TABLES `vendors` WRITE;
/*!40000 ALTER TABLE `vendors` DISABLE KEYS */;
/*!40000 ALTER TABLE `vendors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `webapp_leads`
--

DROP TABLE IF EXISTS `webapp_leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `webapp_leads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `company` varchar(150) NOT NULL DEFAULT '',
  `website` varchar(255) NOT NULL DEFAULT '',
  `message` text DEFAULT NULL,
  `source_page` varchar(120) NOT NULL DEFAULT 'webapp.php',
  `status` enum('new','attempted_contact','contacted','qualified','demo_scheduled','proposal_sent','negotiation','won','lost','junk','converted') NOT NULL DEFAULT 'new',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `webapp_leads`
--

LOCK TABLES `webapp_leads` WRITE;
/*!40000 ALTER TABLE `webapp_leads` DISABLE KEYS */;
/*!40000 ALTER TABLE `webapp_leads` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-10 11:05:45
