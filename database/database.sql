-- =====================================================
-- Student Result Management System
-- Database Schema & Seed Data
-- Version: 1.0.0
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;

-- Create and use database (Commented out for shared hosting compatibility)
-- CREATE DATABASE IF NOT EXISTS `student_result_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `student_result_db`;

-- =====================================================
-- TABLE: admins
-- =====================================================
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin: username=admin, password=admin123
INSERT INTO `admins` (`username`, `password`) VALUES
('admin', '$2y$10$eylp/Y5tE4vlQYgrBTGUZ./f.oJZgCNweHRHcEamWwEPfKorgk99W');

-- =====================================================
-- TABLE: students
-- =====================================================
DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roll_number` varchar(20) NOT NULL UNIQUE,
  `student_name` varchar(150) NOT NULL,
  `mobile` varchar(10) NOT NULL,
  `email` varchar(150) NOT NULL,
  `department` enum('CSE','ECE','EEE','Civil','Mechanical') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_roll_number` (`roll_number`),
  KEY `idx_department` (`department`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample students
INSERT INTO `students` (`roll_number`, `student_name`, `mobile`, `email`, `department`) VALUES
('CSE2024001', 'Bhaumik Hinunia', '9876543210', 'bhaumik.hinunia@gmail.com', 'CSE'),
('CSE2024002', 'Manya Srivastava', '9876543211', 'manya.srivastava@gmail.com', 'CSE'),
('ECE2024001', 'Harsh Dathik', '9876543212', 'harsh.dathik@gmail.com', 'ECE'),
('ECE2024002', 'Divya Kukadkar', '9876543213', 'divya.kukadkar@gmail.com', 'ECE'),
('EEE2024001', 'Tanuj Chaudhary', '9876543214', 'tanuj.chaudhary@gmail.com', 'EEE'),
('CIV2024001', 'Vaidehi Khaturia', '9876543215', 'vaidehi.khaturia@gmail.com', 'Civil'),
('MEC2024001', 'Aryanish Singh Rathore', '9876543216', 'aryanish.singh@gmail.com', 'Mechanical'),
('CSE2024003', 'Subhanshu Singh', '9876543217', 'subhanshu.singh@gmail.com', 'CSE');

-- =====================================================
-- TABLE: subjects
-- =====================================================
DROP TABLE IF EXISTS `subjects`;
CREATE TABLE `subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_code` varchar(20) NOT NULL UNIQUE,
  `subject_name` varchar(150) NOT NULL,
  `max_marks` int(11) NOT NULL DEFAULT 100,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_subject_code` (`subject_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `subjects` (`subject_code`, `subject_name`, `max_marks`) VALUES
('MAT101', 'Engineering Mathematics', 100),
('CSE101', 'Data Structures', 100),
('CSE102', 'Java Programming', 100),
('CSE103', 'Database Systems', 100),
('CSE104', 'Web Technologies', 100),
('ECE101', 'Digital Electronics', 100),
('ECE102', 'Signals & Systems', 100),
('ECE103', 'Microprocessors', 100),
('ECE104', 'Communication Systems', 100),
('EEE101', 'Circuit Theory', 100),
('EEE102', 'Power Systems', 100),
('EEE103', 'Control Systems', 100),
('EEE104', 'Electrical Machines', 100),
('CIV101', 'Structural Analysis', 100),
('CIV102', 'Fluid Mechanics', 100),
('CIV103', 'Surveying', 100),
('CIV104', 'Geotechnical Engineering', 100),
('MEC101', 'Thermodynamics', 100),
('MEC102', 'Kinematics', 100),
('MEC103', 'Machine Design', 100),
('MEC104', 'Manufacturing Processes', 100);

-- =====================================================
-- TABLE: marks
-- =====================================================
DROP TABLE IF EXISTS `marks`;
CREATE TABLE `marks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `marks_obtained` decimal(5,2) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_student_subject` (`student_id`, `subject_id`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_subject_id` (`subject_id`),
  CONSTRAINT `fk_marks_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_marks_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample marks (branch specific)
INSERT INTO `marks` (`student_id`, `subject_id`, `marks_obtained`) VALUES
-- CSE Students (IDs 1, 2, 8) -> Subjects: 1, 2, 3, 4, 5
(1,1,92), (1,2,88), (1,3,95), (1,4,91), (1,5,87),
(2,1,78), (2,2,82), (2,3,74), (2,4,79), (2,5,85),
(8,1,72), (8,2,68), (8,3,75), (8,4,71), (8,5,69),

-- ECE Students (IDs 3, 4) -> Subjects: 1, 6, 7, 8, 9
(3,1,65), (3,6,70), (3,7,68), (3,8,72), (3,9,66),
(4,1,88), (4,6,91), (4,7,85), (4,8,90), (4,9,93),

-- EEE Student (ID 5) -> Subjects: 1, 10, 11, 12, 13
(5,1,45), (5,10,52), (5,11,38), (5,12,48), (5,13,50),

-- Civil Student (ID 6) -> Subjects: 1, 14, 15, 16, 17
(6,1,30), (6,14,42), (6,15,28), (6,16,35), (6,17,33),

-- Mechanical Student (ID 7) -> Subjects: 1, 18, 19, 20, 21
(7,1,55), (7,18,60), (7,19,58), (7,20,62), (7,21,57);

COMMIT;
