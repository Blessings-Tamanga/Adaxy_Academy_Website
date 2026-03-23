-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 23, 2026 at 09:39 PM
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
-- Database: `adaxy_academy`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(60) NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `username`, `full_name`, `email`, `password`, `is_active`, `created_at`) VALUES
(1, 'admin', 'System Administrator', 'admin@adaxy.mw', '$2y$10$xqWQ02zhBWKAaAb5dRwWc.yrWctrvrF/.bB.iWFauyub1Ubyz4WWq', 1, '2026-03-19 15:42:31');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `class_id` int(11) NOT NULL,
  `class_name` varchar(20) NOT NULL,
  `programme` enum('JCE','MSCE') NOT NULL,
  `form_level` tinyint(4) NOT NULL,
  `stream` varchar(5) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`class_id`, `class_name`, `programme`, `form_level`, `stream`, `created_at`) VALUES
(1, 'Form 1A', 'JCE', 1, 'A', '2026-03-19 15:42:26'),
(2, 'Form 1B', 'JCE', 1, 'B', '2026-03-19 15:42:26'),
(3, 'Form 2A', 'JCE', 2, 'A', '2026-03-19 15:42:26'),
(4, 'Form 2B', 'JCE', 2, 'B', '2026-03-19 15:42:26'),
(5, 'Form 3A', 'JCE', 3, 'A', '2026-03-19 15:42:26'),
(6, 'Form 3B', 'JCE', 3, 'B', '2026-03-19 15:42:26'),
(7, 'Form 4A', 'JCE', 4, 'A', '2026-03-19 15:42:26'),
(8, 'Form 4B', 'JCE', 4, 'B', '2026-03-19 15:42:26'),
(9, 'Form 5A', 'MSCE', 5, 'A', '2026-03-19 15:42:26'),
(10, 'Form 5B', 'MSCE', 5, 'B', '2026-03-19 15:42:26'),
(11, 'Form 6A', 'MSCE', 6, 'A', '2026-03-19 15:42:26'),
(12, 'Form 6B', 'MSCE', 6, 'B', '2026-03-19 15:42:26');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`, `created_at`) VALUES
(1, 'Mathematics', '2026-03-19 15:42:25'),
(2, 'Sciences', '2026-03-19 15:42:25'),
(3, 'Languages', '2026-03-19 15:42:25'),
(4, 'Humanities', '2026-03-19 15:42:25'),
(5, 'ICT', '2026-03-19 15:42:25'),
(6, 'Commerce & Business', '2026-03-19 15:42:25');

-- --------------------------------------------------------

--
-- Table structure for table `exam_papers`
--

CREATE TABLE `exam_papers` (
  `paper_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `programme` enum('JCE','MSCE') NOT NULL,
  `paper_number` enum('I','II','III') NOT NULL,
  `paper_title` varchar(100) NOT NULL,
  `duration_minutes` int(11) NOT NULL,
  `total_marks` int(11) NOT NULL,
  `has_practical` tinyint(1) DEFAULT 0,
  `requires_calculator` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exam_papers`
--

INSERT INTO `exam_papers` (`paper_id`, `subject_id`, `programme`, `paper_number`, `paper_title`, `duration_minutes`, `total_marks`, `has_practical`, `requires_calculator`) VALUES
(1, 1, 'MSCE', 'I', 'Mathematics Paper I (Short Answer)', 120, 100, 0, 1),
(2, 1, 'MSCE', 'II', 'Mathematics Paper II (Structured & Essay)', 150, 100, 0, 1),
(3, 2, 'MSCE', 'I', 'English Paper I (Grammar & Composition)', 105, 100, 0, 0),
(4, 2, 'MSCE', 'II', 'English Paper II (Summary & Comprehension)', 120, 100, 0, 0),
(5, 2, 'MSCE', 'III', 'English Paper III (Literature)', 120, 100, 0, 0),
(6, 4, 'MSCE', 'I', 'Physics Theory', 120, 100, 0, 0),
(7, 4, 'MSCE', 'II', 'Physics Structured Questions', 120, 100, 0, 0),
(8, 4, 'MSCE', 'III', 'Physics Practical', 120, 50, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `exam_sections`
--

CREATE TABLE `exam_sections` (
  `section_id` int(11) NOT NULL,
  `paper_id` int(11) NOT NULL,
  `section_letter` varchar(5) NOT NULL COMMENT 'A, B, C',
  `section_name` varchar(100) NOT NULL,
  `section_marks` int(11) NOT NULL,
  `question_type` enum('MCQ','Short Answer','Essay','Practical','Structured') NOT NULL,
  `number_of_questions` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exam_sections`
