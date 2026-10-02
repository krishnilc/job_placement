-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 02, 2026 at 10:09 AM
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
-- Database: `job_placement`
--

-- --------------------------------------------------------

--
-- Table structure for table `application_statuses`
--

CREATE TABLE `application_statuses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `application_statuses`
--

INSERT INTO `application_statuses` (`id`, `name`, `category`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Submitted', 'Active', 1, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(2, 'Under Review', 'Active', 2, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(3, 'Shortlisted', 'Active', 3, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(4, 'Interview Scheduled', 'Active', 4, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(5, 'Interview Completed', 'Active', 5, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(6, 'Accepted', 'Successful', 6, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(7, 'Placed', 'Successful', 7, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(8, 'Rejected', 'Unsuccessful', 8, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(9, 'Withdrawn', 'Withdrawn', 9, '2026-09-02 11:33:00', '2026-09-02 11:33:00');

-- --------------------------------------------------------

--
-- Table structure for table `application_status_history`
--

CREATE TABLE `application_status_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `job_application_id` bigint(20) UNSIGNED NOT NULL,
  `application_status_id` bigint(20) UNSIGNED NOT NULL,
  `changed_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `application_status_history`
--

INSERT INTO `application_status_history` (`id`, `job_application_id`, `application_status_id`, `changed_by`, `created_at`) VALUES
(7, 3, 1, 10, '2026-09-06 12:38:05'),
(8, 3, 2, 9, '2026-09-06 12:38:58'),
(9, 3, 3, 9, '2026-09-06 12:40:00'),
(10, 4, 1, 16, '2026-09-27 11:21:55'),
(12, 6, 1, 17, '2026-09-27 12:34:49'),
(13, 6, 2, 13, '2026-09-27 12:58:16'),
(14, 6, 3, 15, '2026-09-27 12:59:56');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `status`, `created_at`, `updated_at`) VALUES
(3, 'Administration & Office Support', 1, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(4, 'Agriculture & Environment', 1, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(5, 'Accounting, Banking, & Finance', 1, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(6, 'Information Technology (IT) & Computing', 1, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(7, 'Engineering & Technical Fields', 1, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(8, 'Health & Medical Services', 1, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(9, 'Education & Training', 1, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(10, 'Hospitality & Tourism', 1, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(11, 'Creative Arts, Media & Design', 1, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(12, 'Trades & Skilled Services', 1, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(13, 'Science & Research', 1, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(14, 'Government, Legal & Community Services', 1, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(15, 'Student & Graduate Opportunities', 1, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(16, 'Sales, Marketing & Customer Service', 1, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(17, 'Logistics, Transport & Supply Chain', 1, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(18, 'Executive & Management Roles', 1, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(19, 'Other Opportunities', 1, '2026-09-02 11:33:00', '2026-09-02 11:33:00');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `job_type_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `vacancy` int(11) NOT NULL,
  `closing_date` date DEFAULT NULL,
  `salary` varchar(255) DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `benefits` text DEFAULT NULL,
  `responsibilities` text DEFAULT NULL,
  `qualifications` text DEFAULT NULL,
  `keywords` text DEFAULT NULL,
  `experience` varchar(255) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `company_location` varchar(255) DEFAULT NULL,
  `company_website` varchar(255) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `isFeatured` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `title`, `category_id`, `job_type_id`, `user_id`, `vacancy`, `closing_date`, `salary`, `location`, `description`, `benefits`, `responsibilities`, `qualifications`, `keywords`, `experience`, `company_name`, `company_location`, `company_website`, `status`, `isFeatured`, `created_at`, `updated_at`) VALUES
