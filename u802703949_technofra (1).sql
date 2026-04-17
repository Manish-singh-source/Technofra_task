-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 16, 2026 at 09:13 AM
-- Server version: 11.8.6-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u802703949_technofra`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookcall`
--

CREATE TABLE `bookcall` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `meeting_agenda` text DEFAULT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `booking_datetime` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookcall`
--

INSERT INTO `bookcall` (`id`, `name`, `email`, `phone`, `meeting_agenda`, `booking_date`, `booking_time`, `booking_datetime`, `created_at`) VALUES
(26, 'Manish', 'manish@technofra.com', '+91 7709131541', 'testing', '2026-04-23', '00:00:00', '2026-04-23 00:00:00', '2026-04-15 07:52:33');

-- --------------------------------------------------------

--
-- Table structure for table `calendar_events`
--

CREATE TABLE `calendar_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` datetime NOT NULL,
  `event_time` datetime NOT NULL,
  `email_recipients` varchar(255) NOT NULL,
  `whatsapp_recipients` varchar(255) DEFAULT NULL,
  `notification_sent` tinyint(1) NOT NULL DEFAULT 0,
  `reminder_10min_sent` tinyint(1) NOT NULL DEFAULT 0,
  `reminder_10min_sent_at` datetime DEFAULT NULL,
  `event_time_notification_sent` tinyint(1) NOT NULL DEFAULT 0,
  `event_time_notification_sent_at` datetime DEFAULT NULL,
  `notification_sent_at` datetime DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `calendar_events`
--

INSERT INTO `calendar_events` (`id`, `title`, `description`, `event_date`, `event_time`, `email_recipients`, `whatsapp_recipients`, `notification_sent`, `reminder_10min_sent`, `reminder_10min_sent_at`, `event_time_notification_sent`, `event_time_notification_sent_at`, `notification_sent_at`, `created_by`, `status`, `created_at`, `updated_at`) VALUES
(3, 'Testing', 'checking the process for application', '2026-03-16 00:00:00', '2026-03-16 15:16:00', 'shubhamshinde7792@gmail.com', '8850773797', 1, 0, NULL, 1, '2026-03-16 15:16:10', '2026-03-16 15:16:10', 1, 1, '2026-03-16 14:45:23', '2026-03-16 15:16:10'),
(4, 'svsvsv', 'sdvsdvsv', '2026-04-24 00:00:00', '2026-04-24 15:09:00', 'shubhamshinde7792@gmail.com', '8850773797', 0, 0, NULL, 0, NULL, NULL, 1, 1, '2026-04-13 15:09:57', '2026-04-13 15:09:57'),
(5, 'testing', 'bsvkasfkv', '2026-04-13 00:00:00', '2026-04-13 15:26:00', 'kjfva@gmail.com', '8850773797', 1, 0, NULL, 1, '2026-04-13 15:26:08', '2026-04-13 15:26:08', 1, 1, '2026-04-13 15:11:22', '2026-04-13 15:26:08');

-- --------------------------------------------------------

--
-- Table structure for table `chatbot_leads`
--

CREATE TABLE `chatbot_leads` (
  `id` int(10) UNSIGNED NOT NULL,
  `session_token` varchar(128) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `requirement` text NOT NULL,
  `source_page` varchar(120) NOT NULL DEFAULT 'index',
  `conversation_excerpt` text NOT NULL,
  `last_user_message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `chatbot_leads`
--

INSERT INTO `chatbot_leads` (`id`, `session_token`, `name`, `email`, `phone`, `requirement`, `source_page`, `conversation_excerpt`, `last_user_message`, `created_at`, `updated_at`) VALUES
(1, '9gnrr18tle579rrm7hemqr5ib7', 'hi', 'support@technofra.com', '8080751003', 'website regardimg', '/', 'Assistant: Yes, we also provide branding, UI/UX, logo systems, and digital experience design support. Please have a look at our [[Branding Services|https://technofra.com/branding]] page. Visitor: hi Assistant: Hello, and welcome to Technofra. Please share your requirement, and I will guide you with the most relevant next step. Visitor: api Assistant: Thank you for your message. You are currently browsing our home page. Please let me know whether you need website development, app development, branding, marketing, payment integration, or chatbot support. Visitor: API Assistant: Thank you for your message. You are currently browsing our home page. Please let me know whether you need website development, app development, branding, marketing, payment integration, or chatbot support. Visitor: share website services Assistant: Technofra provides the following core services: - Website Development: [[Website Development|https://technofra.com/web-design]] - App Development: [[App Development|https://technofra.com/android-app-development]] - Branding & UI/UX: [[Branding & UI/UX|https://technofra.com/branding]] - Digital Marketing & SEO: [[Digital Marketing & SEO|https://technofra.com/digital-marketing]] - Social Media Marketing: [[Social Media Marketing|https://technofra.com/social-media-marketing]] - Payment Gateway Integration: [[Payment Gateway Integration|https://technofra.com/payment-gateway]] - Chatbot Solutions: Visitor: share website services', 'share website services', '2026-04-15 11:08:23', '2026-04-15 12:36:12'),
(2, 'k9nlh8qb8mj4bjgnk1s22v29av', 'Pradnya ashok jagdale', 'pradnya@technofra.com', '8080751003', 'Api', '/', 'Visitor: Api Assistant: Thanks Pradnya ashok jagdale, your details have been saved. Let me help you with that. Thank you for your message. You are currently browsing our home page. Please let me know whether you need website development, app development, branding, marketing, payment integration, or chatbot support. Visitor: mujhe api ke bare me batao Assistant: Yes, we support API-based integrations for websites and applications, including payment gateway, WhatsApp, SMS / OTP, and other business workflow integrations. Relevant pages: [[WhatsApp Solutions|https://technofra.com/whatsapp]], [[SMS / OTP Solutions|https://technofra.com/sms-otp]], and [[Payment Gateway Solutions|https://technofra.com/payment-gateway]]. Visitor: api Assistant: Thank you for your message. You are currently browsing our home page. Please let me know whether you need website development, app development, branding, marketing, payment integration, or chatbot support. Visitor: Api Assistant: Thank you for your message. You are currently browsing our home page. Please let me know whether you need website development, app development, branding, marketing, payment integration, or chatbot support. Visitor: Api', 'Api', '2026-04-15 11:10:50', '2026-04-15 12:06:27'),
(3, 'tbm85vo6kn6gptho7rb70d84n3', 'Pradnya', 'Pradnya@technofra.com', '7558542831', 'website make', '/domain-hosting-services', 'Assistant: Thanks Pradnya, your details have been saved. Let me help you with that. We can certainly help with website design, WordPress, Shopify, and custom business websites. Please review our [[Website Services|https://technofra.com/web-design]] page, or share your requirement and I will suggest the right solution. Visitor: total services list Assistant: Technofra provides the following core services: - Website Development: [[Website Development|https://technofra.com/web-design]] - App Development: [[App Development|https://technofra.com/android-app-development]] - Branding & UI/UX: [[Branding & UI/UX|https://technofra.com/branding]] - Digital Marketing & SEO: [[Digital Marketing & SEO|https://technofra.com/digital-marketing]] - Social Media Marketing: [[Social Media Marketing|https://technofra.com/social-media-marketing]] - Payment Gateway Integration: [[Payment Gateway Integration|https://technofra.com/payment-gateway]] - Chatbot Solutions: Visitor: i want to go pune Assistant: Thank you for your message. You are currently browsing our domain hosting services page. Please let me know whether you need website development, app development, branding, marketing, payment integration, or chatbot support. Visitor: career Assistant: You can explore current career opportunities and job-related details on our [[Career Opportunities|https://technofra.com/career]] page. If you want, I can also help you with the application or contact process. Visitor: job Assistant: You can explore current career opportunities and job-related details on our [[Career Opportunities|https://technofra.com/career]] page. If you want, I can also help you with the application or contact process. Visitor: job', 'job', '2026-04-16 04:59:03', '2026-04-16 05:00:01');

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cname` varchar(255) NOT NULL,
  `coname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `cname`, `coname`, `email`, `phone`, `address`, `status`, `created_at`, `updated_at`) VALUES
(2, 'Finwert Advisors', 'Finwert Advisors', 'ronak@finwert.com', '+91 99872 49694', '501, Antariksh Thakur House,\r\nMakwana Lane, Off Andheri Kurla Road,\r\nMarol, Andheri East, Mumbai - 400059', 1, '2025-08-23 01:21:56', '2025-09-19 00:22:57'),
(3, 'KARAN TELECOM', 'KARAN TELECOM', 'sales@karantelecom.com', '+91 98211 84620', '221 GUNDECHA INDUS COMP CHS LTD AKURLI ROAD NR BIG BAZAR KANDIVALI EAST', 1, '2025-09-01 04:11:47', '2025-09-19 00:23:14'),
(4, 'AAYUSH WELLNESS LIMITED', 'AAYUSH WELLNESS LIMITED', 'sanjay@aayushwellness.com', '+91 84486 93032', 'FLOOR NO.2 B WING, UNIT NO.310 ROYAL PALMS ORCHARD ROAD MALL Mumbai', 1, '2025-09-01 04:13:01', '2025-09-19 00:26:17'),
(5, 'DYNARX TECHNOLOGY (INDIA) LIMITED', 'DYNARX TECHNOLOGY (INDIA) LIMITED', 'tanuja.k@dynarx.com', '+91 96063 06748', 'PLOT NO.9 KIADB CHIKKABALLAPUR INDUSTRIAL AREA', 1, '2025-09-01 04:14:26', '2025-09-19 00:23:30'),
(6, 'Link Promotions', 'Link Promotions', 'amar@linkpromotions.co.in', '+91 98213 37161', 'Ghanshyam Enclave,913, 9th Floor, Next To Laljipada Police Station, Laljipada, Link Road, Kandivali(W), Mumbai - 400067.', 1, '2025-09-01 04:20:55', '2025-09-19 00:24:32'),
(7, 'Aspirias Teacher Training Center', 'Aspirias Teacher Training Center', 'sonal@aspirias.in', '+91 9867618545', 'Dreamax Vega, Upadhyay Compound Pump, House Near Maruti Mandir, Andheri East', 1, '2025-09-05 04:05:27', '2025-09-19 00:24:47'),
(8, 'TRAVEST HOLIDAYS', 'TRAVEST HOLIDAYS', 'paril@travestholidays.com', '+91 95945 82994', NULL, 1, '2025-09-06 04:12:21', '2025-09-19 00:25:06'),
(9, 'Fragomatrix Fragrance and Flavors Pvt. Ltd..,', 'Fragomatrix Fragrance and Flavors Pvt. Ltd..,', 'factory@fragomatrixfnf.com', '8141123406', 'Survey No. 1193/2 & 1199/1, Khata No. 1502, Opp. Amar Tea, Raiwadi Road, Sanjan-396150, Tal. Umargam, Dist. Valsad, State: Gujarat', 1, '2025-09-06 04:13:53', '2025-09-19 00:25:19'),
(10, 'FRAGOMTRIX PERFUMES LLP', 'FRAGOMTRIX PERFUMES LLP', 'accounts@fragomatrix.net', '9512039535', 'PLOT NO 279 AND 280 M/S FRAGOMATRIX PERFUMES LLP SACHIN SPECIAL ECONOMIC ZONE', 1, '2025-09-06 04:14:48', '2025-09-19 00:25:34'),
(11, 'JIGNA ENTERPRISES', 'JIGNA ENTERPRISES', 'brandwiz@sukanyaindia.in', '+91 75063 34474', '1 124 Cosmos Atlantic plaza premises co-op soc ltd Kakasaheb Gadgil Marg,Garrage gully Dadar West', 1, '2025-09-06 04:19:52', '2025-09-19 00:25:46'),
(12, 'Rustomjee Azziano D & L Co-op Housing Society Ltd.', 'Rustomjee Azziano D & L Co-op Housing Society Ltd.', 'technocarethane@gmail.com', '98336 63567', 'PMS Office, S2 Parking Level,\r\nD Wing, Rustomjee Azziano D & L Co-op Hsg Socy Ltd\r\nNear Bhiwandi Nashik Bypass Highway, Majiwada, Thane (W), 400601', 1, '2025-09-06 04:27:11', '2025-09-19 00:26:50'),
(13, 'Dev International (Yash Tools)', 'Dev International', 'sales@yashtools.in', '+91 93213 56955', 'C-1, B/23 MAHAVIR NAGAR SHANKAR LANE KANDIVALI WEST', 1, '2025-09-06 04:29:56', '2025-09-19 00:28:01'),
(24, 'The Makers & Zadesigns', 'Themakers & Zadesigns', 'salim@themakers.in', '+91 9820874772', NULL, 1, '2025-09-09 05:26:59', '2025-09-19 00:31:03'),
(25, 'Hitesh Sadh', 'SK INTERNATIONAL EXPORT LIMITED.', 'skaccounts@skinternational.in', '+91 8928474575', NULL, 1, '2025-09-10 01:17:28', '2025-09-10 01:17:28'),
(26, 'SOLUTIONS FACILITY SERVICES.', 'SOLUTIONS FACILITY SERVICES.', 'support@solutionsfacility.com', '+91 9920977033', NULL, 1, '2025-09-10 01:41:48', '2025-09-18 04:26:47'),
(27, 'MATRIX FACILITY SERVICES.', 'MATRIX FACILITY SERVICES.', 'support@matrixfacility.in', '0000000000', NULL, 1, '2025-09-10 01:49:39', '2025-09-18 07:28:25'),
(28, 'Sonu Chaudhary', 'GLOBAL FLEET SOLUTIONS (I) PRIVATE LIMITED', 'tirthraj@globalfleet.in', '+91 98105 86611', NULL, 1, '2025-09-10 01:57:09', '2025-09-10 01:57:09'),
(29, 'MARINE SECURITY AND INTELLIGENCE PVT. LTD', 'MARINE SECURITY AND INTELLIGENCE PVT. LTD.', 'finance@marinesecurityindia.com', '022 65856622', NULL, 1, '2025-09-10 02:24:26', '2025-09-18 04:27:58'),
(30, 'EFC INFRASTRUCTURE PRIVATE LIMITED', 'EFC INFRASTRUCTURE PRIVATE LIMITED', 'admin@efcinfra.com', '+91 99677 97777', NULL, 1, '2025-09-11 03:46:55', '2025-09-15 06:33:54'),
(31, 'DIPU ENTERPRISES PRIVATE LIMITED', 'DIPU ENTERPRISES PRIVATE LIMITED', 'hiten@dipuenterprises.com', '+91 8369019301', '314/315/316, Ghanshyam Enclave,Nr. Laljipada police station, New Link Road, Kandivali west, Mumbai-400067', 1, '2025-09-12 01:50:19', '2025-09-19 00:27:15'),
(32, 'Vinayak Deasi', 'Vlegends', 'vinayak@vlegends.in', '+91 9930048704', NULL, 1, '2025-09-12 01:59:28', '2025-09-12 01:59:28'),
(33, 'Kumar Gowda', 'ChempharmaJobs: KRAG International', 'KRGOWDA@hotmail.com', '+91 8928235501', NULL, 1, '2025-09-12 02:06:13', '2025-09-12 02:06:13'),
(34, 'SHAKAMBHARI DYECHEM PRIVATE LIMITED', 'SHAKAMBHARI DYECHEM PRIVATE LIMITED', 'info@shakambharidyechem.com', '+91 90829 85988', NULL, 1, '2025-09-12 02:21:12', '2025-09-12 02:21:12'),
(35, 'SHREE HARI CHEMICALS EXPORT LTD', 'SHREE HARI CHEMICALS EXPORT LTD', 'info@shreeharichemicals.in', '+91 9821381615', NULL, 1, '2025-09-12 02:27:30', '2025-09-12 02:27:30'),
(36, 'MYXXNPACX GROOMING ESSENTIALS PRIVATE LIMITED', 'MYXXNPACX GROOMING ESSENTIALS PRIVATE LIMITED', 'accounts@myxxnpacx.com', '95120 39535', '1st & 2nd Floor, Survey No. 325/3 Behind Jai Corp, Naroli Road, Athal', 1, '2025-09-12 04:28:27', '2025-09-12 04:28:27'),
(37, 'SUN RISK MANAGEMENT AND INSURANCE BROKING SERVICES PRIVATE LIMITED', 'SUN RISK MANAGEMENT AND INSURANCE BROKING SERVICES PRIVATE LIMITED', 'sathyan@sunrisk.co.in', '09821866702', NULL, 1, '2025-09-12 04:42:06', '2025-09-12 04:42:06'),
(38, 'URBAN SPORTS', 'URBAN SPORTS', 'mukesh.smp@gmail.com', '81693 19914', NULL, 1, '2025-09-12 04:51:51', '2025-09-12 04:51:51'),
(39, 'MEHTA AND COMPANY', 'MEHTA AND COMPANY', 'anand@mehtasgroup.com', '98200 91854', NULL, 1, '2025-09-12 04:54:02', '2025-09-12 04:54:02'),
(40, 'CROWN ENTERPRISES', 'CROWN ENTERPRISES', 'saannets123@hotmail.com', '+91 9821166659', NULL, 1, '2025-09-12 06:16:17', '2025-09-12 06:16:17'),
(41, 'RAJPRABHA DEVELOPERS PRIVATE LIMITED', 'RAJPRABHA DEVELOPERS PRIVATE LIMITED', 'hr@rajprabha.in', '+91 95292 45099', NULL, 1, '2025-09-12 06:28:53', '2025-09-12 06:28:53'),
(42, 'SPEEDY CREATION', 'SPEEDY CREATION', 'sanjiv@speedycreation.com', '+91 98206 55848', NULL, 1, '2025-09-12 06:32:03', '2025-09-12 06:32:03'),
(43, 'SHIRKE MECHANISM', 'SHIRKE MECHANISM', 'info@shirkemechanism.com', '022 28614434', NULL, 1, '2025-09-12 06:38:15', '2025-09-12 06:38:15'),
(44, 'GLOBAL OCEAN LOGISTICS INDIA PRIVATE LIMITED', 'GLOBAL OCEAN LOGISTICS INDIA PRIVATE LIMITED', 'accounts@globalocean.in', '9920992189', NULL, 1, '2025-09-12 06:47:43', '2025-09-12 06:47:43'),
(45, 'Kedia & Agrawal', 'Kedia & Agrawal', 'accounts@kediaagrawal.co.in', '9773149764', NULL, 1, '2025-09-13 01:40:00', '2025-09-13 01:40:00'),
(46, 'V.P & SONS', 'V.P & SONS', 'info@vpsons.com', '022 25628001', NULL, 1, '2025-09-13 01:56:20', '2025-09-13 01:56:20'),
(47, 'CONCEPT PHARMACEUTICALS LTD', 'CONCEPT PHARMACEUTICALS LTD', 'kamathsr@conceptpharma.com', '022-42418888', NULL, 1, '2025-09-13 02:09:43', '2025-09-13 02:09:43'),
(48, 'DELTA ELECTRONICS (INDIA)', 'DELTA ELECTRONICS (INDIA)', 'heena@deltacab.co.in', '9820299895', NULL, 1, '2025-09-13 02:13:01', '2025-09-13 02:13:01'),
(49, 'NARESH GANGJI GALA', 'NARESH GANGJI GALA', 'meet_gala@yahoo.co.in', '022 28651293', NULL, 1, '2025-09-13 03:36:41', '2025-09-13 03:36:41'),
(50, 'YELLOWSCA PIGMENTS', 'YELLOWSCA PIGMENTS', 'accounts@yellowscapigments.com', '+91 79 7752 1411', NULL, 1, '2025-09-13 03:38:09', '2025-09-13 03:38:09'),
(51, 'K B TRAVELS AND TICKETING.', 'K B TRAVELS AND TICKETING.', 'director@krossboundaries.ae', '98672 82888', NULL, 1, '2025-09-13 03:44:40', '2025-09-13 03:44:40'),
(52, 'PROPACK INDUSTRIES', 'PROPACK INDUSTRIES', 'kewalshah04@yahoo.co.in', '9820093406', NULL, 1, '2025-09-13 06:06:51', '2025-09-13 06:06:51'),
(53, 'SJ ENTERPRISES', 'SJ ENTERPRISES', 'sjenterprises0527@gmail.com', '7709135480', NULL, 1, '2025-09-13 06:12:38', '2025-09-13 06:12:38'),
(54, 'Viraj Prints', 'Viraj Prints', 'info@virajprints.com', '2240163470', NULL, 1, '2025-09-13 06:24:02', '2025-09-13 06:24:02'),
(55, 'NEX LVL DESIGNS PRIVATE LIMITED', 'NEX LVL DESIGNS PRIVATE LIMITED', 'info@nexlvl.co.in', '7900015023', NULL, 1, '2025-09-15 01:51:13', '2025-09-15 01:51:13'),
(56, 'RAJRATNA ENTERPRISE', 'RAJRATNA ENTERPRISE', 'info@rajratnaaatamaker.com', '+91 - 22 28070911', NULL, 1, '2025-09-15 03:59:44', '2025-09-15 03:59:44'),
(57, 'MANOJ PEN MART', 'MANOJ PEN MART', 'manojpunjabi317@gmail.com', '91-22- 6637 3992', NULL, 1, '2025-09-15 06:29:04', '2025-09-15 06:29:04'),
(58, 'EFC LOGISTICS INDIA PVT. LTD.', 'EFC LOGISTICS INDIA PVT. LTD.', 'ayush@efclogistics.com', '2240929777', NULL, 1, '2025-09-16 06:45:41', '2025-09-16 06:45:41'),
(59, 'LEMINOS LABORATORIES PRIVATE LIMITED', 'LEMINOS LABORATORIES PRIVATE LIMITED', 'info@leminoslab.com', '22-2825 5861', NULL, 1, '2025-09-16 23:17:36', '2025-09-16 23:17:36'),
(60, 'RNISARG FOUNDATION', 'RNISARG FOUNDATION', 'rnisargfoundation@gmail.com', '93237 83266', '2nd Floor, Beauty Arcade, Opp. Pratap Talkies, Khopat, Thane West Mumbai 400601', 1, '2025-09-18 04:28:36', '2025-09-18 04:28:36'),
(61, 'SABAL SHIPPING (INDIA) PVT LTD', 'SABAL SHIPPING (INDIA) PVT LTD', 'accounts@sabalshipping.com', '022 27 809115', '410, Thacker Tower, Plot No. 86, Sector - 17, Next to Saraswat Bank, Vashi, Navi Mumbai', 1, '2025-09-18 07:24:39', '2025-09-18 07:24:39'),
(62, 'ISH INTERNATIONAL MFG CO.', 'ISH INTERNATIONAL MFG CO.', 'shailesh@ishinternational.in', '9422250052', 'Plot No. B-77, MIDC Area, Ambad, Nashik Mumbai 422010', 1, '2025-09-19 02:12:59', '2025-09-19 02:12:59'),
(63, 'YASH RAJ LOGISTICS', 'YASH RAJ LOGISTICS', 'admin@yashrajlogistics.co.in', '22-2262 6718', '94/96, Room No.2, 2nd Floor, Karolia Building, Bora Bazaar Street, Fort Mumbai 400001', 1, '2025-09-19 03:39:32', '2025-09-19 03:39:32'),
(64, 'TORSHAA PHARMA SOLUTION LLP', 'TORSHAA PHARMA SOLUTION LLP', 'torshaa@torshaapharma.com', '9869435303', '1st Floor House No. 654, F-101 Motiram Krishna Patil Compound Purna Bhiwandi', 1, '2025-09-19 03:46:17', '2025-09-19 03:46:17'),
(65, 'MANCHANDA INVESTMENTS', 'MANCHANDA INVESTMENTS', 'rkm@manchanda.biz', '22-4093 3333', 'GROUND FLOOR 29, LAXMI INDUSTRIAL ESTATE NEW LINK ROAD ANDHERI', 1, '2025-09-19 04:11:11', '2025-09-19 04:11:11'),
(66, 'St. Mary Logistics', 'St. Mary Logistics', 'john@stmarylogistics.in', '022 3569 0388', NULL, 1, '2025-09-19 04:16:50', '2025-09-19 04:16:50'),
(67, 'VisitMadhIsland', 'VisitMadhIsland', 'rajsharmaq123@gmail.com', '7977381533', NULL, 1, '2025-09-19 06:36:23', '2025-09-19 06:36:23'),
(68, 'JAGDAMBA PAPER CORPORATION', 'JAGDAMBA PAPER CORPORATION', 'info@jagdambapaper.com', '93244 20871', '304 Kalpataru Plaza Chincholi Bunder Road Ram Baug, Malad West', 1, '2025-09-20 00:15:17', '2025-09-20 00:15:17'),
(69, 'SNA INFOTECH PRIVATE LIMITED.', 'SNA INFOTECH PRIVATE LIMITED.', 'account@sna-infotech.co.in', '022 42182620', '1st FLOOR GALA NO 17 NAVYUG INDUSTRIAL ESTATE VAZIR GLASS FACTORY LANE OFF ANDHERI KURLA ROAD', 1, '2025-09-20 01:14:32', '2025-09-20 01:14:32'),
(70, 'SUNRISE CONSULTANCY', 'SUNRISE CONSULTANCY', 'uday@sunriseconsultancy.net', '9833145995', 'A/603 PARAS CHS LTD M G ROAD, HINDUSTAN NAKA Mumbai', 1, '2025-09-20 01:36:40', '2025-09-20 01:36:40'),
(71, 'HARMONY MONTESSORI', 'HARMONY MONTESSORI', 'admin@harmonymontessori.co.in', '022 2600 0168', NULL, 1, '2025-09-20 02:02:37', '2025-09-20 02:02:37'),
(72, 'BELTING ENTERPRISES PVT LTD', 'BELTING ENTERPRISES PVT LTD', 'accounts@beltingenterprises.in', '022 28372456', NULL, 1, '2025-09-20 02:12:59', '2025-09-20 02:12:59'),
(73, 'SPACEAGE CONSULTANTS', 'SPACEAGE CONSULTANTS', 'jcvspace@spaceagearch.com', '022 2560 8550', 'B-106, C-108, C-109, Natraj Bldg, Shiv Srishti Complex, Mulund - Goregaon Link Road, Near Nahur Railway Station, Mulund (W), Mumbai 400 080', 1, '2025-09-20 02:18:57', '2025-09-20 02:18:57'),
(74, 'RUPAL PLASTICS PRIVATE LIMITED', 'RUPAL PLASTICS PRIVATE LIMITED', 'accounts@rupalplastics.com', '9833831072', '3rd 307-309 NEELKANTH COMMERCIAL CENTRE SAHAR ROAD VIELPARLE-EAST', 1, '2025-09-20 03:41:09', '2025-09-20 03:41:09'),
(75, 'SANJAY AGENCIES', 'SANJAY AGENCIES', 'sagencies.sk@gmail.com', '9820263193', '507 GHANSHYAM ENCLAVES GANDHI NAGAR, NEW LINK ROAD, KANDIVALI WEST', 1, '2025-09-20 04:08:09', '2025-09-20 04:08:09'),
(76, 'NIRBHAY RASAYAN PVT. LTD.', 'NIRBHAY RASAYAN PVT. LTD.', 'admin@nirbhayrasayan.com', '022-28705055', 'A 608, Express Zone, Western Express Highway, Near Oberoi Mall, Malad East, Mumbai - 400097', 1, '2025-09-20 04:16:14', '2025-09-20 04:16:14'),
(77, 'SHREERAM INTERIORS', 'SHREERAM INTERIORS', 'anil@shreeraminteriors.co.in', '9930982704', NULL, 1, '2025-09-20 05:01:35', '2025-09-20 05:01:35'),
(78, 'Omega Products Private Limited', 'Omega Products Private Limited', 'accounts@omegaresistors.com', '+91 22 28290027', 'Unit no 50, SDF II, SEEPZ (SEZ), MIDC, Andheri (East), Mumbai – 400 096, INDIA.', 1, '2025-09-24 04:54:40', '2025-09-24 04:54:40'),
(79, 'SHAH S KATHARIYA.', 'SHAH S KATHARIYA.', 'megha@finwert.com', '+91 97731 49764', NULL, 1, '2025-11-20 02:17:55', '2025-11-20 02:17:55'),
(80, 'Aeritx', 'ADWENTURE ETHERNET RELIABLE PRIVATE LIMITE', 'priyanshu@aeritx.com', '+91 98693 95476', NULL, 1, '2026-01-30 23:31:10', '2026-01-30 23:40:55'),
(81, 'Animal Kingdom India', 'Animal kingdom Welfare Foundation', 'samantha_soni@yahoo.com', '+91 99301 52589', NULL, 1, '2026-01-31 00:05:00', '2026-01-31 00:05:00'),
(82, 'Shivam Industries', 'Shivam Industries', 'accounts@shivaminds.com', '+91 99679 40368', NULL, 1, '2026-01-31 01:01:13', '2026-01-31 01:01:13'),
(83, 'HARMONY MONTESSORI', 'HARMONY MONTESSORI', 'leena@harmonymontessori.co.in', '+91 98203 43688', '', 1, '2026-04-06 16:07:06', '2026-04-06 16:07:06');

