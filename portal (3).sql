-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 30, 2025 at 12:28 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE `account` (
  `id` int(11) NOT NULL,
  `account_id` varchar(11) NOT NULL,
  `fname` varchar(64) NOT NULL,
  `mname` varchar(64) NOT NULL,
  `lname` varchar(64) NOT NULL,
  `date_enrolled` date NOT NULL,
  `password` varchar(32) NOT NULL,
  `role` enum('student','instructor','management','admin') NOT NULL,
  `track` enum('kinder','junior high school','senior high school','') NOT NULL,
  `degree` varchar(50) DEFAULT 'Bachelor',
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `years_active` int(11) DEFAULT 0,
  `last_active_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account`
--

INSERT INTO `account` (`id`, `account_id`, `fname`, `mname`, `lname`, `date_enrolled`, `password`, `role`, `track`, `degree`, `status`, `years_active`, `last_active_date`) VALUES
(1, 'admin', 'ADMIN', 'ADMIN', 'ADMIN', '2025-11-30', 'admin123', 'admin', '', 'Bachelor', 'Active', 0, NULL),
(2, 'slu_reg', 'SLU', 'Pacdal', 'Registrar', '2025-11-30', 'slu_reg123', 'management', '', 'Bachelor', 'Active', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `assessments`
--

CREATE TABLE `assessments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `term_mode` varchar(20) DEFAULT 'Cash',
  `created_at` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_daily`
--

CREATE TABLE `attendance_daily` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `month_year` varchar(10) NOT NULL,
  `day_1` char(1) DEFAULT NULL,
  `day_2` char(1) DEFAULT NULL,
  `day_3` char(1) DEFAULT NULL,
  `day_4` char(1) DEFAULT NULL,
  `day_5` char(1) DEFAULT NULL,
  `day_6` char(1) DEFAULT NULL,
  `day_7` char(1) DEFAULT NULL,
  `day_8` char(1) DEFAULT NULL,
  `day_9` char(1) DEFAULT NULL,
  `day_10` char(1) DEFAULT NULL,
  `day_11` char(1) DEFAULT NULL,
  `day_12` char(1) DEFAULT NULL,
  `day_13` char(1) DEFAULT NULL,
  `day_14` char(1) DEFAULT NULL,
  `day_15` char(1) DEFAULT NULL,
  `day_16` char(1) DEFAULT NULL,
  `day_17` char(1) DEFAULT NULL,
  `day_18` char(1) DEFAULT NULL,
  `day_19` char(1) DEFAULT NULL,
  `day_20` char(1) DEFAULT NULL,
  `day_21` char(1) DEFAULT NULL,
  `day_22` char(1) DEFAULT NULL,
  `day_23` char(1) DEFAULT NULL,
  `day_24` char(1) DEFAULT NULL,
  `day_25` char(1) DEFAULT NULL,
  `day_26` char(1) DEFAULT NULL,
  `day_27` char(1) DEFAULT NULL,
  `day_28` char(1) DEFAULT NULL,
  `day_29` char(1) DEFAULT NULL,
  `day_30` char(1) DEFAULT NULL,
  `day_31` char(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `behavior_records`
--

CREATE TABLE `behavior_records` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `grading_period` int(11) NOT NULL,
  `attendance_score` int(11) DEFAULT 100,
  `conduct_grade` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `date_enrolled` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `quarter` int(1) NOT NULL,
  `grade` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `transaction_date` datetime DEFAULT current_timestamp(),
  `amount` decimal(10,2) NOT NULL,
  `method` varchar(20) DEFAULT 'Cash',
  `reference_no` varchar(50) DEFAULT NULL,
  `purpose` varchar(50) DEFAULT 'Tuition',
  `verified_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `question_type` enum('essay','multiple_choice') DEFAULT 'essay',
  `rubric` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `school_settings`
--

CREATE TABLE `school_settings` (
  `id` int(11) NOT NULL,
  `current_year` varchar(10) NOT NULL,
  `enrollment_status` enum('Open','Closed') DEFAULT 'Open',
  `active_quarter` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` int(11) NOT NULL,
  `section` varchar(16) NOT NULL,
  `code` varchar(16) NOT NULL,
  `description` varchar(64) DEFAULT NULL,
  `instructor_id` int(11) DEFAULT NULL,
  `semester` varchar(32) NOT NULL,
  `school_year` varchar(16) NOT NULL,
  `last_transaction` date DEFAULT NULL,
  `finalized` tinyint(1) DEFAULT NULL,
  `schedule_time` varchar(50) DEFAULT 'TBA',
  `room` varchar(50) DEFAULT 'Online',
  `track` varchar(50) DEFAULT '',
  `year_level` varchar(20) DEFAULT '',
  `adviser_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `section_list`
--

CREATE TABLE `section_list` (
  `id` int(11) NOT NULL,
  `section_name` varchar(50) NOT NULL,
  `year_level` varchar(20) NOT NULL,
  `track` varchar(50) NOT NULL,
  `adviser_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `track` enum('kinder','junior high school','senior high school','') NOT NULL,
  `grade_level` varchar(20) DEFAULT NULL,
  `date_enrolled` date NOT NULL,
  `familyname` varchar(32) NOT NULL,
  `fname` varchar(32) NOT NULL,
  `mname` varchar(32) NOT NULL,
  `suffix` varchar(3) NOT NULL,
  `birthdate` date NOT NULL,
  `birthplace` varchar(16) NOT NULL,
  `religion` varchar(16) NOT NULL,
  `civilstatus` varchar(16) NOT NULL,
  `nationality` varchar(16) NOT NULL,
  `gender` enum('Male','Female') NOT NULL DEFAULT 'Male',
  `first_gen_question` enum('Yes','No') NOT NULL,
  `ethnicity` varchar(16) NOT NULL,
  `contactno` int(11) NOT NULL,
  `email` varchar(32) NOT NULL,
  `housenum_street` varchar(32) NOT NULL,
  `barangay` varchar(32) NOT NULL,
  `city` varchar(16) NOT NULL,
  `province` varchar(16) NOT NULL,
  `zipcode` int(6) NOT NULL,
  `year_graduated` int(4) NOT NULL,
  `sLast` varchar(32) NOT NULL,
  `sStreet` varchar(32) NOT NULL,
  `sBarangay` varchar(16) NOT NULL,
  `sCity` varchar(16) NOT NULL,
  `sProvince` varchar(16) NOT NULL,
  `sZipcode` int(6) NOT NULL,
  `gLname` varchar(32) NOT NULL,
  `gFname` varchar(32) NOT NULL,
  `gMname` varchar(32) NOT NULL,
  `gContactnum` int(11) NOT NULL,
  `gOccupation` varchar(32) NOT NULL,
  `gAddress` varchar(64) NOT NULL,
  `gRelationship` varchar(16) NOT NULL,
  `mLname` varchar(32) NOT NULL,
  `mFname` varchar(32) NOT NULL,
  `mMname` varchar(32) NOT NULL,
  `mContactnum` int(11) NOT NULL,
  `mOccupation` varchar(16) NOT NULL,
  `mAddress` varchar(64) NOT NULL,
  `fLname` varchar(32) NOT NULL,
  `fFname` varchar(32) NOT NULL,
  `fMname` varchar(32) NOT NULL,
  `fContactnum` int(11) NOT NULL,
  `fOccupation` varchar(16) NOT NULL,
  `fAddress` varchar(64) NOT NULL,
  `lrn` varchar(20) DEFAULT NULL,
  `previous_school` varchar(100) DEFAULT NULL,
  `prev_street` varchar(100) DEFAULT NULL,
  `prev_barangay` varchar(100) DEFAULT NULL,
  `prev_city` varchar(100) DEFAULT NULL,
  `prev_province` varchar(100) DEFAULT NULL,
  `prev_zip` varchar(10) DEFAULT NULL,
  `esc_grantee` enum('Yes','No') DEFAULT 'No',
  `has_sibling_enrolled` tinyint(1) DEFAULT 0,
  `section_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_answers`
--

CREATE TABLE `student_answers` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer_text` text DEFAULT NULL,
  `ai_score` int(3) DEFAULT NULL,
  `ai_feedback` text DEFAULT NULL,
  `graded_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `description` varchar(100) NOT NULL,
  `year_level` varchar(20) NOT NULL,
  `track` varchar(50) DEFAULT 'Regular',
  `type` enum('Core','Applied','Specialized') DEFAULT 'Core',
  `price` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `assessments`
--
ALTER TABLE `assessments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance_daily`
--
ALTER TABLE `attendance_daily`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_att` (`student_id`,`section_id`,`month_year`);

--
-- Indexes for table `behavior_records`
--
ALTER TABLE `behavior_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_grade` (`student_id`,`section_id`,`quarter`),
  ADD KEY `grade_section` (`section_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_id` (`exam_id`);

--
-- Indexes for table `school_settings`
--
ALTER TABLE `school_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `instructor_id` (`instructor_id`);

--
-- Indexes for table `section_list`
--
ALTER TABLE `section_list`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_section` (`section_name`,`year_level`,`track`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `lrn` (`lrn`);

--
-- Indexes for table `student_answers`
--
ALTER TABLE `student_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_id` (`exam_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account`
--
ALTER TABLE `account`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `assessments`
--
ALTER TABLE `assessments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance_daily`
--
ALTER TABLE `attendance_daily`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `behavior_records`
--
ALTER TABLE `behavior_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `school_settings`
--
ALTER TABLE `school_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `section_list`
--
ALTER TABLE `section_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_answers`
--
ALTER TABLE `student_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enroll_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enroll_student` FOREIGN KEY (`student_id`) REFERENCES `account` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grade_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grade_student` FOREIGN KEY (`student_id`) REFERENCES `account` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `sections_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `account` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `connection` FOREIGN KEY (`student_id`) REFERENCES `account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_answers`
--
ALTER TABLE `student_answers`
  ADD CONSTRAINT `student_answers_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
