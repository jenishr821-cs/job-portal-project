-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 08, 2025 at 04:04 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `resume_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `company_jobs`
--

CREATE TABLE `company_jobs` (
  `job_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `job_title` varchar(255) NOT NULL,
  `job_type` varchar(100) NOT NULL,
  `job_location` varchar(255) NOT NULL,
  `salary` varchar(100) NOT NULL,
  `experience_required` varchar(100) NOT NULL,
  `job_description` text NOT NULL,
  `responsibilities` text NOT NULL,
  `qualifications` text NOT NULL,
  `contact_no` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `company_logo` varchar(255) DEFAULT NULL,
  `number_of_openings` int(11) NOT NULL,
  `work_mode` varchar(100) NOT NULL,
  `benefits` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` enum('open','filled') DEFAULT 'open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_jobs`
--

INSERT INTO `company_jobs` (`job_id`, `username`, `company_name`, `job_title`, `job_type`, `job_location`, `salary`, `experience_required`, `job_description`, `responsibilities`, `qualifications`, `contact_no`, `email`, `company_logo`, `number_of_openings`, `work_mode`, `benefits`, `created_at`, `status`) VALUES
(5, 'Jio Company', 'Jio Company', 'Php Developer', 'Php Developing', 'Mumbai', '25000', '2', 'Well Provided Jobs Flexibility.', 'Junior Developing', 'MCA', '+91 9845693221', 'jioCompany123@gmail.com', '4.jpeg', 2, 'Full time', 'Add on, Paid Leaves', '2025-07-28 22:34:28', 'open'),
(17, 'Jio Company', 'JIO RELIANCE', 'python developer', 'software developing', 'RAJKOT', '25000', '1', 'qwe', 'JUNIOR', 'MCA', '+91 6320538510', 'jiocompany@gmail.com', '1.webp', 2, 'full time', 'qw', '2025-08-26 09:44:33', 'open'),
(18, 'Jio Company', 'JIO RELIANCE', 'CYBER', 'ATTACKING', 'RAJKOT', '25000', '2', '123', 'JUNIOR', 'MCA', '+91 6320538510', 'jiocompany@gmail.com', '2.webp', 5, 'FULL TIME', '1\r\n', '2025-08-26 13:36:08', 'open');

-- --------------------------------------------------------

--
-- Table structure for table `company_profiles`
--

CREATE TABLE `company_profiles` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_profiles`
--

INSERT INTO `company_profiles` (`id`, `username`, `company_name`, `address`, `email`, `phone`, `logo`, `description`, `created_at`) VALUES
(5, 'Jio Company', 'Jio Company', 'Rajkot', 'jioCompany123@gmail.com', '+91 8590216510', '4.jpeg', 'Well Salary Provided Company.', '2025-07-28 17:02:33');

-- --------------------------------------------------------

--
-- Table structure for table `create_resume`
--

CREATE TABLE `create_resume` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` varchar(100) DEFAULT NULL,
  `experience` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `summary` text DEFAULT NULL,
  `degree` varchar(100) DEFAULT NULL,
  `university` varchar(150) DEFAULT NULL,
  `passing_year` year(4) DEFAULT NULL,
  `percentage` varchar(10) DEFAULT NULL,
  `job_title` varchar(150) DEFAULT NULL,
  `company_name` varchar(150) DEFAULT NULL,
  `work_duration_from` date DEFAULT NULL,
  `work_duration_to` date DEFAULT NULL,
  `previous_salary` varchar(50) DEFAULT NULL,
  `expected_salary` varchar(50) DEFAULT NULL,
  `programming_languages` text DEFAULT NULL,
  `tools_frameworks` text DEFAULT NULL,
  `hobbies` text DEFAULT NULL,
  `ref_name` varchar(150) DEFAULT NULL,
  `ref_position` varchar(150) DEFAULT NULL,
  `ref_contact` varchar(50) DEFAULT NULL,
  `ref_email` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `create_resume`
--

INSERT INTO `create_resume` (`id`, `username`, `name`, `email`, `phone`, `role`, `experience`, `address`, `summary`, `degree`, `university`, `passing_year`, `percentage`, `job_title`, `company_name`, `work_duration_from`, `work_duration_to`, `previous_salary`, `expected_salary`, `programming_languages`, `tools_frameworks`, `hobbies`, `ref_name`, `ref_position`, `ref_contact`, `ref_email`, `created_at`) VALUES
(12, 'jenish@123', 'jenish rathod', 'jenish123@gmail.com', '+91 6789055252', 'junior', '2', 'Rajkot', 'Properly GRaduated', 'MCA', 'saurashtra', '2010', '85', 'Php Developer', 'starlink', '2025-07-01', '2025-07-31', '22000', '32000', 'php', 'flutter', '', 'starlink', 'junior', '+91 9123456780', 'starlink23@gmail.com', '2025-07-28 17:00:55'),
(13, 'jacky@2007', 'Devils Jacky', 'jacky2007@gmail.com', '+91 6789055252', 'junior', '1', 'a', 'a', 'MCA', 'saurashtra', '2010', '80', 'Php Developer', 'starlink', '2025-07-24', '2025-07-31', '25000', '30000', 'php', 'flutter', 'a', 'starlink', 'Intern', '+91 9123456780', 'starlink23@gmail.com', '2025-07-29 04:22:42');

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

CREATE TABLE `job_applications` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `company_name` varchar(255) NOT NULL,
  `job_title` varchar(255) NOT NULL,
  `application_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_applications`
--

