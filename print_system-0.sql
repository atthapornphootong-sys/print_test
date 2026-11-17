-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 29, 2026 at 06:15 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `print_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `activity_id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `activity_type` enum('print','add_points','withdraw_points','refund_money') NOT NULL,
  `points` int(11) DEFAULT 0,
  `points_before` int(11) DEFAULT 0,
  `points_after` int(11) DEFAULT 0,
  `printer_id` int(11) DEFAULT NULL,
  `activity_detail` varchar(255) DEFAULT NULL,
  `pages` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`activity_id`, `student_id`, `activity_type`, `points`, `points_before`, `points_after`, `printer_id`, `activity_detail`, `pages`, `created_at`) VALUES
(2, '6601103071008', 'add_points', 100, 500, 600, NULL, 'เติมพอยต์', 0, '2026-03-18 04:49:07'),
(3, '6601103071008', 'refund_money', -100, 600, 500, NULL, 'ไม่ระบุ', 0, '2026-03-18 04:49:12'),
(4, '6601103071008', 'add_points', 100, 500, 600, NULL, 'เติมพอยต์', 0, '2026-03-18 04:49:25'),
(5, '6601103071008', 'refund_money', -100, 600, 500, NULL, 'ไม่ระบุ', 0, '2026-03-18 04:49:29'),
(6, '6601103071020', 'add_points', 100, 0, 100, NULL, 'เติมพอยต์', 0, '2026-03-19 09:16:48'),
(7, '6601103071026', 'add_points', 100, 0, 100, NULL, 'เติมพอยต์', 0, '2026-03-19 09:17:46'),
(8, '6601103071026', 'refund_money', -50, 100, 50, NULL, 'ไม่ระบุ', 0, '2026-03-19 09:19:47'),
(9, '6601103071020', 'refund_money', -5, 100, 95, NULL, 'ไม่ระบุ', 0, '2026-03-19 09:23:16'),
(10, '6601103071021', 'add_points', 400, 0, 400, NULL, 'เติมพอยต์', 0, '2026-03-19 09:24:06'),
(11, '6601103071026', 'add_points', 60, 50, 110, NULL, 'เติมพอยต์', 0, '2026-03-19 09:28:24'),
(12, '6601103071005', 'add_points', 200, 0, 200, NULL, 'เติมพอยต์', 0, '2026-03-19 09:48:57'),
(13, '6601103071020', 'add_points', 10, 95, 105, NULL, 'เติมพอยต์', 0, '2026-03-19 10:41:43'),
(14, '6601103071020', 'refund_money', -15, 105, 90, NULL, 'ไม่ระบุ', 0, '2026-03-19 10:42:03'),
(15, '6601103071020', 'print', -70, 90, 20, 1, 'พิมพ์เอกสาร 14 หน้า (Job ID: 0)', 14, '2026-03-23 05:55:19'),
(16, '6601103071020', 'print', -5, 20, 15, 1, 'พิมพ์เอกสาร 1 หน้า (Job ID: 0)', 1, '2026-03-23 08:22:38'),
(17, '6601103071020', 'print', -5, 15, 10, 1, 'พิมพ์เอกสาร 1 หน้า (Job ID: 0)', 1, '2026-03-23 08:24:57');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL COMMENT 'ชื่อผู้ใช้',
  `password` varchar(255) NOT NULL COMMENT 'รหัสผ่าน (hashed)',
  `full_name` varchar(255) NOT NULL COMMENT 'ชื่อเต็ม',
  `email` varchar(255) DEFAULT NULL COMMENT 'อีเมล',
  `role` enum('super_admin','admin') DEFAULT 'admin' COMMENT 'บทบาท',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางผู้ดูแลระบบ';

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `username`, `password`, `full_name`, `email`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$LGEmW2eETnG8HYs2Ff0o5OKO/uTWEOWRgTKp.JTwYQ6jsquJIfeUi', 'ผู้ดูแลระบบ', 'admin@example.com', 'super_admin', '2026-03-18 04:17:00', '2026-03-18 04:17:00');

-- --------------------------------------------------------

--
-- Table structure for table `faculties`
--