-- --------------------------------------------------------

--
-- Table structure for table `client_issues`
--

CREATE TABLE `client_issues` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `issue_description` text NOT NULL,
  `priority` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `status` enum('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `client_issues`
--

INSERT INTO `client_issues` (`id`, `project_id`, `customer_id`, `issue_description`, `priority`, `status`, `created_at`, `updated_at`) VALUES
(2, 2, 2, 'Ddhrh', 'low', 'open', '2026-03-14 10:39:43', '2026-03-14 10:39:43'),
(3, 3, 3, 'sasbasbasba', 'low', 'open', '2026-04-16 10:54:46', '2026-04-16 10:54:46'),
(4, 2, 2, 'werff', 'low', 'open', '2026-04-16 11:03:17', '2026-04-16 11:03:17');

-- --------------------------------------------------------

--
-- Table structure for table `client_issue_tasks`
--

CREATE TABLE `client_issue_tasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_issue_id` bigint(20) UNSIGNED NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `client_issue_tasks`
--

INSERT INTO `client_issue_tasks` (`id`, `client_issue_id`, `title`, `description`, `status`, `priority`, `assigned_to`, `start_date`, `due_date`, `due_time`, `reminder_date`, `reminder_time`, `checklist_data`, `labels_data`, `attachment`, `attachments`, `created_at`, `updated_at`) VALUES
(2, 4, 'ufgfdkgdufhgfdgb', 'ddghfdhfdhfghfghgfhgfhgf', 'done', 'medium', NULL, NULL, NULL, NULL, NULL, NULL, '\"[]\"', '\"[{\\\"color\\\":\\\"#bd0042\\\",\\\"text\\\":\\\"IMP\\\"}]\"', 'task-attachments/5DPRfOYTkIaZUekiw1vgR5gxxJhrBie6SqFINhQh.jpg', '[{\"path\":\"task-attachments\\/5DPRfOYTkIaZUekiw1vgR5gxxJhrBie6SqFINhQh.jpg\",\"name\":\"94d0b90f06cbe7efd2b78ba683634110.jpg\"}]', '2026-04-16 11:04:39', '2026-04-16 11:06:30'),
(3, 4, 'dfhdtshdfhdfh', NULL, 'done', 'medium', NULL, NULL, NULL, NULL, NULL, NULL, '\"[]\"', '\"[]\"', NULL, '[]', '2026-04-16 11:07:25', '2026-04-16 11:07:43');

-- --------------------------------------------------------

--
-- Table structure for table `client_issue_team_assignments`
--

CREATE TABLE `client_issue_team_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_issue_id` bigint(20) UNSIGNED NOT NULL,
  `team_name` varchar(255) NOT NULL,
  `assigned_to` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `assigned_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `client_issue_team_assignments`
--

INSERT INTO `client_issue_team_assignments` (`id`, `client_issue_id`, `team_name`, `assigned_to`, `note`, `assigned_by`, `created_at`, `updated_at`) VALUES
(1, 4, 'Web Team', NULL, NULL, 1, '2026-04-16 11:05:06', '2026-04-16 11:05:06');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
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
  `assigned_manager_id` bigint(20) UNSIGNED DEFAULT NULL,
  `default_due_days` int(11) DEFAULT NULL,
  `billing_type` enum('Hourly','Fixed','Retainer') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `user_id`, `client_name`, `contact_person`, `email`, `role`, `password`, `phone`, `website`, `address_line1`, `address_line2`, `city`, `state`, `postal_code`, `country`, `client_type`, `industry`, `status`, `priority_level`, `assigned_manager_id`, `default_due_days`, `billing_type`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 8, 'Satyam Tiwari', 'Satyam Tiwari', 'satyam@srbcomputers.com', 'Client', '$2y$10$hIPhWd8.GCVEsLUufXvCL.PHt8VuqwIyinoBr8To/l.eHzOvNg4UC', '8828813603', 'https://crackteck.com', 'GROUND, SHOP NO-12, VAGHEAL PADA,', 'RAJIVALI ROAD, SATIVALI, VASAI EAST, Palghar', 'Mumbai', 'Maharashtra', '401208', 'India', 'Company', 'SRBComputers', 'Active', 'Medium', NULL, NULL, NULL, '2026-03-10 11:35:44', '2026-03-10 11:35:44', NULL),
(3, 13, 'Arnav', 'Arnav', 'vkpaints78@gmail.com', 'Client', '$2y$10$aCTgq3IJOHxTzRkO.YAmEOBF6UuiJc4wfVXmwJ9jKqfesQg6DnrpC', '7900104615', 'https://technofra.com/oceanic/', 'J-11 Gurudev Industrial Compound Sonale - 421302', NULL, 'Mumbai', 'Maharashtra', '400067', 'India', 'Individual', 'IT', 'Active', 'High', NULL, NULL, NULL, '2026-03-11 14:53:24', '2026-03-11 14:53:24', NULL),
(7, 22, 'Acme Pvt Ltd', 'Rahul Sharma', 'abc@gmail.com', 'client', '$2y$10$Y1y9pfZJD.500XztMCXrHuomN.aqM9Jr89e8AZCyncmDZJasujWWG', '9876543210', 'https://acme.com', 'Sector 62', 'Tower B', 'Noida', 'Uttar Pradesh', '201309', 'India', 'Company', 'IT Services', 'Active', 'High', NULL, 15, 'Retainer', '2026-04-07 15:38:15', '2026-04-07 17:36:20', '2026-04-07 17:36:20'),
(8, NULL, 'shubham technofra', 'shubham technofra', 'shubhamshinde@gmail.com', 'client', '$2y$10$NgxPE3DvR1CEiYxW4KSeSO0qvfUvVvhUpqwM48XhVjq2.RW0xNrqu', '8850773798', 'https://mycrm.technofra.com/clients', 'fihwiha', 'jnfvan', 'mumbai', 'maharashtra', '400067', 'India', 'Individual', 'it company', 'Active', 'Low', NULL, 10, 'Retainer', '2026-04-07 16:47:40', '2026-04-07 17:36:08', '2026-04-07 17:36:08');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `digital_marketing_leads`
--

CREATE TABLE `digital_marketing_leads` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `company` varchar(150) NOT NULL DEFAULT '',
  `website` varchar(255) NOT NULL DEFAULT '',
  `source_page` varchar(120) NOT NULL DEFAULT 'digitalmarketingad.php',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `digital_marketing_leads`
--

INSERT INTO `digital_marketing_leads` (`id`, `name`, `email`, `phone`, `company`, `website`, `source_page`, `created_at`) VALUES
(1, 'Manish', 'manish@technofra.com', '7039553407', 'Technofra', 'https://technofra.com/oceanic', 'digitalmarketingad.php', '2026-04-10 12:53:49'),
(2, 'Manish', 'support@technofra.com', '7558542831', 'V K Paints', 'https://technofra.com/oceanic/', 'digitalmarketingad.php', '2026-04-11 06:06:00'),
(3, 'Lucifer', 'itwebdeveloper7@gmail.com', '12025550147', 'Delta Electronics (India)', '', 'digitalmarketingad.php', '2026-04-11 09:07:14'),
(4, 'test', 'support@technofra.com', '65765766', '', 'https://wotm.in/', 'digitalmarketingad.php', '2026-04-11 10:05:51');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `zipCode` varchar(255) DEFAULT NULL,
  `lead_value` decimal(10,2) DEFAULT NULL,
  `source` varchar(255) DEFAULT NULL,
  `assigned` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`assigned`)),
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `description` text DEFAULT NULL,
  `status` enum('new','contacted','qualified','converted','lost') NOT NULL DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leads`
--

INSERT INTO `leads` (`id`, `name`, `email`, `phone`, `company`, `position`, `website`, `address`, `city`, `state`, `country`, `zipCode`, `lead_value`, `source`, `assigned`, `tags`, `description`, `status`, `created_at`, `updated_at`) VALUES
(2, 'Devang Sir', NULL, NULL, 'JH Implex & JSK Group', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Website & Branding', 'new', '2026-03-19 13:11:54', '2026-03-19 13:11:54'),
(3, 'Sharvari', NULL, NULL, 'TRAYAKSH ARCHITEQTS', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Website & Trademark', 'new', '2026-03-19 13:12:16', '2026-03-19 13:12:16'),
(4, 'Anand Sir', NULL, NULL, 'Wrap Pack N Carry', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Website & Branding', 'new', '2026-03-19 13:12:38', '2026-03-19 13:12:38'),
(5, 'Priyanshu', NULL, NULL, 'Aeritx', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Trademark & Service Renewal', 'new', '2026-03-19 13:13:02', '2026-03-19 13:13:02'),
(6, 'Pradeep Sir', NULL, NULL, 'Modern Lamination', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, NULL, '[\"5\",\"6\",\"11\"]', NULL, 'Website | Branding & Photography', 'new', '2026-03-19 13:13:27', '2026-03-25 12:03:04'),
(7, 'Amar Sir', NULL, NULL, 'Sukanya India', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Sukanya Maintenace & Whatsapp Marketing', 'new', '2026-03-19 13:13:50', '2026-03-19 13:13:50'),
(8, 'Alpesh', NULL, NULL, 'Kanjalochana Finserve Pvt. Ltd.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Email', 'new', '2026-03-19 13:14:22', '2026-03-19 13:14:22'),
(9, 'Sumeet', NULL, NULL, 'Wotm & Inoivz', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Wotm Shopify Payment & Inoviz Webmail Payment', 'new', '2026-03-19 13:14:42', '2026-03-19 13:14:42'),
(10, 'Arnav', NULL, NULL, 'VK paints (Oceanic)', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Website', 'new', '2026-03-19 13:15:07', '2026-03-19 13:15:07'),
(11, 'Amol Ghogare', NULL, NULL, 'Texon Corporation', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Website Maintenace & APP', 'new', '2026-03-19 13:23:47', '2026-03-19 13:23:47'),
(12, 'Kumar Sir', NULL, NULL, 'Chempharma', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'New Requirement', 'new', '2026-03-19 13:24:35', '2026-03-19 13:24:35'),
(13, 'FriCool India', NULL, NULL, 'FriCool India', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Social Media & SEO', 'new', '2026-03-19 13:25:15', '2026-03-19 13:25:15'),
(14, 'Kahan Mehta', NULL, NULL, 'Rupal Plastic', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Social Media', 'new', '2026-03-19 13:25:50', '2026-03-19 13:25:50'),
(15, 'Raj sharma', NULL, NULL, 'Visitmadhisland', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Property details | Social Media & SEO', 'new', '2026-03-19 13:26:40', '2026-03-19 13:26:40'),
(17, 'Rahul Sharma', 'rahul@example.com', '9876543210', 'Tech Solutions Pvt Ltd', 'Manager', 'https://techsolutions.com', 'Sector 62', 'Noida', 'Uttar Pradesh', 'India', '201309', 50000.00, 'website', '[3,4]', '[\"hot\",\"follow-up\"]', 'Interested in web development services', 'new', '2026-04-11 12:22:37', '2026-04-11 12:22:37'),
(18, 'Rahul Sharma', 'rahul@example.com', '9876543210', 'Tech Solutions Pvt Ltd', 'Manager', 'https://techsolutions.com', 'Sector 62', 'Noida', 'Uttar Pradesh', 'India', '201309', 50000.00, 'website', '[3,4]', '[\"hot\",\"follow-up\"]', 'Interested in web development services', 'new', '2026-04-11 12:22:45', '2026-04-11 12:22:45'),
(19, 'Rahul Sharma', 'rahul@example.com', '9876543210', 'Tech Solutions Pvt Ltd', 'Manager', 'https://techsolutions.com', 'Sector 62', 'Noida', 'Uttar Pradesh', 'India', '201309', 50000.00, 'website', '[3,4]', '[\"hot\",\"follow-up\"]', 'Interested in web development services', 'new', '2026-04-11 12:22:46', '2026-04-11 12:22:46'),
(20, 'Rahul Sharma', 'rahul@example.com', '9876543210', 'Tech Solutions Pvt Ltd', 'Manager', 'https://techsolutions.com', 'Sector 62', 'Noida', 'Uttar Pradesh', 'India', '201309', 50000.00, 'website', '[3,4]', '[\"hot\",\"follow-up\"]', 'Interested in web development services', 'new', '2026-04-11 12:22:47', '2026-04-11 12:22:47'),
(21, 'Rahul Sharma', 'rahul@example.com', '9876543210', 'Tech Solutions Pvt Ltd', 'Manager', 'https://techsolutions.com', 'Sector 62', 'Noida', 'Uttar Pradesh', 'India', '201309', 50000.00, 'website', '[3,4]', '[\"hot\",\"follow-up\"]', 'Interested in web development services', 'new', '2026-04-11 12:22:48', '2026-04-11 12:22:48'),
(22, 'Rahul Sharma', 'rahul@example.com', '9876543210', 'Tech Solutions Pvt Ltd', 'Manager', 'https://techsolutions.com', 'Sector 62', 'Noida', 'Uttar Pradesh', 'India', '201309', 50000.00, 'website', '[3,4]', '[\"hot\",\"follow-up\"]', 'Interested in web development services', 'new', '2026-04-11 12:22:49', '2026-04-11 12:22:49'),
(23, 'Rahul Sharma', 'rahul@example.com', '9876543210', 'Tech Solutions Pvt Ltd', 'Manager', 'https://techsolutions.com', 'Sector 62', 'Noida', 'Uttar Pradesh', 'India', '201309', 50000.00, 'website', '[3,4]', '[\"hot\",\"follow-up\"]', 'Interested in web development services', 'new', '2026-04-11 12:22:50', '2026-04-11 12:22:50');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2025_08_19_062727_create_clients_table', 1),
(6, '2025_08_20_045136_create_vendors_table', 1),
(7, '2025_08_20_052845_create_services_table', 1),
(8, '2025_08_20_061133_add_service_details_to_services_table', 1),
(9, '2025_08_21_072958_create_notification_reads_table', 1),
(10, '2025_08_23_101747_make_address_nullable_in_clients_table', 1),
(11, '2025_09_05_092200_update_vendors_table', 1),
(12, '2025_09_05_093541_update_users_table', 1),
(13, '2025_09_05_095319_update_clients_table', 1),
(14, '2025_09_05_101613_update_services_table', 1),
(15, '2025_09_26_050249_replace_amount_with_billing_date_in_services_table', 2),
(16, '2024_01_01_000001_create_settings_table', 3),
(17, '2024_01_01_000002_create_tags_table', 3),
(18, '2025_12_04_061429_make_client_id_nullable_in_services_table', 3),
(19, '2025_12_04_065045_create_vendor_services_table', 3),
(20, '2025_12_04_102317_add_plan_type_to_vendor_services_table', 3),
(21, '2025_12_04_103935_make_billing_date_nullable_in_vendor_services_table', 3),
(22, '2026_01_16_061137_create_staff_table', 3),
(23, '2026_01_16_061425_remove_fields_from_staff_table', 3),
(24, '2026_01_17_063036_create_calendar_events_table', 3),
(25, '2026_01_17_063053_create_jobs_table', 3),
(26, '2026_01_17_100000_add_whatsapp_fields_to_calendar_events', 3),
(27, '2026_01_29_041246_create_customers_table', 3),
(28, '2026_01_29_060408_create_projects_table', 3),
(29, '2026_01_29_073311_add_priority_and_technologies_to_projects_table', 3),
(30, '2026_01_29_100223_create_tasks_table', 3),
(31, '2026_01_29_104904_create_task_attachments_table', 3),
(32, '2026_01_29_113447_create_permission_tables', 3),
(33, '2026_01_31_120000_create_leads_table', 3),
(34, '2026_01_31_120001_add_role_password_to_customers_table', 3),
(35, '2026_02_02_070000_add_user_id_to_staff_and_customers_tables', 3),
(36, '2026_02_03_070000_create_client_issues_table', 3),
(37, '2026_02_04_053732_create_client_issue_tasks_table', 3),
(38, '2026_02_05_111200_add_due_date_to_client_issue_tasks_table', 3),
(39, '2026_02_07_120000_add_attachments_to_client_issue_tasks_table', 3),
(40, '2026_02_09_120000_add_team_to_staff_table', 3),
(41, '2026_02_09_130000_create_client_issue_team_assignments_table', 3),
(42, '2026_02_09_140000_add_assigned_to_to_client_issue_team_assignments_table', 3),
(43, '2026_02_11_101500_create_project_status_logs_table', 3),
(44, '2026_02_11_120000_create_project_files_table', 3),
(45, '2026_02_11_150000_create_project_milestones_table', 3),
(46, '2026_02_12_130709_create_task_comments_table', 3),
(47, '2026_02_13_120000_create_project_comments_table', 3),
(48, '2026_02_17_000000_create_teams_table', 3),
(49, '2026_02_17_010000_add_description_and_icon_to_teams_table', 3),
(50, '2026_03_06_000000_create_project_issues_table', 3),
(51, '2026_03_09_120000_make_project_id_nullable_in_tasks_table', 4),
(52, '2026_03_10_130000_make_customer_id_nullable_in_projects_table', 5),
(53, '2026_03_12_120000_add_remark_fields_to_services_table', 6),
(54, '2026_03_12_130000_add_view_calendar_permission', 6),
(55, '2026_03_12_140000_add_view_dashboard_welcome_permission', 6),
(56, '2026_03_14_140000_create_todos_table', 7),
(57, '2026_03_26_000001_create_departments_table', 8),
(58, '2026_03_26_120000_add_deleted_at_to_staff_table', 8),
(59, '2026_03_27_090000_create_bookcall_table', 9),
(60, '2026_03_27_090100_add_book_call_permission', 9),
(61, '2026_04_02_000000_add_meeting_agenda_to_bookcall_table', 10),
(62, '2026_04_07_000000_add_deleted_at_to_customers_table', 11),
(63, '2026_04_10_000000_create_digital_marketing_leads_table', 12),
(64, '2026_04_11_000000_add_digital_marketing_leads_permissions', 13),
(65, '2026_04_13_113818_update_roles_table', 14),
(66, '2026_04_14_000001_add_attachments_to_todos_table', 15),
(67, '2026_04_15_112122_add_delete_at_to_projects_table', 16),
(68, '2026_04_15_164200_add_deleted_at_to_tasks_table', 16);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 5),
(2, 'App\\Models\\User', 6),
(2, 'App\\Models\\User', 7),
(3, 'App\\Models\\User', 8),
(2, 'App\\Models\\User', 9),
(2, 'App\\Models\\User', 11),
(1, 'App\\Models\\User', 12),
(3, 'App\\Models\\User', 13),
(2, 'App\\Models\\User', 17),
(2, 'App\\Models\\User', 18),
(3, 'App\\Models\\User', 22),
(3, 'App\\Models\\User', 23);

-- --------------------------------------------------------

--
-- Table structure for table `notification_reads`
--

CREATE TABLE `notification_reads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `service_id` bigint(20) UNSIGNED NOT NULL,
  `notification_type` varchar(255) NOT NULL,
  `read_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'view_renewals', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(2, 'create_renewals', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(3, 'edit_renewals', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(4, 'delete_renewals', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(5, 'view_leads', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(6, 'create_leads', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(7, 'edit_leads', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(8, 'delete_leads', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(9, 'view_projects', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(10, 'create_projects', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(11, 'edit_projects', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(12, 'delete_projects', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(13, 'view_tasks', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(14, 'create_tasks', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(15, 'edit_tasks', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(16, 'delete_tasks', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(17, 'view_raise_issue', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(18, 'create_raise_issue', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(19, 'edit_raise_issue', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(20, 'delete_raise_issue', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(21, 'view_clients', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(22, 'create_clients', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(23, 'edit_clients', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(24, 'delete_clients', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(25, 'view_staff', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(26, 'create_staff', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(27, 'edit_staff', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(28, 'delete_staff', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(29, 'view_roles', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(30, 'create_roles', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(31, 'edit_roles', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(32, 'delete_roles', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(33, 'view_permissions', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(34, 'create_permissions', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(35, 'edit_permissions', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(36, 'delete_permissions', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(37, 'view_services', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(38, 'create_services', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(39, 'edit_services', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(40, 'delete_services', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(41, 'view_vendors', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(42, 'create_vendors', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(43, 'edit_vendors', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(44, 'delete_vendors', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(45, 'view_dashboard', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(46, 'create_dashboard', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(47, 'edit_dashboard', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(48, 'delete_dashboard', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(49, 'manage_users', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(50, 'manage_settings', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(51, 'view_general_settings', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(52, 'view_company_information', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(53, 'view_email_settings', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(54, 'view_reports', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(55, 'export_data', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(56, 'import_data', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(57, 'send_notifications', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(58, 'manage_calendar', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(59, 'view_all_projects', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(60, 'view_own_projects', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(61, 'assign_tasks', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(62, 'view_all_tasks', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(63, 'view_own_tasks', 'web', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(64, 'view_calendar', 'web', '2026-03-13 15:18:57', '2026-03-13 15:18:57'),
(65, 'view_dashboard_welcome', 'web', '2026-03-13 15:18:57', '2026-03-13 15:18:57'),
(66, 'view_book_calls', 'web', '2026-03-27 18:09:16', '2026-03-27 18:09:16'),
(67, 'view_digital_marketing_leads', 'web', NULL, NULL),
(68, 'create_digital_marketing_leads', 'web', NULL, NULL),
(69, 'edit_digital_marketing_leads', 'web', NULL, NULL),
(70, 'delete_digital_marketing_leads', 'web', NULL, NULL),
(71, 'create_book_calls', 'web', NULL, NULL),
(72, 'edit_book_calls', 'web', NULL, NULL),
(73, 'delete_book_calls', 'web', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(2, 'App\\Models\\User', 1, 'flutter-app', '4a595a0d91dd296fc625dcbc40361c48515fff87ca044f69c897410535693919', '[\"*\"]', '2026-03-18 14:49:18', NULL, '2026-03-18 13:17:39', '2026-03-18 14:49:18'),
(3, 'App\\Models\\User', 1, 'flutter-app', '897c4901ef51388ac9b2095cf2baae1177f4b484debc542f1b57f0aaa2e06501', '[\"*\"]', NULL, NULL, '2026-03-18 14:46:33', '2026-03-18 14:46:33'),
(4, 'App\\Models\\User', 1, 'flutter-app', '36e47beeb6d070d0a506a565d2b0fb0537e729a6d45b997a6925f0c86cf6b21f', '[\"*\"]', NULL, NULL, '2026-03-18 14:59:04', '2026-03-18 14:59:04'),
(5, 'App\\Models\\User', 1, 'flutter-app', '268844a36803d2fbf1598c39891638c240458f681aa01b61db4072d51d8da5d6', '[\"*\"]', NULL, NULL, '2026-03-18 15:03:21', '2026-03-18 15:03:21'),
(6, 'App\\Models\\User', 1, 'flutter-app', 'affe4bec58aca311f99cf1dc8575e75191a4e8c02b388f0dd78fa12172e1affb', '[\"*\"]', NULL, NULL, '2026-03-18 15:29:03', '2026-03-18 15:29:03'),
(8, 'App\\Models\\User', 1, 'flutter-app', '8c215737f3633fc9fe58900726a549f9d6f66ffc4667e25a3d5bce570c2f11bb', '[\"*\"]', '2026-03-20 10:11:27', NULL, '2026-03-20 10:11:25', '2026-03-20 10:11:27'),
(9, 'App\\Models\\User', 1, 'flutter-app', 'ab763fcc29ef020807273ff4b0e0494183928e383641508053f9a1eaa9d4406d', '[\"*\"]', '2026-03-20 10:22:25', NULL, '2026-03-20 10:14:10', '2026-03-20 10:22:25'),
(10, 'App\\Models\\User', 1, 'flutter-app', '60a8c5c1d7005656582bfab2789e63af999a9401eb3e316861f82c77ecc79caa', '[\"*\"]', '2026-03-20 11:56:32', NULL, '2026-03-20 10:31:27', '2026-03-20 11:56:32'),
(11, 'App\\Models\\User', 1, 'flutter-app', '554ce8784be53303b7a7053e884d2a7cfdad84bb817a1537c5aa5a9c9f58cc97', '[\"*\"]', '2026-03-20 13:13:58', NULL, '2026-03-20 12:39:29', '2026-03-20 13:13:58'),
(12, 'App\\Models\\User', 1, 'flutter-app', 'ea2aefd09a2f36670657cea3875932b1d2a8afb8fda811606441dd8bcf6d88e9', '[\"*\"]', '2026-03-20 13:16:41', NULL, '2026-03-20 13:16:39', '2026-03-20 13:16:41'),
(13, 'App\\Models\\User', 1, 'flutter-app', '485f8ac2ee01498a0f1c854723d255634693d5539590d1e9e4329aa3202a085a', '[\"*\"]', '2026-03-20 13:24:49', NULL, '2026-03-20 13:24:47', '2026-03-20 13:24:49'),
(14, 'App\\Models\\User', 1, 'flutter-app', 'fec6bd421199462591e82d3200e030b6b43d6ab7e22e40d91a0340952171395b', '[\"*\"]', '2026-03-20 13:29:43', NULL, '2026-03-20 13:29:41', '2026-03-20 13:29:43'),
(15, 'App\\Models\\User', 1, 'flutter-app', 'bd02b9a5a2a2005f7bac092ed6adc567fa272226e9f96da8f08c557bf7ab08bf', '[\"*\"]', '2026-03-20 17:17:24', NULL, '2026-03-20 15:51:25', '2026-03-20 17:17:24'),
(16, 'App\\Models\\User', 1, 'flutter-app', 'e2945a121d6660cbaac91929abb25ae63d4904a09701c0b9cc63349f01eedef2', '[\"*\"]', NULL, NULL, '2026-03-27 10:04:36', '2026-03-27 10:04:36'),
(17, 'App\\Models\\User', 1, 'flutter-app', '8435e05716dfbbcf3f0950352b8880fd7068b348080b0dc38cf33ac67cadbea9', '[\"*\"]', NULL, NULL, '2026-03-27 10:04:40', '2026-03-27 10:04:40'),
(18, 'App\\Models\\User', 1, 'flutter-app', '3131ffe803491ee104f226fea1bec4167228b67e4a760f326df966bd673a156b', '[\"*\"]', '2026-03-27 10:15:12', NULL, '2026-03-27 10:15:11', '2026-03-27 10:15:12'),
(19, 'App\\Models\\User', 1, 'flutter-app', 'e751780032dca23959c34d793f27c594b19ff73cd8632c76922f32767e6e340e', '[\"*\"]', '2026-03-27 13:04:25', NULL, '2026-03-27 10:30:19', '2026-03-27 13:04:25'),
(20, 'App\\Models\\User', 1, 'flutter-app', '2d2d3caf9bb02208ead30b246804fa1d16ff5066754f3b38797bb0bcd4fbc797', '[\"*\"]', '2026-03-27 11:47:18', NULL, '2026-03-27 11:47:16', '2026-03-27 11:47:18'),
(21, 'App\\Models\\User', 1, 'flutter-app', 'b2737ff7c074fcdc08e999700a9587df0dcc624826877373c3a991008785303a', '[\"*\"]', '2026-03-27 11:50:38', NULL, '2026-03-27 11:50:37', '2026-03-27 11:50:38'),
(22, 'App\\Models\\User', 1, 'flutter-app', 'a9c45be046f837e2144a1169a8c0e38c15e69854f42661240a1e509ef688f7a1', '[\"*\"]', '2026-03-27 12:10:21', NULL, '2026-03-27 12:10:18', '2026-03-27 12:10:21'),
(23, 'App\\Models\\User', 1, 'flutter-app', '2c409ea78e4f637ad6689bd5d4e14d725bda0c72ab0f943443e8a694e03ee7a5', '[\"*\"]', '2026-03-27 12:26:05', NULL, '2026-03-27 12:20:18', '2026-03-27 12:26:05'),
(24, 'App\\Models\\User', 1, 'flutter-app', '8a98a6118e1393d64692facaeae9078416272019621c18e2b6bf3d7a38f38a95', '[\"*\"]', '2026-03-27 12:56:55', NULL, '2026-03-27 12:54:59', '2026-03-27 12:56:55'),
(25, 'App\\Models\\User', 1, 'flutter-app', 'f4007e52f5c656d6cc218c192d72d7276c7d2d9f8e39356264c072fd524aa96b', '[\"*\"]', '2026-03-27 13:13:30', NULL, '2026-03-27 13:13:22', '2026-03-27 13:13:30'),
(26, 'App\\Models\\User', 1, 'flutter-app', '91f115da9939c21314ee64ffd4ccd52754be9771427641ef39c60a5fe515bac0', '[\"*\"]', '2026-03-27 13:37:43', NULL, '2026-03-27 13:23:43', '2026-03-27 13:37:43'),
(27, 'App\\Models\\User', 1, 'flutter-app', 'eb766701439d1dbcb9c79d12aaa16ef2e263d626fb638bba906e3e8a47694d78', '[\"*\"]', '2026-03-27 15:04:14', NULL, '2026-03-27 14:46:41', '2026-03-27 15:04:14'),
(28, 'App\\Models\\User', 1, 'flutter-app', '38bf4e749f303ee63da4b1eeac8f1d6795ed85144107653b036738349da8f7ee', '[\"*\"]', '2026-03-27 15:17:18', NULL, '2026-03-27 15:16:12', '2026-03-27 15:17:18'),
(29, 'App\\Models\\User', 1, 'flutter-app', 'a36a6d8a125a8111f63927d719d04f482d204b4b7a4b2be239a5d0a9e25bbf41', '[\"*\"]', '2026-04-07 16:16:19', NULL, '2026-04-07 14:48:31', '2026-04-07 16:16:19'),
(30, 'App\\Models\\User', 1, 'flutter-app', 'a29bbb085f5dee551b5fc05e48baae657d9a6a22ddd1f43071e3fba8f1234967', '[\"*\"]', NULL, NULL, '2026-04-07 15:10:34', '2026-04-07 15:10:34'),
(31, 'App\\Models\\User', 1, 'flutter-app', '5370e3b75580fa572ac5eef48832365e1d5ba99611b5957485d006911a7adb9c', '[\"*\"]', '2026-04-07 16:25:58', NULL, '2026-04-07 15:10:35', '2026-04-07 16:25:58'),
(32, 'App\\Models\\User', 1, 'flutter-app', 'a86d272a3ff8fbccf3163228936f5424035d55f85374e081d38ab913f972765c', '[\"*\"]', '2026-04-07 17:18:56', NULL, '2026-04-07 16:17:41', '2026-04-07 17:18:56'),
(33, 'App\\Models\\User', 1, 'flutter-app', 'a074f4d45e33a582e591ae6903764790058efc7f4e5f197c3f76e6506634325a', '[\"*\"]', '2026-04-07 17:23:53', NULL, '2026-04-07 17:23:43', '2026-04-07 17:23:53'),
(34, 'App\\Models\\User', 1, 'flutter-app', '459e5b59c644383769b3b8f4a076ad1d23a4a65a35481989053ecacf937e5864', '[\"*\"]', '2026-04-07 17:27:02', NULL, '2026-04-07 17:26:54', '2026-04-07 17:27:02'),
(35, 'App\\Models\\User', 1, 'flutter-app', '9f964a3e5174d9782077d6e8a4bf08ee4b1d5e0d746ee86087945de4c2410e85', '[\"*\"]', '2026-04-07 17:36:20', NULL, '2026-04-07 17:29:03', '2026-04-07 17:36:20'),
(36, 'App\\Models\\User', 1, 'flutter-app', '2c93cfd67269c459918b502aad21a628b73a018cb96e7716023b8669394b94ce', '[\"*\"]', '2026-04-07 18:32:14', NULL, '2026-04-07 18:31:24', '2026-04-07 18:32:14'),
(37, 'App\\Models\\User', 1, 'flutter-app', '226edb4d769e5ef20adb2d99b500a755ff961211de36d8d9c7c774534560343d', '[\"*\"]', '2026-04-09 17:32:29', NULL, '2026-04-09 16:14:58', '2026-04-09 17:32:29'),
(38, 'App\\Models\\User', 5, 'flutter-app', '031352eced6398f95ba0b47eb7ecd3726c97fbcce2e245286cf8d89e807f0683', '[\"*\"]', '2026-04-09 16:27:51', NULL, '2026-04-09 16:19:17', '2026-04-09 16:27:51'),
(39, 'App\\Models\\User', 5, 'flutter-app', 'fc0d729d73becd5352d7e02e6dd350c049f986badb940e594ed8f26218af22d1', '[\"*\"]', '2026-04-09 16:38:39', NULL, '2026-04-09 16:28:45', '2026-04-09 16:38:39'),
(40, 'App\\Models\\User', 5, 'flutter-app', 'dbf54f86e4df307a3a6a80029475681d5c7d3278da59e331bd831b0c3b8c4514', '[\"*\"]', '2026-04-09 16:41:48', NULL, '2026-04-09 16:41:14', '2026-04-09 16:41:48'),
(41, 'App\\Models\\User', 5, 'flutter-app', 'f9eb5c81838121c5f566655597b47224103f2c814218301dcb572db168f772f8', '[\"*\"]', '2026-04-09 17:03:28', NULL, '2026-04-09 17:03:14', '2026-04-09 17:03:28'),
(42, 'App\\Models\\User', 5, 'flutter-app', 'c1d85562be9d9968d0705f607b34f620ac00f3d76383cc25bf02ecc1d2e60aba', '[\"*\"]', NULL, NULL, '2026-04-09 17:31:03', '2026-04-09 17:31:03'),
(43, 'App\\Models\\User', 5, 'flutter-app', '61f573fa2d87064daa297587040a0c53756b61be4decd535e072f8c7f8775c0c', '[\"*\"]', '2026-04-10 09:50:43', NULL, '2026-04-10 09:50:03', '2026-04-10 09:50:43'),
(44, 'App\\Models\\User', 5, 'flutter-app', '52bc7b39fc90879d38d051b88099181d34806e1c746613b64218a18323e943c0', '[\"*\"]', '2026-04-10 09:52:45', NULL, '2026-04-10 09:52:16', '2026-04-10 09:52:45'),
(45, 'App\\Models\\User', 1, 'flutter-app', 'c48e5faa2ecbb2b25f37c4bb8b5d62aee3b22c56ff0da93db0d2e6c151af6c7e', '[\"*\"]', '2026-04-10 09:55:48', NULL, '2026-04-10 09:55:29', '2026-04-10 09:55:48'),
(46, 'App\\Models\\User', 1, 'flutter-app', '0c4c0f23561136bfac48c34e82bd5f161a3c9fc218ca306cc8c693011264da00', '[\"*\"]', '2026-04-10 18:08:11', NULL, '2026-04-10 16:43:51', '2026-04-10 18:08:11'),
(47, 'App\\Models\\User', 1, 'flutter-app', '5c6fe03b96e3e4e26f6b2079dd284ea43dfc6ef1ff6022a26b8c5609efc98979', '[\"*\"]', '2026-04-10 16:44:58', NULL, '2026-04-10 16:44:57', '2026-04-10 16:44:58'),
(48, 'App\\Models\\User', 1, 'flutter-app', '7928bf4d853616d4b204d45556dfe288a4dd254f71d0b259851e16e30c8514e1', '[\"*\"]', '2026-04-10 17:54:57', NULL, '2026-04-10 17:29:04', '2026-04-10 17:54:57'),
(49, 'App\\Models\\User', 1, 'flutter-app', '3508080ba4f8488c30915f3ca31a16c4e2bec79d79a86d08aa80de0de0a42507', '[\"*\"]', '2026-04-10 17:59:01', NULL, '2026-04-10 17:58:30', '2026-04-10 17:59:01'),
(50, 'App\\Models\\User', 1, 'flutter-app', 'ae97c66ad7f75b9b0d441b8dcdafeb30b1f069e84a19424578a8339b97751395', '[\"*\"]', '2026-04-10 18:25:02', NULL, '2026-04-10 18:24:55', '2026-04-10 18:25:02'),
(51, 'App\\Models\\User', 1, 'flutter-app', 'f87560bbe250539f56cb4df5486711df1b1c1bfa09947aacb763bd19f9d12abe', '[\"*\"]', '2026-04-10 18:39:36', NULL, '2026-04-10 18:36:20', '2026-04-10 18:39:36'),
(52, 'App\\Models\\User', 1, 'flutter-app', 'ed0de8d997a17264d7322f97e71922d6854afdca85f0110a99312b69254939fa', '[\"*\"]', '2026-04-11 16:52:01', NULL, '2026-04-11 09:43:53', '2026-04-11 16:52:01'),
(53, 'App\\Models\\User', 1, 'flutter-app', '2d24e012192c9729c84d1b9d4c1894f7bf1e406ae8a65d67d41187d7b5a8194c', '[\"*\"]', '2026-04-11 10:01:23', NULL, '2026-04-11 09:50:07', '2026-04-11 10:01:23'),
(54, 'App\\Models\\User', 1, 'flutter-app', '853d683ef51aba6b9b768040102c660cca7c3c734454da54cd66c5967918d6ef', '[\"*\"]', '2026-04-11 12:02:52', NULL, '2026-04-11 10:02:41', '2026-04-11 12:02:52'),
(55, 'App\\Models\\User', 1, 'flutter-app', 'a004e4f20c055b7d22d9359e1fdd3a6be330b53139f0cf54a55a960b21999f7d', '[\"*\"]', '2026-04-11 12:19:40', NULL, '2026-04-11 12:06:07', '2026-04-11 12:19:40'),
(56, 'App\\Models\\User', 1, 'flutter-app', '884486f25e037e60bb78d7daeed1201c03d02e94c4fe3a98d9463c440c2d27ef', '[\"*\"]', '2026-04-11 12:28:03', NULL, '2026-04-11 12:26:38', '2026-04-11 12:28:03'),
(57, 'App\\Models\\User', 1, 'flutter-app', '21d0946c1b2566417c2ffc4a8f47a39bd297abb43bbfe526b8bcf455bd2171a6', '[\"*\"]', '2026-04-11 13:00:42', NULL, '2026-04-11 12:50:29', '2026-04-11 13:00:42'),
(58, 'App\\Models\\User', 1, 'flutter-app', 'e898044d57620f91bb728c0f6f451f489a41dbec2b3be02eb4eca696587258cb', '[\"*\"]', '2026-04-11 14:11:03', NULL, '2026-04-11 14:03:03', '2026-04-11 14:11:03'),
(59, 'App\\Models\\User', 1, 'flutter-app', '7085b3b3843d85122488a881c7c227beeb9f6791a2ec0c77501182268defc6d9', '[\"*\"]', '2026-04-11 14:26:36', NULL, '2026-04-11 14:24:38', '2026-04-11 14:26:36'),
(60, 'App\\Models\\User', 1, 'flutter-app', '9883a9e6f8326af022f8c9173690dcefc9023358dd13ee005fed9a67d1002a5f', '[\"*\"]', '2026-04-11 15:29:32', NULL, '2026-04-11 15:17:29', '2026-04-11 15:29:32'),
(61, 'App\\Models\\User', 1, 'flutter-app', '82c6ea8c6932b35611dce814449bdff184e31c751d789ad5c833ec065adb6ac2', '[\"*\"]', '2026-04-11 15:47:33', NULL, '2026-04-11 15:47:32', '2026-04-11 15:47:33'),
(62, 'App\\Models\\User', 5, 'flutter-app', '5130605f58770731d80387bc27c02a99b0da36233df28c4c0e978bab08a6b8a8', '[\"*\"]', NULL, NULL, '2026-04-11 16:20:52', '2026-04-11 16:20:52'),
(63, 'App\\Models\\User', 5, 'flutter-app', '72b5e021d0580a7b7b0892d609cca62ff897132381db5b4a351c4c9323dd5fbe', '[\"*\"]', NULL, NULL, '2026-04-11 16:24:43', '2026-04-11 16:24:43'),
(64, 'App\\Models\\User', 5, 'flutter-app', '3ecdac7c490a301d52047f2851e27d2ccf0365283c428892a8499faaf015ffb5', '[\"*\"]', NULL, NULL, '2026-04-11 16:24:49', '2026-04-11 16:24:49'),
(65, 'App\\Models\\User', 1, 'flutter-app', '81eea1a10d2b972a020bc19602ae8876e705b0052aa02dcfc1bb3e99e4e028af', '[\"*\"]', NULL, NULL, '2026-04-11 16:25:44', '2026-04-11 16:25:44'),
(66, 'App\\Models\\User', 1, 'flutter-app', 'e0151c13a1938ee986330667af116f0ff96b56477c252e9c479a2dff993d4da9', '[\"*\"]', '2026-04-11 16:50:36', NULL, '2026-04-11 16:26:20', '2026-04-11 16:50:36'),
(67, 'App\\Models\\User', 5, 'flutter-app', 'f017073f726c4df0835978069cf744746cc352f07a726e321b8fd1acefc2ee49', '[\"*\"]', NULL, NULL, '2026-04-11 16:37:21', '2026-04-11 16:37:21'),
(68, 'App\\Models\\User', 12, 'flutter-app', 'aa4d2012b6497d6ac31a135a7ffff079b6e3b0f4c96515f05046386b6c63381a', '[\"*\"]', NULL, NULL, '2026-04-11 16:41:43', '2026-04-11 16:41:43'),
(69, 'App\\Models\\User', 1, 'flutter-app', '77351602fdca31b7737628bbd970d6e9f9fdbcb62e2552a00f37ee94df4b6e6e', '[\"*\"]', NULL, NULL, '2026-04-11 16:43:24', '2026-04-11 16:43:24'),
(70, 'App\\Models\\User', 1, 'flutter-app', '35005322389689d5451f7aa88d2e28e4cfd3b5568df6c43b3ae5ca81a8ba7c78', '[\"*\"]', '2026-04-11 17:00:41', NULL, '2026-04-11 16:46:46', '2026-04-11 17:00:41'),
(71, 'App\\Models\\User', 5, 'flutter-app', 'd50aea2f0a77d6ed8d8bc63229bb428d55adb96a72d1072ab195d2aaf9c7322a', '[\"*\"]', '2026-04-11 17:57:46', NULL, '2026-04-11 16:53:45', '2026-04-11 17:57:46'),
(72, 'App\\Models\\User', 1, 'flutter-app', '5f075aba4ed25af6054c8831eb8de712b82f3c064c8919f617d0db7fd3dafd87', '[\"*\"]', '2026-04-11 17:21:06', NULL, '2026-04-11 17:10:39', '2026-04-11 17:21:06'),
(73, 'App\\Models\\User', 1, 'flutter-app', '6c33b3581623b7bf05fe2be1f7a3a44e37393732aadc86b2ea6b283f0cdda09b', '[\"*\"]', NULL, NULL, '2026-04-11 17:28:00', '2026-04-11 17:28:00'),
(74, 'App\\Models\\User', 1, 'flutter-app', 'e16841c82aecb7ac0285e20b9e811dbe4b4e0e9f0fba66943298e12ca7da0c5c', '[\"*\"]', '2026-04-11 17:29:18', NULL, '2026-04-11 17:29:12', '2026-04-11 17:29:18'),
(75, 'App\\Models\\User', 5, 'flutter-app', 'c784c449fc879bfdba46a425458bf974861f022845bce3be10954a8829550ad9', '[\"*\"]', NULL, NULL, '2026-04-11 17:30:17', '2026-04-11 17:30:17'),
(76, 'App\\Models\\User', 5, 'flutter-app', '765b95cd635749c7697d130494c00e197250719a9030c3ceae4daa615caf77a0', '[\"*\"]', '2026-04-13 12:34:29', NULL, '2026-04-11 17:42:59', '2026-04-13 12:34:29'),
(77, 'App\\Models\\User', 1, 'flutter-app', '7ee22d6bb857f8960dee24410f842e7990a0ebbc47847726b96adfbff6d51cd3', '[\"*\"]', '2026-04-11 17:53:06', NULL, '2026-04-11 17:48:57', '2026-04-11 17:53:06'),
(78, 'App\\Models\\User', 1, 'flutter-app', '6dbcff2edf9098f23177b896a2981cee7a6d0be7bcc0ebd7b1e42bfaecc5661f', '[\"*\"]', '2026-04-13 13:02:19', NULL, '2026-04-11 17:58:22', '2026-04-13 13:02:19'),
(82, 'App\\Models\\User', 7, 'flutter-app', '826c69fa798b844ed8f1385f33753819f624e85a90d1be2cfcb72267f4be955c', '[\"*\"]', '2026-04-16 09:43:52', NULL, '2026-04-11 18:57:11', '2026-04-16 09:43:52'),
(83, 'App\\Models\\User', 12, 'flutter-app', '52037badd19f9f434fbe28820a5958acb8d127578466f9d3ce91dd1738325a9c', '[\"*\"]', '2026-04-11 18:58:45', NULL, '2026-04-11 18:58:45', '2026-04-11 18:58:45'),
(84, 'App\\Models\\User', 12, 'flutter-app', 'b8558354593a786dc2d8b1ca784f70724b6640a48f7feb6849ccc3d16e596d3a', '[\"*\"]', '2026-04-11 19:01:26', NULL, '2026-04-11 18:59:30', '2026-04-11 19:01:26'),
(85, 'App\\Models\\User', 1, 'flutter-app', '2f5cac2b8508b0083f85296602f5051b2e2e7d6d0063fd530a9c4d18fa8545dd', '[\"*\"]', '2026-04-13 09:39:28', NULL, '2026-04-13 09:33:51', '2026-04-13 09:39:28'),
(86, 'App\\Models\\User', 1, 'flutter-app', '0a005d5fda4616b57a4a8d01c600818ea1645ebbe8a065b3c5c536cca363f5f1', '[\"*\"]', '2026-04-13 10:55:30', NULL, '2026-04-13 10:55:19', '2026-04-13 10:55:30'),
(87, 'App\\Models\\User', 1, 'flutter-app', 'bb0642dec697aee273001d1d9428ab5773158e248cde4dd2d30e0ad8ab95158e', '[\"*\"]', '2026-04-13 11:09:29', NULL, '2026-04-13 11:07:21', '2026-04-13 11:09:29'),
(88, 'App\\Models\\User', 1, 'flutter-app', '84f4137a843a0f8f2ebced1b3ad23782a0120b3bfa2e329480ac11f7ed0bc3da', '[\"*\"]', '2026-04-13 11:21:05', NULL, '2026-04-13 11:15:02', '2026-04-13 11:21:05'),
(89, 'App\\Models\\User', 1, 'flutter-app', '7fca159110bbf6e5ebc77913fb7cb7cd5d6c1f04f7c3fd8c5e17975c32939a23', '[\"*\"]', '2026-04-13 11:41:50', NULL, '2026-04-13 11:21:59', '2026-04-13 11:41:50'),
(90, 'App\\Models\\User', 1, 'flutter-app', '533cadc55c0e22c81bce60ea8cafbfd7ea9950310426364206700c1ad3396747', '[\"*\"]', '2026-04-13 11:44:59', NULL, '2026-04-13 11:44:12', '2026-04-13 11:44:59'),
(91, 'App\\Models\\User', 1, 'flutter-app', '25191205c4cf277ccc3e46b3981dfe0ad06e36a861c6e1d724117259a6f74726', '[\"*\"]', '2026-04-13 11:45:41', NULL, '2026-04-13 11:45:26', '2026-04-13 11:45:41'),
(92, 'App\\Models\\User', 1, 'flutter-app', '0d67c3a481e579cfe0a1d1b236ce160037fd1eddb05978f6c51102cdd61897d3', '[\"*\"]', '2026-04-13 12:12:17', NULL, '2026-04-13 12:00:21', '2026-04-13 12:12:17'),
(93, 'App\\Models\\User', 1, 'flutter-app', '73dc69d1af639fb654aa0374e3f2189639558e4b0dcdfb95eb739dfff42e1430', '[\"*\"]', '2026-04-13 12:32:08', NULL, '2026-04-13 12:13:18', '2026-04-13 12:32:08'),
(94, 'App\\Models\\User', 1, 'flutter-app', '549319f68e74cc99acc82892917624797e6661f6a3790cf64c2d33351f3c863c', '[\"*\"]', '2026-04-14 15:34:08', NULL, '2026-04-13 12:34:50', '2026-04-14 15:34:08'),
(95, 'App\\Models\\User', 1, 'flutter-app', '5eb6ff3a819bb82729feaf71e3e560a4515be99d4f24cefd618f93d637d5c019', '[\"*\"]', '2026-04-13 12:50:55', NULL, '2026-04-13 12:50:34', '2026-04-13 12:50:55'),
(96, 'App\\Models\\User', 1, 'flutter-app', '002c87f5913a122c79eecbd4f83451cf125d13b67812cd1355dac0a6c285040b', '[\"*\"]', '2026-04-13 13:31:32', NULL, '2026-04-13 13:01:33', '2026-04-13 13:31:32'),
(97, 'App\\Models\\User', 1, 'flutter-app', '14b67e8b57870dbc41a97b0e68a294cbea1569a93cad4b03aa49f6567d14c5fc', '[\"*\"]', '2026-04-16 10:56:05', NULL, '2026-04-13 14:48:30', '2026-04-16 10:56:05'),
(98, 'App\\Models\\User', 1, 'flutter-app', '859b55175c715fd2833494a748b7ab545b5feda4a8d399cd6867ca6583854a97', '[\"*\"]', '2026-04-13 15:57:04', NULL, '2026-04-13 15:20:41', '2026-04-13 15:57:04'),
(99, 'App\\Models\\User', 1, 'flutter-app', '273340c3c4a8d5f48888277ce883014c3b9428b7c3f25ad3906c6e8fb8b4954d', '[\"*\"]', '2026-04-13 16:52:52', NULL, '2026-04-13 16:06:50', '2026-04-13 16:52:52'),
(100, 'App\\Models\\User', 1, 'flutter-app', 'aadc252361b4fb188d61ae2931832815658fb3eb5c9efacbeca5eb0a0e7f9f13', '[\"*\"]', '2026-04-13 17:00:24', NULL, '2026-04-13 16:55:19', '2026-04-13 17:00:24'),
(101, 'App\\Models\\User', 1, 'flutter-app', 'b750f0814cd549f750a145b7109d8f2c605a74c33a18cc07740a097e9e3afaab', '[\"*\"]', '2026-04-13 17:13:00', NULL, '2026-04-13 17:12:39', '2026-04-13 17:13:00'),
(102, 'App\\Models\\User', 1, 'flutter-app', '607932192da8010ac914576c54a0099a35cb3d9064c5d89d5d1927e6b1637df7', '[\"*\"]', '2026-04-13 17:33:30', NULL, '2026-04-13 17:27:49', '2026-04-13 17:33:30'),
(103, 'App\\Models\\User', 1, 'flutter-app', '8f3b83e4ba5ef4cc558f2d384e7626d6b875c2c4573147fc9258577ebfc3d564', '[\"*\"]', '2026-04-13 17:48:01', NULL, '2026-04-13 17:47:28', '2026-04-13 17:48:01'),
(104, 'App\\Models\\User', 1, 'flutter-app', 'fa95e6aea0515e64496180e860b998753a781f578546516c9b29cb0ef86f3be0', '[\"*\"]', '2026-04-13 18:13:25', NULL, '2026-04-13 18:04:28', '2026-04-13 18:13:25'),
(105, 'App\\Models\\User', 1, 'flutter-app', '61ca9162c786514489dd19ecc1e698c9e5a21a02874d963a768d03cb47ec817d', '[\"*\"]', '2026-04-14 12:57:54', NULL, '2026-04-14 12:57:53', '2026-04-14 12:57:54'),
(106, 'App\\Models\\User', 1, 'flutter-app', '8cf598993ebb5c86ab224a377fede07aef654b3a3ad2ac9edc45af2f90a61e08', '[\"*\"]', '2026-04-14 13:21:33', NULL, '2026-04-14 13:14:32', '2026-04-14 13:21:33'),
(107, 'App\\Models\\User', 1, 'flutter-app', '3a75ed2f4ee13455008549f50f7239fbcc5286c3ec399635f631d3b71c9bc699', '[\"*\"]', '2026-04-14 13:24:30', NULL, '2026-04-14 13:24:29', '2026-04-14 13:24:30'),
(108, 'App\\Models\\User', 1, 'flutter-app', 'fca80bf86f5146f222a6a58a89d6024ddcea9c3a7560beed14dba752cec9b103', '[\"*\"]', '2026-04-14 14:42:43', NULL, '2026-04-14 14:42:42', '2026-04-14 14:42:43'),
(109, 'App\\Models\\User', 1, 'flutter-app', 'e39dfb0e2fbca8a747d72435a64d56168e1e17c0befcb4b45d9ed3864120de2d', '[\"*\"]', '2026-04-15 10:58:06', NULL, '2026-04-14 14:46:16', '2026-04-15 10:58:06'),
(110, 'App\\Models\\User', 1, 'flutter-app', '82f5598b611fdfc1a251461ea14fd2bf5ac031e6a34f6a216a10d58857d16b72', '[\"*\"]', '2026-04-14 15:29:54', NULL, '2026-04-14 15:02:39', '2026-04-14 15:29:54'),
(111, 'App\\Models\\User', 1, 'flutter-app', '04e50882ac7196e877ac579376a924aa7a3ca761193e17bfc55dcdfba42007f5', '[\"*\"]', '2026-04-14 15:51:54', NULL, '2026-04-14 15:31:59', '2026-04-14 15:51:54'),
(112, 'App\\Models\\User', 1, 'flutter-app', 'bdae5f6af1a09646c6e3222c836ff4b7b2cad1db3b0863260b52553732752214', '[\"*\"]', '2026-04-14 16:09:52', NULL, '2026-04-14 16:01:26', '2026-04-14 16:09:52'),
(113, 'App\\Models\\User', 1, 'flutter-app', '9009b092e4ae809b18a134f94c68df145608b61c43823f9414ea0ceea0eb71c2', '[\"*\"]', '2026-04-14 16:39:15', NULL, '2026-04-14 16:27:22', '2026-04-14 16:39:15'),
(114, 'App\\Models\\User', 1, 'flutter-app', '45cae0e66c5e9523fa610acd85f3b475679a1ce1ab509b3c4faf913abb53accc', '[\"*\"]', '2026-04-14 17:19:06', NULL, '2026-04-14 17:15:33', '2026-04-14 17:19:06'),
(115, 'App\\Models\\User', 1, 'flutter-app', '80edf806dbf50439ea79e56a1d06e235be7d168c5ca66a941ad3fbc2b58948c7', '[\"*\"]', '2026-04-14 17:59:28', NULL, '2026-04-14 17:46:48', '2026-04-14 17:59:28'),
(116, 'App\\Models\\User', 1, 'flutter-app', '3f754a7c86c636136159b948033f5dbcf3ad24647db4fa7c1e41af6a69e7472f', '[\"*\"]', '2026-04-14 18:11:57', NULL, '2026-04-14 18:10:19', '2026-04-14 18:11:57'),
(117, 'App\\Models\\User', 1, 'flutter-app', '2e50656b914df2197d6641709b9f0a02ac7ff6f18643365620a7102b1b3205fb', '[\"*\"]', '2026-04-15 09:58:02', NULL, '2026-04-15 09:38:09', '2026-04-15 09:58:02'),
(118, 'App\\Models\\User', 1, 'flutter-app', 'abde9323e91e412ef825525dbb80ced3599c3f7565ac10860a87bc78c6ac27b1', '[\"*\"]', '2026-04-15 09:48:17', NULL, '2026-04-15 09:48:12', '2026-04-15 09:48:17'),
(119, 'App\\Models\\User', 1, 'flutter-app', 'aff55419b17229e408a7e8e0ec0bb753f27993bd461c30e0b2178a6b70c50833', '[\"*\"]', '2026-04-15 10:11:39', NULL, '2026-04-15 10:11:21', '2026-04-15 10:11:39'),
(120, 'App\\Models\\User', 1, 'flutter-app', 'b7bf410e281f7336c4c4eea97e81ca7b701078bfdb12770df96028ab44919149', '[\"*\"]', '2026-04-15 10:18:08', NULL, '2026-04-15 10:17:16', '2026-04-15 10:18:08'),
(121, 'App\\Models\\User', 1, 'flutter-app', '63d71b1254dd9310098df5a1623a559935eccb099ca6dce04e156c0533da2cd9', '[\"*\"]', '2026-04-15 10:51:40', NULL, '2026-04-15 10:24:25', '2026-04-15 10:51:40'),
(122, 'App\\Models\\User', 1, 'flutter-app', '77e2b05f4c8305008eba8892a7368828db2e17abfc230b747a24170ea7c280fb', '[\"*\"]', '2026-04-15 10:56:02', NULL, '2026-04-15 10:53:50', '2026-04-15 10:56:02'),
(123, 'App\\Models\\User', 1, 'flutter-app', '219f8ece8c6326dc2c8c31a753404bc3f49d73cbf8c95f98089b1250401954c2', '[\"*\"]', '2026-04-15 11:20:50', NULL, '2026-04-15 11:20:27', '2026-04-15 11:20:50'),
(124, 'App\\Models\\User', 1, 'flutter-app', 'b4f228407966f35b89bce62993294c8932d21fd0826c99a5742666a93580d2ec', '[\"*\"]', '2026-04-15 11:58:33', NULL, '2026-04-15 11:53:07', '2026-04-15 11:58:33'),
(125, 'App\\Models\\User', 1, 'flutter-app', '84b1ef9e11f57a72c6d654f839851896b1aaab0ab623adcdec8e7213d3e9285d', '[\"*\"]', '2026-04-15 12:24:50', NULL, '2026-04-15 12:14:17', '2026-04-15 12:24:50'),
(126, 'App\\Models\\User', 1, 'flutter-app', '8139eb80eb5afbc455b75a26173f53838c984c7bf3891f88a20ef62d9ef07b9d', '[\"*\"]', '2026-04-15 12:46:47', NULL, '2026-04-15 12:44:53', '2026-04-15 12:46:47'),
(127, 'App\\Models\\User', 1, 'flutter-app', '231425272d923e06fd7a4a161b6731eaea63bd9d4b41bc03937bc37f1773fa2b', '[\"*\"]', '2026-04-15 13:07:07', NULL, '2026-04-15 13:06:46', '2026-04-15 13:07:07'),
(129, 'App\\Models\\User', 1, 'flutter-app', 'b69792108e38509e1f30a0b28e2f5dc00ae3bcc326d3b0af31ee0439b5a73175', '[\"*\"]', '2026-04-16 12:12:00', NULL, '2026-04-15 15:29:53', '2026-04-16 12:12:00'),
(131, 'App\\Models\\User', 1, 'flutter-app', 'c31b016a7f10d6e059d813170f653af3f1f0b30ad47a626acde62b8895a091fc', '[\"*\"]', '2026-04-16 12:47:34', NULL, '2026-04-15 17:00:49', '2026-04-16 12:47:34'),
(132, 'App\\Models\\User', 12, 'flutter-app', 'b4ea7eecf7fcdf752aef39847ecf2ece26595e2b15f2fe1b75921daeb7c8c31b', '[\"*\"]', '2026-04-15 20:23:03', NULL, '2026-04-15 17:15:24', '2026-04-15 20:23:03'),
(133, 'App\\Models\\User', 1, 'flutter-app', 'e5f53fd95cfffcdc18fc8a99119ef8a4953ba8fcd4fa8d6a703dfcfbdef2ec63', '[\"*\"]', NULL, NULL, '2026-04-16 10:02:33', '2026-04-16 10:02:33'),
(134, 'App\\Models\\User', 1, 'flutter-app', 'a305ab90ab7700d2d9aa3d9851679f6e89ea1cc7471da579caf627e64ed7a4ce', '[\"*\"]', '2026-04-16 10:33:25', NULL, '2026-04-16 10:05:32', '2026-04-16 10:33:25'),
(135, 'App\\Models\\User', 1, 'flutter-app', '2725ca05f0d7a9bda1621c943557333a70de1781ebbe2408cd6d317bc4f65691', '[\"*\"]', '2026-04-16 10:46:57', NULL, '2026-04-16 10:35:37', '2026-04-16 10:46:57'),
(136, 'App\\Models\\User', 1, 'flutter-app', 'fd2e14a26637d721bb9cf40bfe3b7fdc288fe411c6ac65535770699c02dd5d4f', '[\"*\"]', '2026-04-16 11:19:34', NULL, '2026-04-16 10:47:53', '2026-04-16 11:19:34'),
(137, 'App\\Models\\User', 1, 'flutter-app', 'e42cf8442d83c2084d21fc83fee9a20ef3705b15eaa3d888b6cf584f5b173d43', '[\"*\"]', '2026-04-16 12:05:52', NULL, '2026-04-16 11:29:55', '2026-04-16 12:05:52'),
(138, 'App\\Models\\User', 1, 'flutter-app', 'be5e6557bb447437e11c1a6704e0af22f950260554aa9d28d9025385e714c73d', '[\"*\"]', '2026-04-16 13:16:09', NULL, '2026-04-16 12:12:11', '2026-04-16 13:16:09');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('not_started','in_progress','on_hold','completed','cancelled') NOT NULL DEFAULT 'not_started',
  `start_date` date DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `billing_type` enum('fixed_rate','hourly_rate') DEFAULT NULL,
  `total_rate` decimal(10,2) DEFAULT NULL,
  `estimated_hours` int(11) DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `members` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`members`)),
  `description` text DEFAULT NULL,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `technologies` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`technologies`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `project_name`, `customer_id`, `status`, `start_date`, `deadline`, `billing_type`, `total_rate`, `estimated_hours`, `tags`, `members`, `description`, `priority`, `technologies`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 'Crackteck', 2, 'in_progress', '2026-03-10', '2026-03-31', 'fixed_rate', 100000.00, 80, '[\"web-design\"]', '[\"3\",\"4\",\"5\",\"8\"]', '<p>&lt;p&gt;&amp;lt;p&amp;gt;&amp;amp;lt;p&amp;amp;gt;The project is an E-commerce system.&amp;amp;lt;/p&amp;amp;gt;&amp;lt;/p&amp;gt;&lt;/p&gt;</p>', 'high', '[\"Laravel\",\"MySQL\",\"PHP\",\"HTML5\",\"CSS3\"]', '2026-03-10 11:41:15', '2026-04-15 12:21:44', NULL),
(3, 'Oceanic', 3, 'in_progress', '2026-02-20', '2026-03-12', 'fixed_rate', NULL, NULL, '[\"Website Desgin\"]', '[\"3\"]', '<p>Paint Solution</p>', 'medium', '[\"Bootstrap\",\"html\",\"css\",\"java script\",\"php\"]', '2026-03-11 14:55:40', '2026-03-11 14:55:40', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `project_comments`
--

CREATE TABLE `project_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_comments`
--

INSERT INTO `project_comments` (`id`, `project_id`, `user_id`, `comment`, `created_at`, `updated_at`) VALUES
(2, 2, 1, 'testing app', '2026-04-15 12:47:15', '2026-04-15 12:47:15'),
(3, 2, 1, 'application testing', '2026-04-15 13:07:07', '2026-04-15 13:07:07'),
(4, 2, 1, 'app testing', '2026-04-15 13:21:02', '2026-04-15 13:21:02');

-- --------------------------------------------------------

--
-- Table structure for table `project_files`
--

CREATE TABLE `project_files` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_type` varchar(255) NOT NULL,
  `file_size` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_files`
--

INSERT INTO `project_files` (`id`, `project_id`, `file_name`, `original_name`, `file_type`, `file_size`, `file_path`, `description`, `uploaded_by`, `created_at`, `updated_at`) VALUES
(2, 2, '69afcd7a14212_1773129082.jpeg', 'WhatsApp Image 2026-03-10 at 10.06.34 AM.jpeg', 'image/jpeg', '33830', 'uploads/project_files/2/69afcd7a14212_1773129082.jpeg', NULL, 5, '2026-03-10 13:21:22', '2026-03-10 13:21:22');

-- --------------------------------------------------------

--
-- Table structure for table `project_issues`
--

CREATE TABLE `project_issues` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `issue_description` text NOT NULL,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `status` enum('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_milestones`
--

CREATE TABLE `project_milestones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','in_progress','completed') NOT NULL DEFAULT 'pending',
  `due_date` date DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_status_logs`
--

CREATE TABLE `project_status_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('not_started','in_progress','on_hold','completed','cancelled') NOT NULL,
  `started_at` datetime NOT NULL,
  `ended_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_status_logs`
--

INSERT INTO `project_status_logs` (`id`, `project_id`, `status`, `started_at`, `ended_at`, `created_at`, `updated_at`) VALUES
(2, 2, 'in_progress', '2026-03-10 11:41:15', NULL, '2026-03-10 11:41:15', '2026-03-10 11:41:15'),
(3, 3, 'in_progress', '2026-03-11 14:55:40', NULL, '2026-03-11 14:55:40', '2026-03-11 14:55:40');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `status`, `created_at`, `updated_at`) VALUES
(1, 'super-admin', 'web', 'active', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(2, 'Staff', 'web', 'active', '2026-03-09 14:43:46', '2026-03-09 14:43:46'),
(3, 'Client', 'web', 'active', '2026-03-10 11:06:54', '2026-03-10 11:06:54'),
(5, 'customer', 'web', 'active', '2026-04-07 16:53:45', '2026-04-07 16:53:45');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(35, 1),
(36, 1),
(37, 1),
(38, 1),
(39, 1),
(40, 1),
(41, 1),
(42, 1),
(43, 1),
(44, 1),
(45, 1),
(46, 1),
(47, 1),
(48, 1),
(51, 1),
(52, 1),
(53, 1),
(64, 1),
(65, 1),
(66, 1),
(67, 1),
(68, 1),
(69, 1),
(70, 1),
(71, 1),
(72, 1),
(73, 1),
(5, 2),
(9, 2),
(13, 2),
(14, 2),
(15, 2),
(21, 2),
(45, 2),
(66, 2),
(67, 2),
(68, 2),
(69, 2),
(70, 2),
(71, 2),
(72, 2),
(73, 2),
(9, 3),
(11, 3),
(17, 3),
(18, 3),
(19, 3),
(20, 3),
(45, 3),
(46, 3),
(47, 3),
(48, 3),
(51, 3),
(52, 3),
(53, 3),
(64, 3),
(65, 3);

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED DEFAULT NULL,
  `vendor_id` bigint(20) UNSIGNED NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `service_details` text DEFAULT NULL,
  `remark_text` varchar(255) DEFAULT NULL,
  `remark_color` varchar(20) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `billing_date` date NOT NULL,
  `status` enum('active','inactive','expired','pending') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `client_id`, `vendor_id`, `service_name`, `service_details`, `remark_text`, `remark_color`, `start_date`, `end_date`, `billing_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'Google Workspace: finwert.com', '<p>No.of User: 56</p>', NULL, NULL, '2023-08-29', '2026-08-29', '2026-08-29', 'active', '2025-08-23 01:38:43', '2025-09-12 06:44:19'),
(2, 6, 2, 'Indore - Hosting | SSL & Maintenance', NULL, NULL, NULL, '2020-06-15', '2026-06-15', '2026-06-15', 'active', '2025-09-01 04:49:25', '2025-09-05 00:41:24'),
(3, 7, 3, 'Domain', '<p><a href=\"https://dcc.godaddy.com/control/portfolio/aspirias.in/settings\"><strong>aspirias.in</strong></a></p>', NULL, NULL, '2024-11-05', '2026-11-05', '2026-11-05', 'active', '2025-09-05 06:03:25', '2025-11-10 04:31:56'),
(4, 7, 4, 'Email', '<p>sonal@aspirias.in</p>', NULL, NULL, '2025-01-22', '2027-01-22', '2027-01-22', 'active', '2025-09-05 06:03:25', '2026-01-27 00:02:40'),
(5, 12, 3, 'Domain', '<p><a href=\"https://dcc.godaddy.com/control/portfolio/rustomjeeazzianodl.com/settings\"><strong>rustomjeeazzianodl.com</strong></a></p>', NULL, NULL, '2025-03-26', '2027-03-26', '2027-03-26', 'active', '2025-09-09 01:31:31', '2026-03-24 10:59:44'),
(6, 12, 4, 'Google Workspace Starter Plan', NULL, NULL, NULL, '2025-03-28', '2027-03-28', '2027-03-28', 'active', '2025-09-09 01:31:31', '2026-03-27 11:30:05'),
(7, 11, 1, 'sukanyaindia.in', '<p>Email</p>', NULL, NULL, '2024-09-10', '2026-09-10', '2026-09-10', 'active', '2025-09-09 01:58:50', '2025-09-09 01:58:50'),
(8, 6, 5, 'Sukanya - Website Maintenance', NULL, NULL, NULL, '2024-09-16', '2026-09-16', '2026-09-16', 'active', '2025-09-09 02:11:30', '2025-11-10 06:24:23'),
(9, 8, 1, 'Google Workspace - Starter Plan', '<p>5 Users&nbsp;</p>', NULL, NULL, '2022-09-12', '2026-09-12', '2026-09-12', 'active', '2025-09-09 02:26:50', '2025-09-11 22:57:40'),
(10, 8, 2, 'Hosting | SSL & Yearly Website Maintenance', NULL, NULL, NULL, '2025-02-04', '2027-02-04', '2027-02-04', 'active', '2025-09-09 02:29:39', '2026-02-16 04:14:10'),
(11, 10, 1, 'Google Workspace - Starter Plan', '<p>fragomatrix.net<br>No.of Users: 23</p>', NULL, NULL, '2024-10-03', '2026-10-03', '2025-10-03', 'active', '2025-09-09 04:06:28', '2025-10-30 06:43:29'),
(12, 10, 1, 'Google Workspace - Starter Plan', '<p>fragomatrixfnf.com<br>No. of users: 6</p>', NULL, NULL, '2023-10-06', '2026-10-06', '2025-10-06', 'active', '2025-09-09 04:09:11', '2025-10-30 06:43:49'),
(13, 10, 2, 'Hosting | SSL & Yearly Website Maintenance', '<p>fragomatrix.net<br>&nbsp;</p>', NULL, NULL, '2025-08-13', '2026-08-13', '2026-08-13', 'active', '2025-09-09 04:11:31', '2025-09-09 04:11:31'),
(14, 3, 2, 'Hosting | SSL & Yearly Website Maintenance: www.karantelecom.com', '<p>www.karantelecom.com</p>', NULL, NULL, '2025-08-01', '2026-08-01', '2026-08-01', 'active', '2025-09-09 04:30:55', '2025-09-09 04:30:55'),
(15, 13, 1, 'Google Workspace: www.yashtools.in', '<p>2 Users</p>', NULL, NULL, '2024-10-06', '2026-10-06', '2025-10-06', 'active', '2025-09-09 04:41:32', '2025-10-11 04:43:07'),
(16, 13, 2, 'Hosting & SSL', NULL, NULL, NULL, '2025-04-15', '2027-04-15', '2027-04-15', 'active', '2025-09-09 04:41:32', '2026-04-15 09:44:47'),
(17, 5, 2, 'Hosting & SSL www.dynarx.com', NULL, NULL, NULL, '2025-07-10', '2026-07-10', '2026-07-10', 'active', '2025-09-09 04:56:04', '2025-09-09 04:56:04'),
(18, 2, 3, 'Domain & Hosting:  www.finwert.com', NULL, NULL, NULL, '2025-06-30', '2026-06-30', '2026-06-30', 'active', '2025-09-09 05:15:12', '2025-09-09 05:15:12'),
(19, 2, 1, 'Email Renewal: finwert.com', NULL, NULL, NULL, '2025-08-29', '2026-08-29', '2026-08-29', 'active', '2025-09-09 05:15:12', '2025-09-12 06:43:57'),
(20, 79, 3, 'Domain & Hosting:  www.shahkathariya.com', NULL, NULL, NULL, '2025-01-19', '2027-01-19', '2026-11-01', 'active', '2025-09-09 05:15:12', '2026-01-27 00:02:23'),
(21, 79, 4, 'Email Service & SSL Renewal : Starter Plan | Shahkathriya', '<p>20 Users</p>', NULL, NULL, '2025-03-17', '2027-03-16', '2027-11-01', 'active', '2025-09-09 05:15:12', '2026-03-17 09:18:40'),
(22, 79, 4, 'Email Service & SSL Renewal : Standrad Plan', '<p>No of users: 5 Users</p>', NULL, NULL, '2025-01-16', '2027-01-15', '2026-11-01', 'active', '2025-09-09 05:15:12', '2026-01-27 00:01:44'),
(23, 24, 2, 'Hosting', NULL, NULL, NULL, '2025-04-17', '2027-04-17', '2027-04-17', 'active', '2025-09-09 05:29:31', '2026-04-15 09:43:52'),
(24, 24, 6, 'Domain: themakers.in', NULL, NULL, NULL, '2023-08-14', '2026-08-14', '2026-08-14', 'active', '2025-09-09 06:14:02', '2025-09-09 06:14:02'),
(25, 24, 6, 'Domain: zadesigns.in', NULL, NULL, NULL, '2013-09-18', '2026-09-18', '2026-09-18', 'active', '2025-09-09 06:14:02', '2025-09-09 06:14:02'),
(26, 24, 1, 'No. of Email Users: 6 themakers.in', '<p>No. of Email Users: 6 themakers.in</p>', NULL, NULL, '2024-09-25', '2026-09-25', '2026-09-25', 'active', '2025-09-09 06:14:02', '2025-09-24 04:52:55'),
(27, 24, 4, 'No. of Email Users: 2 zadesigns.in', '<p>No. of Email Users: 2 zadesigns.in</p>', NULL, NULL, '2024-09-28', '2026-09-27', '2025-09-27', 'active', '2025-09-09 06:14:02', '2025-10-03 02:07:30'),
(28, 25, 6, 'Domain: skinternational.in', NULL, NULL, NULL, '2005-05-06', '2026-05-06', '2026-05-06', 'active', '2025-09-10 01:38:07', '2025-09-10 01:38:07'),
(29, 25, 2, 'Hosting | SSL & Website Maintenance', NULL, NULL, NULL, '2025-01-16', '2027-01-15', '2027-01-15', 'active', '2025-09-10 01:38:07', '2026-01-26 23:48:50'),
(30, 25, 1, 'Google Workspace - Starter Plan', '<p>9 Users</p>', NULL, NULL, '2024-09-30', '2026-09-30', '2025-09-30', 'active', '2025-09-10 01:38:07', '2025-10-11 04:41:42'),
(31, 25, 6, 'Domain: scarves.co.in', NULL, NULL, NULL, '2005-05-05', '2026-05-05', '2026-05-05', 'active', '2025-09-10 01:38:07', '2025-09-10 01:38:07'),
(32, 25, 6, 'Domain: satabgat.com', NULL, NULL, NULL, '2005-05-05', '2026-05-05', '2026-05-05', 'active', '2025-09-10 01:38:07', '2025-09-10 01:38:07'),
(33, 26, 6, 'Domain: solutionsfacility.com', NULL, NULL, NULL, '2014-02-26', '2027-02-26', '2027-02-26', 'active', '2025-09-10 01:47:25', '2026-02-25 23:09:44'),
(34, 26, 2, 'Hosting | SSL & Email', NULL, NULL, NULL, '2025-01-01', '2027-01-01', '2027-01-01', 'active', '2025-09-10 01:47:25', '2026-01-09 02:16:16'),
(35, 27, 6, 'Domain: matrixfacility.in', NULL, NULL, NULL, '2011-10-17', '2026-10-17', '2025-10-17', 'active', '2025-09-10 01:53:06', '2025-10-29 23:57:51'),
(37, 28, 2, 'SSL Renewal for globalfleet.in', NULL, NULL, NULL, '2024-09-01', '2026-09-01', '2026-09-01', 'active', '2025-09-10 02:21:40', '2025-09-10 02:21:40'),
(38, 28, 2, 'Hosting Server For Poonam Travels Website', NULL, NULL, NULL, '2024-09-01', '2026-09-01', '2026-09-01', 'active', '2025-09-10 02:21:40', '2025-09-10 02:21:40'),
(40, 29, 1, 'Email: marinesecurityindia.com', NULL, NULL, NULL, '2024-10-27', '2026-10-27', '2025-10-27', 'active', '2025-09-10 02:29:33', '2025-10-29 23:58:34'),
(41, 29, 6, 'Domain: marinesecurityindia.com', NULL, NULL, NULL, '2011-01-12', '2027-01-12', '2027-01-12', 'active', '2025-09-10 02:29:33', '2026-01-09 02:15:33'),
(42, 29, 2, 'Hosting & SSL', NULL, NULL, NULL, '2025-01-01', '2027-01-01', '2027-01-01', 'active', '2025-09-10 02:29:33', '2026-01-26 23:47:14'),
(43, 31, 2, 'Hosting | SSL & Server Maintenace: www.dipuenterprises.com', NULL, 'Pending From Client Side', 'gray', '2023-03-05', '2026-03-05', '2026-03-05', 'inactive', '2025-09-12 01:53:51', '2026-04-06 16:35:36'),
(44, 32, 3, 'Domain: vlegends.in', NULL, NULL, NULL, '2025-03-07', '2027-03-07', '2027-03-07', 'active', '2025-09-12 02:03:35', '2026-03-11 16:38:41'),
(45, 32, 2, 'Hosting & SSL', NULL, 'Renewal pending form client side (Payment)', 'gray', '2025-04-01', '2026-04-01', '2026-04-01', 'inactive', '2025-09-12 02:03:35', '2026-04-15 09:47:33'),
(46, 32, 4, 'Google Workspace Business Starter for 2 users', NULL, 'Renewal Pending From Client Side', 'gray', '2025-03-17', '2026-03-17', '2026-03-17', 'inactive', '2025-09-12 02:03:35', '2026-04-06 16:36:24'),
(47, 33, 2, 'www.chempharmajobs.com', NULL, NULL, NULL, '2025-07-07', '2026-07-07', '2026-07-07', 'active', '2025-09-12 02:10:01', '2025-09-12 22:34:13'),
(48, 33, 1, 'Google Workspace Business Starter for 2 users', NULL, 'Pending From Client Side', 'gray', '2025-01-30', '2026-01-30', '2026-01-30', 'inactive', '2025-09-12 02:10:01', '2026-04-15 09:47:00'),
(49, 34, 3, 'Domain: shakambharidyechem.com', NULL, NULL, NULL, '2025-05-22', '2026-05-22', '2026-05-22', 'active', '2025-09-12 02:26:03', '2025-09-12 02:26:03'),
(50, 34, 4, 'Google Workspace Business Starter', NULL, NULL, NULL, '2024-06-11', '2026-06-11', '2026-06-11', 'active', '2025-09-12 02:26:03', '2025-09-12 02:26:03'),
(51, 35, 6, 'Domain: www.shreeharichemicals.in', NULL, NULL, NULL, '2025-01-01', '2027-01-01', '2027-12-01', 'active', '2025-09-12 04:25:08', '2026-01-09 02:10:14'),
(52, 35, 2, 'Dedicated Windows Hosting & CRL Server', NULL, NULL, NULL, '2025-01-01', '2026-12-01', '2026-12-01', 'active', '2025-09-12 04:25:08', '2025-12-26 23:52:38'),
(54, 35, 2, 'Server Subscription Updatation 12GB Extra', NULL, NULL, NULL, '2025-02-27', '2027-02-27', '2027-02-01', 'active', '2025-09-12 04:25:08', '2026-03-27 11:32:55'),
(55, 35, 2, 'CMS Maintenance & Ongoing Support', NULL, NULL, NULL, '2025-02-27', '2027-02-27', '2027-02-01', 'active', '2025-09-12 04:25:08', '2026-03-27 11:33:12'),
(56, 36, 3, 'Domain: myxxnpacx.com', NULL, NULL, NULL, '2025-07-23', '2026-07-23', '2026-07-23', 'active', '2025-09-12 04:31:29', '2025-09-12 04:31:29'),
(57, 36, 4, 'Email | Hosting | SSL & Software & Antispam Subscription', NULL, NULL, NULL, '2025-06-13', '2026-06-13', '2026-06-13', 'active', '2025-09-12 04:31:29', '2025-09-12 04:31:29'),
(58, 37, 6, 'Domain: sunrisk.co.in', NULL, NULL, NULL, '2005-05-17', '2026-05-17', '2026-05-17', 'active', '2025-09-12 04:48:06', '2025-09-12 04:48:06'),
(59, 37, 2, 'Hosting | SSL & Website Maintenance', NULL, NULL, NULL, '2025-04-27', '2026-04-27', '2026-04-27', 'active', '2025-09-12 04:48:06', '2025-09-12 04:48:06'),
(60, 38, 2, 'Renewal Of Urban Sports Website', NULL, NULL, NULL, '2025-03-01', '2027-03-01', '2027-03-01', 'active', '2025-09-12 04:52:41', '2026-03-02 01:01:16'),
(61, 39, 6, 'Domain: mehtasgroup.com', NULL, NULL, NULL, '2025-08-28', '2026-08-28', '2026-08-28', 'active', '2025-09-12 05:00:53', '2025-09-12 05:00:53'),
(62, 39, 6, 'Domain: dayo.in', NULL, NULL, NULL, '2023-07-12', '2026-07-12', '2026-07-12', 'active', '2025-09-12 05:00:53', '2025-09-12 05:00:53'),
(63, 39, 6, 'Domain: xpertcircuit.com', NULL, NULL, NULL, '2020-04-30', '2026-04-30', '2026-04-30', 'active', '2025-09-12 05:00:53', '2025-09-12 05:00:53'),
(64, 39, 6, 'Domain: riamehta.in & Hosting', NULL, NULL, NULL, '2025-04-11', '2027-04-11', '2027-04-11', 'active', '2025-09-12 05:00:53', '2026-04-15 09:45:46'),
(65, 39, 2, 'Hosting | SSL & Email', NULL, NULL, NULL, '2025-06-10', '2026-06-10', '2026-06-10', 'active', '2025-09-12 05:00:53', '2025-09-12 05:00:53'),
(66, 40, 6, 'Domain: saannets', NULL, NULL, NULL, '2013-02-07', '2026-08-07', '2026-08-07', 'active', '2025-09-12 06:20:54', '2025-09-12 06:20:54'),
(67, 40, 2, 'Hosting & Maintenance', NULL, NULL, NULL, '2025-01-01', '2027-01-01', '2027-01-01', 'active', '2025-09-12 06:20:54', '2026-01-26 23:47:47'),
(68, 41, 3, 'Domain: rajprabha.co.in', NULL, NULL, NULL, '2025-08-22', '2027-08-22', '2027-08-22', 'active', '2025-09-12 06:31:13', '2025-09-12 06:31:13'),
(69, 41, 2, 'Hosting | SSL & Maintenance', NULL, NULL, NULL, '2025-04-11', '2026-04-11', '2026-04-11', 'active', '2025-09-12 06:31:13', '2025-09-12 06:31:13'),
(70, 42, 6, 'Domain: speedycreation.com', NULL, NULL, NULL, '2014-06-12', '2026-06-12', '2026-04-30', 'active', '2025-09-12 06:37:06', '2025-12-21 23:01:52'),
(71, 42, 2, 'Hosting | SSL & Maintenance', NULL, NULL, NULL, '2025-04-30', '2026-04-30', '2026-04-30', 'active', '2025-09-12 06:37:06', '2025-09-12 06:37:06'),
(72, 42, 1, 'Email | Additional Drive | SSL', NULL, NULL, NULL, '2024-02-06', '2027-02-06', '2027-12-01', 'active', '2025-09-12 06:37:06', '2026-02-11 07:11:27'),
(73, 43, 6, 'Domain: shirkemechanism.com', NULL, NULL, NULL, '2014-10-31', '2026-10-31', '2025-10-31', 'active', '2025-09-12 06:41:05', '2025-10-27 05:15:11'),
(74, 43, 2, 'Hosting', NULL, NULL, NULL, '2024-10-02', '2026-10-02', '2026-10-02', 'active', '2025-09-12 06:41:05', '2025-10-30 00:09:07'),
(75, 44, 2, 'Hosting & SSL | Maintenance', NULL, NULL, NULL, '2025-06-16', '2026-06-16', '2026-06-16', 'active', '2025-09-12 07:00:44', '2025-09-12 07:00:44'),
(76, 44, 3, 'Domain: oceanglobal.co.in', NULL, NULL, NULL, '2025-01-29', '2027-01-29', '2027-01-29', 'active', '2025-09-12 07:00:44', '2026-02-01 23:39:25'),
(77, 44, 3, 'Domain: globaloceanclearing.com', NULL, NULL, NULL, '2025-06-20', '2026-06-20', '2026-06-20', 'active', '2025-09-12 07:00:44', '2025-09-12 07:00:44'),
(78, 44, 3, 'Domain: globalocean.co.in', NULL, NULL, NULL, '2025-01-28', '2027-01-28', '2027-01-28', 'active', '2025-09-12 07:00:44', '2026-02-01 23:38:59'),
(79, 45, 3, 'Domain:  kediaagrawal.co.in', NULL, NULL, NULL, '2024-08-07', '2026-08-07', '2026-08-07', 'active', '2025-09-13 01:44:14', '2025-09-13 01:44:14'),
(80, 45, 4, 'Google Workspace Business Starter', NULL, NULL, NULL, '2025-08-09', '2026-08-09', '2026-08-09', 'active', '2025-09-13 01:44:14', '2025-09-13 01:44:14'),
(81, 46, 6, 'Domain: vpsons.com', NULL, NULL, NULL, '2025-08-28', '2026-08-28', '2026-08-28', 'active', '2025-09-13 02:09:07', '2025-09-13 02:09:07'),
(82, 46, 2, 'Hosting & Website Maintenance', NULL, NULL, NULL, '2025-07-29', '2026-07-29', '2026-07-29', 'active', '2025-09-13 02:09:07', '2025-09-13 02:09:07'),
(83, 47, 2, 'Hosting & Website Maintenance', NULL, NULL, NULL, '2025-07-29', '2026-07-29', '2026-07-29', 'active', '2025-09-13 02:10:53', '2025-09-13 02:10:53'),
(84, 48, 7, 'Digital Presence With Social Media', NULL, NULL, NULL, '2025-10-01', '2026-03-31', '2025-09-30', 'active', '2025-09-13 02:20:32', '2025-10-30 00:08:39'),
(85, 48, 9, 'Google Ranking & SEO Packages', NULL, NULL, NULL, '2025-10-01', '2026-03-31', '2025-09-30', 'active', '2025-09-13 02:20:32', '2025-10-30 00:07:42'),
(86, 48, 2, 'Hosting & SSL', NULL, NULL, NULL, '2024-10-22', '2026-10-10', '2025-10-10', 'active', '2025-09-13 02:20:32', '2025-10-30 06:44:11'),
(87, 50, 6, 'Domain: yellowscapigments.com', NULL, NULL, NULL, '2018-07-24', '2026-07-24', '2026-07-24', 'active', '2025-09-13 03:43:23', '2025-09-13 03:43:23'),
(88, 50, 2, 'Hosting & Email', NULL, NULL, NULL, '2025-07-21', '2026-07-20', '2026-07-20', 'active', '2025-09-13 03:43:23', '2025-09-13 03:43:23'),
(89, 51, 6, 'Domain: krossboundaries.com', NULL, NULL, NULL, '2019-06-24', '2026-06-24', '2026-06-24', 'active', '2025-09-13 03:51:29', '2025-09-13 03:51:29'),
(90, 51, 2, 'Hosting | SSL & Website Maintenance', NULL, NULL, NULL, '2025-04-17', '2026-04-17', '2026-04-17', 'active', '2025-09-13 03:51:29', '2025-09-13 03:51:29'),
(91, 51, 4, 'Email', NULL, NULL, NULL, '2025-06-23', '2026-06-23', '2026-06-23', 'active', '2025-09-13 03:51:29', '2025-09-13 03:51:29'),
(92, 51, 10, 'Domain: krossboundaries.ae', NULL, NULL, NULL, '2016-01-11', '2027-01-11', '2026-12-01', 'active', '2025-09-13 06:04:10', '2025-12-26 23:55:54'),
(93, 51, 2, 'Hosting & Email', NULL, NULL, NULL, '2025-04-17', '2026-04-17', '2026-04-17', 'active', '2025-09-13 06:04:10', '2025-09-13 06:04:10'),
(94, 52, 3, 'Domain: propackind.in', NULL, NULL, NULL, '2025-08-24', '2026-08-24', '2026-08-24', 'active', '2025-09-13 06:11:42', '2025-09-13 06:11:42'),
(95, 52, 2, 'Hosting | SSL & Maintenance', NULL, NULL, NULL, '2025-06-07', '2026-06-06', '2026-06-06', 'active', '2025-09-13 06:11:42', '2025-09-13 06:11:42'),
(96, 52, 4, 'Email', NULL, NULL, NULL, '2025-01-13', '2027-01-13', '2027-01-13', 'active', '2025-09-13 06:11:42', '2026-01-27 00:03:11'),
(97, 53, 3, 'Domain: sjent.co.in', NULL, NULL, NULL, '2025-08-26', '2026-08-26', '2026-06-16', 'active', '2025-09-13 06:19:47', '2025-12-21 22:56:53'),
(98, 53, 2, 'Hosting | SSL & Maintenance', NULL, NULL, NULL, '2025-06-10', '2026-06-10', '2026-06-16', 'active', '2025-09-13 06:19:48', '2025-12-21 22:57:06'),
(99, 53, 4, 'Email', NULL, NULL, NULL, '2024-12-06', '2026-12-06', '2026-06-16', 'active', '2025-09-13 06:19:51', '2025-12-21 22:56:09'),
(100, 54, 6, 'virajprints.com', NULL, NULL, NULL, '2025-07-21', '2026-07-21', '2026-07-21', 'active', '2025-09-13 06:30:58', '2025-09-13 06:30:58'),
(101, 54, 4, 'Email', NULL, NULL, NULL, '2025-02-24', '2027-02-24', '2027-02-24', 'active', '2025-09-13 06:30:58', '2026-02-25 23:06:46'),
(102, 54, 2, 'Hosting | SSL & Maintenance', NULL, NULL, NULL, '2025-04-17', '2027-04-17', '2027-04-17', 'active', '2025-09-13 06:30:58', '2026-04-15 09:44:20'),
(103, 55, 2, 'Microsoft | SSL & Website Maintenance', NULL, NULL, NULL, '2024-11-13', '2026-11-13', '2026-11-13', 'active', '2025-09-15 03:58:54', '2025-11-12 00:15:18'),
(104, 55, 4, 'Google Workspace Business', NULL, NULL, NULL, '2025-06-13', '2026-06-12', '2026-06-12', 'active', '2025-09-15 03:58:54', '2025-09-15 03:58:54'),
(105, 56, 6, 'Domain: rajratnaaatamaker.com', NULL, NULL, NULL, '2015-05-01', '2026-05-01', '2026-05-01', 'active', '2025-09-15 05:40:54', '2025-09-15 05:40:54'),
(106, 56, 2, 'Hosting & Email Server Maintenance', NULL, NULL, NULL, '2025-04-01', '2026-04-01', '2026-04-01', 'active', '2025-09-15 05:40:54', '2025-09-15 05:40:54'),
(107, 57, 6, 'Domain: manojpenmart.co.in | .net | .com | .in', NULL, NULL, NULL, '2015-06-04', '2026-06-04', '2026-06-04', 'active', '2025-09-15 06:32:24', '2025-09-15 06:32:24'),
(108, 57, 2, 'Hosting', NULL, NULL, NULL, '2025-03-13', '2027-03-13', '2027-03-13', 'active', '2025-09-15 06:32:24', '2026-03-17 09:17:57'),
(109, 30, 6, 'Domain: efcinfra.net | 	efcinfra.in | efcinfra.com', NULL, NULL, NULL, '2016-05-08', '2026-05-08', '2026-05-08', 'active', '2025-09-16 06:44:36', '2025-09-16 06:44:36'),
(110, 30, 2, 'Hosting | SSL & Maintenance', NULL, NULL, NULL, '2025-03-08', '2027-03-08', '2027-03-08', 'active', '2025-09-16 06:44:36', '2026-03-24 10:59:07'),
(111, 58, 6, 'Domain: efclogistics.in | .net | efclipl.in | .com| .net', NULL, NULL, NULL, '2016-12-07', '2026-12-07', '2026-12-07', 'active', '2025-09-16 06:47:42', '2025-12-21 22:45:52'),
(112, 59, 6, 'Domain: leminoslab.com', NULL, NULL, NULL, '2014-05-20', '2026-05-20', '2026-05-20', 'active', '2025-09-16 23:24:18', '2025-09-16 23:24:18'),
(113, 59, 2, 'Hosting | SSL & Maintenance', NULL, NULL, NULL, '2025-04-21', '2026-04-21', '2026-04-21', 'active', '2025-09-16 23:24:18', '2025-09-16 23:24:18'),
(114, 60, 3, 'Domain: www.rnisargfoundation.com', NULL, NULL, NULL, '2025-05-10', '2026-05-10', '2026-05-10', 'active', '2025-09-18 07:21:42', '2025-09-18 07:21:42'),
(115, 60, 2, 'Hosting | SSL & Website Maintenace', NULL, NULL, NULL, '2025-05-10', '2026-05-10', '2026-05-10', 'active', '2025-09-18 07:21:42', '2025-09-18 07:21:42'),
(116, 61, 6, 'Domain: sabalshipping.com', NULL, NULL, NULL, '2013-04-03', '2027-04-03', '2027-01-01', 'active', '2025-09-18 07:27:13', '2026-04-03 09:34:14'),
(117, 61, 2, 'Hosting & Support', NULL, NULL, NULL, '2025-01-01', '2027-01-01', '2027-01-01', 'active', '2025-09-18 07:27:13', '2026-01-26 23:48:25'),
(119, 27, 2, 'Hosting | SSL & Email', NULL, NULL, NULL, '2025-01-01', '2027-01-01', '2027-01-01', 'active', '2025-09-18 07:30:28', '2026-02-16 04:18:15'),
(120, 62, 3, 'Domain: ishinternationalmfg.com', NULL, NULL, NULL, '2025-03-03', '2027-03-03', '2027-03-03', 'active', '2025-09-19 02:19:29', '2026-03-05 00:59:12'),
(121, 62, 1, 'Email', NULL, NULL, NULL, '2025-05-15', '2026-05-15', '2026-05-15', 'active', '2025-09-19 02:19:29', '2025-09-19 02:19:29'),
(122, 62, 2, 'Hosting | SSL & Maintance', NULL, NULL, NULL, '2024-11-25', '2026-11-25', '2026-11-25', 'active', '2025-09-19 02:19:29', '2025-12-21 22:41:14'),
(123, 63, 6, 'Domain: yashrajlogistics.co.in', NULL, NULL, NULL, '2016-04-03', '2027-04-03', '2027-04-03', 'active', '2025-09-19 03:44:04', '2026-04-02 17:58:45'),
(124, 63, 4, 'Email: Starter & Business Plan', '<p>2 Users</p>', NULL, NULL, '2025-03-28', '2027-03-28', '2027-03-28', 'active', '2025-09-19 03:44:04', '2026-03-25 11:22:39'),
(125, 63, 2, 'Hosting | SSL & Website Maintenance', NULL, NULL, NULL, '2025-01-10', '2027-01-10', '2027-01-10', 'active', '2025-09-19 03:44:04', '2026-01-09 02:09:18'),
(126, 64, 1, 'Email & Maintenance', NULL, NULL, NULL, '2024-02-06', '2027-02-06', '2027-02-06', 'active', '2025-09-19 04:04:32', '2026-02-11 07:12:48'),
(127, 64, 6, 'Domain: torshaapharma.com', NULL, NULL, NULL, '2021-03-16', '2027-03-16', '2027-03-16', 'active', '2025-09-19 04:04:32', '2026-03-16 09:37:20'),
(128, 64, 2, 'Hosting | SSL & Website Maintenance', NULL, NULL, NULL, '2025-02-01', '2027-02-01', '2027-02-01', 'active', '2025-09-19 04:04:32', '2026-02-11 07:14:16'),
(129, 65, 4, 'Email', NULL, NULL, NULL, '2025-01-01', '2027-02-07', '2027-01-01', 'active', '2025-09-19 04:14:41', '2026-02-11 07:12:17'),
(130, 66, 4, 'Email Renewal', NULL, NULL, NULL, '2025-04-14', '2027-04-14', '2027-04-14', 'active', '2025-09-19 06:35:45', '2026-04-15 09:45:11'),
(131, 67, 2, 'Hosting Renewal', NULL, NULL, NULL, '2025-01-01', '2027-01-01', '2027-01-01', 'active', '2025-09-19 06:37:08', '2026-01-09 02:08:49'),
(132, 68, 2, 'Hosting | SSL & Maintenance: venkyglobal.com', NULL, NULL, NULL, '2025-02-25', '2027-02-25', '2027-02-25', 'active', '2025-09-20 00:37:42', '2026-03-17 09:17:31'),
(133, 68, 7, 'Social Media For Implementation Yearly', NULL, NULL, NULL, '2024-12-01', '2026-12-01', '2026-12-01', 'active', '2025-09-20 00:37:42', '2025-12-21 22:44:26'),
(134, 68, 6, 'Domain: jpcindia.in', NULL, NULL, NULL, '2016-02-08', '2027-02-08', '2026-11-10', 'active', '2025-09-20 00:37:42', '2026-02-11 07:13:15'),
(135, 68, 2, 'Hosting | SSL | Email & Maintenance', NULL, NULL, NULL, '2024-11-15', '2026-11-15', '2026-11-15', 'active', '2025-09-20 00:37:42', '2025-11-10 06:23:09'),
(136, 69, 6, 'Domain: creativeprintinks.com', NULL, NULL, NULL, '2018-03-30', '2027-03-30', '2027-03-30', 'active', '2025-09-20 01:27:33', '2026-03-25 11:22:16'),
(137, 69, 2, 'Hosting | SSL & Maintenance - creativeprintinks.com', NULL, NULL, NULL, '2025-03-17', '2027-03-17', '2027-03-17', 'active', '2025-09-20 01:27:33', '2026-03-18 09:40:09'),
(138, 69, 2, 'Website Hosting Server & SSL: www.newkrishna.com', NULL, NULL, NULL, '2025-05-28', '2026-05-29', '2026-05-29', 'active', '2025-09-20 01:27:33', '2025-09-20 01:27:33'),
(139, 69, 3, 'Domain: superplast.in', NULL, NULL, NULL, '2025-03-03', '2027-03-03', '2027-03-03', 'active', '2025-09-20 01:27:33', '2026-03-05 01:00:03'),
(140, 69, 2, 'Hosting - superplast.in', NULL, NULL, NULL, '2024-11-28', '2026-11-27', '2026-11-27', 'active', '2025-09-20 01:27:33', '2025-11-27 01:04:27'),
(141, 69, 2, 'Hosting | SSL & Website Maintance', NULL, NULL, NULL, '2024-10-30', '2026-10-30', '2026-10-30', 'active', '2025-09-20 01:29:02', '2025-11-12 00:15:39'),
(142, 70, 6, 'Domain: sunriseconsultancy.net', NULL, NULL, NULL, '2016-12-15', '2026-12-15', '2026-10-01', 'active', '2025-09-20 02:00:42', '2025-12-21 22:53:57'),
(143, 70, 2, 'Hosting | SSL & Website Maintance', NULL, NULL, NULL, '2024-11-06', '2026-11-06', '2026-11-06', 'active', '2025-09-20 02:00:42', '2025-11-24 01:22:59'),
(144, 70, 4, 'Email', NULL, NULL, NULL, '2024-03-12', '2027-03-12', '2027-03-12', 'active', '2025-09-20 02:00:42', '2026-03-12 09:51:00'),
(145, 6, 3, 'Domain: aakritispacedesigns.com', NULL, NULL, NULL, '2025-03-13', '2030-04-13', '2030-04-13', 'active', '2025-09-20 02:11:53', '2026-04-09 15:04:07'),
(146, 6, 2, 'Hosting & SSL for linkpromotions', NULL, NULL, NULL, '2025-03-26', '2026-03-26', '2026-03-26', 'active', '2025-09-20 02:11:53', '2025-09-20 02:11:53'),
(147, 72, 2, 'Hosting | SSL & Website Maintance', NULL, NULL, NULL, '2025-05-22', '2026-05-22', '2026-05-22', 'active', '2025-09-20 02:17:49', '2025-09-24 04:52:07'),
(148, 73, 6, 'Domain: spaceagearch.com', NULL, NULL, NULL, '2016-09-06', '2026-09-06', '2026-09-06', 'active', '2025-09-20 02:31:53', '2025-09-20 02:31:53'),
(149, 73, 2, 'Hosting & Website Maintance', NULL, NULL, NULL, '2025-02-01', '2027-02-01', '2027-02-01', 'active', '2025-09-20 02:31:53', '2026-02-11 07:13:41'),
(150, 73, 4, 'Email', NULL, NULL, NULL, '2024-11-13', '2026-11-13', '2026-11-13', 'active', '2025-09-20 02:31:53', '2025-11-12 00:16:00'),
(151, 74, 2, 'Hosting | SSL | Email & Website Maintance', NULL, NULL, NULL, '2025-06-18', '2026-06-18', '2026-06-18', 'active', '2025-09-20 03:49:38', '2025-09-20 03:49:38'),
(152, 75, 3, 'Domain: sanjayagencies.com', NULL, NULL, NULL, '2024-12-05', '2026-12-05', '2026-12-05', 'active', '2025-09-20 04:14:18', '2025-12-21 22:45:22'),
(153, 75, 2, 'Hosting | SSL & Website Maintance', NULL, NULL, NULL, '2024-12-01', '2026-12-01', '2026-12-01', 'active', '2025-09-20 04:14:18', '2025-12-21 22:44:55'),
(154, 75, 4, 'Email', NULL, NULL, NULL, '2025-01-29', '2027-01-29', '2027-01-29', 'active', '2025-09-20 04:14:18', '2026-02-11 07:16:08'),
(155, 76, 6, 'Domain: nirbhayrasayan.com', NULL, NULL, NULL, '2016-03-03', '2027-03-03', '2027-03-03', 'active', '2025-09-20 04:20:29', '2026-03-05 00:59:39'),
(156, 76, 6, 'Email', NULL, NULL, NULL, '2015-10-14', '2026-10-14', '2025-10-14', 'active', '2025-09-20 04:20:29', '2025-10-10 00:40:14'),
(157, 77, 6, 'Email', NULL, NULL, NULL, '2024-10-02', '2026-10-02', '2026-09-08', 'active', '2025-09-20 05:24:40', '2025-11-26 07:04:59'),
(158, 77, 6, 'Domain: shreeraminteriors.co.in', NULL, NULL, NULL, '2019-04-12', '2027-04-12', '2027-04-12', 'active', '2025-09-20 05:24:40', '2026-04-11 09:31:12'),
(159, 78, 2, 'Hosting & SSL', NULL, NULL, NULL, '2025-09-08', '2026-09-08', '2026-09-08', 'active', '2025-09-24 04:58:53', '2025-09-24 04:58:53'),
(160, 78, 1, 'Webmail & Outlook', NULL, NULL, NULL, '2023-11-01', '2026-11-01', '2026-09-08', 'active', '2025-09-24 04:58:53', '2025-11-26 07:02:58'),
(161, 44, 8, 'Social Media (Linkedin)', NULL, NULL, NULL, '2026-01-01', '2026-07-01', '2026-07-01', 'active', '2025-11-12 04:26:53', '2026-04-11 09:29:59'),
(162, 70, 12, 'LinkedIn Page Handling', NULL, 'Pending From Client Side', 'gray', '2025-10-15', '2026-01-15', '2026-01-15', 'inactive', '2025-11-12 04:32:21', '2026-04-06 16:34:55'),
(163, 80, 3, 'Domain: aeritx.com', NULL, NULL, NULL, '2025-02-18', '2027-02-18', '2027-02-01', 'active', '2026-01-30 23:46:07', '2026-02-25 23:04:48'),
(164, 80, 13, 'Hosting & SSL For aeritx.com', NULL, NULL, NULL, '2025-02-04', '2027-02-04', '2027-02-01', 'active', '2026-01-30 23:46:07', '2026-02-25 23:05:38'),
(165, 80, 4, 'Business Starter 2 Users', NULL, NULL, NULL, '2025-02-27', '2027-02-27', '2027-02-04', 'active', '2026-01-30 23:46:07', '2026-02-25 23:06:12'),
(166, 80, 14, 'IOS Store Renewal', NULL, NULL, NULL, '2025-07-08', '2026-07-08', '2026-07-01', 'active', '2026-01-30 23:50:45', '2026-01-30 23:50:45'),
(167, 81, 14, 'IOS Store Renewal Animal Kingdom: Lost & Found', NULL, NULL, NULL, '2025-10-11', '2027-01-27', '2027-01-27', 'active', '2026-01-31 00:35:56', '2026-01-31 00:36:41'),
(168, 38, 2, 'Server Subscriptions Update Space & Storage - Added Extra Urban Sports', NULL, NULL, NULL, '2025-02-08', '2027-02-08', '2027-02-01', 'active', '2026-01-31 00:50:04', '2026-02-25 23:07:36'),
(169, 82, 6, 'Domain: shivaminds.com', NULL, NULL, NULL, '2009-05-26', '2026-05-26', '2026-05-26', 'active', '2026-01-31 01:03:53', '2026-01-31 01:03:53'),
(170, 82, 2, 'Hosting & SSL For shivaminds.com', NULL, NULL, NULL, '2025-12-06', '2026-12-06', '2026-12-06', 'active', '2026-01-31 01:04:58', '2026-01-31 01:07:11'),
(171, 69, 2, 'Website Maintenance For shivaminds.com', NULL, NULL, NULL, '2025-12-06', '2026-12-06', '2026-12-06', 'active', '2026-01-31 01:06:41', '2026-01-31 01:06:41'),
(172, 71, 16, 'AMC Laptop | Printer | Microsoft Office OS', NULL, NULL, NULL, '2025-04-01', '2026-03-31', '2026-04-01', 'active', '2026-04-06 16:27:49', '2026-04-06 16:27:49'),
(173, 71, 17, 'Tally License', NULL, NULL, NULL, '2026-02-16', '2027-04-15', '2027-03-01', 'active', '2026-04-06 16:32:29', '2026-04-06 16:32:29'),
(174, 71, 17, 'Tally Cloud', NULL, NULL, NULL, '2025-05-01', '2026-05-01', '2026-05-01', 'active', '2026-04-06 16:32:29', '2026-04-06 16:32:29'),
(175, 69, 9, 'Shivam Industries', NULL, NULL, NULL, '2025-11-01', '2026-05-01', '2026-05-01', 'active', '2026-04-15 18:19:29', '2026-04-15 18:27:04');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'text',
  `group` varchar(255) NOT NULL DEFAULT 'general',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `type`, `group`, `created_at`, `updated_at`) VALUES
(1, 'renewal_admin_email', '\"accounts@technofra.com\"', 'text', 'general', '2026-03-12 12:32:08', '2026-03-12 12:37:16'),
(2, 'renewal_notification_time', '\"09:00\"', 'text', 'general', '2026-03-12 12:32:08', '2026-03-12 17:10:16'),
(3, 'renewal_notice_days', '\"5\"', 'text', 'general', '2026-03-12 12:32:08', '2026-03-12 12:32:08'),
(4, 'renewal_notifications_enabled', '\"1\"', 'text', 'general', '2026-03-12 12:32:08', '2026-03-12 12:32:08'),
(5, 'company_name', '\"Technofra\"', 'text', 'general', '2026-03-12 18:04:20', '2026-03-12 18:04:20'),
(6, 'crm_logo', '\"uploads\\/settings\\/logo_69b3ddcd243ef0.61868078.webp\"', 'image', 'general', '2026-03-12 18:04:20', '2026-03-13 15:20:05'),
(7, 'company_email', '\"info@technofra.com\"', 'text', 'general', '2026-03-12 18:09:42', '2026-03-12 18:09:42'),
(8, 'company_phone', '\"8080803374\"', 'text', 'general', '2026-03-12 18:09:42', '2026-03-12 18:09:42'),
(9, 'address', '\"Office No. 501, 5th Floor, Ghanshyam Enclave, Laljipada New Link Road, Kandivali (West)\"', 'text', 'general', '2026-03-12 18:09:42', '2026-03-12 18:09:42'),
(10, 'city', '\"Mumbai\"', 'text', 'general', '2026-03-12 18:09:42', '2026-03-12 18:09:42'),
(11, 'state', '\"Maharashtra\"', 'text', 'general', '2026-03-12 18:09:42', '2026-03-12 18:09:42'),
(12, 'zip', '\"400067\"', 'text', 'general', '2026-03-12 18:09:42', '2026-03-12 18:09:42'),
(13, 'country', '\"India\"', 'text', 'general', '2026-03-12 18:09:42', '2026-03-12 18:09:42'),
(14, 'website', '\"https:\\/\\/technofra.com\\/\"', 'text', 'general', '2026-03-12 18:09:42', '2026-03-12 18:09:42'),
(15, 'gst_number', '\"27BAZPG8422J2Z1\"', 'text', 'general', '2026-03-12 18:09:42', '2026-03-12 18:09:42'),
(16, 'office_start_time', '\"09:00\"', 'text', 'general', '2026-03-12 18:09:42', '2026-03-12 18:09:42'),
(17, 'lunch_start_time', '\"13:30\"', 'text', 'general', '2026-03-12 18:09:42', '2026-03-12 18:09:42'),
(18, 'lunch_end_time', '\"14:30\"', 'text', 'general', '2026-03-12 18:09:42', '2026-03-12 18:09:42'),
(19, 'office_end_time', '\"18:00\"', 'text', 'general', '2026-03-12 18:09:42', '2026-03-12 18:09:42');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
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
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `user_id`, `profile_image`, `first_name`, `last_name`, `email`, `phone`, `role`, `password`, `status`, `departments`, `team`, `created_at`, `updated_at`, `deleted_at`) VALUES
(3, 5, '1773120691.jpg', 'Manish', 'Singh', 'manish@technofra.com', '7039553407', 'Staff', '$2y$10$yUFMqx0LdvC5Ex50Jh0O/.jPjQjeE/eWtdb8WPSnOA0WEP8Lazchu', 'active', '[\"Web Developers\"]', 'Web Team', '2026-03-10 11:01:31', '2026-03-27 09:48:09', NULL),
(4, 6, '1773127194_4.jpg', 'Saurabh', 'Damale', 'damalesaurabh34@gmail.com', '7709131547', 'Staff', '$2y$10$NiEl2TAF.tv2olB9FRv.mOFkKa9cm.smZX8g6bJg1BLvS.DJQALOW', 'active', NULL, NULL, '2026-03-10 11:29:00', '2026-03-10 12:49:54', NULL),
(5, 7, '1773122459.png', 'Roshan', 'Yadav', 'roshany544@gmail.com', '8928339535', 'Staff', '$2y$10$C/hgmHYeeIG4mb6PeGBtLu/6oxQgGM2Bt8eL3wne3Tw74zw9nhRS.', 'active', NULL, NULL, '2026-03-10 11:30:59', '2026-03-10 11:30:59', NULL),
(6, 9, '1773123704.jpg', 'Pradnya', 'Jagdale', 'pradnyajagdale96k@gmail.com', '7558542831', 'Staff', '$2y$10$rPFmvCHIAv4w5TeQTUEMjOKLAGmiivpacpxn0pkz3v88/MoEDyoUm', 'active', NULL, NULL, '2026-03-10 11:51:44', '2026-03-10 11:51:44', NULL),
(8, 11, '1773140326.jpeg', 'Shubham', 'Shinde', 'shindeshubham7792@gmail.com', '8850773797', 'Staff', '$2y$10$JhPg9sD73n/827WBHRWzBex.RRbe.SacdLVonIe4ThW06mk/ZFJT.', 'active', '[\"Web Developers\"]', 'Web Team', '2026-03-10 16:28:46', '2026-03-27 15:17:18', NULL),
(9, 12, '1773143372.jpg', 'Gopal', 'Giri', 'gopal@technofra.com', '8080803374', 'super-admin', '$2y$10$OnCMKiu98Y2aDSP/y5WejOVsHLC8Glu3g9K7F7GQTWLSYcQW4t7dO', 'active', '[\"Admin\"]', NULL, '2026-03-10 17:19:32', '2026-03-11 14:49:59', NULL),
(10, 17, NULL, 'Anjali', 'Jaiswar', 'anjalijaiswar8686@gmail.com', '7021824246', 'Staff', '$2y$10$WiPwykdfhdU6Q4fADHcq5emMAea70weqYGJeFLv6Lr9xQ4bJ19e5C', 'active', '[\"Design and Graphics\"]', NULL, '2026-03-14 10:32:00', '2026-03-14 10:32:00', NULL),
(11, 18, NULL, 'Khushi', 'Yadav', 'accounts@technofra.com', '8080803375', 'Staff', '$2y$10$TJ36hW.ND.5Yml2nIP4MxutFElsyJCG2NIbYVtkRIzPAoFGmqOvG6', 'active', '[\"Admin\"]', 'Account Team', '2026-03-19 12:51:10', '2026-03-19 12:53:22', NULL),
(15, 24, NULL, 'Amit', 'Kumar', 'amit@example.com', '9876543210', 'admin', '$2y$10$JYZ/suW9gpWGXyNLnlFnKulOwzUjRPseq2HlDMKdstd98cTXzlFcO', 'active', '[\"Admin\",\"Web Developers\"]', 'Web Team', '2026-04-11 11:25:34', '2026-04-11 11:28:08', '2026-04-11 11:28:08'),
(16, 25, NULL, 'dinesh', 'dinesh', 'dp778317@gmail.com2', '8080621003', 'digital marketing', '$2y$10$64T7.va.MGRdilX2fzyGQON2hZDbo9Vg8zo7LJ.e/YBvzUGYSBQWi', 'active', '[\"Web Team\"]', 'Web Team', '2026-04-11 11:26:35', '2026-04-11 11:28:02', '2026-04-11 11:28:02');

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#3498db',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `followers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`followers`)),
  `assignees` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`assignees`)),
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `status` enum('not_started','in_progress','on_hold','completed','cancelled') NOT NULL DEFAULT 'not_started',
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `start_date` date DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `description`, `project_id`, `followers`, `assignees`, `tags`, `status`, `priority`, `start_date`, `deadline`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 'Creating Policy Pages in E-commerce website', 'Creating Policy Pages in E-commerce website for Razorpay integration', 2, '[\"3\",\"4\"]', '[\"5\"]', '[\"web-design\"]', 'completed', 'high', '2026-03-10', '2026-03-11', '2026-03-10 11:45:58', '2026-03-10 15:01:19', NULL),
(3, 'Correcting AMC (Offline & Online) Flow', 'Correcting AMC Offline and Online buying and diagnosis flow.', 2, '[\"3\",\"5\"]', '[\"4\"]', '[\"web-design\"]', 'in_progress', 'high', '2026-03-10', '2026-03-13', '2026-03-10 11:46:23', '2026-03-13 10:13:30', NULL),
(4, 'technofra CRM', NULL, NULL, NULL, NULL, '[]', 'in_progress', 'medium', NULL, NULL, '2026-03-10 13:12:33', '2026-03-11 15:01:20', NULL),
(6, 'Adding with installation product on e-commerce product and modify delivery flow', '1. Change in e-commerce product table\r\n2. Some modification in order flow', 2, '[\"4\",\"9\"]', '[\"4\",\"5\"]', '[\"development\"]', 'completed', 'medium', '2026-03-11', '2026-03-12', '2026-03-11 09:32:28', '2026-03-12 15:58:13', NULL),
(7, 'Login with  Google, email password, and phone number OTP', 'Client want login in phone otp which is done ,  Google OAuth and Email password. Want to complete in customer Application and final Customer.\r\nChanged the status to ON Hold because need the api of login  through email password', 2, '[\"3\",\"4\",\"5\"]', '[\"8\"]', '[\"development\"]', 'in_progress', 'medium', '2026-03-11', '2026-03-14', '2026-03-11 09:51:55', '2026-03-11 14:40:54', NULL),
(9, 'Cancel Order function in the ecommerce and replace product  and also showing the data properly', 'In these task as per the client changes \r\n1) the User can cancel the order when the order is not proceed to shipped \r\n2) the User can replace the order\r\n3) should show the data properly it is not shown properly', 2, '[\"3\",\"4\",\"9\"]', '[\"8\"]', '[]', 'completed', 'medium', '2026-03-11', '2026-03-12', '2026-03-11 14:57:29', '2026-03-11 16:38:20', NULL),
(10, 'Oceanic Video And Catelouge', 'Working on Product Catelogue \r\nand VIdeo genrate', 3, '[\"9\"]', '[\"3\"]', '[\"web-design\"]', 'on_hold', 'medium', '2026-03-11', '2026-03-12', '2026-03-11 14:57:44', '2026-03-11 15:01:27', NULL),
(11, 'My CRM Working', 'Working on APi and pending work', NULL, '[\"9\"]', '[\"3\"]', '[\"development\",\"CRM\",\"Project\",\"Task\",\"Leads\",\"Renewal\"]', 'in_progress', 'medium', NULL, NULL, '2026-03-11 14:59:15', '2026-03-11 14:59:15', NULL),
(12, 'Add Rewards', 'want to add the rewards in the orders , service and reward section', 2, '[\"3\",\"4\"]', '[\"8\"]', '[\"development\"]', 'completed', 'medium', '2026-03-12', '2026-03-13', '2026-03-12 09:53:01', '2026-03-13 11:55:58', NULL),
(13, 'Separate Online AMC and Offline AMC', 'Separate Online AMC and Offline AMC', 2, '[\"4\"]', '[\"5\"]', '[\"development\"]', 'completed', 'high', '2026-03-12', '2026-03-14', '2026-03-12 16:00:01', '2026-03-13 16:00:48', NULL),
(14, 'Adding all the necessary data in the admin panel to store the product in the warehouse.', 'Creating warehouse, vendor, purchase bill, categories, brand etc for Razorpay Payment Gateway', 2, '[\"4\"]', '[\"5\"]', '[\"development\"]', 'completed', 'high', '2026-03-13', '2026-03-16', '2026-03-13 16:25:34', '2026-03-16 14:54:18', NULL),
(16, 'Updating Signup flow for app and admin panel', 'During staff signup, the following documents should be required: Address Proof, Electricity Bill / Light Bill, and \r\nQualification Documents.', 2, '[\"4\"]', '[\"5\"]', '[\"development\"]', 'completed', 'medium', '2026-03-16', '2026-03-16', '2026-03-16 15:08:35', '2026-03-16 18:07:10', NULL),
(17, 'Adding Reward System for e-commerc order product', 'A rewards feature should be introduced for customers.', 2, '[\"4\"]', '[\"5\"]', '[\"development\"]', 'completed', 'medium', '2026-03-16', '2026-03-17', '2026-03-16 18:08:20', '2026-03-17 17:04:30', NULL),
(18, 'Creating api for reward system', 'Creating api for reward system \r\n- Claim Reward API\r\n- Check Reward Availability API\r\n- Display Reward in order detail API', 2, '[\"4\"]', '[\"5\"]', '[\"development\"]', 'completed', 'medium', '2026-03-18', '2026-03-18', '2026-03-18 09:41:07', '2026-03-19 09:39:39', NULL),
(19, 'Quantity updation after buy now to checkout page', 'Quantity updation after buy now to checkout page', 2, '[\"4\"]', '[\"5\"]', '[\"development\"]', 'completed', 'medium', '2026-03-19', '2026-03-19', '2026-03-19 11:58:41', '2026-03-19 15:12:37', NULL),
(20, 'My Account changes', 'My Account changes if user not logged in show popup for login', 2, NULL, '[\"5\"]', '[\"development\"]', 'completed', 'medium', '2026-03-19', '2026-03-19', '2026-03-19 15:52:07', '2026-03-20 10:46:20', NULL),
(21, 'Categories filter feature in crackteck', 'Categories filter feature in crackteck', 2, NULL, '[\"5\"]', '[\"development\"]', 'completed', 'medium', '2026-03-20', '2026-03-20', '2026-03-20 10:54:26', '2026-03-20 14:44:15', NULL),
(22, 'testing my crm', NULL, NULL, '[]', '[]', '[]', 'not_started', 'medium', NULL, NULL, '2026-04-13 17:30:31', '2026-04-13 17:30:31', NULL),
(23, 'testing mobile application', NULL, NULL, '[]', '[]', '[]', 'not_started', 'medium', NULL, NULL, '2026-04-13 17:33:27', '2026-04-13 17:33:27', NULL),
(24, 'mobile application testing', NULL, NULL, '[\"4\",\"3\"]', '[\"8\"]', '[\"hfjfk\"]', 'not_started', 'low', '2026-04-13', NULL, '2026-04-13 18:08:47', '2026-04-13 18:08:47', NULL),
(25, 'testing app', NULL, 3, '[\"3\",\"4\"]', '[\"8\"]', '[]', 'on_hold', 'low', '2026-04-13', '2026-04-28', '2026-04-13 18:10:05', '2026-04-14 15:17:41', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `task_attachments`
--

CREATE TABLE `task_attachments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `task_id` bigint(20) UNSIGNED NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_attachments`
--

INSERT INTO `task_attachments` (`id`, `task_id`, `file_name`, `file_path`, `file_type`, `file_size`, `created_at`, `updated_at`) VALUES
(2, 2, 'Policies.pdf', 'uploads/task_attachments/2/1773123358_Policies.pdf', 'application/pdf', 17323, '2026-03-10 11:45:58', '2026-03-10 17:15:54'),
(3, 3, 'WhatsApp Image 2026-03-10 at 10.06.34 AM.jpeg', 'uploads/task_attachments/3/1773128302_WhatsApp Image 2026-03-10 at 10.06.34 AM.jpeg', 'image/jpeg', 33830, '2026-03-10 13:08:22', '2026-03-13 10:11:03'),
(4, 4, 'WhatsApp Image 2026-03-10 at 10.06.34 AM.jpeg', 'uploads/task_attachments/4/1773128553_WhatsApp Image 2026-03-10 at 10.06.34 AM.jpeg', 'image/jpeg', 33830, '2026-03-10 13:12:33', '2026-03-10 16:30:18');

-- --------------------------------------------------------

--
-- Table structure for table `task_comments`
--

CREATE TABLE `task_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `task_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_comments`
--

INSERT INTO `task_comments` (`id`, `task_id`, `user_id`, `comment`, `created_at`, `updated_at`) VALUES
(1, 2, 7, 'All the page in  Policies.pdf are added in E-commerce website', '2026-03-10 15:00:59', '2026-03-10 15:00:59'),
(2, 25, 1, 'hi testing', '2026-04-15 16:41:51', '2026-04-15 16:41:51');

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `name`, `description`, `icon_path`, `is_active`, `created_at`, `updated_at`) VALUES
(6, 'Graphic Designer', 'Graphic Team', 'uploads/team-icons/team_69b2b396925278.63759675.png', 1, '2026-03-19 12:52:58', '2026-03-19 12:52:58'),
(7, 'Web Team', 'Support for Website', 'uploads/team-icons/team_69afad648dd960.09277989.png', 1, '2026-03-19 12:52:58', '2026-03-19 12:52:58'),
(8, 'Account Team', 'Account Team', NULL, 1, '2026-03-19 12:52:58', '2026-03-19 12:52:58');

-- --------------------------------------------------------

--
-- Table structure for table `todos`
--

CREATE TABLE `todos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attachments`)),
  `task_date` date NOT NULL,
  `task_time` time DEFAULT NULL,
  `repeat_interval` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `repeat_unit` enum('day','week','month','year') NOT NULL DEFAULT 'day',
  `repeat_days` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`repeat_days`)),
  `reminder_time` time DEFAULT NULL,
  `starts_on` date NOT NULL,
  `ends_type` enum('never','on','after') NOT NULL DEFAULT 'never',
  `ends_on` date DEFAULT NULL,
  `ends_after_occurrences` int(10) UNSIGNED DEFAULT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL,
  `last_reminded_occurrence_on` date DEFAULT NULL,
  `last_reminder_sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `todos`
--

INSERT INTO `todos` (`id`, `user_id`, `title`, `description`, `attachments`, `task_date`, `task_time`, `repeat_interval`, `repeat_unit`, `repeat_days`, `reminder_time`, `starts_on`, `ends_type`, `ends_on`, `ends_after_occurrences`, `is_completed`, `completed_at`, `last_reminded_occurrence_on`, `last_reminder_sent_at`, `created_at`, `updated_at`) VALUES
(1, 5, 'Working List', 'Oceanic \nMy CRM', NULL, '2026-03-14', '17:37:00', 1, 'day', NULL, NULL, '2026-03-14', 'never', NULL, NULL, 0, NULL, '2026-04-15', '2026-04-15 17:37:03', '2026-03-14 17:36:02', '2026-04-15 17:37:07'),
(2, 12, 'Technofra Job Posting List', 'Backoffice & Accountant Team (1)\r\nWebsite Designer Team Leader (1)\r\nWebsite Developer (1)', '[]', '2026-03-13', '18:04:00', 1, 'day', NULL, '07:03:00', '2026-03-13', 'never', NULL, NULL, 0, NULL, '2026-04-16', '2026-04-16 07:03:03', '2026-03-14 18:03:27', '2026-04-16 07:03:08'),
(4, 1, 'shubham', 'hjxkdks', '[{\"name\":\"website content.docx\",\"path\":\"uploads\\/todo_attachments\\/1776255564_WaXE9K0Erm3P.docx\"},{\"name\":\"MPB Presentation.pdf\",\"path\":\"uploads\\/todo_attachments\\/1776257518_g9352CybL0Bl.pdf\"}]', '2026-04-11', '14:35:00', 1, 'day', NULL, '14:45:00', '2026-04-11', 'on', '2026-04-30', NULL, 0, NULL, '2026-04-15', '2026-04-15 14:45:03', '2026-04-11 14:03:55', '2026-04-16 11:57:03'),
(7, 7, 'Wrap Pack N Carry', 'Wrap Pack N Carry admin panel banana hai', '[]', '2026-04-11', '18:58:00', 1, 'day', NULL, '12:00:00', '2026-04-11', 'on', '2026-04-16', NULL, 1, '2026-04-16 09:43:52', '2026-04-15', '2026-04-15 12:00:05', '2026-04-11 18:59:03', '2026-04-16 09:43:52'),
(8, 11, 'take the changes from the sir', 'take changes from the sir of my crm application', NULL, '2026-04-13', '14:45:00', 1, 'day', NULL, '14:45:00', '2026-04-13', 'after', NULL, 1, 0, NULL, '2026-04-13', '2026-04-13 14:45:05', '2026-04-13 13:16:43', '2026-04-13 14:45:11'),
(9, 1, 'Prepare weekly report', 'Collect status from all teams', '[{\"name\":\"Crackteck logo.png\",\"path\":\"uploads\\/todo_attachments\\/1776167122_uNUueXi5H8dv.png\"},{\"name\":\"Screenshot_20260415_163653.jpg\",\"path\":\"uploads\\/todo_attachments\\/1776255376_Ig5WpIqrygZm.jpg\"}]', '2026-04-14', '10:00:00', 1, 'week', '[\"monday\",\"friday\"]', '09:30:00', '2026-04-16', 'never', NULL, NULL, 0, NULL, NULL, NULL, '2026-04-14 17:15:22', '2026-04-16 11:57:33'),
(11, 12, 'Agreement - Terms & Conditions for Vendors, Client Projects & Team', NULL, '[]', '2026-04-15', NULL, 1, 'day', NULL, NULL, '2026-04-15', 'never', NULL, NULL, 0, NULL, '2026-04-16', '2026-04-16 09:00:06', '2026-04-15 17:57:19', '2026-04-16 09:00:11'),
(12, 1, 'Prepare weekly report - updated', 'Include blockers and approvals', '[{\"name\":\"bag assignment.jpg.jpeg\",\"path\":\"uploads\\/todo_attachments\\/1776321316_3qqJvvsuAdcQ.jpeg\"},{\"name\":\"IMG-20260416-WA0005.jpg\",\"path\":\"uploads\\/todo_attachments\\/1776322183_JUciiuQKmrZB.jpg\"}]', '2026-04-14', '11:00:00', 1, 'week', '[\"monday\"]', '10:30:00', '2026-04-14', 'after', NULL, 5, 0, NULL, NULL, NULL, '2026-04-16 12:02:33', '2026-04-16 12:47:34'),
(13, 1, 'new testing api', 'testing', '[{\"name\":\"IMG-20260415-WA0027.jpg\",\"path\":\"uploads\\/todo_attachments\\/1776322262_zMfpjVKYDvy1.jpg\"}]', '2026-04-16', '12:20:00', 1, 'day', NULL, '12:25:00', '2026-04-17', 'never', NULL, NULL, 0, NULL, NULL, NULL, '2026-04-16 12:21:02', '2026-04-16 12:39:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `status`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'admin@gmail.com', NULL, '$2y$10$zFzu5Gs0d11zLmcK5uMzjOs54FMm0kAf/O4BzeAx66kQE8UoboxJ6', 1, 'YQWhdjTG8f9fzT3bzLR4x8rDEJm2dwZLdZfsook4Tt81AyNjvZPj0KBPyExV', '2026-03-07 17:50:17', '2026-03-07 17:50:17'),
(5, 'Manish Singh', 'manish@technofra.com', NULL, '$2y$10$TXEBG6XF3mgfWBzJR6Z5uumDG1LJ5YQ.khU/tzd49aWXSvdcbcdFy', 1, 'xjODvUWG1u3rGilUoqajAkUnXKEXglRG8S3W6Em6vWD1fj2dexR0YIDhs4J8', '2026-03-10 11:01:31', '2026-03-10 11:01:31'),
(6, 'Saurabh Damale', 'damalesaurabh34@gmail.com', NULL, '$2y$10$.7nICkWaDjBa3DO0Nfhqruw03rr/ThyBESWOVo2miz2J472uGmYW2', 1, NULL, '2026-03-10 11:29:00', '2026-03-10 11:29:00'),
(7, 'Roshan Yadav', 'roshany544@gmail.com', NULL, '$2y$10$mVh0z.ih4YIspCcwzY/VWesb22.a6iXIJ.yJ2WgY5m4JINMdax.Ge', 1, NULL, '2026-03-10 11:30:59', '2026-03-10 11:30:59'),
(8, 'Satyam Tiwari', 'satyam@srbcomputers.com', NULL, '$2y$10$N.mNvLpi8clvx7HUzhEXsOmO1nYyWo55lkyyIjapBcI1AibwI4qpC', 1, NULL, '2026-03-10 11:35:44', '2026-03-10 11:35:44'),
(9, 'Pradnya Jagdale', 'pradnyajagdale96k@gmail.com', NULL, '$2y$10$YaEhL2n7kbBve5UbhsSjNOF3SoCcYShLK8wrb64Vk8EH3ENuu5gVG', 1, NULL, '2026-03-10 11:51:44', '2026-03-10 11:51:44'),
(11, 'Shubham Shinde', 'shindeshubham7792@gmail.com', NULL, '$2y$10$1r9T59fsR4Otz/idIgz7b.cOE.lHPg7iERYMQMWtOA5V2laS6BL6i', 1, 'hWJmBTO9TnJQa4mWLgAReBkVZ2j968WmHIMwXx98yTpTituLnRxBxg7SPoUu', '2026-03-10 16:28:46', '2026-03-27 15:17:18'),
(12, 'Gopal Giri', 'gopal@technofra.com', NULL, '$2y$10$iyjhOCODGUUTWgjAVy8qOeKSBsFsKW9yJshNVg9w65bt2MB8tzqiG', 1, 'U2FAHo1MvLEHc6ECwbNskO8SYdJZ0fvYx4X2M5pI5ZH5OVDSXDPtzOnpg8R5', '2026-03-10 17:19:32', '2026-03-11 14:49:59'),
(13, 'Arnav', 'vkpaints78@gmail.com', NULL, '$2y$10$P81UAHxq3t/fRW7V3h2Qx.0lehMhl4n9sXXo4nBu70nCVPZp4GOn.', 1, NULL, '2026-03-11 14:53:24', '2026-03-11 14:53:24'),
(17, 'Anjali Jaiswar', 'anjalijaiswar8686@gmail.com', NULL, '$2y$10$3hZQ.XD/70f4M4xIHiezbOfYlyHoqWECrpi5BWztVxH9vl9vy48Fa', 1, 'y7OMP3NuMYPT2z0xzcZfnGajto28dEx6uOfdQuUlAHzWm9189vT3ayKKtB6I', '2026-03-14 10:32:00', '2026-03-14 10:32:00'),
(18, 'Khushi Yadav', 'accounts@technofra.com', NULL, '$2y$10$wXee1fqp1UcGyQmGByyH1.bkRKKXDkTNEms/QkZ7k1Af9f9isrRwW', 1, 'fhU7uDGiMZgSo9yNf6e58EvFa9ZBe9bS0GqW5ETGR2e7bafvtA1alSwWkPop', '2026-03-19 12:51:10', '2026-03-19 12:51:10'),
(22, 'Acme Pvt Ltd', 'abc@gmail.com', NULL, '$2y$10$jRka7wsNQW9hgW6QZamDsuQgcWV9JRLj/ayb8j4eAM8WTheyOgp5.', 1, NULL, '2026-04-07 15:38:15', '2026-04-07 15:38:15'),
(23, 'shubham technofra', 'shubhamshinde@gmail.com', NULL, '$2y$10$WJkZAgyHQVLY6bjjuAYTpOnJPNhlz31nS64LioIU08tJ3kRNug57u', 1, NULL, '2026-04-07 16:47:40', '2026-04-07 16:53:45'),
(24, 'Amit Kumar', 'amit@example.com', NULL, '$2y$10$RxJBhIUyxaMS/6Xq2mxByeGXYiP0.P2ID0TTPvAz6nUkuS1QFVfIu', 1, NULL, '2026-04-11 11:25:34', '2026-04-11 11:25:34'),
(25, 'dinesh dinesh', 'dp778317@gmail.com2', NULL, '$2y$10$Ex.vlZLQOLDkRflfpBNeAet5Q20JLZT/a1eAYok93oZJU1G9PcIku', 1, NULL, '2026-04-11 11:26:35', '2026-04-11 11:26:35');

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vendors`
--

INSERT INTO `vendors` (`id`, `name`, `email`, `phone`, `address`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Tizzy Cloud', 'support@tiizzycloud.in', '9004330467', NULL, 1, '2025-08-23 01:23:19', '2025-08-23 01:23:19'),
(2, 'Hosting Spell', 'support@hostingspell.com', '9409594000', NULL, 1, '2025-09-01 04:35:51', '2025-09-01 04:35:51'),
(3, 'GoDaddy', 'renewals@godaddy.com', '04067607600', NULL, 1, '2025-09-01 05:00:40', '2025-09-05 05:58:47'),
(4, 'Shivaami Cloud Services Pvt. Ltd.', 'accounts@shivaami.com', '7757841333', NULL, 1, '2025-09-05 06:00:21', '2025-09-05 06:04:44'),
(5, 'Shopify', 'support@shopify.com', '0000000000', NULL, 1, '2025-09-09 02:01:20', '2025-09-09 02:01:20'),
(6, 'Resellerclub India', 'helpdesk@resellerclub.com', '02267209090', NULL, 1, '2025-09-09 06:04:53', '2025-09-09 06:04:53'),
(7, 'Social Media 6 Months', 'accounts@technofra.com', '8080803374', NULL, 1, '2025-09-13 02:15:22', '2025-09-13 02:15:22'),
(8, 'Social Media 3 Months', 'gopal@technofra.com', '8080803374', NULL, 1, '2025-09-13 02:15:52', '2025-09-13 02:15:52'),
(9, 'SEO 6 Months', 'info@technofra.com', '8080721003', NULL, 1, '2025-09-13 02:16:56', '2025-09-13 02:16:56'),
(10, 'Aeserver', 'director@krossboundaries.ae', '1234567890', NULL, 1, '2025-09-13 06:02:05', '2025-09-13 06:02:05'),
(11, 'Social Media For Yearly', 'ertyu@hj.com', '0967876541', NULL, 1, '2025-09-20 00:38:39', '2025-09-20 00:38:39'),
(12, 'Social Media 2 Months', 'qwsdfgfh@ghgfjg', '23456785678', NULL, 1, '2025-11-12 04:28:34', '2025-11-12 04:28:34'),
(13, 'Hostinger', 'hostinger@gmail.com', '0000000000', NULL, 1, '2025-12-04 04:33:01', '2025-12-04 04:33:01'),
(14, 'Apple Developer Program IOS Store Renewal', 'iosstore@gmail.com', '1234567890', NULL, 1, '2026-01-30 23:48:45', '2026-01-30 23:48:45'),
(15, 'Mukund Kava', 'mukundkava16@gmail.com', '8976423572', NULL, 1, '2026-04-03 12:15:01', '2026-04-03 12:15:01'),
(16, 'Desktop & Laptop System Non Comprehensive AMC Renewal', 'support@technofra.com', '0000000000', NULL, 1, '2026-04-06 12:01:41', '2026-04-06 12:01:41'),
(17, 'Parth Infotech Solutions Pvt. Ltd. - Tally Certified Partner', 'tally@parthinfotech.com', '7498082769', NULL, 1, '2026-04-06 16:05:52', '2026-04-06 16:05:52'),
(18, 'Desktop & Laptop System Non Comprehensive AMC Renewal', 'dfg@gmail.com', '9876543219', NULL, 1, '2026-04-06 16:23:43', '2026-04-06 16:23:43');

-- --------------------------------------------------------

--
-- Table structure for table `vendor_services`
--

CREATE TABLE `vendor_services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `vendor_id` bigint(20) UNSIGNED NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `service_details` text DEFAULT NULL,
  `plan_type` enum('monthly','yearly','quarterly') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `billing_date` date DEFAULT NULL,
  `status` enum('active','inactive','expired','pending') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vendor_services`
--

INSERT INTO `vendor_services` (`id`, `vendor_id`, `service_name`, `service_details`, `plan_type`, `start_date`, `end_date`, `billing_date`, `status`, `created_at`, `updated_at`) VALUES
(3, 13, 'Headquaters VPS Hosting', '<p>Headquater CRM&nbsp;<br><strong>Login Email :-</strong> Rohit@technofra.com</p>', 'monthly', '2025-07-23', '2026-04-23', '2025-07-23', 'active', '2025-12-04 05:36:00', '2026-03-27 13:22:09'),
(4, 13, 'Crackteck VPS Hostinger', '<p>Creackteck CRM&nbsp;<br><strong>Email Login :-</strong> crackteck2025@gmail.com</p>', 'monthly', '2025-11-25', '2026-05-09', '2025-11-25', 'active', '2025-12-04 05:41:45', '2026-04-11 09:31:50'),
(5, 13, 'Aeritx VPS Hosting', '<p>Aeritx CRM&nbsp;<br><strong>Email Login:</strong>- sales@aeritx.com</p>', 'monthly', '2025-03-03', '2026-05-04', '2025-03-03', 'active', '2025-12-05 05:59:58', '2026-04-03 09:35:52'),
(6, 2, 'SSD Web Hosting - Jupiter', NULL, 'yearly', '2024-01-25', '2027-01-25', '2027-01-25', 'active', '2025-12-21 23:27:59', '2026-01-26 04:49:02'),
(7, 14, 'ADWENTURE ETHERNET RELIABLE PRIVATE LIMITED {aeritx} IOS Store Renewal', NULL, 'yearly', '2025-07-08', '2026-07-08', NULL, 'active', '2026-01-31 00:00:11', '2026-01-31 00:00:11'),
(8, 14, 'IOS Store Renewal:- Animal Kingdom', NULL, 'yearly', '2026-01-27', '2027-01-27', NULL, 'active', '2026-01-31 04:12:19', '2026-01-31 04:12:19'),
(9, 15, 'Shivam Industries SEO', NULL, 'monthly', '2026-01-19', '2026-04-19', NULL, 'active', '2026-04-03 12:16:56', '2026-04-03 12:16:56'),
(11, 17, 'Tally Cloud - Harmony Montessori', NULL, 'yearly', '2025-06-02', '2026-06-02', '2026-05-01', 'active', '2026-04-06 16:13:35', '2026-04-06 16:13:35'),
(12, 17, 'Tally License - Harmony Montessori', NULL, 'yearly', '2025-03-31', '2027-03-31', '2027-03-31', 'active', '2026-04-06 16:13:35', '2026-04-06 16:13:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookcall`
--
ALTER TABLE `bookcall`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_booking_slot` (`booking_date`,`booking_time`);

--
-- Indexes for table `calendar_events`
--
ALTER TABLE `calendar_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `calendar_events_event_date_index` (`event_date`),
  ADD KEY `calendar_events_notification_sent_index` (`notification_sent`),
  ADD KEY `calendar_events_created_by_index` (`created_by`);

--
-- Indexes for table `chatbot_leads`
--
ALTER TABLE `chatbot_leads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_session_token` (`session_token`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_issues`
--
ALTER TABLE `client_issues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_issues_project_id_foreign` (`project_id`),
  ADD KEY `client_issues_customer_id_foreign` (`customer_id`);

--
-- Indexes for table `client_issue_tasks`
--
ALTER TABLE `client_issue_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_issue_tasks_client_issue_id_foreign` (`client_issue_id`);

--
-- Indexes for table `client_issue_team_assignments`
--
ALTER TABLE `client_issue_team_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_issue_team_assignments_client_issue_id_foreign` (`client_issue_id`),
  ADD KEY `client_issue_team_assignments_assigned_by_foreign` (`assigned_by`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customers_email_unique` (`email`),
  ADD KEY `customers_user_id_foreign` (`user_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `departments_name_unique` (`name`);

--
-- Indexes for table `digital_marketing_leads`
--
ALTER TABLE `digital_marketing_leads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `notification_reads`
--
ALTER TABLE `notification_reads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `notification_reads_user_id_service_id_notification_type_unique` (`user_id`,`service_id`,`notification_type`),
  ADD KEY `notification_reads_service_id_foreign` (`service_id`),
  ADD KEY `notification_reads_user_id_service_id_index` (`user_id`,`service_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `projects_customer_id_foreign` (`customer_id`);

--
-- Indexes for table `project_comments`
--
ALTER TABLE `project_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_comments_project_id_foreign` (`project_id`),
  ADD KEY `project_comments_user_id_foreign` (`user_id`);

--
-- Indexes for table `project_files`
--
ALTER TABLE `project_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_files_project_id_foreign` (`project_id`),
  ADD KEY `project_files_uploaded_by_foreign` (`uploaded_by`);

--
-- Indexes for table `project_issues`
--
ALTER TABLE `project_issues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_issues_project_id_foreign` (`project_id`),
  ADD KEY `project_issues_customer_id_foreign` (`customer_id`);

--
-- Indexes for table `project_milestones`
--
ALTER TABLE `project_milestones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_milestones_project_id_foreign` (`project_id`);

--
-- Indexes for table `project_status_logs`
--
ALTER TABLE `project_status_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_status_logs_project_id_foreign` (`project_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `services_client_id_foreign` (`client_id`),
  ADD KEY `services_vendor_id_foreign` (`vendor_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staff_email_unique` (`email`),
  ADD KEY `staff_user_id_foreign` (`user_id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tags_name_unique` (`name`),
  ADD UNIQUE KEY `tags_slug_unique` (`slug`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tasks_project_id_foreign` (`project_id`);

--
-- Indexes for table `task_attachments`
--
ALTER TABLE `task_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_attachments_task_id_foreign` (`task_id`);

--
-- Indexes for table `task_comments`
--
ALTER TABLE `task_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_comments_task_id_foreign` (`task_id`),
  ADD KEY `task_comments_user_id_foreign` (`user_id`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teams_name_unique` (`name`);

--
-- Indexes for table `todos`
--
ALTER TABLE `todos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `todos_user_id_foreign` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vendors_email_unique` (`email`);

--
-- Indexes for table `vendor_services`
--
ALTER TABLE `vendor_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_services_vendor_id_foreign` (`vendor_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookcall`
--
ALTER TABLE `bookcall`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `calendar_events`
--
ALTER TABLE `calendar_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `chatbot_leads`
--
ALTER TABLE `chatbot_leads`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `client_issues`
--
ALTER TABLE `client_issues`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `client_issue_tasks`
--
ALTER TABLE `client_issue_tasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `client_issue_team_assignments`
--
ALTER TABLE `client_issue_team_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `digital_marketing_leads`
--
ALTER TABLE `digital_marketing_leads`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `notification_reads`
--
ALTER TABLE `notification_reads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `project_comments`
--
ALTER TABLE `project_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `project_files`
--
ALTER TABLE `project_files`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `project_issues`
--
ALTER TABLE `project_issues`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `project_milestones`
--
ALTER TABLE `project_milestones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `project_status_logs`
--
ALTER TABLE `project_status_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=176;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `task_attachments`
--
ALTER TABLE `task_attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `task_comments`
--
ALTER TABLE `task_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `todos`
--
ALTER TABLE `todos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `vendor_services`
--
ALTER TABLE `vendor_services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `calendar_events`
--
ALTER TABLE `calendar_events`
  ADD CONSTRAINT `calendar_events_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `client_issues`
--
ALTER TABLE `client_issues`
  ADD CONSTRAINT `client_issues_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `client_issues_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `client_issue_tasks`
--
ALTER TABLE `client_issue_tasks`
  ADD CONSTRAINT `client_issue_tasks_client_issue_id_foreign` FOREIGN KEY (`client_issue_id`) REFERENCES `client_issues` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `client_issue_team_assignments`
--
ALTER TABLE `client_issue_team_assignments`
  ADD CONSTRAINT `client_issue_team_assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `client_issue_team_assignments_client_issue_id_foreign` FOREIGN KEY (`client_issue_id`) REFERENCES `client_issues` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_reads`
--
ALTER TABLE `notification_reads`
  ADD CONSTRAINT `notification_reads_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notification_reads_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `project_comments`
--
ALTER TABLE `project_comments`
  ADD CONSTRAINT `project_comments_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_files`
--
ALTER TABLE `project_files`
  ADD CONSTRAINT `project_files_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_files_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `project_issues`
--
ALTER TABLE `project_issues`
  ADD CONSTRAINT `project_issues_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_issues_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_milestones`
--
ALTER TABLE `project_milestones`
  ADD CONSTRAINT `project_milestones_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_status_logs`
--
ALTER TABLE `project_status_logs`
  ADD CONSTRAINT `project_status_logs_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `services_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `task_attachments`
--
ALTER TABLE `task_attachments`
  ADD CONSTRAINT `task_attachments_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_comments`
--
ALTER TABLE `task_comments`
  ADD CONSTRAINT `task_comments_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `todos`
--
ALTER TABLE `todos`
  ADD CONSTRAINT `todos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_services`
--
ALTER TABLE `vendor_services`
  ADD CONSTRAINT `vendor_services_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
