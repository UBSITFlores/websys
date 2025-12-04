-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 24, 2025 at 01:52 PM
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
  `track` enum('kinder','junior high school','senior high school','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account`
--

INSERT INTO `account` (`id`, `account_id`, `fname`, `mname`, `lname`, `date_enrolled`, `password`, `role`, `track`) VALUES
(1, 'admin', 'Admin', '', '', '2025-11-14', 'admin123', 'admin', ''),
(2, 'test', '', '', '', '0000-00-00', '123', 'student', 'kinder'),
(3, 'prof', '', '', '', '2025-11-14', '123', 'instructor', ''),
(4, 'management', 'management', '', '', '2025-11-21', '123', 'management', ''),
(5, '20250001', 'mac', 'mac', 'mac', '2025-11-20', '20250001', 'student', 'junior high school'),
(6, '20250002', 'mac', 'mac', 'mac', '2025-11-20', '20250002', 'student', 'junior high school'),
(7, '20250003', 'Mac', 'Mac', 'Mac', '2025-11-20', '20250003', 'student', 'junior high school'),
(8, '20250004', 'mac', 'mac', 'mac', '2025-11-20', '20250004', 'student', 'senior high school'),
(9, '20250005', 'mac', 'mac', 'mac', '2025-11-20', '20250005', 'student', 'junior high school'),
(10, '20250006', 'mac', 'mac', 'mac', '2025-11-20', '20250006', 'student', 'junior high school'),
(11, '20250007', 'mac', 'mac', 'mac', '2025-11-20', '20250007', 'student', 'junior high school'),
(12, '20250008', 'mac', 'mac', 'mac', '2025-11-20', '20250008', 'student', 'junior high school'),
(13, '20250009', 'mac', 'mac', 'mac', '2025-11-20', '20250009', 'student', 'junior high school'),
(14, '20250010', 'mac', 'mac', 'mac', '2025-11-20', '20250010', 'student', 'junior high school'),
(15, '20250011', 'mac', 'mac', 'mac', '2025-11-20', '20250011', 'student', 'junior high school');

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
  `finalized` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `track` enum('kinder','junior high school','senior high school','') NOT NULL,
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
  `gender` enum('Cisgender','Transgender','Non-binary','Other') NOT NULL,
  `sex` enum('Female','Male','Intersex','Oter') NOT NULL,
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
  `fAddress` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `track`, `date_enrolled`, `familyname`, `fname`, `mname`, `suffix`, `birthdate`, `birthplace`, `religion`, `civilstatus`, `nationality`, `gender`, `sex`, `first_gen_question`, `ethnicity`, `contactno`, `email`, `housenum_street`, `barangay`, `city`, `province`, `zipcode`, `year_graduated`, `sLast`, `sStreet`, `sBarangay`, `sCity`, `sProvince`, `sZipcode`, `gLname`, `gFname`, `gMname`, `gContactnum`, `gOccupation`, `gAddress`, `gRelationship`, `mLname`, `mFname`, `mMname`, `mContactnum`, `mOccupation`, `mAddress`, `fLname`, `fFname`, `fMname`, `fContactnum`, `fOccupation`, `fAddress`) VALUES
(6, 'junior high school', '2025-11-20', 'mac', 'mac', 'mac', 'mac', '2025-11-21', 'mac', 'mac', 'single', 'Filipino', 'Cisgender', 'Male', 'No', 'mac', 2302323, 'mac@gmail.com', 'mac', 'mac', 'mac', 'mac', 2600, 1, 'mac', 'mac', 'mac', 'mac', 'mac', 2600, 'mac', 'mac', 'mac', 232323123, 'mac', 'mac', 'mac', 'mac', 'mac', '', 23123123, 'mac', 'mac', 'mac', 'mac', 'mac', 12312312, 'mac', 'mac'),
(7, 'junior high school', '2025-11-20', 'Mac', 'Mac', 'Mac', 'Mac', '2025-11-21', 'Mac', 'Mac', 'Mac', 'Mac', 'Cisgender', 'Male', 'No', 'Mac', 12312312, 'Mac@gmail.com', 'Mac', 'Mac', 'Mac', 'Mac', 2600, 0, 'Mac', 'Mac', 'Mac', 'Mac', 'Mac', 1234, 'Mac', 'Mac', 'Mac', 123123123, 'Mac', 'Mac', 'Mac', 'Mac', 'Mac', 'Mac', 123123, 'Mac', 'Mac', 'Mac', 'Mac', 'Mac', 12312312, 'Mac', 'Mac'),
(8, 'senior high school', '2025-11-20', 'mac', 'mac', 'mac', 'asd', '2025-11-21', 'mac', 'mac', 'mac', 'mac', 'Cisgender', 'Male', 'No', 'mac', 123123, 'mac@gmail.com', '56', 'mac', 'mac', 'mac', 2123, 1, 'mac', 'mac', 'mac', 'mac', 'mac', 1234, 'mac', 'mac', 'mac', 1234, 'mac', 'mac', 'mac', 'mac', 'mac', 'mac', 1234, 'mac', 'mac', 'mac', 'mac', 'mac', 1234, 'mac', 'mac'),
(14, 'junior high school', '2025-11-20', 'mac', 'mac', 'mac', 'mac', '2025-11-21', 'mac', 'mac', 'mac', 'mac', 'Cisgender', 'Male', '', 'mac', 123123, 'mac@gmail.com', '', 'mac', 'mac', 'mac', 1234, 2023, 'mac', 'mac', 'mac', 'mac', 'mac', 1234, 'mac', 'mac', 'mac', 123123, 'mac', 'mac', 'mac', 'mac', 'mac', 'mac', 123123, 'mac', 'mac', 'mac', 'mac', 'mac', 123123, 'mac', 'mac'),
(15, 'junior high school', '2025-11-20', 'mac', 'mac', 'mac', 'mac', '2025-11-21', 'mac', 'mac', 'mac', 'mac', 'Cisgender', 'Male', 'No', 'mac', 123123123, 'mac@gmail.com', '56', 'mac', 'mac', 'mac', 1234, 2023, 'mac', 'mac', 'mac', 'mac', 'mac', 1234, 'mac', 'mac', 'mac', 1234, 'mac', 'mac', 'mac', 'mac', 'mac', 'mac', 1234, 'mac', 'mac', 'mac', 'mac', 'mac', 1234, 'mac', 'mac');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `instructor_id` (`instructor_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account`
--
ALTER TABLE `account`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