CREATE TABLE `faculties` (
  `faculty_id` char(2) NOT NULL COMMENT 'รหัสคณะจริงของมหาวิทยาลัย (เช่น 01, 02)',
  `faculty_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faculties`
--

INSERT INTO `faculties` (`faculty_id`, `faculty_name`, `created_at`) VALUES
('01', 'คณะวิศวกรรมศาสตร์', '2026-03-18 04:10:14'),
('02', 'คณะบริหารธุรกิจ', '2026-03-18 04:10:14'),
('03', 'คณะวิทยาศาสตร์และเทคโนโลยี', '2026-03-18 04:10:14'),
('04', 'คณะบัญชี', '2026-03-18 04:10:14');

-- --------------------------------------------------------

--
-- Table structure for table `majors`
--

CREATE TABLE `majors` (
  `major_id` char(2) NOT NULL COMMENT 'รหัสสาขาจริงของมหาวิทยาลัย (เช่น 01, 02)',
  `faculty_id` char(2) NOT NULL COMMENT 'รหัสคณะ (อ้างอิงจาก faculties)',
  `major_abbr` varchar(10) NOT NULL COMMENT 'ตัวย่อสาขา เช่น ME, EE, IT',
  `major_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `majors`
--

INSERT INTO `majors` (`major_id`, `faculty_id`, `major_abbr`, `major_name`, `created_at`) VALUES
('01', '01', 'ME', 'วิศวกรรมเครื่องกล', '2026-03-18 04:10:14'),
('02', '01', 'EE', 'วิศวกรรมไฟฟ้า', '2026-03-18 04:10:14'),
('03', '01', 'IE', 'วิศวกรรมอุตสาหการ', '2026-03-18 04:10:14'),
('04', '02', 'MN', 'การจัดการ', '2026-03-18 04:10:14'),
('05', '02', 'DBC', 'คอมพิวเตอร์ธุรกิจ', '2026-03-18 04:10:14'),
('06', '04', 'AC', 'การบัญชี', '2026-03-18 04:10:14'),
('07', '03', 'IT', 'เทคโนโลยีสารสนเทศ', '2026-03-18 04:10:14'),
('08', '02', 'MK', 'การตลาด', '2026-03-18 04:10:14'),
('09', '02', 'LM', 'การจัดการโลจิสติกส์', '2026-03-18 04:10:14'),
('10', '01', 'IM', 'เทคโนโลยีการจัดการอุตสาหกรรม', '2026-03-18 04:10:14'),
('11', '02', 'IB', 'การจัดการธุรกิจระหว่างประเทศ', '2026-03-18 04:10:14'),
('12', '02', 'TM', 'การจัดการธุรกิจท่องเที่ยว', '2026-03-18 04:10:14'),
('13', '03', 'DM', 'เทคโนโลยีดิจิทัลมีเดีย', '2026-03-18 04:10:14');

-- --------------------------------------------------------

--
-- Table structure for table `point_transactions`
--

CREATE TABLE `point_transactions` (
  `transaction_id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `points` int(11) NOT NULL,
  `transaction_type` enum('add','deduct','refund') NOT NULL DEFAULT 'add',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `printers`
--

CREATE TABLE `printers` (
  `printer_id` int(11) NOT NULL COMMENT 'รหัสเครื่องพิมพ์',
  `printer_name` varchar(100) NOT NULL COMMENT 'ชื่อเครื่องพิมพ์',
  `location` varchar(255) NOT NULL COMMENT 'สถานที่ตั้ง',
  `paper_current` int(11) DEFAULT 500 COMMENT 'กระดาษคงเหลือ (แผ่น)',
  `paper_max` int(11) DEFAULT 500 COMMENT 'กระดาษสูงสุด (แผ่น)',
  `status` enum('ready','low_paper','offline') DEFAULT 'ready' COMMENT 'สถานะเครื่อง',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางเครื่องพิมพ์';

--
-- Dumping data for table `printers`
--

INSERT INTO `printers` (`printer_id`, `printer_name`, `location`, `paper_current`, `paper_max`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Printer-01', 'อาคาร 1 ชั้น 2', 450, 500, 'ready', '2026-03-18 04:16:59', '2026-03-18 04:16:59'),
(2, 'Printer-02', 'อาคาร 1 ชั้น 3', 320, 500, 'ready', '2026-03-18 04:16:59', '2026-03-18 04:16:59'),
(3, 'Printer-03', 'อาคาร 2 ชั้น 1', 50, 500, 'low_paper', '2026-03-18 04:16:59', '2026-03-18 04:16:59'),
(4, 'Printer-04', 'อาคาร 2 ชั้น 2', 0, 500, 'offline', '2026-03-18 04:16:59', '2026-03-18 04:16:59'),
(5, 'Printer-05', 'อาคาร 3 ชั้น 1', 480, 500, 'ready', '2026-03-18 04:16:59', '2026-03-18 04:16:59'),
(6, 'Printer-06', 'อาคาร 3 ชั้น 2', 390, 500, 'ready', '2026-03-18 04:16:59', '2026-03-18 04:16:59');

-- --------------------------------------------------------

--
-- Stand-in structure for view `printer_stats`
-- (See below for the actual view)
--
CREATE TABLE `printer_stats` (
`total_printers` bigint(21)
,`ready_printers` decimal(22,0)
,`low_paper_printers` decimal(22,0)
,`offline_printers` decimal(22,0)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `print_dashboard`
-- (See below for the actual view)
--
CREATE TABLE `print_dashboard` (
`student_name` varchar(255)
,`points_balance` int(11)
,`total_prints` int(11)
,`total_pages` int(11)
,`total_points_spent` int(11)
);

-- --------------------------------------------------------

--
-- Table structure for table `print_logs`
--

CREATE TABLE `print_logs` (
  `log_id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `pages` int(11) NOT NULL DEFAULT 1,
  `points_used` int(11) NOT NULL DEFAULT 0,
  `print_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `key` varchar(50) NOT NULL,
  `value` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`key`, `value`, `description`, `updated_at`) VALUES
('points_per_page', '1', 'พอยต์ต่อ 1 หน้า', '2026-03-29 04:08:14'),
('debt_limit', '50', 'วงเงินติดหนี้สูงสุด (0 = ไม่อนุญาตติดหนี้)', '2026-03-29 04:08:14'),
('allow_debt', '0', 'อนุญาตติดหนี้ (0=ปิด, 1=เปิด)', NOW()),
('cashier_pin', '', 'PIN Code สำหรับเข้าหน้าแคชเชียร์ (ว่าง = ไม่ใช้ PIN)', '2026-03-29 04:08:14');

-- --------------------------------------------------------

--
-- Stand-in structure for view `stats_summary`
-- (See below for the actual view)
--
CREATE TABLE `stats_summary` (
`total_students` bigint(21)
,`total_points` decimal(32,0)
,`avg_points` decimal(14,4)
,`active_today` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` varchar(20) NOT NULL,
  `student_name` varchar(255) NOT NULL,
  `major_id` char(2) DEFAULT NULL COMMENT 'รหัสสาขา (อ้างอิงจาก majors) ระบบจะดึงคณะอัตโนมัติ',
  `points_balance` int(11) DEFAULT 0,
  `total_prints` int(11) DEFAULT 0,
  `total_pages` int(11) DEFAULT 0,
  `total_points_spent` int(11) DEFAULT 0,
  `last_active` datetime DEFAULT NULL,
  `status` enum('active','low','debt','inactive') DEFAULT 'active' COMMENT 'debt = ยอดติดลบ',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `student_name`, `major_id`, `points_balance`, `total_prints`, `total_pages`, `total_points_spent`, `last_active`, `status`, `created_at`, `updated_at`) VALUES
('6601103071002', 'นาย ณัฐพงษ์ ตุ้มโหมด', '07', 0, 0, 0, 0, NULL, 'active', '2026-03-18 04:10:14', '2026-03-18 04:10:14'),
('6601103071003', 'นาย ณภัทร ทองกำพร้า', '07', 0, 0, 0, 0, NULL, 'active', '2026-03-18 04:10:14', '2026-03-18 04:10:14'),
('6601103071004', 'นาย ปณชัย ปราสาททอง', '07', 0, 0, 0, 0, NULL, 'active', '2026-03-18 04:10:14', '2026-03-18 04:10:14'),
('6601103071005', 'นาย จักรภัทร เอี๋ยวประดิษฐ', '07', 200, 0, 0, 0, '2026-03-19 16:48:57', 'active', '2026-03-18 04:10:14', '2026-03-19 09:48:57'),
('6601103071006', 'นาย ศราวุฒิ วงษ์ธรรม', '07', 0, 0, 0, 0, NULL, 'active', '2026-03-18 04:10:14', '2026-03-18 04:10:14'),
('6601103071007', 'นาย พลกฤต ธิดา', '07', 0, 0, 0, 0, NULL, 'active', '2026-03-18 04:10:14', '2026-03-18 04:10:14'),
('6601103071008', 'นาย ชญานนท์ ไชยกุสินธ์', '07', 500, 0, 0, 0, '2026-03-18 11:49:29', 'active', '2026-03-18 04:10:14', '2026-03-18 04:49:29'),
('6601103071009', 'นาย นพสิทธิ์ ชัยพิสิทธ์พัฒนา', '07', 0, 0, 0, 0, NULL, 'active', '2026-03-18 04:10:14', '2026-03-18 04:10:14'),
('6601103071010', 'นาย จิตภัทร เย็นยิ้ม', '07', 0, 0, 0, 0, NULL, 'active', '2026-03-18 04:10:14', '2026-03-18 04:10:14'),
('6601103071011', 'นาย ภาณุภพ มิ่งระกูล', '07', 0, 0, 0, 0, NULL, 'active', '2026-03-18 04:10:14', '2026-03-18 04:10:14'),
('6601103071012', 'นางสาว วิมลรัตน์ สวัสดี', '07', 0, 0, 0, 0, NULL, 'active', '2026-03-18 04:10:14', '2026-03-18 04:10:14'),
('6601103071014', 'นาย กิตติชาติ เฟื่องชูนุช', '07', 0, 0, 0, 0, NULL, 'active', '2026-03-18 04:10:14', '2026-03-18 04:10:14'),
('6601103071015', 'นาย วีรศักดิ์ ภูใบบัว', '07', 0, 0, 0, 0, NULL, 'active', '2026-03-18 04:10:14', '2026-03-18 04:10:14'),
('6601103071016', 'นาย ศุภกช พลชา', '07', 0, 0, 0, 0, NULL, 'active', '2026-03-18 04:10:14', '2026-03-18 04:10:14'),
('6601103071017', 'นาย ธนิสร เปลไสล', '07', 0, 0, 0, 0, NULL, 'active', '2026-03-18 04:10:14', '2026-03-18 04:10:14'),
('6601103071018', 'นาย ธีรภัทร หาญสูงเนิน', '07', 0, 0, 0, 0, NULL, 'active', '2026-03-18 04:10:14', '2026-03-18 04:10:14'),
('6601103071019', 'นางสาว จีรนันท์ อินชูกุล', '07', 0, 0, 0, 0, NULL, 'active', '2026-03-18 04:10:14', '2026-03-18 04:10:14'),
('6601103071020', 'นาย อรรถพร ภู่ทอง', '07', 10, 3, 16, 80, '2026-03-23 15:24:57', 'low', '2026-03-18 04:10:14', '2026-03-23 08:24:57'),
('6601103071021', 'นาย ธนโชติ ศรีสัน', '07', 400, 0, 0, 0, '2026-03-19 16:24:06', 'active', '2026-03-18 04:10:14', '2026-03-19 09:24:06'),
('6601103071022', 'นาย เศรษฐพงศ์ มหัตกุล', '07', 0, 0, 0, 0, NULL, 'active', '2026-03-18 04:10:14', '2026-03-18 04:10:14'),
('6601103071023', 'นาย ศุภกฤต นิมะบุตร', '07', 0, 0, 0, 0, NULL, 'active', '2026-03-18 04:10:14', '2026-03-18 04:10:14'),
('6601103071024', 'นางสาว ณัฐญา ชมทรัพย์จำเริญ', '07', 0, 0, 0, 0, NULL, 'active', '2026-03-18 04:10:14', '2026-03-18 04:10:14'),
('6601103071025', 'นาย อินทัช จีนทอง', '07', 0, 0, 0, 0, NULL, 'active', '2026-03-18 04:10:14', '2026-03-18 04:10:14'),
('6601103071026', 'นายธนาวัฒน์ ทิพย์สันพา', '07', 110, 0, 0, 0, '2026-03-19 16:28:24', 'active', '2026-03-18 04:10:14', '2026-03-19 09:28:24'),
('6601103071027', 'นาย ชาญวิทย์ นวลจันทร์', '07', 0, 0, 0, 0, NULL, 'active', '2026-03-18 04:10:14', '2026-03-18 04:10:14'),
('6601103071028', 'นาย อนุรักข์ เถื่อนแก้ว', '07', 0, 0, 0, 0, NULL, 'active', '2026-03-18 04:10:14', '2026-03-18 04:10:14'),
('6601103071030', 'นาย รัชต เจริญศรี', '07', 0, 0, 0, 0, NULL, 'active', '2026-03-18 04:10:14', '2026-03-18 04:10:14'),
('6601103071031', 'นาย สิทธิพงษ์ เสียงล้ำเลิศ', '07', 0, 0, 0, 0, NULL, 'active', '2026-03-18 04:10:14', '2026-03-18 04:10:14');

-- --------------------------------------------------------

--
-- Structure for view `printer_stats`
--
DROP TABLE IF EXISTS `printer_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `printer_stats`  AS SELECT count(0) AS `total_printers`, sum(case when `printers`.`status` = 'ready' then 1 else 0 end) AS `ready_printers`, sum(case when `printers`.`status` = 'low_paper' then 1 else 0 end) AS `low_paper_printers`, sum(case when `printers`.`status` = 'offline' then 1 else 0 end) AS `offline_printers` FROM `printers` ;

-- --------------------------------------------------------

--
-- Structure for view `print_dashboard`
--
DROP TABLE IF EXISTS `print_dashboard`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `print_dashboard`  AS SELECT `students`.`student_name` AS `student_name`, `students`.`points_balance` AS `points_balance`, `students`.`total_prints` AS `total_prints`, `students`.`total_pages` AS `total_pages`, `students`.`total_points_spent` AS `total_points_spent` FROM `students` ;

-- --------------------------------------------------------

--
-- Structure for view `stats_summary`
--
DROP TABLE IF EXISTS `stats_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `stats_summary`  AS SELECT count(0) AS `total_students`, sum(`students`.`points_balance`) AS `total_points`, avg(`students`.`points_balance`) AS `avg_points`, (select count(distinct `activities`.`student_id`) from `activities` where cast(`activities`.`created_at` as date) = curdate()) AS `active_today` FROM `students` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `faculties`
--
ALTER TABLE `faculties`
  ADD PRIMARY KEY (`faculty_id`);

--
-- Indexes for table `majors`
--
ALTER TABLE `majors`
  ADD PRIMARY KEY (`major_id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `point_transactions`
--
ALTER TABLE `point_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `printers`
--
ALTER TABLE `printers`
  ADD PRIMARY KEY (`printer_id`);

--
-- Indexes for table `print_logs`
--
ALTER TABLE `print_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD KEY `major_id` (`major_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `point_transactions`
--
ALTER TABLE `point_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `printers`
--
ALTER TABLE `printers`
  MODIFY `printer_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสเครื่องพิมพ์', AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `print_logs`
--
ALTER TABLE `print_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `majors`
--
ALTER TABLE `majors`
  ADD CONSTRAINT `majors_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`faculty_id`) ON DELETE CASCADE;

--
-- Constraints for table `point_transactions`
--
ALTER TABLE `point_transactions`
  ADD CONSTRAINT `point_transactions_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `print_logs`
--
ALTER TABLE `print_logs`
  ADD CONSTRAINT `print_logs_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`major_id`) REFERENCES `majors` (`major_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