(4, 'Food & Beverage Manager', 18, 3, 9, 1, '2026-09-30', 'Att', 'Suva, Fiji', '<p><span style=\"color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">The Food &amp; Beverage Manager has the key responsibility of ensuring that all Food &amp; Beverage outlets, Conference and Banqueting operations are managed as successful independent profit centers, ensuring maximum guest satisfaction and consistency in line with Hilton International standards. This role will achieve these through the key strategies of planning, controlling, organizing and marketing.&nbsp;</span></p>', NULL, '<p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">As the Food &amp; Beverage Manager, you will be responsible for performing the following tasks to the highest standards:&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Ensure that each food and beverage outlet and conference and banqueting event is managed in line with key service standards and specified profit margins as an independent profit centre.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Ensure that each outlet is managed by a management team (Restaurant Manager / Sous Chef) who are totally accountable for the profitability and service standards achieved.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Coordinate the formulation of the annual operating budget in determining outlet projected revenues and expenses, manning, operating equipment and FF&amp;E requirements in line with the annual business plans, supported by key marketing plans as well as revenue driven initiatives.&nbsp;&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Provide accurate and realistic forecasts and updates on anticipated changes in the business whenever appropriate.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Ensure that supplier liaison with the Purchasing Officer ensures maximum support with regards to sponsorship, marketing and pricing initiatives.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Monitor all costs and recommend measures to control them.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Ensure that the department operational budget is strictly adhered to.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Ensure that all outlets and banquets are managed efficiently according to the established concept statements.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Closely monitor productivity levels through productivity schedules in each outlet and take immediate corrective action if necessary.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Monitor and control vacation planning for the department.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Monitor, control and minimize overtime for the department.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Ensure that the minimum operating standards are adhered to in order to achieve the level of service established in the departmental operations manual.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Maintain and amend where appropriate all SOPs in line with company brand standards and outlet requirements.&nbsp;&nbsp;&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Work with the Outlet Managers, Banquet Service Managers and all respective Team Members to take corrective action where necessary.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Handle all guest complaints, requests and enquiries on food, beverages and services, adhering to established and clearly defined procedures and protocols.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Take personal responsibility for maintaining and revising the policies and procedures manual associated with the department and inter dependent departments to ensure no ambiguity.&nbsp;&nbsp;&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Establish a rapport with guests. maintaining good customer relationship.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Coordinate the formulation of the annual marketing plan to establish a list of marketing activities in line with the annual business plan, supported by appropriate advertising and promotion budgets from suppliers.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Ensure that all Food &amp; Beverage forms and reports are completed and forwarded to the relevant office in a professional and timely manner.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Conduct monthly departmental meetings and daily operations briefings with Outlet Managers.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Maintain good working relationships with colleagues and all other departments.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Have complete understanding of the team member handbook and ensure that team members adhere to the regulations contained within.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Train and develop Outlet Managers so that they are able to operate independently within their own profit centres.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Ensure that each Outlet Manager plans and implements effective training programs for their team members with the Training Manager and Departmental Trainers.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Develop F&amp;B marketing activities and promotions in close cooperation with Outlet Managers, the Executive Chef and the Marketing Communications Manager.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Conduct annual PDR for direct reports and ensure process is followed through by all Outlet Managers.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Ensure that all team members report for duty punctually wearing the correct uniform and name badge at all times.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Have a complete understanding of and adhere to the hotel’s policy relating to Fire, Hygiene, Health and Safety.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Be the key person in driving the hotel’s Food Safety Management System (FSMS).&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Ensure that one of the key responsibilities of all direct reports is to focus on the 9 high risk policies as well as to give Health and Safety compliance top priority.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Ensure that full compliance is maintained in all aspects of Health and Safety within the hotel and any identified shortfalls are addressed with due priority.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Assist in the building of an efficient team of team members by taking an active interest in their welfare, safety and development.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Ensure that all team members provide courteous and professional service at all times.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Assist in the training of team members ensuring that they have the necessary skills to perform their duties with maximum efficiency.&nbsp;&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Ensure that all team members have a complete understanding of and adhere to the hotel’s policy relating to Fire, Hygiene, Health and Safety.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Carry out bi-yearly inventory of operating equipment.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Adhere to the hotel’s security and emergency policies and procedures.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Ensure that all team members have a complete understanding of and adhere to the hotel’s team member rules and regulations.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Carry out any other reasonable duties and responsibilities as assigned.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• The Management reserves the right to make changes to this job description at its sole discretion and without advance notice.&nbsp;</p>', '<p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">A Food &amp; Beverage Manager serving Hilton Brands is always working on behalf of our Guests and working with other Team Members. To successfully fill this role, you should maintain the attitude, behaviours, skills, and values that follow:&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• 5-8 years as Head of Food &amp; Beverage in a 4 / 5-star category hotel or individual restaurants with high standards.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Good command in English, both verbal and written to meet business needs.&nbsp;&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Working knowledge of mathematics.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Familiar with computer systems.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Relevant knowledge of food and beverage.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Motivated and committed, approaching all tasks with enthusiasm and seize opportunities to learn new skills or knowledge in order to improve personal performance.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Flexible and responds quickly and positively to changing requirements including the performance of any tasks requested of you.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Maintain high team focus by showing cooperation and support to colleagues in the pursuit of team goals.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Knowledgeable of Food &amp; Beverage and Conference &amp; Banqueting operations and skills.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Strong leadership, people management and training skills.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Guest oriented and able to confidently build and exceed service standards.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Thorough knowledge of services, cost control in F&amp;B, labour controls, beverage menu writing, maintenance, merchandising, computer and accountings.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Strong interpersonal skills and attention to details.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Key strengths (under the 9 competencies) in people management communication and planning.&nbsp;&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Thorough knowledge of food and beverage operations including food, beverages, supervisory aspects, service techniques, and guest interaction.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Considerable skill in math and algebraic equations using percentages.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Able to communicate in English, both verbally and in writing, with guests and employees, some of whom will require high levels of patience, tact, and diplomacy to defuse anger and to collect.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Able to work under pressure and deal with stressful situations during busy periods.&nbsp;</p><p style=\"margin: 0px 0px 10px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\">• Able to walk, stand, and /or bend continuously to perform essential job functions.&nbsp;</p>', 'Purchasing Officer', '5', 'Hilton Garden Inn', 'Suva, Fiji', 'https://www.hilton.com/en/hotels/suvbugi-hilton-garden-inn-suva/', 1, 0, '2026-09-06 11:59:53', '2026-09-06 11:59:53'),
(5, 'MANAGER DISTRIBUTION (WESTERN)', 3, 3, 14, 1, '2026-10-11', 'Attractive Salary', 'Kinoya Office', '<p>The position will be based at the Energy Fiji Limited’s Navutu Depot and will report to the General Manager Network.</p><p>The position is responsible for providing leadership, direction, and development/implementation of operation and maintenance procedures and practices for the 11kV and low voltage distribution network as assigned. The role also liaises with relevant SBA Managers to develop practical solutions and support the implementation of project plans and business decisions in compliance with organizational and external standards</p>', NULL, '<ul style=\"margin-bottom: 10px; padding-left: 40px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\"><li>Carry out research and develop distribution network maintenance standards and practices.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\"><li>Prepare designs for modifications to the distribution system, including detailed analysis and structural/electrical calculations where appropriate.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\"><li>Prepare detailed analyses, plans, studies, and documentation to support distribution network maintenance and improve SAIDI and SAIFI indices.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\"><li>Prepare and maintain distribution network drawings and databases and use relevant software for performance analysis and future planning.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\"><li>Drive innovation and future-proofing in the department.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\"><li>Prepare, implement, monitor, and report on operational and capital budgets (OPEX and CAPEX).</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\"><li>Develop written documentation and reports, including material specifications, electrical reviews, maintenance policies, and procedures.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\"><li>Develop and implement a defect management classification system for asset defects.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\"><li>Provide technical leadership, training, and mentoring to engineers and technicians.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\"><li>Lead the distribution team to achieve Network Asset Management Plans, Corporate Plan, and Balanced Scorecard targets.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\"><li>Approve work scopes for maintenance and vegetation management programs and liaise with internal and external stakeholders.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\"><li>Prepare tender specifications, conduct tender evaluations, and prepare tender papers and business case reports.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\"><li>Demonstrate commitment to EFL values, safety, environmental programs, and quality assurance.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\"><li>Ensure timely preparation and submission of Department reports with appropriate quality checks.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\"><li>Maintain Occupational Health and Safety requirements in accordance with EFL and Fiji legislation.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\"><li>Lead by example in complying with EFL policies and procedures.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\"><li>Set and achieve budgetary and technical performance targets.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\"><li>Maintain the highest degree of integrity.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\"><li>Perform other work-related duties consistent with the responsibilities of the position.</li></ul>', '<ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li style=\"\"><font color=\"#000000\" face=\"Open Sans, sans-serif\"><span style=\"font-size: 15px;\">A Bachelor\'s Degree in Electrical Engineering from a recognized institution with at least 8 years\' relevant post-graduate experience in </span></font>distribution <font color=\"#000000\" face=\"Open Sans, sans-serif\"><span style=\"font-size: 15px;\">network operations, maintenance, construction, and asset management; OR an Advanced Diploma/Diploma in Electrical Engineering with at least 15 years\' relevant post-diploma experience in distribution network operations and maintenance;</span></font></li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\"><li>Applicants qualifying through the Diploma pathway must possess a Postgraduate Diploma in a related field and provide evidence of active progression towards a Master\'s qualification in a related field.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\"><li>Minimum of five (5) years\' experience in a supervisory engineering role.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\"><li>Eligibility for corporate membership in a recognized professional engineering body.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\"><li>Consistently excellent annual performance reports.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\"><li>EFL High Voltage Authorisation in Categories A, B, C, and F is mandatory.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\"><li>Clean and valid full group 2 manual driver\'s license (not provisional) is mandatory.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px; color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, sans-serif; font-size: 15px;\"><li>Ability to lead, motivate, and work effectively with diverse stakeholders both within and outside the organization.</li></ul>', 'network operations, Voltage Authorisation', '5', 'Energy Fiji Limited (EFL)', 'Suva Fiji', 'https://efl.com.fj/', 1, 1, '2026-09-27 10:56:24', '2026-09-27 10:57:38'),
(6, 'MANAGER DISTRIBUTION (CENTRAL)', 3, 3, 14, 1, '2026-10-11', 'Attractive Salary', 'Kinoya Office', '<p style=\"margin: 0px 0px 10px;\">The position will be based at the Energy Fiji Limited’s Kinoya Depot and will report to the General Manager Network.</p><p style=\"margin: 0px 0px 10px;\">The position is responsible for providing leadership, direction, and development/implementation of operation and maintenance procedures and practices for the 11kV and low voltage distribution network as assigned. The role also liaises with relevant SBA Managers to develop practical solutions and support the implementation of project plans and business decisions in compliance with organizational and external standards</p>', NULL, '<ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li>Carry out research and develop distribution network maintenance standards and practices.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li>Prepare designs for modifications to the distribution system, including detailed analysis and structural/electrical calculations where appropriate.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li>Prepare detailed analyses, plans, studies, and documentation to support distribution network maintenance and improve SAIDI and SAIFI indices.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li>Prepare and maintain distribution network drawings and databases and use relevant software for performance analysis and future planning.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li>Drive innovation and future-proofing in the department.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li>Prepare, implement, monitor, and report on operational and capital budgets (OPEX and CAPEX).</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li>Develop written documentation and reports, including material specifications, electrical reviews, maintenance policies, and procedures.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li>Develop and implement a defect management classification system for asset defects.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li>Provide technical leadership, training, and mentoring to engineers and technicians.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li>Lead the distribution team to achieve Network Asset Management Plans, Corporate Plan, and Balanced Scorecard targets.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li>Approve work scopes for maintenance and vegetation management programs and liaise with internal and external stakeholders.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li>Prepare tender specifications, conduct tender evaluations, and prepare tender papers and business case reports.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li>Demonstrate commitment to EFL values, safety, environmental programs, and quality assurance.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li>Ensure timely preparation and submission of Department reports with appropriate quality checks.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li>Maintain Occupational Health and Safety requirements in accordance with EFL and Fiji legislation.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li>Lead by example in complying with EFL policies and procedures.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li>Set and achieve budgetary and technical performance targets.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li>Maintain the highest degree of integrity.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li>Perform other work-related duties consistent with the responsibilities of the position.</li></ul>', '<ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li>A Bachelor\'s Degree in Electrical Engineering from a recognized institution with at least 8 years\' relevant post-graduate experience in distribution network operations, maintenance, construction, and asset management; OR an Advanced Diploma/Diploma in Electrical Engineering with at least 15 years\' relevant post-diploma experience in distribution network operations and maintenance;</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li>Applicants qualifying through the Diploma pathway must possess a Postgraduate Diploma in a related field and provide evidence of active progression towards a Master\'s qualification in a related field.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li>Minimum of five (5) years\' experience in a supervisory engineering role.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li>Eligibility for corporate membership in a recognised professional engineering body.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li>Consistently excellent annual performance reports.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li>EFL High Voltage Authorisation in Categories A, B, C, and F is mandatory.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li>Clean and valid full group 2 manual driver\'s license (not provisional) is mandatory.</li></ul><ul style=\"margin-bottom: 10px; padding-left: 40px;\"><li>Ability to lead, motivate, and work effectively with diverse stakeholders both within and outside the organization.</li></ul>', 'EFL values, SAIDI and SAIFI indices.', '5', 'Energy Fiji Limited (EFL)', 'Suva Fiji', 'https://efl.com.fj/', 1, 1, '2026-09-27 11:01:25', '2026-09-27 11:02:16'),
(7, 'Investment Specialist', 5, 3, 15, 1, '2026-09-29', 'Attractive Salary', 'Suva Fiji', '<p style=\"margin: 0px 0px 10px;\">In line with the commitment to safeguard capacity and support personnel already in the Organization, a majority of UNDP UNCDF/UNV vacancies are advertised using a tiered application process whereby:</p><p style=\"margin: 0px 0px 10px;\"></p><ul><li><strong>Tier 0: </strong>UNDP/UNCDF/UNV IP staff holding permanent (PA) and fixed-term (FTA) appointments, whose posts will be abolished, or contracts will be terminated or not renewed during 2026.</li><li><strong>Tier 1:</strong> Other UNDP/UNCDF/UNV staff holding permanent (PA) and fixed-term (FTA) appointments</li><li><strong>Tier 2:</strong> UNDP/UNCDF/UNV staff holding temporary appointments (TA), personnel on regular PSA contracts, and Expert and Specialist UN Volunteers</li><li><strong>Tier 3 </strong>or no tier indicated: All other contract types from UNDP/UNCDF/UNV and other agencies, and other external candidates</li></ul><p></p><p style=\"margin: 0px 0px 10px;\">Please make note of the Tier(s) indicated in the vacancy title, if any, and ensure that you satisfy the eligibility to apply.</p>', NULL, '<p style=\"margin: 0px 0px 10px;\">UNCDF is seeking an Investment Specialist to lead sourcing, structuring, and deploying concessional capital to MSMEs and financial institutions that demonstrate viable business models and measurable positive impact.</p><p style=\"margin: 0px 0px 10px;\">The incumbent will support the structuring, deployment, and monitoring of financial instruments across the Pacific region, including those implemented under the EU-funded Sustainable Pacific Blue Circle Fund (SPBCF) and the GFCR-funded Investing in Coral Reefs and Blue Economy initiative, as well as other UNCDF investment and blended finance projects.</p><p style=\"margin: 0px 0px 10px;\">Under the supervision of the Regional Investment Specialist for the Pacific and in close collaboration with other colleagues in the front and middle office, the Investment Specialist will contribute directly to the development, structuring, approval, and execution of investment transactions as described below.</p>', '<p></p><ul><li>Advanced university degree (Master\'s degree or equivalent) in Business Administration, Finance, Marketing, Economics, Accounting, or related fields is required, or&nbsp;</li><li>A first-level university degree (Bachelor´s degree) in the areas mentioned above in combination with additional 2 years of qualifying experience, will be given due consideration in lieu of Master´s degree.</li></ul><p></p>', 'Regional Investment Specialist', '7', 'UNDP Pacific in Fiji', 'Suva, Fji', 'https://www.undp.org/pacific', 1, 1, '2026-09-27 11:08:21', '2026-09-29 14:43:59'),
(8, 'People and Culture Officer', 17, 3, 15, 1, '2026-11-14', 'Attractive Salary', 'Suva', '<p>In the Pacific we work in Cook Islands, Fiji, Kiribati, Marshall Islands, Federated States of Micronesia, Nauru, Niue, Palau, Samoa, Solomon Islands, Tokelau, Tonga, Tuvalu, Vanuatu: These 14 Pacific island countries are home to 2.3 million people, including 1.2 million children and youth, living on more than 660 islands and atolls stretching across 17.2 million square kilometers of the Pacific Ocean, an area comparable to the combined size of the United States of America and Canada. Kiribati, Marshall Islands, Federated States of Micronesia, Solomon Islands, and Tuvalu are classified as Fragile States according to World Bank/OECD criteria.</p>', NULL, '<ul type=\"disc\" style=\"margin-bottom: 10px; padding-left: 40px;\"><li style=\"\">Language: Knowledge of another official UN language (Arabic, Chinese, French, Russian or Spanish) or a local language is an asset.</li><li style=\"\">Relevant experience in a UN system agency or organization is considered an asset. Additional years of experience in multiple areas of HR is highly desirable.<br>Ability to communicate effectively in a diverse organization tailoring language, tone, style and format to match audience is an asset.<br>Ability to empathize with client managers, supervisors and staff while advocating for consistent and equitable applications of promulgated HR regulations and rules is an asset.</li></ul><ul type=\"disc\" style=\"margin-bottom: 10px; padding-left: 40px;\"><li style=\"\">Relevant experience at country level, particularly in development, fragile settings and humanitarian contexts.</li></ul>', '<p>(1) Builds and maintains partnerships<br style=\"\">(2) Demonstrates self-awareness and ethical awareness<br style=\"\">(3) Drive to achieve results for impact<br style=\"\">(4) Innovates and embraces change<br style=\"\">(5) Manages ambiguity and complexity<br style=\"\">(6) Thinks and acts strategically<br style=\"\">(7) Works collaboratively with others</p>', 'people, Culture, pacific', '2', 'UNICEF', 'Suva Fiji', 'https://www.unicef.org/', 1, 1, '2026-09-27 12:53:43', '2026-09-27 12:56:19');

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