INSERT INTO `job_applications` (`id`, `job_id`, `username`, `fullname`, `applied_at`, `company_name`, `job_title`, `application_status`, `updated_at`) VALUES
(14, 5, 'jenish@123', '', '2025-07-28 17:05:30', 'Jio Company', 'Php Developer', 'approved', '2025-07-28 22:37:20'),
(15, 5, 'jacky@2007', '', '2025-07-29 04:19:36', 'Jio Company', 'Php Developer', 'approved', '2025-07-29 09:53:11'),
(42, 17, 'jenish@123', 'Jenish Rathod', '2025-08-26 04:21:46', 'JIO RELIANCE', 'python developer', 'approved', '2025-08-26 13:35:25'),
(44, 18, 'jenish@123', 'Jenish Rathod', '2025-08-31 13:10:11', 'JIO RELIANCE', 'CYBER', 'approved', '2025-09-01 23:24:22');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(250) NOT NULL,
  `receiver_username` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` varchar(20) DEFAULT 'general'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `receiver_username`, `message`, `link`, `is_read`, `created_at`, `type`) VALUES
(1, 'jacky@2007', 'Your application for <strong>Php Developer</strong> at <strong>Jio Company</strong> was <strong>Approved</strong>.', 'track_applications.php', 1, '2025-07-29 04:23:11', 'general'),
(2, 'jenish@123', 'New job posted: <strong>java developer</strong> at <strong>Jio Company</strong>', 'job_details.php?id=6', 1, '2025-07-29 04:48:25', 'general'),
(3, 'jacky@2007', 'New job posted: <strong>java developer</strong> at <strong>Jio Company</strong>', 'job_details.php?id=6', 1, '2025-07-29 04:48:25', 'general'),
(4, 'jenish@123', 'Your application for <strong>java developer</strong> at <strong>Jio Company</strong> was <strong>Approved</strong>.', 'track_applications.php', 1, '2025-07-29 05:00:42', 'general'),
(5, 'jenish@123', 'New job posted:  at ', 'job_details.php?id=7', 1, '2025-07-29 15:31:07', 'general'),
(6, 'jacky@2007', 'New job posted:  at ', 'job_details.php?id=7', 1, '2025-07-29 15:31:07', 'general'),
(7, 'Jio Company', '<strong>jenish@123</strong> applied for <strong>   laravel developer</strong>', 'view_applicants.php?job_id=7 ', 1, '2025-07-29 15:33:59', 'general'),
(8, 'jenish@123', 'Your application for <strong>   laravel developer</strong> at <strong>Jio Company</strong> was <strong>Approved</strong>.', 'track_applications.php', 1, '2025-07-29 15:34:35', 'general'),
(9, 'Jio Company', '\n        <div class=\'space-y-1 leading-6\'>\n            \n<p><span class=\'font-semibold\'>jacky@2007</span> applied for <span class=\'font-semibold\'>java developer</span></p>\n<p class=\'text-slate-400\'>Check applicant details below.</p>\n\n            \n        </div>\n    ', 'view_applicants.php?job_id=6 ', 1, '2025-07-29 15:43:46', 'general'),
(10, 'Jio Company', '\n        <div class=\'space-y-1 leading-6\'>\n            \n<p><span class=\'font-semibold\'>jacky@2007</span> applied for <span class=\'font-semibold\'>   laravel developer</span></p>\n<p class=\'text-slate-400\'>Check applicant details below.</p>\n\n            \n        </div>\n    ', 'view_applicants.php?job_id=7 ', 1, '2025-07-29 15:43:49', 'general'),
(11, 'jenish@123', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n                <p>New job posted: <span class=\'font-semibold\'></span> at <span class=\'font-semibold\'></span></p>\n                <p class=\'text-slate-400\'>View job details below.</p>\n            </div>\n            \n        </div>\n    ', 'job_details.php?id=8', 1, '2025-08-02 05:51:06', 'general'),
(12, 'jacky@2007', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n                <p>New job posted: <span class=\'font-semibold\'></span> at <span class=\'font-semibold\'></span></p>\n                <p class=\'text-slate-400\'>View job details below.</p>\n            </div>\n            \n        </div>\n    ', 'job_details.php?id=8', 0, '2025-08-02 05:51:06', 'general'),
(13, 'Jio Company', '\n        <div class=\'space-y-1 leading-6\'>\n            \n<p><span class=\'font-semibold\'>jenish@123</span> applied for <span class=\'font-semibold\'>php developer</span></p>\n<p class=\'text-slate-400\'>Check applicant details below.</p>\n\n            \n        </div>\n    ', 'view_applicants.php?job_id=8 ', 1, '2025-08-02 06:09:35', 'general'),
(14, 'jacky@2007', '\n        <div class=\'space-y-1 leading-6\'>\n            \n<p><span class=\'font-semibold\'>Job Title:</span>    laravel developer</p>\n<p><span class=\'font-semibold\'>Company:</span> Jio Company</p>\n\n            <span class=\'text-green-400 font-semibold\'>Status: Approved</span>\n        </div>\n    ', 'track_application.php?job_id=7', 0, '2025-08-02 06:15:30', 'general'),
(15, 'jenish@123', '\n        <div class=\'space-y-1 leading-6\'>\n            \n<p><span class=\'font-semibold\'>Job Title:</span> php developer</p>\n<p><span class=\'font-semibold\'>Company:</span> Jio Company</p>\n\n            <span class=\'text-green-400 font-semibold\'>Status: Approved</span>\n        </div>\n    ', 'track_application.php?job_id=8', 1, '2025-08-02 06:15:35', 'general'),
(16, 'Jio Company', '\n        <div class=\'space-y-1 leading-6\'>\n            \n<p><span class=\'font-semibold\'>jacky@2007</span> applied for <span class=\'font-semibold\'>php developer</span></p>\n<p class=\'text-slate-400\'>Check applicant details below.</p>\n\n            \n        </div>\n    ', 'view_applicants.php?job_id=8 ', 1, '2025-08-02 06:21:19', 'general'),
(17, 'jacky@2007', '\n        <div class=\'space-y-1 leading-6\'>\n            \n<p><span class=\'font-semibold\'>Job Title:</span> php developer</p>\n<p><span class=\'font-semibold\'>Company:</span> Jio Company</p>\n\n            <span class=\'text-green-400 font-semibold\'>Status: Approved</span>\n        </div>\n    ', 'track_application.php?job_id=8', 0, '2025-08-02 06:21:44', 'general'),
(18, 'jenish@123', 'Your application is approved, now you can meet for an interview.', '#', 0, '2025-08-08 08:54:03', 'general'),
(19, 'jenish@123', 'Your application is approved, now you can meet for an interview.', '#', 0, '2025-08-08 08:54:17', 'general'),
(20, 'jenish@123', 'Your application is approved, now you can meet for an interview.', '#', 0, '2025-08-08 08:57:22', 'general'),
(21, 'jenish@123', 'Your application is approved, now you can meet for an interview.', '#', 0, '2025-08-08 08:57:55', 'general'),
(22, 'jenish@123', 'Your application is approved, now you can meet for an interview.', '#', 0, '2025-08-08 09:00:00', 'general'),
(23, 'jenish@123', 'Your application is approved, now you can meet for an interview.', '#', 0, '2025-08-08 09:01:20', 'general'),
(24, 'jenish@123', 'Your application is approved, now you can meet for an interview.', '#', 0, '2025-08-08 09:01:23', 'general'),
(25, 'jenish@123', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>Perl Developer</span> at <span class=\'font-semibold\'>Jio Company</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-17 10:23:04', 'general'),
(26, 'jacky@2007', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>Perl Developer</span> at <span class=\'font-semibold\'>Jio Company</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-17 10:23:04', 'general'),
(27, 'jenish@123', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>Perl Developer</span> at <span class=\'font-semibold\'>Jio Company</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-17 10:23:18', 'general'),
(28, 'jacky@2007', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>Perl Developer</span> at <span class=\'font-semibold\'>Jio Company</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-17 10:23:18', 'general'),
(29, 'jenish@123', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>Perl Developer</span> at <span class=\'font-semibold\'>Jio Company</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-17 10:25:49', 'general'),
(30, 'jacky@2007', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>Perl Developer</span> at <span class=\'font-semibold\'>Jio Company</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-17 10:25:49', 'general'),
(31, 'Jio Company', '\n        <div class=\'space-y-1 leading-6\'>\n            abc\n            \n        </div>\n    ', 'abc has applied for your job post.', 0, '2025-08-17 10:49:52', 'general'),
(32, 'Jio Company', '\n        <div class=\'space-y-1 leading-6\'>\n            abc\n            \n        </div>\n    ', '<p><span class=\'font-semibold\'>abc</span> applied for <span class=\'font-semibold\'></span></p>\n<p class=\'text-slate-400\'>Check applicant details below.</p>\n', 0, '2025-08-17 10:56:39', 'general'),
(33, 'jenish@123', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>Perl Developer</span> at <span class=\'font-semibold\'>Jio Company</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-17 11:01:27', 'general'),
(34, 'jacky@2007', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>Perl Developer</span> at <span class=\'font-semibold\'>Jio Company</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-17 11:01:27', 'general'),
(35, 'abc', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>Perl Developer</span> at <span class=\'font-semibold\'>Jio Company</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-17 11:01:27', 'general'),
(36, 'Jio Company', '\n        <div class=\'space-y-1 leading-6\'>\n            jenish@123\n            \n        </div>\n    ', '\n                        <p><span class=\'font-semibold\'>jenish@123</span> applied for <span class=\'font-semibold\'>Perl Developer</span></p>\n                        <p class=\'text-slate-400\'>Check applicant details below.</p>\n                    ', 0, '2025-08-17 11:09:57', 'general'),
(37, 'Jio Company', '\n        <div class=\'space-y-1 leading-6\'>\n            jacky@2007\n            \n        </div>\n    ', '\n                        <p><span class=\'font-semibold\'>jacky@2007</span> applied for <span class=\'font-semibold\'>Perl Developer</span></p>\n                        <p class=\'text-slate-400\'>Check applicant details below.</p>\n                    ', 0, '2025-08-17 11:13:49', 'general'),
(38, 'jenish@123', '\n        <div class=\'space-y-1 leading-6\'>\n            \n<p><span class=\'font-semibold\'>Job Title:</span> Perl Developer</p>\n<p><span class=\'font-semibold\'>Company:</span> Jio Company</p>\n\n            <span class=\'text-green-400 font-semibold\'>Status: Approved</span>\n        </div>\n    ', 'track_application.php?job_id=12', 0, '2025-08-17 11:21:33', 'general'),
(39, 'jacky@2007', '\n        <div class=\'space-y-1 leading-6\'>\n            \n<p><span class=\'font-semibold\'>Job Title:</span> Perl Developer</p>\n<p><span class=\'font-semibold\'>Company:</span> Jio Company</p>\n\n            <span class=\'text-green-400 font-semibold\'>Status: Approved</span>\n        </div>\n    ', 'track_application.php?job_id=12', 0, '2025-08-17 11:21:40', 'general'),
(40, 'jenish@123', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>Perl Developer</span> at <span class=\'font-semibold\'>Jio Company</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-18 10:57:39', 'general'),
(41, 'jacky@2007', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>Perl Developer</span> at <span class=\'font-semibold\'>Jio Company</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-18 10:57:39', 'general'),
(42, 'abc', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>Perl Developer</span> at <span class=\'font-semibold\'>Jio Company</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-18 10:57:39', 'general'),
(43, 'jenish@123', '\n        <div class=\'space-y-1 leading-6\'>\n            \n                        <p><span class=\'font-semibold\'>jenish@123</span> applied for <span class=\'font-semibold\'>Perl Developer</span></p>\n                        <p class=\'text-slate-400\'>Check applicant details below.</p>\n                    \n            \n        </div>\n    ', NULL, 0, '2025-08-18 10:58:18', 'general'),
(44, 'jacky@2007', '\n        <div class=\'space-y-1 leading-6\'>\n            \n                        <p><span class=\'font-semibold\'>jacky@2007</span> applied for <span class=\'font-semibold\'>Perl Developer</span></p>\n                        <p class=\'text-slate-400\'>Check applicant details below.</p>\n                    \n            \n        </div>\n    ', NULL, 0, '2025-08-18 11:00:19', 'general'),
(45, 'abc', '\n        <div class=\'space-y-1 leading-6\'>\n            \n                        <p><span class=\'font-semibold\'>abc</span> applied for <span class=\'font-semibold\'>Perl Developer</span></p>\n                        <p class=\'text-slate-400\'>Check applicant details below.</p>\n                    \n            \n        </div>\n    ', NULL, 0, '2025-08-18 11:01:44', 'general'),
(46, 'abc', '\n                        <p><span class=\'font-semibold\'>abc</span> applied for <span class=\'font-semibold\'>Perl Developer</span></p>\n                        <p class=\'text-slate-400\'>Check applicant details below.</p>\n                    ', NULL, 0, '2025-08-18 11:01:44', 'general'),
(47, 'Jio Company', '\n        <div class=\'space-y-1 leading-6\'>\n            \n                        <p><span class=\'font-semibold\'>xyz</span> applied for <span class=\'font-semibold\'>java developer</span></p>\n                        <p class=\'text-slate-400\'>Check applicant details below.</p>\n                    \n            \n        </div>\n    ', 'view_applicants.php?job_id=6 ', 0, '2025-08-18 11:04:43', 'general'),
(48, 'Jio Company', '\n        <div class=\'space-y-1 leading-6\'>\n            \n                        <p><span class=\'font-semibold\'></span> applied for <span class=\'font-semibold\'>   laravel developer</span></p>\n                        <p class=\'text-slate-400\'>Check applicant details below.</p>\n                    \n            \n        </div>\n    ', 'view_applicants.php?job_id=7 ', 0, '2025-08-18 11:12:00', 'general'),
(49, 'jenish@123', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>Perl Developer</span> at <span class=\'font-semibold\'>jio company</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-18 11:13:50', 'general'),
(50, 'jacky@2007', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>Perl Developer</span> at <span class=\'font-semibold\'>jio company</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-18 11:13:50', 'general'),
(51, 'abc', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>Perl Developer</span> at <span class=\'font-semibold\'>jio company</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-18 11:13:50', 'general'),
(52, 'xyz', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>Perl Developer</span> at <span class=\'font-semibold\'>jio company</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-18 11:13:50', 'general'),
(53, 'Jio Company', '\n        <div class=\'space-y-1 leading-6\'>\n            \n                        <p><span class=\'font-semibold\'>Jenish Rathod</span> applied for <span class=\'font-semibold\'>Perl Developer</span></p>\n                        <p class=\'text-slate-400\'>Check applicant details below.</p>\n                    \n            \n        </div>\n    ', 'view_applicants.php?job_id=14 ', 0, '2025-08-18 11:14:09', 'general'),
(54, 'jenish@123', '\n        <div class=\'space-y-1 leading-6\'>\n            \n<p><span class=\'font-semibold\'>Job Title:</span> Perl Developer</p>\n<p><span class=\'font-semibold\'>Company:</span> jio company</p>\n\n            <span class=\'text-green-400 font-semibold\'>Status: Approved</span>\n        </div>\n    ', 'track_application.php?job_id=14', 0, '2025-08-18 11:15:08', 'general'),
(55, 'Jio Company', '\n        <div class=\'space-y-1 leading-6\'>\n            \n                        <p><span class=\'font-semibold\'>Devils Jacky</span> applied for <span class=\'font-semibold\'>Perl Developer</span></p>\n                        <p class=\'text-slate-400\'></p>\n                    \n            \n        </div>\n    ', 'view_applicants.php?job_id=14 ', 0, '2025-08-18 11:45:13', 'general'),
(56, 'jacky@2007', '\n        <div class=\'space-y-1 leading-6\'>\n            \n<p><span class=\'font-semibold\'>Job Title:</span> Perl Developer</p>\n<p><span class=\'font-semibold\'>Company:</span> jio company</p>\n\n            <span class=\'text-green-400 font-semibold\'>Status: Approved</span>\n        </div>\n    ', 'track_application.php?job_id=14', 0, '2025-08-18 11:45:44', 'general'),
(57, 'jenish@123', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>CYBER</span> at <span class=\'font-semibold\'>JIO RELIANCE</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-22 10:32:10', 'general'),
(58, 'jacky@2007', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>CYBER</span> at <span class=\'font-semibold\'>JIO RELIANCE</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-22 10:32:10', 'general'),
(59, 'abc', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>CYBER</span> at <span class=\'font-semibold\'>JIO RELIANCE</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-22 10:32:10', 'general'),
(60, 'xyz', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>CYBER</span> at <span class=\'font-semibold\'>JIO RELIANCE</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-22 10:32:10', 'general'),
(61, 'Jio Company', '\n        <div class=\'space-y-1 leading-6\'>\n            \n                        <p><span class=\'font-semibold\'>Jenish Rathod</span> applied for <span class=\'font-semibold\'>CYBER</span></p>\n                        <p class=\'text-slate-400\'></p>\n                    \n            \n        </div>\n    ', 'view_applicants.php?job_id=15 ', 0, '2025-08-26 03:45:53', 'general'),
(62, 'jenish@123', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>CYBER</span> at <span class=\'font-semibold\'>JIO RELIANCE</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-26 04:11:30', 'general'),
(63, 'jacky@2007', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>CYBER</span> at <span class=\'font-semibold\'>JIO RELIANCE</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-26 04:11:30', 'general'),
(64, 'abc', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>CYBER</span> at <span class=\'font-semibold\'>JIO RELIANCE</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-26 04:11:30', 'general'),
(65, 'xyz', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>CYBER</span> at <span class=\'font-semibold\'>JIO RELIANCE</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-26 04:11:30', 'general'),
(66, 'jenish@123', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>python developer</span> at <span class=\'font-semibold\'>JIO RELIANCE</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-26 04:14:33', 'general'),
(67, 'jacky@2007', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>python developer</span> at <span class=\'font-semibold\'>JIO RELIANCE</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-26 04:14:33', 'general'),
(68, 'abc', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>python developer</span> at <span class=\'font-semibold\'>JIO RELIANCE</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-26 04:14:33', 'general'),
(69, 'xyz', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>python developer</span> at <span class=\'font-semibold\'>JIO RELIANCE</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-26 04:14:33', 'general'),
(70, 'Jio Company', '\n        <div class=\'space-y-1 leading-6\'>\n            \n                        <p><span class=\'font-semibold\'>Jenish Rathod</span> applied for <span class=\'font-semibold\'>python developer</span></p>\n                        <p class=\'text-slate-400\'></p>\n                    \n            \n        </div>\n    ', 'view_applicants.php?job_id=17 ', 0, '2025-08-26 04:21:46', 'general'),
(71, 'Jio Company', '\n        <div class=\'space-y-1 leading-6\'>\n            \n                        <p><span class=\'font-semibold\'></span> applied for <span class=\'font-semibold\'>python developer</span></p>\n                        <p class=\'text-slate-400\'></p>\n                    \n            \n        </div>\n    ', 'view_applicants.php?job_id=17 ', 0, '2025-08-26 08:00:31', 'general'),
(72, 'jenish@123', '\n        <div class=\'space-y-1 leading-6\'>\n            \n<p><span class=\'font-semibold\'>Job Title:</span> python developer</p>\n<p><span class=\'font-semibold\'>Company:</span> JIO RELIANCE</p>\n\n            <span class=\'text-green-400 font-semibold\'>Status: Approved</span>\n        </div>\n    ', 'track_application.php?job_id=17', 0, '2025-08-26 08:05:25', 'general'),
(73, 'jenish@123', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>CYBER</span> at <span class=\'font-semibold\'>JIO RELIANCE</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-26 08:06:08', 'general'),
(74, 'jacky@2007', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>CYBER</span> at <span class=\'font-semibold\'>JIO RELIANCE</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-26 08:06:08', 'general'),
(75, 'abc', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>CYBER</span> at <span class=\'font-semibold\'>JIO RELIANCE</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-26 08:06:08', 'general'),
(76, 'xyz', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>CYBER</span> at <span class=\'font-semibold\'>JIO RELIANCE</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-26 08:06:08', 'general'),
(77, 'qwe', '\n        <div class=\'space-y-1 leading-6\'>\n            <div class=\'space-y-1 leading-6\'>\n        <p>New job posted: <span class=\'font-semibold\'>CYBER</span> at <span class=\'font-semibold\'>JIO RELIANCE</span></p>\n        <p class=\'text-slate-400\'>View job details below.</p>\n    </div>\n            \n        </div>\n    ', 'job_matches.php', 0, '2025-08-26 08:06:08', 'general'),
(78, 'Jio Company', '\n        <div class=\'space-y-1 leading-6\'>\n            \n                        <p><span class=\'font-semibold\'>Jenish Rathod</span> applied for <span class=\'font-semibold\'>CYBER</span></p>\n                        <p class=\'text-slate-400\'></p>\n                    \n            \n        </div>\n    ', 'view_applicants.php?job_id=18 ', 0, '2025-08-31 13:10:11', 'general'),
(79, 'qwe', '\n        <div class=\'space-y-1 leading-6\'>\n            \n<p><span class=\'font-semibold\'>Job Title:</span> python developer</p>\n<p><span class=\'font-semibold\'>Company:</span> JIO RELIANCE</p>\n\n            <span class=\'text-green-400 font-semibold\'>Status: Approved</span>\n        </div>\n    ', 'track_application.php?job_id=17', 0, '2025-09-01 17:54:18', 'general'),
(80, 'jenish@123', '\n        <div class=\'space-y-1 leading-6\'>\n            \n<p><span class=\'font-semibold\'>Job Title:</span> CYBER</p>\n<p><span class=\'font-semibold\'>Company:</span> JIO RELIANCE</p>\n\n            <span class=\'text-green-400 font-semibold\'>Status: Approved</span>\n        </div>\n    ', 'track_application.php?job_id=18', 0, '2025-09-01 17:54:22', 'general'),
(81, 'admin', 'User <strong>abc</strong> logged in.', 'manage_user.php', 0, '2025-09-03 04:12:41', 'login'),
(82, 'admin', 'User <strong>admin</strong> logged in.', 'manage_user.php', 0, '2025-09-03 04:12:52', 'login'),
(83, 'admin', 'Company <strong>infosys company</strong> logged in.', 'manage_companies.php', 0, '2025-09-03 04:13:42', 'login'),
(84, 'admin', 'User <strong>admin</strong> logged in.', 'manage_user.php', 0, '2025-09-03 04:13:50', 'login'),
(85, 'admin', 'User <strong>admin</strong> logged in.', 'manage_user.php', 0, '2025-09-03 04:16:12', 'login'),
(86, 'admin', 'User <strong>jenish@123</strong> logged in.', 'manage_user.php', 0, '2025-09-03 14:21:33', 'login'),
(87, 'admin', 'Company <strong>Jio Company</strong> logged in.', 'manage_companies.php', 0, '2025-09-03 14:29:41', 'login'),
(88, 'admin', 'User <strong>admin</strong> logged in.', 'manage_user.php', 0, '2025-09-03 14:35:05', 'login'),
(89, 'admin', 'User <strong>admin</strong> logged in.', 'manage_user.php', 0, '2025-09-06 11:03:55', 'login'),
(90, 'admin', 'User <strong>jenish@123</strong> logged in.', 'manage_user.php', 0, '2025-09-06 11:12:13', 'login'),
(91, 'admin', 'User <strong>admin</strong> logged in.', 'manage_user.php', 0, '2025-09-06 11:13:23', 'login'),
(92, 'admin', 'User <strong>jenish@123</strong> logged in.', 'manage_user.php', 0, '2025-09-06 12:01:23', 'login'),
(93, 'admin', 'User <strong>admin</strong> logged in.', 'manage_user.php', 0, '2025-09-06 12:04:01', 'login'),
(94, 'admin', 'User <strong>jenish@123</strong> logged in.', 'manage_user.php', 0, '2025-09-06 12:04:15', 'login'),
(95, 'admin', 'User <strong>admin</strong> logged in.', 'manage_user.php', 0, '2025-09-06 12:14:37', 'login'),
(96, 'admin', 'Company <strong>Jio Company</strong> logged in.', 'manage_companies.php', 0, '2025-09-06 12:15:22', 'login'),
(97, 'admin', 'User <strong>admin</strong> logged in.', 'manage_user.php', 0, '2025-09-06 12:15:43', 'login'),
(98, 'admin', 'User <strong>jenish@123</strong> logged in.', 'manage_user.php', 0, '2025-09-06 12:42:03', 'login'),
(99, 'admin', 'User <strong>admin</strong> logged in.', 'manage_user.php', 0, '2025-09-07 07:23:45', 'login'),
(100, 'admin', 'Company <strong>Jio Company</strong> logged in.', 'manage_companies.php', 0, '2025-09-07 07:27:16', 'login'),
(101, 'admin', 'User <strong>admin</strong> logged in.', 'manage_user.php', 0, '2025-09-07 08:02:53', 'login'),
(102, 'abc', '⛔ Your account has been permanently banned by admin.', 'profile.php?username=abc', 0, '2025-09-07 08:19:55', 'status'),
(103, 'admin', 'User <strong>abc</strong> logged in.', 'manage_user.php', 0, '2025-09-07 08:20:35', 'login'),
(104, 'admin', 'User <strong>abc</strong> logged in.', 'manage_user.php', 0, '2025-09-07 08:41:41', 'login'),
(105, 'admin', 'User <strong>admin</strong> logged in.', 'manage_user.php', 0, '2025-09-07 08:46:51', 'login'),
(106, 'admin', 'User <strong>abc</strong> logged in.', 'manage_user.php', 0, '2025-09-07 08:57:46', 'login'),
(107, 'admin', 'User <strong>abc</strong> logged in.', 'manage_user.php', 0, '2025-09-07 08:58:11', 'login'),
(108, 'admin', 'User <strong>admin</strong> logged in.', 'manage_user.php', 0, '2025-09-07 08:58:49', 'login'),
(109, 'admin', 'User <strong>jenish@123</strong> logged in.', 'manage_user.php', 0, '2025-09-07 10:39:38', 'login'),
(110, 'admin', 'User <strong>jenish@123</strong> logged in.', 'manage_user.php', 0, '2025-09-08 13:56:54', 'login'),
(111, 'admin', 'User <strong>admin</strong> logged in.', 'manage_user.php', 0, '2025-09-08 13:57:10', 'login');

-- --------------------------------------------------------

--
-- Table structure for table `resumes`
--

CREATE TABLE `resumes` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resumes`
--