--

INSERT INTO `exam_sections` (`section_id`, `paper_id`, `section_letter`, `section_name`, `section_marks`, `question_type`, `number_of_questions`) VALUES
(1, 1, 'A', 'Short Answer Questions', 100, 'Short Answer', 20),
(2, 2, 'A', 'Mandatory Structured Questions', 60, 'Structured', 6),
(3, 2, 'B', 'Choice-Based Long Questions', 40, 'Essay', 4),
(4, 3, 'A', 'Grammar and Vocabulary', 50, 'MCQ', 50),
(5, 3, 'B', 'Composition', 50, 'Essay', 1);

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `grade_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `paper_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `class_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `grade_type` enum('test','end_of_term') NOT NULL,
  `ca1_score` decimal(5,2) DEFAULT 0.00,
  `ca2_score` decimal(5,2) DEFAULT 0.00,
  `ca3_score` decimal(5,2) DEFAULT 0.00,
  `exam_score` decimal(5,2) DEFAULT 0.00,
  `total_score` decimal(5,2) DEFAULT 0.00,
  `letter_grade` varchar(3) DEFAULT NULL,
  `term` tinyint(4) NOT NULL COMMENT '1, 2 or 3',
  `academic_year` year(4) NOT NULL,
  `remarks` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_practical` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`grade_id`, `student_id`, `subject_id`, `paper_id`, `section_id`, `class_id`, `teacher_id`, `grade_type`, `ca1_score`, `ca2_score`, `ca3_score`, `exam_score`, `total_score`, `letter_grade`, `term`, `academic_year`, `remarks`, `created_at`, `updated_at`, `is_practical`) VALUES
(1, 1, 1, NULL, NULL, 7, 1, 'test', 72.00, 68.00, 75.00, 0.00, 72.00, 'B', 3, '2025', 'Good effort', '2026-03-19 15:42:30', '2026-03-19 15:42:30', 0),
(2, 1, 4, NULL, NULL, 7, 2, 'test', 65.00, 70.00, 60.00, 0.00, 65.00, 'C', 3, '2025', 'Needs improvement', '2026-03-19 15:42:30', '2026-03-19 15:42:30', 0),
(3, 1, 2, NULL, NULL, 7, 3, 'test', 88.00, 85.00, 90.00, 0.00, 88.00, 'A', 3, '2025', 'Excellent', '2026-03-19 15:42:30', '2026-03-19 15:42:30', 0),
(4, 1, 1, NULL, NULL, 7, 1, 'end_of_term', 72.00, 68.00, 75.00, 80.00, 76.00, 'B', 2, '2025', 'Good performance', '2026-03-19 15:42:30', '2026-03-19 15:42:30', 0),
(5, 1, 4, NULL, NULL, 7, 2, 'end_of_term', 65.00, 70.00, 60.00, 74.00, 70.00, 'C', 2, '2025', 'Pass', '2026-03-19 15:42:30', '2026-03-19 15:42:30', 0),
(6, 2, 1, NULL, NULL, 7, 1, 'test', 90.00, 88.00, 92.00, 0.00, 90.00, 'A', 3, '2025', 'Outstanding', '2026-03-19 15:42:30', '2026-03-19 15:42:30', 0),
(7, 2, 2, NULL, NULL, 7, 3, 'test', 82.00, 80.00, 85.00, 0.00, 82.00, 'A', 3, '2025', 'Very good', '2026-03-19 15:42:30', '2026-03-19 15:42:30', 0),
(8, 2, 1, NULL, NULL, 7, 1, 'end_of_term', 90.00, 88.00, 92.00, 95.00, 91.00, 'A', 2, '2025', 'Top of class', '2026-03-19 15:42:30', '2026-03-19 15:42:30', 0),
(9, 4, 4, NULL, NULL, 9, 2, 'test', 78.00, 80.00, 75.00, 0.00, 78.00, 'B', 3, '2025', 'Good', '2026-03-19 15:42:30', '2026-03-19 15:42:30', 0),
(10, 4, 9, NULL, NULL, 9, 4, 'test', 95.00, 92.00, 98.00, 0.00, 95.00, 'A', 3, '2025', 'Exceptional', '2026-03-19 15:42:30', '2026-03-19 15:42:30', 0),
(11, 4, 4, NULL, NULL, 9, 2, 'end_of_term', 78.00, 80.00, 75.00, 82.00, 79.00, 'B', 2, '2025', 'Solid work', '2026-03-19 15:42:30', '2026-03-19 15:42:30', 0),
(12, 10, 1, NULL, NULL, 8, 1, 'test', 55.00, 58.00, 50.00, 0.00, 54.00, 'D', 3, '2025', 'Needs more practice', '2026-03-19 15:42:30', '2026-03-19 15:42:30', 0),
(13, 10, 5, NULL, NULL, 8, 5, 'test', 60.00, 65.00, 62.00, 0.00, 62.00, 'C', 3, '2025', 'Average', '2026-03-19 15:42:30', '2026-03-19 15:42:30', 0);