CREATE TABLE `job_applications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `job_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `employer_id` bigint(20) UNSIGNED NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT NULL,
  `application_file` varchar(255) DEFAULT NULL,
  `application_file_name` varchar(255) DEFAULT NULL,
  `resume_file` varchar(255) DEFAULT NULL,
  `resume_file_name` varchar(255) DEFAULT NULL,
  `certificates_file` text DEFAULT NULL,
  `certificates_file_names` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `application_status_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_applications`
--

INSERT INTO `job_applications` (`id`, `job_id`, `user_id`, `employer_id`, `applied_at`, `status`, `application_file`, `application_file_name`, `resume_file`, `resume_file_name`, `certificates_file`, `certificates_file_names`, `created_at`, `updated_at`, `application_status_id`) VALUES
(3, 4, 10, 9, '2026-09-06 12:38:04', 'pending', 'application_10_4_1788741484.pdf', 'Lab Exercise 1.pdf', 'resume_10_4_1788741485.pdf', 'Lab Exercise 3.pdf', '[\"certificate_10_4_1788741485_6a9e076d1ebe9.docx\",\"certificate_10_4_1788741485_6a9e076d20495.pdf\"]', '[\"Lab Exercise 6.docx\",\"Lab Exercise 6.pdf\"]', '2026-09-06 12:38:05', '2026-09-06 12:40:00', 3),
(4, 7, 16, 15, '2026-09-27 11:21:55', 'pending', 'application_16_7_1790551315.pdf', 'CIN623 Lab Activities 6-8.pdf', 'resume_16_7_1790551315.pdf', 'Lab Exercise 4.pdf', '[\"certificate_16_7_1790551315_6ab9a5136dbcd.png\",\"certificate_16_7_1790551315_6ab9a5136f2a2.pdf\",\"certificate_16_7_1790551315_6ab9a51372b2a.pdf\"]', '[\"Final ERD S2 14.png\",\"Lab Exercise 4.pdf\",\"Transaction mgt Tutorial.pdf\"]', '2026-09-27 11:21:55', '2026-09-27 11:21:55', 1),
(6, 7, 17, 15, '2026-09-27 12:34:49', 'pending', 'application_17_7_1790555689.pdf', 'CIN623 Lab Activities 6-8.pdf', 'resume_17_7_1790555689.pdf', 'CIN623 Lab Exercise - SQL.pdf', '[\"certificate_17_7_1790555689_6ab9b62967e52.docx\",\"certificate_17_7_1790555689_6ab9b629687cb.pdf\",\"certificate_17_7_1790555689_6ab9b6296ae87.doc\",\"certificate_17_7_1790555689_6ab9b6296cba4.pdf\",\"certificate_17_7_1790555689_6ab9b6296e9ee.png\",\"certificate_17_7_1790555689_6ab9b6296f678.pdf\"]', '[\"CIN623 Lab Activities 6-8.docx\",\"CIN623 Lab Activities 6-8.pdf\",\"CIN623 Lab Activity 5.doc\",\"CIN623 Lab Exercise - SQL.pdf\",\"Final ERD S2 14.png\",\"Lab Exercise 4.pdf\"]', '2026-09-27 12:34:49', '2026-09-27 12:59:56', 3);