INSERT INTO `resumes` (`id`, `username`, `file_name`, `file_path`, `uploaded_at`) VALUES
(1, 'jenish@123', 'Resume.pdf', 'uploads/resumes/resume_68b5d764a565e.pdf', '2025-09-01 17:27:00');

-- --------------------------------------------------------

--
-- Table structure for table `search_history`
--

CREATE TABLE `search_history` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `search_term` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `search_history`
--

INSERT INTO `search_history` (`id`, `username`, `search_term`, `created_at`) VALUES
(1, 'jenish@123', 'p', '2025-08-26 08:31:38'),
(2, 'jenish@123', 'py', '2025-08-26 08:31:39'),
(3, 'jenish@123', 'pyth', '2025-08-26 08:31:39'),
(4, 'jenish@123', 'pyth', '2025-08-26 08:31:39'),
(5, 'jenish@123', 'pythi', '2025-08-26 08:31:39'),
(6, 'jenish@123', 'pythin', '2025-08-26 08:31:39'),
(7, 'jenish@123', 'pythi', '2025-08-26 08:31:40'),
(8, 'jenish@123', 'pyth', '2025-08-26 08:31:40'),
(9, 'jenish@123', 'pytho', '2025-08-26 08:31:41'),
(10, 'jenish@123', 'python', '2025-08-26 08:31:41'),
(11, 'jenish@123', 'python', '2025-08-26 08:31:42'),
(12, 'jenish@123', 'python', '2025-08-26 08:31:43'),
(13, 'jenish@123', 'p', '2025-08-26 08:38:04'),
(14, 'jenish@123', 'py', '2025-08-26 08:38:04'),
(15, 'jenish@123', 'pyt', '2025-08-26 08:38:04'),
(16, 'jenish@123', 'pyth', '2025-08-26 08:38:05'),
(17, 'jenish@123', 'pytho', '2025-08-26 08:38:05'),
(18, 'jenish@123', 'pytho', '2025-08-26 08:38:06'),
(19, 'jenish@123', 'python', '2025-08-26 08:38:07'),
(20, 'jenish@123', 'python\'', '2025-08-26 08:38:07'),
(21, 'jenish@123', 'python', '2025-08-26 08:38:08'),
(22, 'jenish@123', 'python', '2025-08-26 08:38:09'),
(23, 'jenish@123', 'python', '2025-08-26 08:38:09'),
(24, 'jenish@123', 'python', '2025-08-26 08:38:09'),
(25, 'jenish@123', 'p', '2025-08-26 08:41:39'),
(26, 'jenish@123', 'py', '2025-08-26 08:41:39'),
(27, 'jenish@123', 'pyt', '2025-08-26 08:41:39'),
(28, 'jenish@123', 'py', '2025-08-26 08:41:40'),
(29, 'jenish@123', 'pyt', '2025-08-26 08:41:41'),
(30, 'jenish@123', 'pyth', '2025-08-26 08:41:41'),
(31, 'jenish@123', 'pytho', '2025-08-26 08:41:41'),
(32, 'jenish@123', 'python', '2025-08-26 08:41:42'),
(33, 'jenish@123', 'python', '2025-08-26 08:41:42'),
(34, 'jenish@123', 'python', '2025-08-26 08:41:43'),
(35, 'jenish@123', 'python', '2025-08-26 08:41:43'),
(36, 'jenish@123', 'python', '2025-08-26 08:41:43'),
(37, 'jenish@123', 'python', '2025-08-26 08:41:44'),
(38, 'jenish@123', 'python', '2025-08-26 08:41:44'),
(39, 'jenish@123', 'python', '2025-08-26 08:41:44'),
(40, 'jenish@123', 'python', '2025-08-26 08:41:44'),
(41, 'jenish@123', 'python', '2025-08-26 08:41:44'),
(42, 'jenish@123', 'python', '2025-08-26 08:41:44'),
(43, 'jenish@123', 'python', '2025-08-26 08:41:45'),
(44, 'jenish@123', 'p', '2025-08-31 12:04:27'),
(45, 'jenish@123', 'py', '2025-08-31 12:04:27'),
(46, 'jenish@123', 'pyt', '2025-08-31 12:04:27'),
(47, 'jenish@123', 'pyth', '2025-08-31 12:04:27'),
(48, 'jenish@123', 'pytho', '2025-08-31 12:04:28'),
(49, 'jenish@123', 'python', '2025-08-31 12:04:28'),
(50, 'jenish@123', 'python', '2025-08-31 12:04:29'),
(51, 'jenish@123', 'python', '2025-08-31 12:04:31'),
(52, 'jenish@123', 'python', '2025-08-31 12:04:31'),
(53, 'jenish@123', 'python', '2025-08-31 12:04:32'),
(54, 'jenish@123', 'CYBER', '2025-08-31 12:04:45'),
(55, 'jenish@123', 'CYBER', '2025-08-31 12:06:24'),
(56, 'jenish@123', 'CYBER', '2025-08-31 12:06:34'),
(57, 'jenish@123', 'CYBER', '2025-08-31 12:07:24'),
(58, 'jenish@123', 'CYBER', '2025-08-31 12:07:43'),
(59, 'jenish@123', 'CYBER', '2025-08-31 12:07:46'),
(60, 'jenish@123', 'CYBER', '2025-08-31 12:08:05'),
(61, 'jenish@123', 'CYBER', '2025-08-31 12:08:08'),
(62, 'jenish@123', 'CYBER', '2025-08-31 12:08:10'),
(63, 'jenish@123', 'CYBER', '2025-08-31 12:08:10'),
(64, 'jenish@123', 'php', '2025-08-31 12:08:17'),
(65, 'jenish@123', 'c', '2025-08-31 12:24:58'),
(66, 'jenish@123', 'cy', '2025-08-31 12:24:58'),
(67, 'jenish@123', 'cyb', '2025-08-31 12:24:59'),
(68, 'jenish@123', 'cybe', '2025-08-31 12:24:59'),
(69, 'jenish@123', 'cyber', '2025-08-31 12:24:59'),
(70, 'jenish@123', 'cyber', '2025-08-31 12:25:00'),
(71, 'jenish@123', 'cyber', '2025-08-31 12:25:01'),
(72, 'jenish@123', 'cyber', '2025-08-31 12:25:04'),
(73, 'jenish@123', 'cyber', '2025-08-31 12:25:04'),
(74, 'jenish@123', 'cyber', '2025-08-31 12:25:04'),
(75, 'jenish@123', 'cyber', '2025-08-31 12:25:04'),
(76, 'jenish@123', 'cyber', '2025-08-31 12:25:04'),
(77, 'jenish@123', 'cyber', '2025-08-31 12:25:05'),
(78, 'jenish@123', 'cyber', '2025-08-31 12:25:05'),
(79, 'jenish@123', 'cyber', '2025-08-31 12:25:05'),
(80, 'jenish@123', 'cyber', '2025-08-31 12:25:05'),
(81, 'jenish@123', 'cyber', '2025-08-31 12:25:05'),
(82, 'jenish@123', 'cyber', '2025-08-31 12:25:06'),
(83, 'jenish@123', 'cyber', '2025-08-31 12:25:06'),
(84, 'jenish@123', 'cyber', '2025-08-31 12:25:06'),
(85, 'jenish@123', 'cyber', '2025-08-31 12:25:06'),
(86, 'jenish@123', 'cyber', '2025-08-31 12:25:06'),
(87, 'jenish@123', 'cyber', '2025-08-31 12:25:06'),
(88, 'jenish@123', 'cyber', '2025-08-31 12:25:07'),
(89, 'jenish@123', 'cyber', '2025-08-31 12:25:07'),
(90, 'jenish@123', 'cyber', '2025-08-31 12:25:07'),
(91, 'jenish@123', 'cyber', '2025-08-31 12:25:07'),
(92, 'jenish@123', 'cyber', '2025-08-31 12:25:08'),
(93, 'jenish@123', 'cyber', '2025-08-31 12:25:08'),
(94, 'jenish@123', 'cyber', '2025-08-31 12:25:08'),
(95, 'jenish@123', 'cyber', '2025-08-31 12:26:17'),
(96, 'jenish@123', 'cyber', '2025-08-31 12:26:17'),
(97, 'jenish@123', 'cyber', '2025-08-31 12:28:42'),
(98, 'jenish@123', 'cyber', '2025-08-31 12:28:42'),
(99, 'jenish@123', 'cyber', '2025-08-31 12:30:35'),
(100, 'jenish@123', 'cyber', '2025-08-31 12:30:35'),
(101, 'jenish@123', 'php', '2025-08-31 12:31:01'),
(102, 'jenish@123', 'php', '2025-08-31 12:31:01'),
(103, 'jenish@123', 'php', '2025-08-31 13:05:42'),
(104, 'jenish@123', 'php', '2025-08-31 13:05:42'),
(105, 'jenish@123', 'php', '2025-08-31 13:06:38'),
(106, 'jenish@123', 'php', '2025-08-31 13:06:38'),
(107, 'jenish@123', '3', '2025-08-31 13:06:53'),
(108, 'jenish@123', '3', '2025-08-31 13:06:53'),
(109, 'jenish@123', 'rajkot', '2025-08-31 13:07:08'),
(110, 'jenish@123', 'rajkot', '2025-08-31 13:07:08'),
(111, 'jenish@123', '2', '2025-08-31 13:07:30'),
(112, 'jenish@123', '2', '2025-08-31 13:07:30'),
(113, 'jenish@123', 'CYBER', '2025-08-31 13:07:39'),
(114, 'jenish@123', 'CYBER', '2025-08-31 13:07:39'),
(115, 'jenish@123', 'CYBER', '2025-08-31 13:08:16'),
(116, 'jenish@123', 'CYBER', '2025-08-31 13:08:16'),
(117, 'jenish@123', 'CYBER', '2025-08-31 13:08:22'),
(118, 'jenish@123', 'CYBER', '2025-08-31 13:08:22'),
(119, 'jenish@123', 'python', '2025-08-31 13:08:34'),
(120, 'jenish@123', 'python', '2025-08-31 13:08:34'),
(121, 'jenish@123', 'php', '2025-08-31 13:09:00'),
(122, 'jenish@123', 'php', '2025-08-31 13:09:00'),
(123, 'jenish@123', 'php', '2025-08-31 13:09:53'),
(124, 'jenish@123', 'php', '2025-08-31 13:09:53'),
(125, 'jenish@123', 'python', '2025-09-06 12:11:21'),
(126, 'jenish@123', 'python', '2025-09-06 12:11:21'),
(127, 'jenish@123', 'python developer', '2025-09-06 12:11:36'),
(128, 'jenish@123', 'python developer', '2025-09-06 12:11:36'),
(129, 'jenish@123', 'jio', '2025-09-06 12:11:47'),
(130, 'jenish@123', 'jio', '2025-09-06 12:11:47'),
(131, 'jenish@123', 'jio', '2025-09-06 12:14:26'),
(132, 'jenish@123', 'jio', '2025-09-06 12:14:26');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin','company') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','banned') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`, `status`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$10$tqeI2gY3aUAXPuxrPd.38O/Uk.bEm4ePUJj5A9XioKyeWxw.sSkfG', 'admin', '2025-07-23 07:30:43', 'active'),
(2, 'jenish@123', 'jenish123@gmail.com', '$2y$10$W6/5TXOBzmYtjAYbKvWGXuF0ZMhvfGodYWXp1TIlsFw6yJZQBpasm', 'user', '2025-07-23 07:31:34', 'active'),
(16, 'Jio Company', 'jioCompany123@gmail.com', '$2y$10$CJTu25XwBgvnhxkXU.EVnu3/Q7d2QsG9NwHQHByQGIsOSp2TWjtoS', 'company', '2025-07-28 17:01:34', 'active'),
(17, 'jacky@2007', 'jacky2007@gmail.com', '$2y$10$UL9TCvij3SRRoiJtUL.7TeWVe/wEZpZN73yfHAEK4Iffjm7KO14kO', 'user', '2025-07-29 04:19:21', 'active'),
(22, 'abc', 'abc@gmail.com', '$2y$10$kwo3ZrWtYuJNDH0Y8gJGn.akZ3iLvkCs9V8OF9oAHA1R67o/49JWy', 'user', '2025-09-03 04:12:34', 'active'),
(23, 'infosys company', 'infosyscom@gmail.com', '$2y$10$KPP7fc0wUphN4A1udaovKe48QapUeO4piP7egkHYQp.kTNapREBcy', 'company', '2025-09-03 04:13:31', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `fullname` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `language` varchar(100) DEFAULT NULL,
  `aboutme` text DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`id`, `username`, `fullname`, `phone`, `address`, `email`, `birthdate`, `language`, `aboutme`, `skills`, `logo`, `created_at`, `is_verified`) VALUES
(1, 'jenish@123', 'Jenish Rathod', '+91 6789055252', 'rajkot', 'jenish123@gmail.com', '2025-01-01', 'English,gujarati,hindi,spanish', 'hi ', 'java', '1.webp', '2025-07-23 08:26:59', 1),
(7, 'jacky@2007', 'Devils Jacky', '+91 6789055252', 'a', 'jacky2007@gmail.com', '2025-07-01', 'English,gujarati,hindi,spanish', 'a', 'a', '2.webp', '2025-07-29 04:21:44', 0),
(8, 'abc', 'abc', '', '', '', '0000-00-00', '', '', '', '', '2025-09-07 08:42:09', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_status`
--