-- --------------------------------------------------------

--
-- Table structure for table `management`
--

CREATE TABLE `management` (
  `management_id` int(11) NOT NULL,
  `username` varchar(60) NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `email` varchar(120) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `title` varchar(100) NOT NULL COMMENT 'e.g. Headmaster, Deputy Head, Registrar',
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `management`
--

INSERT INTO `management` (`management_id`, `username`, `full_name`, `email`, `phone`, `title`, `password`, `is_active`, `created_at`) VALUES
(1, 'headmaster', 'Mr. Bernard Mwale', 'headmaster@adaxy.mw', '+265991000001', 'Headmaster', '$2y$10$xqWQ02zhBWKAaAb5dRwWc.yrWctrvrF/.bB.iWFauyub1Ubyz4WWq', 1, '2026-03-19 15:42:32'),
(2, 'deputyhead', 'Mrs. Grace Nkhata', 'deputy@adaxy.mw', '+265991000002', 'Deputy Head – Academics', '$2y$10$xqWQ02zhBWKAaAb5dRwWc.yrWctrvrF/.bB.iWFauyub1Ubyz4WWq', 1, '2026-03-19 15:42:32'),
(3, 'registrar', 'Mr. Charles Tembo', 'registrar@adaxy.mw', '+265991000003', 'Registrar', '$2y$10$xqWQ02zhBWKAaAb5dRwWc.yrWctrvrF/.bB.iWFauyub1Ubyz4WWq', 1, '2026-03-19 15:42:32'),
(4, 'bursar', 'Mrs. Alile Soko', 'bursar@adaxy.mw', '+265991000004', 'Bursar', '$2y$10$xqWQ02zhBWKAaAb5dRwWc.yrWctrvrF/.bB.iWFauyub1Ubyz4WWq', 1, '2026-03-19 15:42:32');

-- --------------------------------------------------------

--
-- Table structure for table `mock_exams`
--

CREATE TABLE `mock_exams` (
  `mock_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `exam_date` date NOT NULL,
  `exam_type` enum('Mid-Term','End-of-Term','Mock','Trial') NOT NULL,
  `total_marks` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notices`
--

CREATE TABLE `notices` (
  `notice_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `audience` enum('all','students','teachers','parents') DEFAULT 'all',
  `posted_by` varchar(100) DEFAULT NULL,
  `posted_role` enum('admin','management') DEFAULT 'admin',
  `is_published` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notices`
--

INSERT INTO `notices` (`notice_id`, `title`, `content`, `audience`, `posted_by`, `posted_role`, `is_published`, `created_at`) VALUES
(1, 'Welcome Back — Term 3 2025', 'All students and staff are welcomed back for Term 3. Classes commence on 1st September 2025.', 'all', 'Admin', 'admin', 1, '2026-03-19 15:42:30'),
(2, 'Grade Submission Deadline', 'All teachers must submit end of term grades by 10th October 2025.', 'teachers', 'Management', 'management', 1, '2026-03-19 15:42:30'),
(3, 'Parent-Teacher Conference', 'The Term 3 Parent-Teacher Conference will be held on 28th October 2025 at 14:00 in the Main Hall.', 'parents', 'Admin', 'admin', 1, '2026-03-19 15:42:30'),
(4, 'Department Meeting — Sciences', 'All Science department teachers are required to attend a meeting on Friday at 14:00 in Lab 3.', 'teachers', 'Management', 'management', 1, '2026-03-19 15:42:30'),
(5, 'MSCE Mock Exam Timetable Released', 'Form 6 students can now view their mock examination timetable on the notice board and student portal.', 'students', 'Admin', 'admin', 1, '2026-03-19 15:42:30'),
(6, 'Salary Advance Applications Open', 'Teachers wishing to apply for a salary advance should submit their forms to the bursar by 15th October.', 'teachers', 'Management', 'management', 1, '2026-03-19 15:42:30');

-- --------------------------------------------------------

--
-- Table structure for table `performance_benchmarks`
--

CREATE TABLE `performance_benchmarks` (
  `benchmark_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `programme` enum('JCE','MSCE') NOT NULL,
  `grade_letter` varchar(2) NOT NULL,
  `min_percentage` decimal(5,2) NOT NULL,
  `max_percentage` decimal(5,2) NOT NULL,
  `description` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `performance_benchmarks`
--

INSERT INTO `performance_benchmarks` (`benchmark_id`, `subject_id`, `programme`, `grade_letter`, `min_percentage`, `max_percentage`, `description`) VALUES
(1, 1, 'MSCE', 'A', 75.00, 100.00, 'Distinction - Excellent understanding of all topics'),
(2, 1, 'MSCE', 'B', 65.00, 74.99, 'Credit - Good understanding with minor errors'),
(3, 1, 'MSCE', 'C', 50.00, 64.99, 'Pass - Satisfactory understanding'),
(4, 1, 'MSCE', 'D', 40.00, 49.99, 'Marginal Pass - Basic understanding'),
(5, 1, 'MSCE', 'F', 0.00, 39.99, 'Fail - Needs improvement'),
(6, 2, 'MSCE', 'A', 80.00, 100.00, 'Distinction - Excellent language command'),
(7, 2, 'MSCE', 'B', 70.00, 79.99, 'Credit - Good language proficiency'),
(8, 2, 'MSCE', 'C', 55.00, 69.99, 'Pass - Adequate language skills'),
(9, 2, 'MSCE', 'D', 45.00, 54.99, 'Marginal Pass - Basic language ability'),
(10, 2, 'MSCE', 'F', 0.00, 44.99, 'Fail - Needs improvement'),
(11, 4, 'MSCE', 'A', 75.00, 100.00, 'Distinction - Excellent scientific reasoning'),
(12, 4, 'MSCE', 'B', 65.00, 74.99, 'Credit - Good practical understanding'),
(13, 4, 'MSCE', 'C', 50.00, 64.99, 'Pass - Satisfactory knowledge'),
(14, 4, 'MSCE', 'D', 40.00, 49.99, 'Marginal Pass - Basic understanding'),
(15, 4, 'MSCE', 'F', 0.00, 39.99, 'Fail - Needs improvement'),
(16, 1, 'JCE', 'A', 70.00, 100.00, 'Distinction - Excellent'),
(17, 1, 'JCE', 'B', 60.00, 69.99, 'Credit - Very Good'),
(18, 1, 'JCE', 'C', 45.00, 59.99, 'Pass - Good'),
(19, 1, 'JCE', 'D', 35.00, 44.99, 'Pass - Satisfactory'),
(20, 1, 'JCE', 'F', 0.00, 34.99, 'Fail - Needs improvement');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `roll_number` varchar(20) NOT NULL,
  `username` varchar(60) NOT NULL,
  `first_name` varchar(60) NOT NULL,
  `last_name` varchar(60) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `programme` enum('JCE','MSCE') NOT NULL,
  `form_level` tinyint(4) NOT NULL,
  `parent_name` varchar(120) DEFAULT NULL,
  `parent_phone` varchar(20) DEFAULT NULL,
  `parent_email` varchar(120) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `roll_number`, `username`, `first_name`, `last_name`, `date_of_birth`, `gender`, `class_id`, `programme`, `form_level`, `parent_name`, `parent_phone`, `parent_email`, `address`, `password`, `is_active`, `created_at`) VALUES
(1, '2025001', 'cbanda', 'Chisomo', 'Banda', '2009-04-12', 'Male', 7, 'JCE', 4, 'Margaret Banda', '+265991111001', 'mbanda@gmail.com', 'Area 25, Lilongwe', '$2y$10$xqWQ02zhBWKAaAb5dRwWc.yrWctrvrF/.bB.iWFauyub1Ubyz4WWq', 1, '2026-03-19 15:42:28'),
(2, '2025002', 'tphiri', 'Thandie', 'Phiri', '2009-07-23', 'Female', 7, 'JCE', 4, 'James Phiri', '+265991111002', 'jphiri@gmail.com', 'Area 47, Lilongwe', '$2y$10$xqWQ02zhBWKAaAb5dRwWc.yrWctrvrF/.bB.iWFauyub1Ubyz4WWq', 1, '2026-03-19 15:42:28'),
(3, '2025003', 'kmwanza', 'Kondwani', 'Mwanza', '2010-01-05', 'Male', 3, 'JCE', 2, 'Rose Mwanza', '+265991111003', 'rmwanza@gmail.com', 'Area 18, Lilongwe', '$2y$10$xqWQ02zhBWKAaAb5dRwWc.yrWctrvrF/.bB.iWFauyub1Ubyz4WWq', 1, '2026-03-19 15:42:28'),
(4, '2025004', 'lchirwa', 'Limbani', 'Chirwa', '2008-11-30', 'Male', 9, 'MSCE', 5, 'Elias Chirwa', '+265991111004', 'echirwa@gmail.com', 'Area 12, Lilongwe', '$2y$10$xqWQ02zhBWKAaAb5dRwWc.yrWctrvrF/.bB.iWFauyub1Ubyz4WWq', 1, '2026-03-19 15:42:28'),
(5, '2025005', 'mnyirenda', 'Mercy', 'Nyirenda', '2008-06-18', 'Female', 9, 'MSCE', 5, 'Beatrice Nyirenda', '+265991111005', 'bnyirenda@gmail.com', 'Area 9, Lilongwe', '$2y$10$xqWQ02zhBWKAaAb5dRwWc.yrWctrvrF/.bB.iWFauyub1Ubyz4WWq', 1, '2026-03-19 15:42:28'),
(6, '2025006', 'amoto', 'Alinafe', 'Moto', '2011-03-14', 'Female', 1, 'JCE', 1, 'John Moto', '+265991111006', 'jmoto@gmail.com', 'Area 33, Lilongwe', '$2y$10$xqWQ02zhBWKAaAb5dRwWc.yrWctrvrF/.bB.iWFauyub1Ubyz4WWq', 1, '2026-03-19 15:42:28'),
(7, '2025007', 'pmkandawire', 'Peter', 'Mkandawire', '2011-05-20', 'Male', 1, 'JCE', 1, 'Susan Mkandawire', '+265991111007', 'smkandawire@gmail.com', 'Area 4, Lilongwe', '$2y$10$xqWQ02zhBWKAaAb5dRwWc.yrWctrvrF/.bB.iWFauyub1Ubyz4WWq', 1, '2026-03-19 15:42:28'),
(8, '2025008', 'nkamwendo', 'Natasha', 'Kamwendo', '2010-09-08', 'Female', 5, 'JCE', 3, 'Paul Kamwendo', '+265991111008', 'pkamwendo@gmail.com', 'Area 6, Lilongwe', '$2y$10$xqWQ02zhBWKAaAb5dRwWc.yrWctrvrF/.bB.iWFauyub1Ubyz4WWq', 1, '2026-03-19 15:42:28'),
(9, '2025009', 'bmaliro', 'Blessings', 'Maliro', '2008-02-25', 'Male', 11, 'MSCE', 6, 'Grace Maliro', '+265991111009', 'gmaliro@gmail.com', 'Area 22, Lilongwe', '$2y$10$xqWQ02zhBWKAaAb5dRwWc.yrWctrvrF/.bB.iWFauyub1Ubyz4WWq', 1, '2026-03-19 15:42:28'),
(10, '2025010', 'lzuze', 'Lovemore', 'Zuze', '2009-12-01', 'Male', 8, 'JCE', 4, 'Martha Zuze', '+265991111010', 'mzuze@gmail.com', 'Area 49, Lilongwe', '$2y$10$xqWQ02zhBWKAaAb5dRwWc.yrWctrvrF/.bB.iWFauyub1Ubyz4WWq', 1, '2026-03-19 15:42:28');

-- --------------------------------------------------------

--
-- Table structure for table `student_topic_progress`
--

CREATE TABLE `student_topic_progress` (
  `progress_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `mastery_percentage` decimal(5,2) DEFAULT 0.00,
  `needs_revision` tinyint(1) DEFAULT 0,
  `last_assessed` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `subject_code` varchar(20) NOT NULL,
  `department_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `subject_name`, `subject_code`, `department_id`) VALUES
(1, 'Mathematics', 'MATH', 1),
(2, 'English Language', 'ENG', 3),
(3, 'Chichewa', 'CHI', 3),
(4, 'Physics', 'PHY', 2),
(5, 'Chemistry', 'CHEM', 2),
(6, 'Biology', 'BIO', 2),
(7, 'History', 'HIST', 4),
(8, 'Geography', 'GEO', 4),
(9, 'Computer Science', 'ICT', 5),
(10, 'Business Studies', 'BUS', 6),
(11, 'French', 'FRE', 3),
(12, 'Religious Studies', 'RE', 4);

-- --------------------------------------------------------

--
-- Table structure for table `subject_topics`
--

CREATE TABLE `subject_topics` (
  `topic_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `programme` enum('JCE','MSCE') NOT NULL,
  `topic_name` varchar(200) NOT NULL,
  `topic_code` varchar(20) NOT NULL,
  `weight_percentage` decimal(5,2) NOT NULL COMMENT 'Weight in final exam'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subject_topics`
--

INSERT INTO `subject_topics` (`topic_id`, `subject_id`, `programme`, `topic_name`, `topic_code`, `weight_percentage`) VALUES
(1, 1, 'MSCE', 'Algebra and Indices', 'MATH-101', 15.00),
(2, 1, 'MSCE', 'Factorization and Quadratic Equations', 'MATH-102', 12.00),
(3, 1, 'MSCE', 'Geometry and Trigonometry', 'MATH-103', 18.00),
(4, 1, 'MSCE', 'Statistics and Probability', 'MATH-104', 10.00),
(5, 1, 'MSCE', 'Calculus', 'MATH-105', 15.00),
(6, 1, 'MSCE', 'Coordinate Geometry', 'MATH-106', 10.00),
(7, 1, 'MSCE', 'Vectors and Matrices', 'MATH-107', 10.00),
(8, 1, 'MSCE', 'Mensuration', 'MATH-108', 10.00),
(9, 2, 'MSCE', 'Grammar and Usage', 'ENG-101', 20.00),
(10, 2, 'MSCE', 'Composition Writing', 'ENG-102', 20.00),
(11, 2, 'MSCE', 'Comprehension', 'ENG-103', 20.00),
(12, 2, 'MSCE', 'Summary and Note-making', 'ENG-104', 15.00),
(13, 2, 'MSCE', 'Poetry Analysis', 'ENG-105', 12.50),
(14, 2, 'MSCE', 'Prose Literature', 'ENG-106', 12.50),
(15, 6, 'MSCE', 'Cell Biology', 'BIO-101', 10.00),
(16, 6, 'MSCE', 'Human Physiology', 'BIO-102', 20.00),
(17, 6, 'MSCE', 'Plant Biology', 'BIO-103', 15.00),
(18, 6, 'MSCE', 'Ecology and Environment', 'BIO-104', 15.00),
(19, 6, 'MSCE', 'Genetics', 'BIO-105', 15.00),
(20, 6, 'MSCE', 'Evolution and Diversity', 'BIO-106', 10.00),
(21, 6, 'MSCE', 'Practical Skills', 'BIO-107', 15.00);

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `teacher_id` int(11) NOT NULL,
  `employee_no` varchar(20) NOT NULL,
  `username` varchar(60) NOT NULL,
  `first_name` varchar(60) NOT NULL,
  `last_name` varchar(60) NOT NULL,
  `email` varchar(120) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `qualification` varchar(150) DEFAULT NULL,
  `date_joined` date DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`teacher_id`, `employee_no`, `username`, `first_name`, `last_name`, `email`, `phone`, `gender`, `department_id`, `subject_id`, `qualification`, `date_joined`, `password`, `is_active`, `created_at`) VALUES
(1, 'EMP001', 'bmwale', 'Bernard', 'Mwale', 'b.mwale@adaxy.mw', '+265991001001', 'Male', 1, 1, 'B.Ed Mathematics', '2019-01-10', 'password', 1, '2026-03-19 15:42:27'),
(2, 'EMP002', 'gnkhata', 'Grace', 'Nkhata', 'g.nkhata@adaxy.mw', '+265991001002', 'Female', 2, 4, 'B.Sc Physics', '2018-03-01', '$2y$10$xqWQ02zhBWKAaAb5dRwWc.yrWctrvrF/.bB.iWFauyub1Ubyz4WWq', 1, '2026-03-19 15:42:27'),
(3, 'EMP003', 'ctembo', 'Charles', 'Tembo', 'c.tembo@adaxy.mw', '+265991001003', 'Male', 3, 2, 'B.A English', '2020-07-15', '$2y$10$xqWQ02zhBWKAaAb5dRwWc.yrWctrvrF/.bB.iWFauyub1Ubyz4WWq', 1, '2026-03-19 15:42:27'),
(4, 'EMP004', 'asoko', 'Alile', 'Soko', 'a.soko@adaxy.mw', '+265991001004', 'Female', 5, 9, 'B.Sc Computer Sci', '2021-01-20', '$2y$10$xqWQ02zhBWKAaAb5dRwWc.yrWctrvrF/.bB.iWFauyub1Ubyz4WWq', 1, '2026-03-19 15:42:27'),
(5, 'EMP005', 'jphiri', 'James', 'Phiri', 'j.phiri@adaxy.mw', '+265991001005', 'Male', 2, 5, 'B.Sc Chemistry', '2017-08-05', '$2y$10$xqWQ02zhBWKAaAb5dRwWc.yrWctrvrF/.bB.iWFauyub1Ubyz4WWq', 1, '2026-03-19 15:42:27'),
(6, 'EMP006', 'rmtonga', 'Rose', 'Mtonga', 'r.mtonga@adaxy.mw', '+265991001006', 'Female', 4, 7, 'B.A History', '2022-02-01', '$2y$10$xqWQ02zhBWKAaAb5dRwWc.yrWctrvrF/.bB.iWFauyub1Ubyz4WWq', 1, '2026-03-19 15:42:27'),
(7, 'EMP007', 'dkamanga', 'David', 'Kamanga', 'd.kamanga@adaxy.mw', '+265991001007', 'Male', 2, 6, 'B.Sc Biology', '2016-05-12', '$2y$10$xqWQ02zhBWKAaAb5dRwWc.yrWctrvrF/.bB.iWFauyub1Ubyz4WWq', 1, '2026-03-19 15:42:27'),
(8, 'EMP008', 'fzulu', 'Fatima', 'Zulu', 'f.zulu@adaxy.mw', '+265991001008', 'Female', 6, 10, 'B.Com Business', '2023-01-09', '$2y$10$xqWQ02zhBWKAaAb5dRwWc.yrWctrvrF/.bB.iWFauyub1Ubyz4WWq', 1, '2026-03-19 15:42:27');

-- --------------------------------------------------------

--
-- Table structure for table `timetable`
--

CREATE TABLE `timetable` (
  `timetable_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday') NOT NULL,
  `period_no` tinyint(4) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `room_no` varchar(20) DEFAULT NULL,
  `academic_year` year(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `timetable`
--

INSERT INTO `timetable` (`timetable_id`, `class_id`, `subject_id`, `teacher_id`, `day_of_week`, `period_no`, `start_time`, `end_time`, `room_no`, `academic_year`) VALUES
(1, 7, 1, 1, 'Monday', 1, '07:30:00', '08:30:00', 'Room 10', '2025'),
(2, 7, 4, 2, 'Monday', 2, '08:30:00', '09:30:00', 'Lab 1', '2025'),
(3, 7, 2, 3, 'Monday', 3, '09:45:00', '10:45:00', 'Room 10', '2025'),
(4, 7, 9, 4, 'Monday', 4, '10:45:00', '11:45:00', 'ICT Lab', '2025'),
(5, 7, 5, 5, 'Tuesday', 1, '07:30:00', '08:30:00', 'Lab 2', '2025'),
(6, 7, 1, 1, 'Tuesday', 2, '08:30:00', '09:30:00', 'Room 10', '2025'),
(7, 7, 6, 7, 'Tuesday', 3, '09:45:00', '10:45:00', 'Lab 3', '2025'),
(8, 9, 4, 2, 'Monday', 1, '07:30:00', '08:30:00', 'Lab 1', '2025'),
(9, 9, 1, 1, 'Monday', 2, '08:30:00', '09:30:00', 'Room 5', '2025'),
(10, 9, 9, 4, 'Monday', 3, '09:45:00', '10:45:00', 'ICT Lab', '2025'),
(11, 1, 2, 3, 'Wednesday', 1, '07:30:00', '08:30:00', 'Room 1', '2025'),
(12, 1, 1, 1, 'Wednesday', 2, '08:30:00', '09:30:00', 'Room 1', '2025'),
(13, 1, 7, 6, 'Wednesday', 3, '09:45:00', '10:45:00', 'Room 1', '2025');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`);

--
-- Indexes for table `exam_papers`
--
ALTER TABLE `exam_papers`
  ADD PRIMARY KEY (`paper_id`),
  ADD UNIQUE KEY `unique_paper` (`subject_id`,`programme`,`paper_number`);

--
-- Indexes for table `exam_sections`
--
ALTER TABLE `exam_sections`
  ADD PRIMARY KEY (`section_id`),
  ADD KEY `paper_id` (`paper_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`grade_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `paper_id` (`paper_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `management`
--
ALTER TABLE `management`
  ADD PRIMARY KEY (`management_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `mock_exams`
--
ALTER TABLE `mock_exams`
  ADD PRIMARY KEY (`mock_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `notices`
--
ALTER TABLE `notices`
  ADD PRIMARY KEY (`notice_id`);

--
-- Indexes for table `performance_benchmarks`
--
ALTER TABLE `performance_benchmarks`
  ADD PRIMARY KEY (`benchmark_id`),
  ADD UNIQUE KEY `unique_grade` (`subject_id`,`programme`,`grade_letter`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `roll_number` (`roll_number`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `student_topic_progress`
--
ALTER TABLE `student_topic_progress`
  ADD PRIMARY KEY (`progress_id`),
  ADD UNIQUE KEY `unique_progress` (`student_id`,`topic_id`),
  ADD KEY `topic_id` (`topic_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`),
  ADD UNIQUE KEY `subject_code` (`subject_code`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `subject_topics`
--
ALTER TABLE `subject_topics`
  ADD PRIMARY KEY (`topic_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`teacher_id`),
  ADD UNIQUE KEY `employee_no` (`employee_no`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `timetable`
--
ALTER TABLE `timetable`
  ADD PRIMARY KEY (`timetable_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `exam_papers`
--
ALTER TABLE `exam_papers`
  MODIFY `paper_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `exam_sections`
--
ALTER TABLE `exam_sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `grade_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `management`
--
ALTER TABLE `management`
  MODIFY `management_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `mock_exams`
--
ALTER TABLE `mock_exams`
  MODIFY `mock_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notices`
--
ALTER TABLE `notices`
  MODIFY `notice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `performance_benchmarks`
--
ALTER TABLE `performance_benchmarks`
  MODIFY `benchmark_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `student_topic_progress`
--
ALTER TABLE `student_topic_progress`
  MODIFY `progress_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `subject_topics`
--
ALTER TABLE `subject_topics`
  MODIFY `topic_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `timetable`
--
ALTER TABLE `timetable`
  MODIFY `timetable_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `exam_papers`
--
ALTER TABLE `exam_papers`
  ADD CONSTRAINT `exam_papers_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`);

--
-- Constraints for table `exam_sections`
--
ALTER TABLE `exam_sections`
  ADD CONSTRAINT `exam_sections_ibfk_1` FOREIGN KEY (`paper_id`) REFERENCES `exam_papers` (`paper_id`);

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`),
  ADD CONSTRAINT `grades_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`),
  ADD CONSTRAINT `grades_ibfk_4` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`),
  ADD CONSTRAINT `grades_ibfk_5` FOREIGN KEY (`paper_id`) REFERENCES `exam_papers` (`paper_id`),
  ADD CONSTRAINT `grades_ibfk_6` FOREIGN KEY (`section_id`) REFERENCES `exam_sections` (`section_id`);

--
-- Constraints for table `mock_exams`
--
ALTER TABLE `mock_exams`
  ADD CONSTRAINT `mock_exams_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`),
  ADD CONSTRAINT `mock_exams_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`);

--
-- Constraints for table `performance_benchmarks`
--
ALTER TABLE `performance_benchmarks`
  ADD CONSTRAINT `performance_benchmarks_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`);

--
-- Constraints for table `student_topic_progress`
--
ALTER TABLE `student_topic_progress`
  ADD CONSTRAINT `student_topic_progress_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `student_topic_progress_ibfk_2` FOREIGN KEY (`topic_id`) REFERENCES `subject_topics` (`topic_id`);

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);

--
-- Constraints for table `subject_topics`
--
ALTER TABLE `subject_topics`
  ADD CONSTRAINT `subject_topics_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`);

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`),
  ADD CONSTRAINT `teachers_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`);

--
-- Constraints for table `timetable`
--
ALTER TABLE `timetable`
  ADD CONSTRAINT `timetable_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`),
  ADD CONSTRAINT `timetable_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`),
  ADD CONSTRAINT `timetable_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