-- --------------------------------------------------------

--
-- Table structure for table `job_types`
--

CREATE TABLE `job_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_types`
--

INSERT INTO `job_types` (`id`, `name`, `status`, `created_at`, `updated_at`) VALUES
(3, 'Full Time', 1, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(4, 'Part Time', 1, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(5, 'Contract', 1, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(6, 'Remote', 1, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(7, 'Freelance', 1, '2026-09-02 11:33:00', '2026-09-02 11:33:00'),
(8, 'Attachment', 1, '2026-09-02 11:33:00', '2026-09-02 11:33:00');

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
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '2026_05_03_103641_create_categories_table', 1),
(4, '2026_05_03_103717_create_job_types_table', 1),
(5, '2026_05_03_103737_create_jobs_table', 1),
(6, '2026_05_05_010509_alter_jobs_table', 1),
(7, '2026_05_06_001701_alter_jobs_table', 1),
(8, '2026_05_11_110617_crate_job_applications_table', 1),
(9, '2026_05_13_033921_create_saved_jobs_table', 1),
(10, '2026_05_17_083741_alter_users_table', 1),
(11, '2026_06_04_000000_add_files_to_job_applications_table', 1),
(12, '2026_06_08_000000_add_status_to_job_applications_table', 1),
(13, '2026_07_03_000000_update_users_role_enum', 1),
(14, '2026_07_31_000000_add_student_id_to_users_table', 1),
(15, '2026_08_06_000000_add_closing_date_to_jobs_table', 1),
(16, '2026_08_17_000000_add_original_file_names_to_job_applications_table', 1),
(17, '2026_08_18_000000_add_approval_status_to_jobs_table', 1),
(18, '2026_08_18_010000_combine_job_approval_into_status', 1),
(19, '2026_08_19_000000_add_status_to_users_table', 1),
(20, '2026_08_19_000001_add_student_profile_fields_to_users_table', 1),
(21, '2026_08_23_000000_update_user_profile_fields', 1),
(22, '2026_08_25_000000_add_company_name_to_users_table', 1),
(23, '2026_08_25_120000_add_company_address_to_users_table', 1),
(24, '2026_08_25_130000_add_employer_profile_fields_to_users_table', 1),
(25, '2026_09_02_000000_create_application_statuses_table', 1),
(26, '2026_09_02_000001_add_application_status_id_to_job_applications_table', 1),
(27, '2026_09_02_000002_create_application_status_history_table', 1),
(28, '2026_09_07_000001_backfill_application_status_history', 2),
(29, '2026_09_13_000000_add_super_admin_to_users_role_enum', 2);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `saved_jobs`
--

CREATE TABLE `saved_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `job_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `saved_jobs`
--

INSERT INTO `saved_jobs` (`id`, `job_id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 7, 16, '2026-09-27 11:22:25', '2026-09-27 11:22:25'),
(2, 4, 17, '2026-09-27 12:37:40', '2026-09-27 12:37:40'),
(3, 7, 10, '2026-09-29 14:42:05', '2026-09-29 14:42:05');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_2` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `company_address` text DEFAULT NULL,
  `website_url` varchar(255) DEFAULT NULL,
  `company_description` text DEFAULT NULL,
  `mobile` varchar(255) DEFAULT NULL,
  `mobile_2` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `residential_address` text DEFAULT NULL,
  `postal_address` text DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `high_school` varchar(255) DEFAULT NULL,
  `high_school_graduation_year` varchar(255) DEFAULT NULL,
  `university` varchar(255) DEFAULT NULL,
  `degree` varchar(255) DEFAULT NULL,
  `major` varchar(255) DEFAULT NULL,
  `graduation_year` varchar(255) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `availability` varchar(255) DEFAULT NULL,
  `role` enum('admin','super_admin','student','employer') NOT NULL DEFAULT 'student',
  `status` enum('pending','active','blocked') NOT NULL DEFAULT 'active',
  `student_id` varchar(9) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_2`, `email_verified_at`, `password`, `image`, `designation`, `company_name`, `company_address`, `website_url`, `company_description`, `mobile`, `mobile_2`, `date_of_birth`, `gender`, `address`, `residential_address`, `postal_address`, `city`, `country`, `high_school`, `high_school_graduation_year`, `university`, `degree`, `major`, `graduation_year`, `skills`, `bio`, `linkedin_url`, `facebook_url`, `availability`, `role`, `status`, `student_id`, `remember_token`, `created_at`, `updated_at`) VALUES
(9, 'Krishnil Chand', 'krishnilc@gmail.com', NULL, NULL, '$2y$12$cRcjGSBPNDqP73s.iZC6sOzXAHnn7RWKOn8ANlMZXn.iqmP6Gic66', '9_1788473112.jpg', 'Systems Developer', 'Fiji National University', 'Nasinu, Suva', NULL, NULL, '9589394', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'super_admin', 'active', NULL, NULL, '2026-09-02 11:31:34', '2026-09-03 10:05:13'),
(10, 'Arvind Krishna', 'arvind@gmail.com', NULL, NULL, '$2y$12$bAsXt1IvW7ue7xdOLa.0GeaOPGfi1c3Pgp//9QubZnvjAmd8qR1kC', NULL, 'Full-time Student', NULL, NULL, NULL, NULL, '9876543', NULL, '1990-09-03', 'Male', NULL, 'Bau Rd Nausori', 'P O Box A266, Nausori', 'Suva', 'Fiji', 'Bhawani Dayal Arya Samach', '2023', 'College of Engineering and Technical Vocational Education and Training (CETVET)', 'BSc', 'Computer Science & Information Systems', '2027', 'HTML, CSS, PHP', 'I am a friendly person willing to explore and help.', NULL, NULL, 'Part time in the weekends only', 'student', 'active', 'A00123654', NULL, '2026-09-02 11:40:01', '2026-09-02 11:46:10'),
(11, 'kamlesh Nand', 'kamlesh@fnu.ac.fj', NULL, NULL, '$2y$12$RTDl26YbuwLJCBvboVzd6.f2/7iV5yrdtjyvEhxEv/EWt5E/XTEoe', NULL, 'HR Manager', 'Fiji National University', 'Nasinu Fiji', 'https://careers.fnu.ac.fj/', 'Univerisity', '9876543', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'employer', 'active', NULL, NULL, '2026-09-02 11:49:04', '2026-09-02 11:51:34'),
(12, 'Praneel Sharma', 'praneel.sharma@fnu.ac.fj', NULL, NULL, '$2y$12$1sjZPy6HYSLZK9nSg9WED.AHX/5abPPWp63K6bJp1kcsxA2VUsA5G', NULL, 'Manager Student Support Services', 'Fiji National University', 'Nasinu Campus', NULL, NULL, '8065546', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'super_admin', 'active', NULL, NULL, '2026-09-27 10:38:33', '2026-09-27 10:39:11'),
(13, 'Laisa M. Narayan', 'laisa.narayan@fnu.ac.fj', NULL, NULL, '$2y$12$0aTQ4bTGCE5QP0vRK3NYZ.WIYxRr/aahv7mytoDuBFXGdterZA.w6', NULL, 'Placement Officer', 'Fiji National University', 'Nasinu Campus', NULL, NULL, '9876543', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'admin', 'active', NULL, NULL, '2026-09-27 10:42:50', '2026-09-27 10:43:18'),
(14, 'Jack Mini', 'jack@efl.com.fj', NULL, NULL, '$2y$12$W.Em9LBHojLhZtqziDbKtO2wbcnpVZrzZ9bd9iPGTNPEmgr4GNShq', NULL, 'HR Manager', 'Energy Fiji Limited (EFL)', 'Suva Fiji', NULL, 'Energy Fiji Limited (EFL) plays a critical role in advancing the economy and is responsible for generating, transmitting and retailing of electricity in Fiji and sets high priority on key values such as Customer Focus, Teamwork, Honesty, Transparency, the Courage to do what is right, Individual Accountability and Innovation.', '9876543', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://efl.com.fj/', NULL, 'employer', 'active', NULL, NULL, '2026-09-27 10:49:08', '2026-09-27 10:52:52'),
(15, 'Michael Ford', 'michael@undp.org', NULL, NULL, '$2y$12$FRpxtiBQrFZrj6E8U5QG7.IfHHU9ErqaGW97fC2/th6KhuE.8YPjG', NULL, 'HR Manager', 'UNDP Pacific in Fiji', 'Suva, Fji', NULL, NULL, '9589394', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'employer', 'active', NULL, NULL, '2026-09-27 11:04:30', '2026-09-27 11:04:53'),
(16, 'Mary Wati', 'mary@student.fnu.ac.fj', 'computervdmc@gmail.com', NULL, '$2y$12$pkCeXWmcV2eAvhxwm9EPWOHSQKayYAFzt1I3W3vu/4N2nW2yZhqzq', '16_1790551220.jpeg', 'Full-time Student', NULL, NULL, NULL, NULL, '9589394', NULL, '2011-07-08', 'Male', NULL, 'P O Box A266', 'RB Centrepoint', 'Suva', 'Fiji', 'Jai Narayan College', '2023', 'College of Business, Hospitality and Tourism Studies (CBHTS)', 'BA', 'Accounting and Economics', '2027', 'Budgeting, Financial control, team leader', 'I am an energetic person with some experience in accounts', NULL, NULL, 'Available for part time work only on saturdays', 'student', 'active', 'A00123456', NULL, '2026-09-27 11:11:23', '2026-09-27 11:20:20'),
(17, 'Bianca Sargent', 'dinun@gmail.com', 'krishnilc@gmail.com', NULL, '$2y$12$LFCd/Vp78AoZZGg0qOxD7.VlnBXI83LlJJNgRx.TsiEiM.6gN0Drm', '17_1790555928.jpeg', 'Full-time Student', NULL, NULL, NULL, NULL, '9876543', NULL, '2022-06-08', 'Other', NULL, 'RB Centerpoint', NULL, 'Suva', 'Fiji', 'Jai Narayan College', '2023', 'College of Business, Hospitality and Tourism Studies (CBHTS)', 'BA', 'Accounting and Economics', '20247', 'Good command of english...', 'I am a team player...', NULL, NULL, 'Available for part time work only on saturdays', 'student', 'active', 'A00123547', NULL, '2026-09-27 12:25:04', '2026-09-27 12:42:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `application_statuses`
--
ALTER TABLE `application_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_statuses_name_unique` (`name`),
  ADD UNIQUE KEY `application_statuses_sort_order_unique` (`sort_order`);

--
-- Indexes for table `application_status_history`
--
ALTER TABLE `application_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_status_history_job_application_id_foreign` (`job_application_id`),
  ADD KEY `application_status_history_application_status_id_foreign` (`application_status_id`),
  ADD KEY `application_status_history_changed_by_foreign` (`changed_by`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_category_id_foreign` (`category_id`),
  ADD KEY `jobs_job_type_id_foreign` (`job_type_id`),
  ADD KEY `jobs_user_id_foreign` (`user_id`);

--
-- Indexes for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_applications_job_id_foreign` (`job_id`),
  ADD KEY `job_applications_user_id_foreign` (`user_id`),
  ADD KEY `job_applications_employer_id_foreign` (`employer_id`),
  ADD KEY `job_applications_application_status_id_foreign` (`application_status_id`);

--
-- Indexes for table `job_types`
--
ALTER TABLE `job_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `saved_jobs`
--
ALTER TABLE `saved_jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `saved_jobs_job_id_foreign` (`job_id`),
  ADD KEY `saved_jobs_user_id_foreign` (`user_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `application_statuses`
--
ALTER TABLE `application_statuses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `application_status_history`
--
ALTER TABLE `application_status_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `job_types`
--
ALTER TABLE `job_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `saved_jobs`
--
ALTER TABLE `saved_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `application_status_history`
--
ALTER TABLE `application_status_history`
  ADD CONSTRAINT `application_status_history_application_status_id_foreign` FOREIGN KEY (`application_status_id`) REFERENCES `application_statuses` (`id`),
  ADD CONSTRAINT `application_status_history_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `application_status_history_job_application_id_foreign` FOREIGN KEY (`job_application_id`) REFERENCES `job_applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `jobs_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jobs_job_type_id_foreign` FOREIGN KEY (`job_type_id`) REFERENCES `job_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jobs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD CONSTRAINT `job_applications_application_status_id_foreign` FOREIGN KEY (`application_status_id`) REFERENCES `application_statuses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `job_applications_employer_id_foreign` FOREIGN KEY (`employer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_applications_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_applications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `saved_jobs`
--
ALTER TABLE `saved_jobs`
  ADD CONSTRAINT `saved_jobs_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `saved_jobs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