CREATE TABLE `user_status` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `status` enum('normal','trusted','banned') DEFAULT 'normal',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_status`
--

INSERT INTO `user_status` (`id`, `username`, `status`, `created_at`) VALUES
(1, 'abc', 'banned', '2025-09-07 08:19:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `company_jobs`
--
ALTER TABLE `company_jobs`
  ADD PRIMARY KEY (`job_id`);

--
-- Indexes for table `company_profiles`
--
ALTER TABLE `company_profiles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `create_resume`
--
ALTER TABLE `create_resume`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `job_id` (`job_id`,`username`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_receiver` (`receiver_username`);

--
-- Indexes for table `resumes`
--
ALTER TABLE `resumes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `search_history`
--
ALTER TABLE `search_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username_2` (`username`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `user_status`
--
ALTER TABLE `user_status`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `company_jobs`
--
ALTER TABLE `company_jobs`
  MODIFY `job_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `company_profiles`
--
ALTER TABLE `company_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `create_resume`
--
ALTER TABLE `create_resume`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(250) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `resumes`
--
ALTER TABLE `resumes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `search_history`
--
ALTER TABLE `search_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_status`
--
ALTER TABLE `user_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `create_resume`
--
ALTER TABLE `create_resume`
  ADD CONSTRAINT `create_resume_ibfk_1` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE;

--
-- Constraints for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD CONSTRAINT `job_applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `company_jobs` (`job_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_applications_ibfk_2` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
