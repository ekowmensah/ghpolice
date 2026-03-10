-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 19, 2025 at 07:17 AM
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
-- Database: `ghpims`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_add_suspect_to_case` (IN `p_case_id` INT, IN `p_person_id` INT, IN `p_added_by` INT)   BEGIN
    DECLARE v_suspect_id INT;
    DECLARE v_existing_suspect INT DEFAULT NULL;
    
    -- Check if person is already a suspect
    SELECT id INTO v_existing_suspect 
    FROM suspects 
    WHERE person_id = p_person_id
    LIMIT 1;
    
    -- If not a suspect yet, create suspect record
    IF v_existing_suspect IS NULL THEN
        INSERT INTO suspects (person_id, current_status) 
        VALUES (p_person_id, 'Suspect');
        SET v_suspect_id = LAST_INSERT_ID();
        
        -- Update person record
        UPDATE persons 
        SET has_criminal_record = TRUE,
            risk_level = 'Medium'
        WHERE id = p_person_id;
    ELSE
        SET v_suspect_id = v_existing_suspect;
    END IF;
    
    -- Link suspect to case
    INSERT INTO case_suspects (case_id, suspect_id)
    VALUES (p_case_id, v_suspect_id)
    ON DUPLICATE KEY UPDATE case_id = p_case_id;
    
    -- Add to criminal history
    INSERT INTO person_criminal_history (
        person_id, case_id, involvement_type, case_date
    ) 
    SELECT p_person_id, p_case_id, 'Suspect', CURDATE()
    FROM cases WHERE id = p_case_id;
    
    -- Log in audit
    INSERT INTO audit_logs (
        user_id, module, action_type, action_description,
        case_id, suspect_id
    ) VALUES (
        p_added_by, 'Case Management', 'CREATE',
        'Suspect added to case', p_case_id, v_suspect_id
    );
    
    -- Return suspect details with alerts
    SELECT 
        s.*,
        p.full_name,
        p.has_criminal_record,
        p.is_wanted,
        p.risk_level
    FROM suspects s
    JOIN persons p ON s.person_id = p.id
    WHERE s.id = v_suspect_id;
    
    -- Return any active alerts
    SELECT * FROM person_alerts 
    WHERE person_id = p_person_id AND is_active = TRUE;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_check_person_criminal_record` (IN `p_ghana_card` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, IN `p_contact` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, IN `p_passport` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, IN `p_drivers_license` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, IN `p_first_name` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, IN `p_last_name` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci)   BEGIN
    DECLARE v_person_id INT;
    
    -- Try to find person by ANY unique identifier
    SELECT id INTO v_person_id 
    FROM persons 
    WHERE (p_ghana_card IS NOT NULL AND ghana_card_number = p_ghana_card COLLATE utf8mb4_unicode_ci)
       OR (p_contact IS NOT NULL AND contact = p_contact COLLATE utf8mb4_unicode_ci)
       OR (p_passport IS NOT NULL AND passport_number = p_passport COLLATE utf8mb4_unicode_ci)
       OR (p_drivers_license IS NOT NULL AND drivers_license = p_drivers_license COLLATE utf8mb4_unicode_ci)
    LIMIT 1;
    
    -- If not found by unique identifiers, try by name
    IF v_person_id IS NULL AND (p_first_name IS NOT NULL OR p_last_name IS NOT NULL) THEN
        SELECT id INTO v_person_id 
        FROM persons 
        WHERE (p_first_name IS NULL OR first_name = p_first_name COLLATE utf8mb4_unicode_ci)
          AND (p_last_name IS NULL OR last_name = p_last_name COLLATE utf8mb4_unicode_ci)
        LIMIT 1;
    END IF;
    
    -- Return person details and criminal history
    IF v_person_id IS NOT NULL THEN
        -- Person found - return their details
        SELECT 
            p.*,
            'PERSON FOUND IN SYSTEM' as alert_status,
            CASE 
                WHEN p.has_criminal_record = TRUE THEN 'WARNING: HAS CRIMINAL RECORD'
                WHEN p.is_wanted = TRUE THEN 'ALERT: PERSON IS WANTED'
                ELSE 'No criminal record'
            END as criminal_status
        FROM persons p
        WHERE p.id = v_person_id;
        
        -- Return active alerts
        SELECT * FROM person_alerts 
        WHERE person_id = v_person_id AND is_active = TRUE
        ORDER BY alert_priority DESC, issued_date DESC;
        
        -- Return criminal history
        SELECT 
            pch.*,
            c.case_number,
            c.description as case_description,
            c.status as case_status
        FROM person_criminal_history pch
        JOIN cases c ON pch.case_id = c.id
        WHERE pch.person_id = v_person_id
        ORDER BY pch.case_date DESC;
        
        -- Return if person is currently a suspect in any case
        SELECT 
            s.id as suspect_id,
            s.current_status,
            cs.case_id,
            c.case_number,
            c.description,
            c.status as case_status,
            c.case_priority
        FROM suspects s
        JOIN case_suspects cs ON s.id = cs.suspect_id
        JOIN cases c ON cs.case_id = c.id
        WHERE s.person_id = v_person_id
        ORDER BY c.created_at DESC;
        
    ELSE
        -- Person not found
        SELECT 'PERSON NOT FOUND IN SYSTEM' as alert_status, NULL as person_id;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_convert_officer_to_user` (IN `p_officer_id` INT, IN `p_role_id` INT, IN `p_username` VARCHAR(50), IN `p_password_hash` VARCHAR(255), IN `p_email` VARCHAR(100), IN `p_created_by` INT)   BEGIN
    DECLARE v_user_id INT;
    DECLARE v_officer_exists INT;
    DECLARE v_user_exists INT;
    
    -- Check if officer exists
    SELECT COUNT(*) INTO v_officer_exists FROM officers WHERE id = p_officer_id;
    
    IF v_officer_exists = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Officer does not exist';
    END IF;
    
    -- Check if officer already has user account
    SELECT user_id INTO v_user_exists FROM officers WHERE id = p_officer_id;
    
    IF v_user_exists IS NOT NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Officer already has a user account';
    END IF;
    
    -- Create user account
    INSERT INTO users (
        service_number, first_name, middle_name, last_name, rank, role_id, username, 
        password_hash, email, station_id, district_id, 
        division_id, region_id, status
    )
    SELECT 
        o.service_number, o.first_name, o.middle_name, o.last_name, pr.rank_name, p_role_id, p_username,
        p_password_hash, COALESCE(p_email, o.email), o.current_station_id, 
        o.current_district_id, o.current_division_id, o.current_region_id, 
        CASE WHEN o.employment_status = 'Active' THEN 'Active' ELSE 'Inactive' END
    FROM officers o
    JOIN police_ranks pr ON o.rank_id = pr.id
    WHERE o.id = p_officer_id;
    
    SET v_user_id = LAST_INSERT_ID();
    
    -- Link user to officer
    UPDATE officers SET user_id = v_user_id WHERE id = p_officer_id;
    
    -- Log the action
    INSERT INTO audit_logs (
        user_id, module, action_type, action_description, 
        action_details, officer_id
    ) VALUES (
        p_created_by, 'User Management', 'CREATE', 
        'Officer converted to user with system access',
        CONCAT('Officer ID: ', p_officer_id, ', Username: ', p_username),
        p_officer_id
    );
    
    SELECT v_user_id as user_id, 'Officer successfully converted to user' as message;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_find_similar_persons` (IN `p_first_name` VARCHAR(50), IN `p_last_name` VARCHAR(50), IN `p_date_of_birth` DATE, IN `p_contact` VARCHAR(50))   BEGIN
    -- Find persons with similar details
    SELECT 
        p.*,
        CASE 
            WHEN p.first_name = p_first_name AND p.last_name = p_last_name THEN 3
            WHEN p.first_name = p_first_name OR p.last_name = p_last_name THEN 2
            ELSE 1
        END as name_match_score,
        CASE 
            WHEN p.date_of_birth = p_date_of_birth THEN 2
            ELSE 0
        END as dob_match_score,
        CASE 
            WHEN p.contact = p_contact THEN 2
            ELSE 0
        END as contact_match_score,
        (CASE 
            WHEN p.first_name = p_first_name AND p.last_name = p_last_name THEN 3
            WHEN p.first_name = p_first_name OR p.last_name = p_last_name THEN 2
            ELSE 1 
         END +
         CASE WHEN p.date_of_birth = p_date_of_birth THEN 2 ELSE 0 END +
         CASE WHEN p.contact = p_contact THEN 2 ELSE 0 END) as total_match_score
    FROM persons p
    WHERE (p_first_name IS NOT NULL AND p.first_name = p_first_name)
       OR (p_last_name IS NOT NULL AND p.last_name = p_last_name)
       OR (p_date_of_birth IS NOT NULL AND p.date_of_birth = p_date_of_birth)
       OR (p_contact IS NOT NULL AND p.contact = p_contact)
    HAVING total_match_score >= 3
    ORDER BY total_match_score DESC, p.has_criminal_record DESC
    LIMIT 10;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_register_person` (IN `p_first_name` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, IN `p_middle_name` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, IN `p_last_name` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, IN `p_gender` VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, IN `p_date_of_birth` DATE, IN `p_contact` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, IN `p_email` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, IN `p_address` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, IN `p_ghana_card` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, IN `p_passport` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, IN `p_drivers_license` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, OUT `p_person_id` INT, OUT `p_is_duplicate` BOOLEAN, OUT `p_duplicate_message` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci)   BEGIN
    DECLARE v_existing_id INT DEFAULT NULL;
    
    -- Check for existing person by ANY unique identifier
    SELECT id INTO v_existing_id 
    FROM persons 
    WHERE (p_ghana_card IS NOT NULL AND ghana_card_number = p_ghana_card COLLATE utf8mb4_unicode_ci)
       OR (p_contact IS NOT NULL AND contact = p_contact COLLATE utf8mb4_unicode_ci)
       OR (p_passport IS NOT NULL AND passport_number = p_passport COLLATE utf8mb4_unicode_ci)
       OR (p_drivers_license IS NOT NULL AND drivers_license = p_drivers_license COLLATE utf8mb4_unicode_ci)
    LIMIT 1;
    
    -- If found, return existing person
    IF v_existing_id IS NOT NULL THEN
        SET p_person_id = v_existing_id;
        SET p_is_duplicate = TRUE;
        SET p_duplicate_message = CONCAT('Person already exists in system with ID: ', v_existing_id);
    ELSE
        -- Create new person
        INSERT INTO persons (
            first_name, middle_name, last_name, gender, date_of_birth, 
            contact, email, address, ghana_card_number, passport_number, drivers_license
        ) VALUES (
            p_first_name, p_middle_name, p_last_name, p_gender, p_date_of_birth,
            p_contact, p_email, p_address, p_ghana_card, p_passport, p_drivers_license
        );
        
        SET p_person_id = LAST_INSERT_ID();
        SET p_is_duplicate = FALSE;
        SET p_duplicate_message = 'New person registered successfully';
    END IF;
    
    -- Return person details
    SELECT * FROM persons WHERE id = p_person_id;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `ammunition_stock`
--

CREATE TABLE `ammunition_stock` (
  `id` int(11) NOT NULL,
  `station_id` int(11) NOT NULL,
  `ammunition_type` varchar(100) NOT NULL,
  `caliber` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `minimum_threshold` int(11) DEFAULT 100,
  `last_restocked_date` date DEFAULT NULL,
  `last_restocked_quantity` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `arrests`
--

CREATE TABLE `arrests` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `suspect_id` int(11) NOT NULL,
  `arresting_officer_id` int(11) NOT NULL,
  `arrest_date` datetime NOT NULL,
  `arrest_location` varchar(255) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `warrant_number` varchar(100) DEFAULT NULL,
  `arrest_type` enum('With Warrant','Without Warrant') DEFAULT 'Without Warrant',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` int(11) NOT NULL,
  `asset_name` varchar(100) NOT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `asset_type` varchar(50) NOT NULL,
  `condition_status` varchar(50) DEFAULT NULL,
  `current_location` varchar(100) DEFAULT NULL,
  `case_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `asset_movements`
--

CREATE TABLE `asset_movements` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `moved_from` varchar(100) DEFAULT NULL,
  `moved_to` varchar(100) NOT NULL,
  `moved_by` int(11) NOT NULL,
  `movement_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `purpose` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `action_type` enum('CREATE','UPDATE','DELETE','LOGIN','LOGOUT','VIEW','EXPORT','APPROVE','REJECT','ASSIGN','TRANSFER','PROMOTE','SUSPEND') NOT NULL,
  `action_description` varchar(255) NOT NULL,
  `action_details` text DEFAULT NULL,
  `case_id` int(11) DEFAULT NULL,
  `officer_id` int(11) DEFAULT NULL,
  `suspect_id` int(11) DEFAULT NULL,
  `evidence_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `old_values` longtext DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext DEFAULT NULL CHECK (json_valid(`new_values`)),
  `action_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bail_records`
--

CREATE TABLE `bail_records` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `suspect_id` int(11) NOT NULL,
  `bail_status` enum('Granted','Denied','Revoked') NOT NULL,
  `bail_amount` decimal(15,2) DEFAULT NULL,
  `bail_conditions` text DEFAULT NULL,
  `bail_date` date NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cases`
--

CREATE TABLE `cases` (
  `id` int(11) NOT NULL,
  `case_number` varchar(50) NOT NULL,
  `case_type` enum('Complaint','Police Initiated') NOT NULL,
  `case_priority` enum('Low','Medium','High','Critical') DEFAULT 'Medium',
  `description` text DEFAULT NULL,
  `incident_location` text DEFAULT NULL,
  `incident_date` datetime DEFAULT NULL,
  `complainant_id` int(11) DEFAULT NULL,
  `station_id` int(11) NOT NULL,
  `district_id` int(11) NOT NULL,
  `division_id` int(11) NOT NULL,
  `region_id` int(11) NOT NULL,
  `status` enum('Open','Under Investigation','Referred','Closed','Archived') DEFAULT 'Open',
  `investigation_deadline` date DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `closed_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cases`
--

INSERT INTO `cases` (`id`, `case_number`, `case_type`, `case_priority`, `description`, `incident_location`, `incident_date`, `complainant_id`, `station_id`, `district_id`, `division_id`, `region_id`, `status`, `investigation_deadline`, `created_by`, `created_at`, `updated_at`, `closed_date`) VALUES
(4, 'TEST-2024-0001', 'Complaint', 'Medium', 'Test', NULL, NULL, NULL, 1, 1, 1, 1, 'Open', NULL, 1, '2025-12-18 07:10:46', '2025-12-18 07:10:46', NULL),
(6, 'BASK001-2025-0002', 'Complaint', 'High', 'Robbed at gun point behind methodist school by unknown people.', '', '2025-03-12 21:15:00', 8, 1, 1, 1, 1, 'Under Investigation', NULL, 1, '2025-12-18 07:14:13', '2025-12-18 13:15:59', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `case_assignments`
--

CREATE TABLE `case_assignments` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `assigned_to` int(11) NOT NULL,
  `assigned_by` int(11) NOT NULL,
  `assignment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Active','Completed','Reassigned') DEFAULT 'Active',
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `case_assignments`
--

INSERT INTO `case_assignments` (`id`, `case_id`, `assigned_to`, `assigned_by`, `assignment_date`, `status`, `remarks`) VALUES
(1, 6, 1, 1, '2025-12-18 07:14:13', 'Active', 'Investigating Officer');

-- --------------------------------------------------------

--
-- Table structure for table `case_crimes`
--

CREATE TABLE `case_crimes` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `crime_category_id` int(11) NOT NULL,
  `crime_description` text DEFAULT NULL,
  `crime_date` datetime DEFAULT NULL,
  `crime_location` varchar(255) DEFAULT NULL,
  `added_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `case_documents`
--

CREATE TABLE `case_documents` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `document_type` enum('Report','Warrant','Court Order','Medical Report','Forensic Report','Affidavit','Other') NOT NULL,
  `document_title` varchar(200) NOT NULL,
  `document_number` varchar(100) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `case_investigation_checklist`
--

CREATE TABLE `case_investigation_checklist` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `checklist_item` varchar(200) NOT NULL,
  `item_description` varchar(200) DEFAULT NULL,
  `item_category` enum('Initial Response','Evidence','Witnesses','Suspects','Documentation','Court Preparation','Case Closure') NOT NULL,
  `item_order` int(11) DEFAULT 0,
  `is_completed` tinyint(1) DEFAULT 0,
  `completed_by` int(11) DEFAULT NULL,
  `completed_date` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `case_investigation_checklist`
--

INSERT INTO `case_investigation_checklist` (`id`, `case_id`, `checklist_item`, `item_description`, `item_category`, `item_order`, `is_completed`, `completed_by`, `completed_date`, `completed_at`, `notes`, `created_at`) VALUES
(1, 4, 'Initial complaint recorded', 'Initial complaint recorded', 'Initial Response', 1, 0, NULL, NULL, NULL, NULL, '2025-12-18 12:34:13'),
(2, 6, 'Initial complaint recorded', 'Initial complaint recorded', 'Initial Response', 1, 0, NULL, NULL, NULL, NULL, '2025-12-18 12:34:13'),
(4, 4, 'Scene visited and documented', 'Scene visited and documented', 'Initial Response', 2, 0, NULL, NULL, NULL, NULL, '2025-12-18 12:34:13'),
(5, 6, 'Scene visited and documented', 'Scene visited and documented', 'Initial Response', 2, 0, NULL, NULL, NULL, NULL, '2025-12-18 12:34:13'),
(6, 4, 'Witnesses identified', 'Witnesses identified', 'Witnesses', 3, 0, NULL, NULL, NULL, NULL, '2025-12-18 12:34:13'),
(7, 6, 'Witnesses identified', 'Witnesses identified', 'Witnesses', 3, 0, NULL, NULL, NULL, NULL, '2025-12-18 12:34:13'),
(8, 4, 'Statements recorded', 'Statements recorded', 'Witnesses', 4, 0, NULL, NULL, NULL, NULL, '2025-12-18 12:34:13'),
(9, 6, 'Statements recorded', 'Statements recorded', 'Witnesses', 4, 0, NULL, NULL, NULL, NULL, '2025-12-18 12:34:13'),
(10, 4, 'Evidence collected', 'Evidence collected', 'Evidence', 5, 0, NULL, NULL, NULL, NULL, '2025-12-18 12:34:13'),
(11, 6, 'Evidence collected', 'Evidence collected', 'Evidence', 5, 0, NULL, NULL, NULL, NULL, '2025-12-18 12:34:13'),
(12, 4, 'Suspects identified', 'Suspects identified', 'Suspects', 6, 0, NULL, NULL, NULL, NULL, '2025-12-18 12:34:13'),
(13, 6, 'Suspects identified', 'Suspects identified', 'Suspects', 6, 0, NULL, NULL, NULL, NULL, '2025-12-18 12:34:13'),
(14, 4, 'Forensic analysis requested', 'Forensic analysis requested', 'Evidence', 7, 0, NULL, NULL, NULL, NULL, '2025-12-18 12:34:13'),
(15, 6, 'Forensic analysis requested', 'Forensic analysis requested', 'Evidence', 7, 0, NULL, NULL, NULL, NULL, '2025-12-18 12:34:13'),
(16, 4, 'Investigation report prepared', 'Investigation report prepared', 'Documentation', 8, 0, NULL, NULL, NULL, NULL, '2025-12-18 12:34:13'),
(17, 6, 'Investigation report prepared', 'Investigation report prepared', 'Documentation', 8, 0, NULL, NULL, NULL, NULL, '2025-12-18 12:34:13'),
(18, 4, 'Case file reviewed', 'Case file reviewed', 'Documentation', 9, 0, NULL, NULL, NULL, NULL, '2025-12-18 12:34:13'),
(19, 6, 'Case file reviewed', 'Case file reviewed', 'Documentation', 9, 0, NULL, NULL, NULL, NULL, '2025-12-18 12:34:13'),
(20, 4, 'Prosecution recommendation made', 'Prosecution recommendation made', 'Case Closure', 10, 0, NULL, NULL, NULL, NULL, '2025-12-18 12:34:13'),
(21, 6, 'Prosecution recommendation made', 'Prosecution recommendation made', 'Case Closure', 10, 0, NULL, NULL, NULL, NULL, '2025-12-18 12:34:13');

-- --------------------------------------------------------

--
-- Table structure for table `case_investigation_tasks`
--

CREATE TABLE `case_investigation_tasks` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `task_title` varchar(200) NOT NULL,
  `task_description` text DEFAULT NULL,
  `task_type` enum('Interview','Evidence Collection','Document Review','Follow-up','Court Preparation','Other') NOT NULL,
  `assigned_to` int(11) NOT NULL,
  `assigned_by` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `priority` enum('Low','Medium','High','Urgent') DEFAULT 'Medium',
  `due_date` date DEFAULT NULL,
  `status` enum('Pending','In Progress','Completed','Cancelled') DEFAULT 'Pending',
  `completion_date` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `completion_notes` text DEFAULT NULL,
  `completed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `case_investigation_timeline`
--

CREATE TABLE `case_investigation_timeline` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `milestone_id` int(11) DEFAULT NULL,
  `event_type` varchar(100) DEFAULT NULL,
  `activity_type` enum('Investigation','Evidence','Interview','Arrest','Court','Administrative','Other') NOT NULL,
  `activity_title` varchar(200) NOT NULL,
  `activity_description` text DEFAULT NULL,
  `event_description` text DEFAULT NULL,
  `activity_date` datetime NOT NULL,
  `event_date` datetime DEFAULT NULL,
  `completed_by` int(11) NOT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `outcome` text DEFAULT NULL,
  `next_steps` text DEFAULT NULL,
  `attachments` varchar(255) DEFAULT NULL,
  `is_milestone` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `case_milestones`
--

CREATE TABLE `case_milestones` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `milestone_title` varchar(200) NOT NULL,
  `milestone_description` text DEFAULT NULL,
  `target_date` date DEFAULT NULL,
  `achieved_date` datetime DEFAULT NULL,
  `is_achieved` tinyint(1) DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `case_milestones`
--

INSERT INTO `case_milestones` (`id`, `case_id`, `milestone_title`, `milestone_description`, `target_date`, `achieved_date`, `is_achieved`, `created_by`, `created_at`) VALUES
(1, 6, 'ARREST SUSPECT EKOW MENSAH', '', '2025-12-18', NULL, 0, 1, '2025-12-18 13:11:46');

-- --------------------------------------------------------

--
-- Table structure for table `case_referrals`
--

CREATE TABLE `case_referrals` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `from_level` varchar(50) NOT NULL,
  `to_level` varchar(50) NOT NULL,
  `from_station_id` int(11) DEFAULT NULL,
  `to_station_id` int(11) DEFAULT NULL,
  `referred_by` int(11) NOT NULL,
  `referral_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `remarks` text DEFAULT NULL,
  `status` enum('Pending','Accepted','Rejected') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `case_status_history`
--

CREATE TABLE `case_status_history` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `changed_by` int(11) NOT NULL,
  `change_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `case_status_history`
--

INSERT INTO `case_status_history` (`id`, `case_id`, `old_status`, `new_status`, `changed_by`, `change_date`, `remarks`) VALUES
(2, 6, NULL, 'Open', 1, '2025-12-18 07:14:13', 'Case registered');

-- --------------------------------------------------------

--
-- Table structure for table `case_suspects`
--

CREATE TABLE `case_suspects` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `suspect_id` int(11) NOT NULL,
  `added_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `case_suspects`
--

INSERT INTO `case_suspects` (`id`, `case_id`, `suspect_id`, `added_date`) VALUES
(4, 6, 7, '2025-12-18 08:12:48'),
(5, 6, 8, '2025-12-18 10:23:48'),
(6, 6, 9, '2025-12-18 11:20:01'),
(7, 6, 10, '2025-12-18 12:18:51');

-- --------------------------------------------------------

--
-- Table structure for table `case_updates`
--

CREATE TABLE `case_updates` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `update_note` text NOT NULL,
  `updated_by` int(11) NOT NULL,
  `update_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `case_witnesses`
--

CREATE TABLE `case_witnesses` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `witness_id` int(11) NOT NULL,
  `added_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `case_witnesses`
--

INSERT INTO `case_witnesses` (`id`, `case_id`, `witness_id`, `added_date`) VALUES
(1, 6, 1, '2025-12-18 09:15:57');

-- --------------------------------------------------------

--
-- Table structure for table `charges`
--

CREATE TABLE `charges` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `suspect_id` int(11) NOT NULL,
  `offence_name` varchar(255) NOT NULL,
  `law_section` varchar(100) DEFAULT NULL,
  `charge_date` date NOT NULL,
  `charged_by` int(11) NOT NULL,
  `charge_status` enum('Pending','Filed','Withdrawn','Dismissed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complainants`
--

CREATE TABLE `complainants` (
  `id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  `complainant_type` enum('Individual','Organization','Anonymous') DEFAULT 'Individual',
  `organization_name` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complainants`
--

INSERT INTO `complainants` (`id`, `person_id`, `complainant_type`, `organization_name`, `created_at`, `updated_at`) VALUES
(8, 1, 'Individual', NULL, '2025-12-18 07:14:13', '2025-12-18 07:14:13');

-- --------------------------------------------------------

--
-- Table structure for table `court_proceedings`
--

CREATE TABLE `court_proceedings` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `suspect_id` int(11) NOT NULL,
  `court_name` varchar(100) NOT NULL,
  `court_date` date NOT NULL,
  `hearing_type` enum('Arraignment','Hearing','Verdict','Sentencing','Appeal') NOT NULL,
  `outcome` text DEFAULT NULL,
  `next_hearing_date` date DEFAULT NULL,
  `judge_name` varchar(100) DEFAULT NULL,
  `prosecutor_name` varchar(100) DEFAULT NULL,
  `recorded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `crime_categories`
--

CREATE TABLE `crime_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `parent_category_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `severity_level` enum('Minor','Moderate','Serious','Very Serious') DEFAULT 'Moderate',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custody_records`
--

CREATE TABLE `custody_records` (
  `id` int(11) NOT NULL,
  `suspect_id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `custody_start` datetime NOT NULL,
  `custody_end` datetime DEFAULT NULL,
  `custody_location` varchar(100) DEFAULT NULL,
  `custody_status` enum('In Custody','Released','Transferred','Escaped') DEFAULT 'In Custody',
  `reason` text DEFAULT NULL,
  `released_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `districts`
--

CREATE TABLE `districts` (
  `id` int(11) NOT NULL,
  `district_name` varchar(100) NOT NULL,
  `district_code` varchar(20) DEFAULT NULL,
  `division_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `districts`
--

INSERT INTO `districts` (`id`, `district_name`, `district_code`, `division_id`, `created_at`) VALUES
(1, 'Asikuma Odoben Brakwa', 'AOB001', 1, '2025-12-18 06:34:13');

-- --------------------------------------------------------

--
-- Table structure for table `divisions`
--

CREATE TABLE `divisions` (
  `id` int(11) NOT NULL,
  `division_name` varchar(100) NOT NULL,
  `division_code` varchar(20) DEFAULT NULL,
  `region_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `divisions`
--

INSERT INTO `divisions` (`id`, `division_name`, `division_code`, `region_id`, `created_at`) VALUES
(1, 'Mankessim', 'MANK001', 1, '2025-12-18 06:20:57');

-- --------------------------------------------------------

--
-- Table structure for table `duty_roster`
--

CREATE TABLE `duty_roster` (
  `id` int(11) NOT NULL,
  `officer_id` int(11) NOT NULL,
  `station_id` int(11) NOT NULL,
  `shift_id` int(11) NOT NULL,
  `duty_date` date NOT NULL,
  `duty_type` enum('Regular','Overtime','Special Assignment','Court Duty','Training') DEFAULT 'Regular',
  `duty_location` varchar(255) DEFAULT NULL,
  `supervisor_id` int(11) DEFAULT NULL,
  `status` enum('Scheduled','On Duty','Completed','Absent','Sick') DEFAULT 'Scheduled',
  `check_in_time` datetime DEFAULT NULL,
  `check_out_time` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `duty_shifts`
--

CREATE TABLE `duty_shifts` (
  `id` int(11) NOT NULL,
  `shift_name` varchar(50) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `duty_shifts`
--

INSERT INTO `duty_shifts` (`id`, `shift_name`, `start_time`, `end_time`, `description`, `created_at`) VALUES
(1, 'Morning Shift', '06:00:00', '14:00:00', 'Regular morning duty shift', '2025-12-17 14:31:01'),
(2, 'Afternoon Shift', '14:00:00', '22:00:00', 'Regular afternoon duty shift', '2025-12-17 14:31:01'),
(3, 'Night Shift', '22:00:00', '06:00:00', 'Regular night duty shift', '2025-12-17 14:31:01'),
(4, 'Day Shift', '08:00:00', '17:00:00', 'Standard day shift for administrative staff', '2025-12-17 14:31:01');

-- --------------------------------------------------------

--
-- Table structure for table `evidence`
--

CREATE TABLE `evidence` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `evidence_type` varchar(50) NOT NULL,
  `evidence_number` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `verification_hash` varchar(255) DEFAULT NULL,
  `collection_date` date DEFAULT NULL,
  `collection_location` varchar(255) DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `evidence`
--

INSERT INTO `evidence` (`id`, `case_id`, `evidence_type`, `evidence_number`, `description`, `file_path`, `file_size`, `mime_type`, `verification_hash`, `collection_date`, `collection_location`, `uploaded_by`, `uploaded_at`) VALUES
(2, 6, 'Digital', 'EV-6-1766051001', 'djkl ;alkjf lkjaf kljdf aj;falkfj alk f;ks fla flkas f;lasfn lak flsa flanf askfn a flaknf lasfkaf ;lsf klsa la; fla fla f;a fkaf l;ak f;a lkn a;fa kla;f as fla fd flas fnafna ;fkalf;a flkal ;a d flakf alf la;f la; fla lfafa;f afafla ;f a dlfa;f la;kf', 'evidence/evidence_6943ccb9b0610_1766051001.png', 8275, 'image/png', NULL, '2025-12-18', 'Office', 1, '2025-12-18 09:43:21');

-- --------------------------------------------------------

--
-- Table structure for table `evidence_custody_chain`
--

CREATE TABLE `evidence_custody_chain` (
  `id` int(11) NOT NULL,
  `evidence_id` int(11) NOT NULL,
  `transferred_from` int(11) DEFAULT NULL,
  `transferred_to` int(11) NOT NULL,
  `transfer_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `purpose` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exhibits`
--

CREATE TABLE `exhibits` (
  `id` int(11) NOT NULL,
  `exhibit_number` varchar(50) NOT NULL,
  `case_id` int(11) NOT NULL,
  `exhibit_type` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `seized_from` varchar(100) DEFAULT NULL,
  `seized_location` varchar(255) DEFAULT NULL,
  `seized_date` datetime NOT NULL,
  `seized_by` int(11) NOT NULL,
  `current_location` varchar(255) NOT NULL,
  `storage_condition` varchar(100) DEFAULT NULL,
  `exhibit_status` enum('In Custody','In Court','Released','Destroyed','Missing') DEFAULT 'In Custody',
  `photo_path` varchar(255) DEFAULT NULL,
  `disposal_date` date DEFAULT NULL,
  `disposal_method` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exhibit_movements`
--

CREATE TABLE `exhibit_movements` (
  `id` int(11) NOT NULL,
  `exhibit_id` int(11) NOT NULL,
  `moved_from` varchar(255) NOT NULL,
  `moved_to` varchar(255) NOT NULL,
  `moved_by` int(11) NOT NULL,
  `received_by` int(11) DEFAULT NULL,
  `movement_date` datetime DEFAULT current_timestamp(),
  `purpose` varchar(255) DEFAULT NULL,
  `condition_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `firearms`
--

CREATE TABLE `firearms` (
  `id` int(11) NOT NULL,
  `serial_number` varchar(100) NOT NULL,
  `firearm_type` enum('Pistol','Rifle','Shotgun','Submachine Gun','Other') NOT NULL,
  `make` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `caliber` varchar(50) DEFAULT NULL,
  `acquisition_date` date DEFAULT NULL,
  `acquisition_source` varchar(100) DEFAULT NULL,
  `firearm_status` enum('In Service','In Armory','Under Repair','Decommissioned','Lost','Stolen') DEFAULT 'In Armory',
  `current_holder_id` int(11) DEFAULT NULL,
  `station_id` int(11) NOT NULL,
  `last_maintenance_date` date DEFAULT NULL,
  `next_maintenance_due` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `firearm_assignments`
--

CREATE TABLE `firearm_assignments` (
  `id` int(11) NOT NULL,
  `firearm_id` int(11) NOT NULL,
  `officer_id` int(11) NOT NULL,
  `issued_by` int(11) NOT NULL,
  `issue_date` datetime NOT NULL,
  `return_date` datetime DEFAULT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `ammunition_issued` int(11) DEFAULT 0,
  `ammunition_returned` int(11) DEFAULT 0,
  `condition_on_issue` varchar(100) DEFAULT NULL,
  `condition_on_return` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `incident_reports`
--

CREATE TABLE `incident_reports` (
  `id` int(11) NOT NULL,
  `incident_number` varchar(50) NOT NULL,
  `incident_type` enum('Traffic Accident','Fire','Medical Emergency','Public Disturbance','Lost Property','Found Property','Noise Complaint','Other') NOT NULL,
  `incident_date` datetime NOT NULL,
  `incident_location` varchar(255) NOT NULL,
  `reported_by_name` varchar(100) DEFAULT NULL,
  `reported_by_contact` varchar(50) DEFAULT NULL,
  `description` text NOT NULL,
  `station_id` int(11) NOT NULL,
  `attending_officer_id` int(11) NOT NULL,
  `status` enum('Open','Under Review','Resolved','Closed') DEFAULT 'Open',
  `resolution` text DEFAULT NULL,
  `escalated_to_case` tinyint(1) DEFAULT 0,
  `case_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `informants`
--

CREATE TABLE `informants` (
  `id` int(11) NOT NULL,
  `informant_code` varchar(50) NOT NULL,
  `alias` varchar(100) DEFAULT NULL,
  `gender` enum('Male','Female','Undisclosed') DEFAULT NULL,
  `contact_method` varchar(100) DEFAULT NULL,
  `reliability_rating` enum('Unproven','Somewhat Reliable','Reliable','Very Reliable','Highly Reliable') DEFAULT 'Unproven',
  `handler_officer_id` int(11) NOT NULL,
  `station_id` int(11) NOT NULL,
  `registration_date` date NOT NULL,
  `last_contact_date` date DEFAULT NULL,
  `status` enum('Active','Inactive','Compromised','Relocated') DEFAULT 'Active',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `informant_intelligence`
--

CREATE TABLE `informant_intelligence` (
  `id` int(11) NOT NULL,
  `informant_id` int(11) NOT NULL,
  `intelligence_date` datetime NOT NULL,
  `intelligence_type` varchar(100) DEFAULT NULL,
  `intelligence_details` text NOT NULL,
  `verification_status` enum('Unverified','Partially Verified','Verified','False') DEFAULT 'Unverified',
  `case_id` int(11) DEFAULT NULL,
  `handler_officer_id` int(11) NOT NULL,
  `action_taken` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `intelligence_bulletins`
--

CREATE TABLE `intelligence_bulletins` (
  `id` int(11) NOT NULL,
  `bulletin_number` varchar(50) NOT NULL,
  `bulletin_type` enum('Crime Alert','Wanted Person','Stolen Vehicle','Missing Person','Public Safety','Operational','Intelligence Update') NOT NULL,
  `priority` enum('Routine','Priority','Urgent','Emergency') DEFAULT 'Routine',
  `subject` varchar(200) NOT NULL,
  `bulletin_content` text NOT NULL,
  `action_required` text DEFAULT NULL,
  `valid_from` date NOT NULL,
  `valid_until` date DEFAULT NULL,
  `issued_by` int(11) NOT NULL,
  `target_audience` enum('All Stations','Regional','Divisional','District','Specific Stations','Public') DEFAULT 'All Stations',
  `is_public` tinyint(1) DEFAULT 0,
  `status` enum('Draft','Active','Expired','Cancelled') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `intelligence_reports`
--

CREATE TABLE `intelligence_reports` (
  `id` int(11) NOT NULL,
  `report_number` varchar(50) NOT NULL,
  `report_type` enum('Strategic','Tactical','Operational','Crime Pattern','Threat Assessment') NOT NULL,
  `intelligence_source` enum('Informant','Surveillance','CCTV','Social Media','Public Tip','Inter-Agency','Other') NOT NULL,
  `source_reference` varchar(100) DEFAULT NULL,
  `report_title` varchar(200) NOT NULL,
  `intelligence_summary` text NOT NULL,
  `detailed_analysis` text DEFAULT NULL,
  `reliability_assessment` enum('A - Completely Reliable','B - Usually Reliable','C - Fairly Reliable','D - Not Usually Reliable','E - Unreliable','F - Cannot Be Judged') NOT NULL,
  `information_accuracy` enum('1 - Confirmed','2 - Probably True','3 - Possibly True','4 - Doubtful','5 - Improbable','6 - Cannot Be Judged') NOT NULL,
  `classification_level` enum('Public','Internal','Confidential','Secret','Top Secret') DEFAULT 'Confidential',
  `priority_level` enum('Low','Medium','High','Critical') DEFAULT 'Medium',
  `geographic_area` varchar(255) DEFAULT NULL,
  `target_subjects` text DEFAULT NULL,
  `related_crimes` text DEFAULT NULL,
  `actionable` tinyint(1) DEFAULT 0,
  `action_required` text DEFAULT NULL,
  `dissemination_list` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `report_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` enum('Draft','Under Review','Approved','Distributed','Archived') DEFAULT 'Draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `intelligence_report_distribution`
--

CREATE TABLE `intelligence_report_distribution` (
  `id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `distributed_to_station_id` int(11) DEFAULT NULL,
  `distributed_to_district_id` int(11) DEFAULT NULL,
  `distributed_to_division_id` int(11) DEFAULT NULL,
  `distributed_to_region_id` int(11) DEFAULT NULL,
  `distributed_to_unit_id` int(11) DEFAULT NULL,
  `distributed_by` int(11) NOT NULL,
  `distribution_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `acknowledgment_required` tinyint(1) DEFAULT 0,
  `acknowledged_by` int(11) DEFAULT NULL,
  `acknowledged_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `investigation_milestones`
--

CREATE TABLE `investigation_milestones` (
  `id` int(11) NOT NULL,
  `milestone_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `typical_sequence` int(11) DEFAULT NULL,
  `is_mandatory` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `investigation_milestones`
--

INSERT INTO `investigation_milestones` (`id`, `milestone_name`, `description`, `typical_sequence`, `is_mandatory`, `created_at`) VALUES
(1, 'Case Opened', 'Initial case registration and assignment', 1, 1, '2025-12-17 14:31:01'),
(2, 'Crime Scene Processed', 'Crime scene examined and evidence collected', 2, 1, '2025-12-17 14:31:01'),
(3, 'Witnesses Interviewed', 'Key witnesses identified and statements recorded', 3, 1, '2025-12-17 14:31:01'),
(4, 'Suspect Identified', 'Primary suspect(s) identified', 4, 0, '2025-12-17 14:31:01'),
(5, 'Suspect Arrested', 'Suspect taken into custody', 5, 0, '2025-12-17 14:31:01'),
(6, 'Evidence Analyzed', 'Forensic and physical evidence analyzed', 6, 0, '2025-12-17 14:31:01'),
(7, 'Charges Filed', 'Formal charges filed against suspect', 7, 0, '2025-12-17 14:31:01'),
(8, 'Court Proceedings Started', 'Case presented to court', 8, 0, '2025-12-17 14:31:01'),
(9, 'Verdict Delivered', 'Court delivers judgment', 9, 0, '2025-12-17 14:31:01'),
(10, 'Case Closed', 'Investigation concluded and case closed', 10, 1, '2025-12-17 14:31:01');

-- --------------------------------------------------------

--
-- Table structure for table `missing_persons`
--

CREATE TABLE `missing_persons` (
  `id` int(11) NOT NULL,
  `report_number` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `age_at_disappearance` int(11) DEFAULT NULL,
  `height` varchar(20) DEFAULT NULL,
  `weight` varchar(20) DEFAULT NULL,
  `complexion` varchar(50) DEFAULT NULL,
  `distinguishing_marks` text DEFAULT NULL,
  `last_seen_date` datetime NOT NULL,
  `last_seen_location` varchar(255) NOT NULL,
  `last_seen_wearing` text DEFAULT NULL,
  `circumstances` text DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `reported_by_name` varchar(100) NOT NULL,
  `reported_by_contact` varchar(50) DEFAULT NULL,
  `relationship_to_missing` varchar(50) DEFAULT NULL,
  `station_id` int(11) NOT NULL,
  `investigating_officer_id` int(11) DEFAULT NULL,
  `status` enum('Missing','Found Alive','Found Deceased','Closed') DEFAULT 'Missing',
  `found_date` date DEFAULT NULL,
  `found_location` varchar(255) DEFAULT NULL,
  `case_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `case_id` int(11) DEFAULT NULL,
  `notification_type` enum('Case Assignment','Status Change','Court Date','Custody Alert','Escalation','System Alert') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `officers`
--

CREATE TABLE `officers` (
  `id` int(11) NOT NULL,
  `service_number` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `rank_id` int(11) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `national_id` varchar(50) DEFAULT NULL,
  `ghana_card_number` varchar(50) DEFAULT NULL,
  `phone_number` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `residential_address` text DEFAULT NULL,
  `next_of_kin_name` varchar(100) DEFAULT NULL,
  `next_of_kin_contact` varchar(50) DEFAULT NULL,
  `next_of_kin_relationship` varchar(50) DEFAULT NULL,
  `date_of_enlistment` date NOT NULL,
  `current_station_id` int(11) DEFAULT NULL,
  `current_district_id` int(11) DEFAULT NULL,
  `current_division_id` int(11) DEFAULT NULL,
  `current_region_id` int(11) DEFAULT NULL,
  `current_unit_id` int(11) DEFAULT NULL,
  `employment_status` enum('Active','On Leave','Suspended','Retired','Deceased','Dismissed') DEFAULT 'Active',
  `specialization` varchar(100) DEFAULT NULL,
  `blood_group` varchar(10) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `officer_biometrics`
--

CREATE TABLE `officer_biometrics` (
  `id` int(11) NOT NULL,
  `officer_id` int(11) NOT NULL,
  `biometric_type` enum('Fingerprint','Face','Iris','Palm Print','Voice') NOT NULL,
  `biometric_data` longblob DEFAULT NULL,
  `biometric_template` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `capture_device` varchar(100) DEFAULT NULL,
  `capture_quality` enum('Poor','Fair','Good','Excellent') DEFAULT NULL,
  `captured_by` int(11) NOT NULL,
  `captured_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verification_status` enum('Pending','Verified','Failed') DEFAULT 'Verified',
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `officer_commendations`
--

CREATE TABLE `officer_commendations` (
  `id` int(11) NOT NULL,
  `officer_id` int(11) NOT NULL,
  `commendation_type` enum('Award','Medal','Certificate of Merit','Letter of Commendation','Other') NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `award_date` date NOT NULL,
  `awarded_by` varchar(100) DEFAULT NULL,
  `certificate_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `officer_disciplinary_records`
--

CREATE TABLE `officer_disciplinary_records` (
  `id` int(11) NOT NULL,
  `officer_id` int(11) NOT NULL,
  `case_id` int(11) DEFAULT NULL,
  `offence_type` varchar(100) NOT NULL,
  `offence_description` text NOT NULL,
  `incident_date` date NOT NULL,
  `reported_date` date NOT NULL,
  `disciplinary_action` enum('Warning','Suspension','Demotion','Dismissal','Fine','Other') NOT NULL,
  `action_details` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('Under Investigation','Action Taken','Cleared','Appeal Pending') DEFAULT 'Under Investigation',
  `recorded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `officer_leave_records`
--

CREATE TABLE `officer_leave_records` (
  `id` int(11) NOT NULL,
  `officer_id` int(11) NOT NULL,
  `leave_type` enum('Annual Leave','Sick Leave','Maternity Leave','Paternity Leave','Study Leave','Compassionate Leave','Other') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_days` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `leave_status` enum('Pending','Approved','Rejected','Cancelled') DEFAULT 'Pending',
  `approved_by` int(11) DEFAULT NULL,
  `approval_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `officer_postings`
--

CREATE TABLE `officer_postings` (
  `id` int(11) NOT NULL,
  `officer_id` int(11) NOT NULL,
  `station_id` int(11) DEFAULT NULL,
  `district_id` int(11) DEFAULT NULL,
  `division_id` int(11) DEFAULT NULL,
  `region_id` int(11) DEFAULT NULL,
  `posting_type` enum('Initial Posting','Transfer','Promotion Transfer','Temporary Assignment') NOT NULL,
  `position_title` varchar(100) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `posting_order_number` varchar(100) DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT 1,
  `remarks` text DEFAULT NULL,
  `posted_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `officer_promotions`
--

CREATE TABLE `officer_promotions` (
  `id` int(11) NOT NULL,
  `officer_id` int(11) NOT NULL,
  `from_rank_id` int(11) NOT NULL,
  `to_rank_id` int(11) NOT NULL,
  `promotion_date` date NOT NULL,
  `promotion_order_number` varchar(100) DEFAULT NULL,
  `effective_date` date NOT NULL,
  `remarks` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `officer_training`
--

CREATE TABLE `officer_training` (
  `id` int(11) NOT NULL,
  `officer_id` int(11) NOT NULL,
  `training_name` varchar(200) NOT NULL,
  `training_type` enum('Basic Training','Advanced Training','Specialized Course','Workshop','Seminar','Certification') NOT NULL,
  `training_institution` varchar(200) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `certificate_number` varchar(100) DEFAULT NULL,
  `certificate_path` varchar(255) DEFAULT NULL,
  `grade_score` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `operations`
--

CREATE TABLE `operations` (
  `id` int(11) NOT NULL,
  `operation_code` varchar(50) NOT NULL,
  `operation_name` varchar(200) NOT NULL,
  `operation_type` enum('Raid','Surveillance','Roadblock','Search Operation','Arrest Operation','Special Operation') NOT NULL,
  `operation_date` date NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `target_location` varchar(255) NOT NULL,
  `operation_commander_id` int(11) NOT NULL,
  `station_id` int(11) NOT NULL,
  `officers_deployed` int(11) DEFAULT NULL,
  `operation_status` enum('Planned','In Progress','Completed','Aborted') DEFAULT 'Planned',
  `objectives` text DEFAULT NULL,
  `outcome_summary` text DEFAULT NULL,
  `arrests_made` int(11) DEFAULT 0,
  `exhibits_seized` int(11) DEFAULT 0,
  `case_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `operation_officers`
--

CREATE TABLE `operation_officers` (
  `id` int(11) NOT NULL,
  `operation_id` int(11) NOT NULL,
  `officer_id` int(11) NOT NULL,
  `role_in_operation` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patrol_incidents`
--

CREATE TABLE `patrol_incidents` (
  `id` int(11) NOT NULL,
  `patrol_id` int(11) NOT NULL,
  `incident_time` datetime NOT NULL,
  `incident_location` varchar(255) NOT NULL,
  `incident_type` varchar(100) NOT NULL,
  `incident_description` text DEFAULT NULL,
  `action_taken` text DEFAULT NULL,
  `case_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patrol_logs`
--

CREATE TABLE `patrol_logs` (
  `id` int(11) NOT NULL,
  `patrol_number` varchar(50) NOT NULL,
  `station_id` int(11) NOT NULL,
  `patrol_type` enum('Foot Patrol','Vehicle Patrol','Motorcycle Patrol','Bicycle Patrol','Community Patrol') NOT NULL,
  `patrol_area` varchar(255) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `patrol_leader_id` int(11) NOT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `patrol_status` enum('In Progress','Completed','Interrupted') DEFAULT 'In Progress',
  `incidents_reported` int(11) DEFAULT 0,
  `arrests_made` int(11) DEFAULT 0,
  `report_summary` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patrol_officers`
--

CREATE TABLE `patrol_officers` (
  `id` int(11) NOT NULL,
  `patrol_id` int(11) NOT NULL,
  `officer_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `persons`
--

CREATE TABLE `persons` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `contact` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `alternative_contact` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `ghana_card_number` varchar(50) DEFAULT NULL,
  `passport_number` varchar(50) DEFAULT NULL,
  `drivers_license` varchar(50) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `fingerprint_captured` tinyint(1) DEFAULT 0,
  `face_captured` tinyint(1) DEFAULT 0,
  `has_criminal_record` tinyint(1) DEFAULT 0,
  `is_wanted` tinyint(1) DEFAULT 0,
  `risk_level` enum('None','Low','Medium','High','Critical') DEFAULT 'None',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `persons`
--

INSERT INTO `persons` (`id`, `first_name`, `middle_name`, `last_name`, `gender`, `date_of_birth`, `age`, `contact`, `email`, `alternative_contact`, `address`, `ghana_card_number`, `passport_number`, `drivers_license`, `photo_path`, `fingerprint_captured`, `face_captured`, `has_criminal_record`, `is_wanted`, `risk_level`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'EKOW', '', 'MENSAH', 'Female', '0000-00-00', NULL, '0545644749', 'ekowme@gmail.com', NULL, 'b241, owusu kofi str', 'GHA-123456789-0', 'P1234567890', 'DL1234567890', NULL, 0, 0, 1, 0, 'None', NULL, '2025-12-17 14:58:27', '2025-12-18 10:23:48'),
(2, 'Yaw', '', 'Mensah', 'Male', '1998-05-08', NULL, '0241010956', 'pakowmensah@gmail.com', NULL, 'Sakumono', '', '', '', NULL, 0, 0, 1, 0, 'None', NULL, '2025-12-18 06:06:29', '2025-12-18 07:51:12'),
(7, 'Ekow', '', 'Mensah', '', '0000-00-00', NULL, '0545644741', '', '', '', 'GHA-123456789-1', 'P1234567891', 'DL1234567891', NULL, 0, 0, 0, 0, 'None', NULL, '2025-12-18 12:16:17', '2025-12-18 12:16:17'),
(8, 'Erika', 'Aku', 'Akwei', 'Female', '1956-01-01', NULL, '0241019565', 'erika@gmail.com', '0242109740', 'Dansoman', 'GHA-123456789-2', 'G123456', 'DL3829220', NULL, 0, 0, 1, 0, 'None', NULL, '2025-12-18 12:18:27', '2025-12-18 12:18:51');

-- --------------------------------------------------------

--
-- Table structure for table `person_alerts`
--

CREATE TABLE `person_alerts` (
  `id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  `alert_type` enum('Wanted','Dangerous','Flight Risk','Repeat Offender','Missing','Other') NOT NULL,
  `alert_priority` enum('Low','Medium','High','Critical') DEFAULT 'Medium',
  `alert_message` text NOT NULL,
  `alert_details` text DEFAULT NULL,
  `issued_by` int(11) NOT NULL,
  `issued_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `person_aliases`
--

CREATE TABLE `person_aliases` (
  `id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  `alias_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `person_criminal_history`
--

CREATE TABLE `person_criminal_history` (
  `id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `involvement_type` enum('Suspect','Arrested','Charged','Convicted','Acquitted','Witness','Complainant') NOT NULL,
  `offence_category` varchar(100) DEFAULT NULL,
  `case_status` varchar(50) DEFAULT NULL,
  `case_date` date DEFAULT NULL,
  `outcome` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `person_relationships`
--

CREATE TABLE `person_relationships` (
  `id` int(11) NOT NULL,
  `person_id_1` int(11) NOT NULL,
  `person_id_2` int(11) NOT NULL,
  `relationship_type` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ;

--
-- Dumping data for table `person_relationships`
--

INSERT INTO `person_relationships` (`id`, `person_id_1`, `person_id_2`, `relationship_type`, `notes`, `created_at`, `created_by`) VALUES
(1, 2, 1, 'Sibling', '', '2025-12-18 11:14:30', 1),
(2, 1, 2, 'Sibling', '', '2025-12-18 11:14:30', 1);

-- --------------------------------------------------------

--
-- Table structure for table `police_ranks`
--

CREATE TABLE `police_ranks` (
  `id` int(11) NOT NULL,
  `rank_name` varchar(50) NOT NULL,
  `rank_level` int(11) NOT NULL,
  `rank_category` enum('Junior Officer','Senior Officer','Commissioned Officer','Senior Command') NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `police_ranks`
--

INSERT INTO `police_ranks` (`id`, `rank_name`, `rank_level`, `rank_category`, `description`, `created_at`) VALUES
(1, 'Recruit Constable', 1, 'Junior Officer', 'Entry level recruit in training', '2025-12-17 14:31:01'),
(2, 'General Constable', 2, 'Junior Officer', 'Basic operational officer', '2025-12-17 14:31:01'),
(3, 'Lance Corporal', 3, 'Junior Officer', 'Junior supervisory role', '2025-12-17 14:31:01'),
(4, 'Corporal', 4, 'Junior Officer', 'Section leader', '2025-12-17 14:31:01'),
(5, 'Sergeant', 5, 'Senior Officer', 'Team supervisor', '2025-12-17 14:31:01'),
(6, 'Station Sergeant', 6, 'Senior Officer', 'Station operations supervisor', '2025-12-17 14:31:01'),
(7, 'Inspector', 7, 'Senior Officer', 'Unit commander', '2025-12-17 14:31:01'),
(8, 'Chief Inspector', 8, 'Senior Officer', 'Senior unit commander', '2025-12-17 14:31:01'),
(9, 'Superintendent', 9, 'Senior Officer', 'District operations commander', '2025-12-17 14:31:01'),
(10, 'Chief Superintendent', 10, 'Commissioned Officer', 'District commander', '2025-12-17 14:31:01'),
(11, 'Assistant Commissioner of Police (ACP)', 11, 'Commissioned Officer', 'Divisional commander', '2025-12-17 14:31:01'),
(12, 'Deputy Commissioner of Police (DCOP)', 12, 'Commissioned Officer', 'Regional deputy commander', '2025-12-17 14:31:01'),
(13, 'Commissioner of Police (COP)', 13, 'Commissioned Officer', 'Regional commander', '2025-12-17 14:31:01'),
(14, 'Deputy Inspector General of Police (DIGP)', 14, 'Senior Command', 'Deputy national commander', '2025-12-17 14:31:01'),
(15, 'Inspector General of Police (IGP)', 15, 'Senior Command', 'National police commander', '2025-12-17 14:31:01');

-- --------------------------------------------------------

--
-- Table structure for table `public_complaints`
--

CREATE TABLE `public_complaints` (
  `id` int(11) NOT NULL,
  `complaint_number` varchar(50) NOT NULL,
  `complainant_name` varchar(100) NOT NULL,
  `complainant_contact` varchar(50) DEFAULT NULL,
  `complainant_address` text DEFAULT NULL,
  `complaint_date` date NOT NULL,
  `incident_date` date DEFAULT NULL,
  `incident_location` varchar(255) DEFAULT NULL,
  `complaint_type` enum('Misconduct','Excessive Force','Corruption','Negligence','Unprofessional Conduct','Other') NOT NULL,
  `complaint_details` text NOT NULL,
  `officer_complained_against` int(11) DEFAULT NULL,
  `station_id` int(11) NOT NULL,
  `investigating_officer_id` int(11) DEFAULT NULL,
  `complaint_status` enum('Received','Under Investigation','Resolved','Dismissed','Referred to CHRAJ') DEFAULT 'Received',
  `resolution` text DEFAULT NULL,
  `resolution_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `public_intelligence_tips`
--

CREATE TABLE `public_intelligence_tips` (
  `id` int(11) NOT NULL,
  `tip_number` varchar(50) NOT NULL,
  `tip_source` enum('Phone','Email','Web Form','SMS','Walk-in','Anonymous Hotline','Social Media') NOT NULL,
  `tipster_name` varchar(100) DEFAULT NULL,
  `tipster_contact` varchar(50) DEFAULT NULL,
  `is_anonymous` tinyint(1) DEFAULT 0,
  `tip_content` text NOT NULL,
  `tip_category` varchar(100) DEFAULT NULL,
  `location_mentioned` varchar(255) DEFAULT NULL,
  `date_of_incident` date DEFAULT NULL,
  `urgency` enum('Low','Medium','High','Critical') DEFAULT 'Medium',
  `assigned_to` int(11) DEFAULT NULL,
  `follow_up_required` tinyint(1) DEFAULT 1,
  `follow_up_notes` text DEFAULT NULL,
  `verification_status` enum('Pending','Verified','False','Cannot Verify') DEFAULT 'Pending',
  `case_created` tinyint(1) DEFAULT 0,
  `case_id` int(11) DEFAULT NULL,
  `intelligence_report_id` int(11) DEFAULT NULL,
  `received_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `regions`
--

CREATE TABLE `regions` (
  `id` int(11) NOT NULL,
  `region_name` varchar(100) NOT NULL,
  `region_code` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `regions`
--

INSERT INTO `regions` (`id`, `region_name`, `region_code`, `created_at`) VALUES
(1, 'Central Region', '001', '2025-12-18 06:15:06'),
(2, 'Ashanti Region', '002', '2025-12-18 06:15:37');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `access_level` enum('Own','Unit','Station','District','Division','Region','National') DEFAULT 'Own',
  `can_manage_cases` tinyint(1) DEFAULT 0,
  `can_manage_officers` tinyint(1) DEFAULT 0,
  `can_manage_evidence` tinyint(1) DEFAULT 0,
  `can_manage_firearms` tinyint(1) DEFAULT 0,
  `can_view_intelligence` tinyint(1) DEFAULT 0,
  `can_approve_operations` tinyint(1) DEFAULT 0,
  `can_manage_users` tinyint(1) DEFAULT 0,
  `can_view_reports` tinyint(1) DEFAULT 0,
  `can_export_data` tinyint(1) DEFAULT 0,
  `is_system_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `description`, `access_level`, `can_manage_cases`, `can_manage_officers`, `can_manage_evidence`, `can_manage_firearms`, `can_view_intelligence`, `can_approve_operations`, `can_manage_users`, `can_view_reports`, `can_export_data`, `is_system_admin`, `created_at`) VALUES
(1, 'Super Admin', 'Full system access', 'National', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, '2025-12-17 14:31:01'),
(2, 'Regional Commander', 'Regional level access', 'Own', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-12-17 14:31:01'),
(3, 'Divisional Commander', 'Divisional level access', 'Own', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-12-17 14:31:01'),
(4, 'District Commander', 'District level access', 'Own', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-12-17 14:31:01'),
(5, 'Station Officer', 'Station level access', 'Own', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-12-17 14:31:01'),
(6, 'Investigator', 'Case investigation access', 'Own', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-12-17 14:31:01'),
(7, 'Records Officer', 'Records management access', 'Own', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-12-17 14:31:01'),
(8, 'Evidence Officer', 'Evidence management access', 'Own', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-12-17 14:31:01');

-- --------------------------------------------------------

--
-- Table structure for table `sensitive_data_access_log`
--

CREATE TABLE `sensitive_data_access_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `record_id` int(11) NOT NULL,
  `access_type` enum('VIEW','EXPORT','PRINT','MODIFY','DELETE') NOT NULL,
  `access_reason` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `access_granted` tinyint(1) DEFAULT 1,
  `access_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `statements`
--

CREATE TABLE `statements` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `suspect_id` int(11) DEFAULT NULL,
  `witness_id` int(11) DEFAULT NULL,
  `complainant_id` int(11) DEFAULT NULL,
  `statement_type` enum('Suspect','Witness','Complainant') NOT NULL,
  `statement_text` text DEFAULT NULL,
  `status` enum('active','cancelled','superseded') DEFAULT 'active',
  `parent_statement_id` int(11) DEFAULT NULL,
  `version` int(11) DEFAULT 1,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` int(11) DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `scanned_copy` varchar(255) DEFAULT NULL,
  `recorded_by` int(11) NOT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `statements`
--

INSERT INTO `statements` (`id`, `case_id`, `suspect_id`, `witness_id`, `complainant_id`, `statement_type`, `statement_text`, `status`, `parent_statement_id`, `version`, `cancelled_at`, `cancelled_by`, `cancellation_reason`, `scanned_copy`, `recorded_by`, `recorded_at`) VALUES
(16, 6, NULL, NULL, 8, 'Complainant', 'a statement can be denied or rewritten, let\'s implement something for the statements. for instance, suspect or witness wants to write another statement and cancel the first one. but the older statement must not be deleted but showed as cancelled or something', 'active', NULL, 1, NULL, NULL, NULL, NULL, 1, '2025-12-18 10:15:29');

-- --------------------------------------------------------

--
-- Table structure for table `stations`
--

CREATE TABLE `stations` (
  `id` int(11) NOT NULL,
  `station_name` varchar(100) NOT NULL,
  `station_code` varchar(20) DEFAULT NULL,
  `district_id` int(11) NOT NULL,
  `division_id` int(11) DEFAULT NULL,
  `region_id` int(11) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stations`
--

INSERT INTO `stations` (`id`, `station_name`, `station_code`, `district_id`, `division_id`, `region_id`, `address`, `contact_number`, `created_at`) VALUES
(1, 'Breman Asikuma', 'BASK001', 1, 1, 1, 'b241, owusu kofi str', '0545644749', '2025-12-18 06:47:02');

-- --------------------------------------------------------

--
-- Table structure for table `surveillance_officers`
--

CREATE TABLE `surveillance_officers` (
  `id` int(11) NOT NULL,
  `surveillance_id` int(11) NOT NULL,
  `officer_id` int(11) NOT NULL,
  `role_in_surveillance` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `surveillance_operations`
--

CREATE TABLE `surveillance_operations` (
  `id` int(11) NOT NULL,
  `operation_code` varchar(50) NOT NULL,
  `operation_name` varchar(200) NOT NULL,
  `surveillance_type` enum('Physical','Electronic','Aerial','Vehicle','Covert','Overt') NOT NULL,
  `target_type` enum('Person','Location','Vehicle','Organization','Event') NOT NULL,
  `target_description` text NOT NULL,
  `target_location` varchar(255) DEFAULT NULL,
  `operation_commander_id` int(11) NOT NULL,
  `station_id` int(11) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `operation_status` enum('Planned','Active','Suspended','Completed','Aborted') DEFAULT 'Planned',
  `authorization_level` enum('Station','District','Division','Region','Court Order') NOT NULL,
  `authorization_reference` varchar(100) DEFAULT NULL,
  `objectives` text NOT NULL,
  `surveillance_log` text DEFAULT NULL,
  `findings_summary` text DEFAULT NULL,
  `evidence_collected` text DEFAULT NULL,
  `case_id` int(11) DEFAULT NULL,
  `intelligence_report_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suspects`
--

CREATE TABLE `suspects` (
  `id` int(11) NOT NULL,
  `person_id` int(11) DEFAULT NULL,
  `current_status` enum('Suspect','Arrested','Charged','Discharged','Acquitted','Convicted','Released','Deceased') DEFAULT 'Suspect',
  `alias` varchar(100) DEFAULT NULL,
  `last_known_location` varchar(255) DEFAULT NULL,
  `arrest_date` date DEFAULT NULL,
  `identifying_marks` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `unknown_description` varchar(255) DEFAULT NULL,
  `estimated_age` varchar(50) DEFAULT NULL,
  `unknown_gender` enum('Male','Female','Unknown') DEFAULT NULL,
  `height_build` varchar(100) DEFAULT NULL,
  `complexion` varchar(50) DEFAULT NULL,
  `clothing` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suspects`
--

INSERT INTO `suspects` (`id`, `person_id`, `current_status`, `alias`, `last_known_location`, `arrest_date`, `identifying_marks`, `notes`, `unknown_description`, `estimated_age`, `unknown_gender`, `height_build`, `complexion`, `clothing`, `created_at`, `updated_at`) VALUES
(1, 2, 'Suspect', '', '', '0000-00-00', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-18 07:51:12', '2025-12-18 08:17:27'),
(5, 1, 'Suspect', 'KB', 'Odoben', '0000-00-00', 'dyed hair', 'nothing bad', 'Tito Nash', '30 years', '', 'Tall', 'Dark', 'White Shirt and Black Jeans', '2025-12-18 08:02:56', '2025-12-18 08:17:27'),
(6, 1, 'Suspect', 'bigboss', 'kasoa', '1988-05-09', 'tatoos', 'good person', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-18 08:09:56', '2025-12-18 08:17:27'),
(7, NULL, 'Charged', 'Tito Nash', 'Odoben', '0000-00-00', 'tatoos', 'nothing', 'Tito Nash', '30 years', '', 'Tall', 'Dark', 'White Shirt and Black Jeans', '2025-12-18 08:12:48', '2025-12-18 11:18:33'),
(8, 1, 'Suspect', '', '', '0000-00-00', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-18 10:23:48', '2025-12-18 10:23:48'),
(9, 2, 'Suspect', 'Tito Nash', 'kasoa', '0000-00-00', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-18 11:20:01', '2025-12-18 11:20:34'),
(10, 8, 'Arrested', '', '', '0000-00-00', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-18 12:18:51', '2025-12-18 12:19:37');

-- --------------------------------------------------------

--
-- Table structure for table `suspect_biometrics`
--

CREATE TABLE `suspect_biometrics` (
  `id` int(11) NOT NULL,
  `suspect_id` int(11) NOT NULL,
  `biometric_type` enum('Fingerprint','Face','Iris','Palm Print','Voice') NOT NULL,
  `biometric_data` longblob DEFAULT NULL,
  `biometric_template` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `capture_device` varchar(100) DEFAULT NULL,
  `capture_quality` enum('Poor','Fair','Good','Excellent') DEFAULT NULL,
  `captured_by` int(11) NOT NULL,
  `captured_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verification_status` enum('Pending','Verified','Failed') DEFAULT 'Pending',
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suspect_status_history`
--

CREATE TABLE `suspect_status_history` (
  `id` int(11) NOT NULL,
  `suspect_id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `remarks` text DEFAULT NULL,
  `changed_by` int(11) NOT NULL,
  `change_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `temporary_permissions`
--

CREATE TABLE `temporary_permissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `permission_type` varchar(100) NOT NULL,
  `granted_by` int(11) NOT NULL,
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `reason` text NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `revoked_by` int(11) DEFAULT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `threat_assessments`
--

CREATE TABLE `threat_assessments` (
  `id` int(11) NOT NULL,
  `assessment_number` varchar(50) NOT NULL,
  `threat_type` enum('Terrorism','Organized Crime','Gang Activity','Cybercrime','Public Safety','VIP Security','Event Security','Other') NOT NULL,
  `threat_level` enum('Low','Moderate','Substantial','Severe','Critical') NOT NULL,
  `threat_description` text NOT NULL,
  `threat_indicators` text DEFAULT NULL,
  `vulnerable_targets` text DEFAULT NULL,
  `geographic_scope` varchar(255) DEFAULT NULL,
  `time_frame` varchar(100) DEFAULT NULL,
  `likelihood` enum('Remote','Unlikely','Possible','Likely','Highly Likely') NOT NULL,
  `potential_impact` enum('Minor','Moderate','Significant','Major','Catastrophic') NOT NULL,
  `mitigation_measures` text DEFAULT NULL,
  `recommended_actions` text DEFAULT NULL,
  `assessed_by` int(11) NOT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `assessment_date` date NOT NULL,
  `next_review_date` date DEFAULT NULL,
  `status` enum('Active','Under Review','Mitigated','Closed') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` int(11) NOT NULL,
  `unit_name` varchar(100) NOT NULL,
  `unit_code` varchar(20) DEFAULT NULL,
  `unit_type_id` int(11) NOT NULL,
  `station_id` int(11) DEFAULT NULL,
  `district_id` int(11) DEFAULT NULL,
  `division_id` int(11) DEFAULT NULL,
  `region_id` int(11) DEFAULT NULL,
  `parent_unit_id` int(11) DEFAULT NULL,
  `unit_head_officer_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `unit_officer_assignments`
--

CREATE TABLE `unit_officer_assignments` (
  `id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `officer_id` int(11) NOT NULL,
  `assignment_type` enum('Permanent','Temporary','Secondment') DEFAULT 'Permanent',
  `position_in_unit` varchar(100) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT 1,
  `assigned_by` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `unit_types`
--

CREATE TABLE `unit_types` (
  `id` int(11) NOT NULL,
  `unit_type_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `unit_types`
--

INSERT INTO `unit_types` (`id`, `unit_type_name`, `description`, `created_at`) VALUES
(1, 'Criminal Investigation Department (CID)', 'Investigates serious crimes and criminal activities', '2025-12-17 14:31:01'),
(2, 'Traffic Unit', 'Manages traffic control and road safety', '2025-12-17 14:31:01'),
(3, 'Special Weapons and Tactics (SWAT)', 'Handles high-risk operations and tactical situations', '2025-12-17 14:31:01'),
(4, 'K-9 Unit', 'Police dog unit for detection and patrol', '2025-12-17 14:31:01'),
(5, 'Cybercrime Unit', 'Investigates cyber-related crimes', '2025-12-17 14:31:01'),
(6, 'Domestic Violence and Victim Support Unit (DOVVSU)', 'Handles domestic violence and vulnerable persons cases', '2025-12-17 14:31:01'),
(7, 'Anti-Armed Robbery Unit', 'Specialized unit for armed robbery cases', '2025-12-17 14:31:01'),
(8, 'Narcotics Control Unit', 'Drug enforcement and narcotics investigations', '2025-12-17 14:31:01'),
(9, 'Intelligence Unit', 'Gathers and analyzes intelligence information', '2025-12-17 14:31:01'),
(10, 'Public Relations Unit', 'Manages public communication and media relations', '2025-12-17 14:31:01'),
(11, 'Administration Unit', 'Handles administrative and support functions', '2025-12-17 14:31:01'),
(12, 'Operations Unit', 'Manages day-to-day operational activities', '2025-12-17 14:31:01'),
(13, 'Patrol Unit', 'Conducts routine patrols and community policing', '2025-12-17 14:31:01'),
(14, 'Rapid Response Unit', 'Quick response to emergency situations', '2025-12-17 14:31:01'),
(15, 'Formed Police Unit (FPU)', 'Riot control and public order management', '2025-12-17 14:31:01'),
(16, 'Marine Police Unit', 'Water-based law enforcement', '2025-12-17 14:31:01'),
(17, 'Motor Traffic and Transport Department (MTTD)', 'Vehicle licensing and traffic law enforcement', '2025-12-17 14:31:01'),
(18, 'Counter Terrorism Unit', 'Prevents and responds to terrorist activities', '2025-12-17 14:31:01'),
(19, 'Financial Crimes Unit', 'Investigates fraud and financial crimes', '2025-12-17 14:31:01'),
(20, 'Juvenile and Child Welfare Unit', 'Handles cases involving minors', '2025-12-17 14:31:01');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `service_number` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `rank` varchar(50) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `station_id` int(11) DEFAULT NULL,
  `district_id` int(11) DEFAULT NULL,
  `division_id` int(11) DEFAULT NULL,
  `region_id` int(11) DEFAULT NULL,
  `status` enum('Active','Suspended','Inactive') DEFAULT 'Active',
  `last_login` timestamp NULL DEFAULT NULL,
  `failed_login_attempts` int(11) DEFAULT 0,
  `account_locked_until` timestamp NULL DEFAULT NULL,
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires` timestamp NULL DEFAULT NULL,
  `must_change_password` tinyint(1) DEFAULT 1,
  `password_changed_at` timestamp NULL DEFAULT NULL,
  `allowed_ip_addresses` text DEFAULT NULL,
  `session_timeout_minutes` int(11) DEFAULT 30,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `service_number`, `first_name`, `middle_name`, `last_name`, `rank`, `role_id`, `username`, `email`, `password_hash`, `station_id`, `district_id`, `division_id`, `region_id`, `status`, `last_login`, `failed_login_attempts`, `account_locked_until`, `two_factor_enabled`, `two_factor_secret`, `password_reset_token`, `password_reset_expires`, `must_change_password`, `password_changed_at`, `allowed_ip_addresses`, `session_timeout_minutes`, `created_at`, `updated_at`) VALUES
(1, 'ADMIN001', 'System', NULL, 'Administrator', 'Administrator', 1, 'admin', 'admin@ghpims.local', '$2y$10$UvULeKrXhYfFBHnhkqHpr.uGEdroyQbLZAOlQIHsCMxPvqCzdX0Ne', NULL, NULL, NULL, NULL, 'Active', '2025-12-19 05:25:33', 0, NULL, 0, NULL, NULL, NULL, 1, NULL, NULL, 30, '2025-12-17 14:35:27', '2025-12-19 05:25:33');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `logout_time` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `registration_number` varchar(50) NOT NULL,
  `vehicle_make` varchar(100) DEFAULT NULL,
  `vehicle_model` varchar(100) DEFAULT NULL,
  `vehicle_year` year(4) DEFAULT NULL,
  `vehicle_color` varchar(50) DEFAULT NULL,
  `chassis_number` varchar(100) DEFAULT NULL,
  `engine_number` varchar(100) DEFAULT NULL,
  `owner_name` varchar(100) DEFAULT NULL,
  `owner_contact` varchar(50) DEFAULT NULL,
  `owner_address` text DEFAULT NULL,
  `vehicle_status` enum('Registered','Stolen','Recovered','Impounded','Evidence') DEFAULT 'Registered',
  `case_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_officers_full_names`
-- (See below for the actual view)
--
CREATE TABLE `v_officers_full_names` (
`id` int(11)
,`full_name` varchar(152)
,`full_name_formal` varchar(153)
,`first_name` varchar(50)
,`middle_name` varchar(50)
,`last_name` varchar(50)
,`service_number` varchar(50)
,`rank_name` varchar(50)
,`current_station_id` int(11)
,`employment_status` enum('Active','On Leave','Suspended','Retired','Deceased','Dismissed')
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_persons_full_names`
-- (See below for the actual view)
--
CREATE TABLE `v_persons_full_names` (
`id` int(11)
,`full_name` varchar(152)
,`full_name_formal` varchar(153)
,`first_name` varchar(50)
,`middle_name` varchar(50)
,`last_name` varchar(50)
,`gender` enum('Male','Female','Other')
,`date_of_birth` date
,`contact` varchar(50)
,`ghana_card_number` varchar(50)
,`has_criminal_record` tinyint(1)
,`is_wanted` tinyint(1)
,`risk_level` enum('None','Low','Medium','High','Critical')
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_users_full_names`
-- (See below for the actual view)
--
CREATE TABLE `v_users_full_names` (
`id` int(11)
,`full_name` varchar(152)
,`full_name_formal` varchar(153)
,`first_name` varchar(50)
,`middle_name` varchar(50)
,`last_name` varchar(50)
,`username` varchar(50)
,`email` varchar(100)
,`rank` varchar(50)
,`role_id` int(11)
,`status` enum('Active','Suspended','Inactive')
);

-- --------------------------------------------------------

--
-- Table structure for table `witnesses`
--

CREATE TABLE `witnesses` (
  `id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  `witness_type` enum('Eye Witness','Expert Witness','Character Witness','Other') DEFAULT 'Eye Witness',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `witnesses`
--

INSERT INTO `witnesses` (`id`, `person_id`, `witness_type`, `created_at`, `updated_at`) VALUES
(1, 1, 'Eye Witness', '2025-12-18 09:15:57', '2025-12-18 09:15:57');

-- --------------------------------------------------------

--
-- Structure for view `v_officers_full_names`
--
DROP TABLE IF EXISTS `v_officers_full_names`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_officers_full_names`  AS SELECT `o`.`id` AS `id`, concat_ws(' ',`o`.`first_name`,`o`.`middle_name`,`o`.`last_name`) AS `full_name`, concat(`o`.`last_name`,', ',`o`.`first_name`,coalesce(concat(' ',`o`.`middle_name`),'')) AS `full_name_formal`, `o`.`first_name` AS `first_name`, `o`.`middle_name` AS `middle_name`, `o`.`last_name` AS `last_name`, `o`.`service_number` AS `service_number`, `pr`.`rank_name` AS `rank_name`, `o`.`current_station_id` AS `current_station_id`, `o`.`employment_status` AS `employment_status` FROM (`officers` `o` join `police_ranks` `pr` on(`o`.`rank_id` = `pr`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_persons_full_names`
--
DROP TABLE IF EXISTS `v_persons_full_names`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_persons_full_names`  AS SELECT `persons`.`id` AS `id`, concat_ws(' ',`persons`.`first_name`,`persons`.`middle_name`,`persons`.`last_name`) AS `full_name`, concat(`persons`.`last_name`,', ',`persons`.`first_name`,coalesce(concat(' ',`persons`.`middle_name`),'')) AS `full_name_formal`, `persons`.`first_name` AS `first_name`, `persons`.`middle_name` AS `middle_name`, `persons`.`last_name` AS `last_name`, `persons`.`gender` AS `gender`, `persons`.`date_of_birth` AS `date_of_birth`, `persons`.`contact` AS `contact`, `persons`.`ghana_card_number` AS `ghana_card_number`, `persons`.`has_criminal_record` AS `has_criminal_record`, `persons`.`is_wanted` AS `is_wanted`, `persons`.`risk_level` AS `risk_level` FROM `persons` ;

-- --------------------------------------------------------

--
-- Structure for view `v_users_full_names`
--
DROP TABLE IF EXISTS `v_users_full_names`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_users_full_names`  AS SELECT `users`.`id` AS `id`, concat_ws(' ',`users`.`first_name`,`users`.`middle_name`,`users`.`last_name`) AS `full_name`, concat(`users`.`last_name`,', ',`users`.`first_name`,coalesce(concat(' ',`users`.`middle_name`),'')) AS `full_name_formal`, `users`.`first_name` AS `first_name`, `users`.`middle_name` AS `middle_name`, `users`.`last_name` AS `last_name`, `users`.`username` AS `username`, `users`.`email` AS `email`, `users`.`rank` AS `rank`, `users`.`role_id` AS `role_id`, `users`.`status` AS `status` FROM `users` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ammunition_stock`
--
ALTER TABLE `ammunition_stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ammo_station` (`station_id`),
  ADD KEY `idx_ammo_type` (`ammunition_type`);

--
-- Indexes for table `arrests`
--
ALTER TABLE `arrests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `arresting_officer_id` (`arresting_officer_id`),
  ADD KEY `idx_arrests_case` (`case_id`),
  ADD KEY `idx_arrests_suspect` (`suspect_id`),
  ADD KEY `idx_arrests_date` (`arrest_date`);

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serial_number` (`serial_number`),
  ADD KEY `idx_assets_case` (`case_id`),
  ADD KEY `idx_assets_type` (`asset_type`);

--
-- Indexes for table `asset_movements`
--
ALTER TABLE `asset_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `moved_by` (`moved_by`),
  ADD KEY `idx_asset_movements` (`asset_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `suspect_id` (`suspect_id`),
  ADD KEY `evidence_id` (`evidence_id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_module` (`module`),
  ADD KEY `idx_audit_table` (`table_name`),
  ADD KEY `idx_audit_action` (`action_type`),
  ADD KEY `idx_audit_time` (`action_time`),
  ADD KEY `idx_audit_case` (`case_id`),
  ADD KEY `idx_audit_officer` (`officer_id`);

--
-- Indexes for table `bail_records`
--
ALTER TABLE `bail_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_bail_case` (`case_id`),
  ADD KEY `idx_bail_suspect` (`suspect_id`);

--
-- Indexes for table `cases`
--
ALTER TABLE `cases`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `case_number` (`case_number`),
  ADD KEY `complainant_id` (`complainant_id`),
  ADD KEY `division_id` (`division_id`),
  ADD KEY `region_id` (`region_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_cases_status` (`status`),
  ADD KEY `idx_cases_created_at` (`created_at`),
  ADD KEY `idx_cases_priority` (`case_priority`),
  ADD KEY `idx_cases_station` (`station_id`),
  ADD KEY `idx_cases_district` (`district_id`);

--
-- Indexes for table `case_assignments`
--
ALTER TABLE `case_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `idx_assignment_case` (`case_id`),
  ADD KEY `idx_assignment_user` (`assigned_to`),
  ADD KEY `idx_assignment_status` (`status`);

--
-- Indexes for table `case_crimes`
--
ALTER TABLE `case_crimes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `added_by` (`added_by`),
  ADD KEY `idx_case_crimes_case` (`case_id`),
  ADD KEY `idx_case_crimes_category` (`crime_category_id`);

--
-- Indexes for table `case_documents`
--
ALTER TABLE `case_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `idx_case_documents_case` (`case_id`),
  ADD KEY `idx_case_documents_type` (`document_type`);

--
-- Indexes for table `case_investigation_checklist`
--
ALTER TABLE `case_investigation_checklist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `completed_by` (`completed_by`),
  ADD KEY `idx_checklist_case` (`case_id`),
  ADD KEY `idx_checklist_category` (`item_category`),
  ADD KEY `idx_checklist_status` (`is_completed`);

--
-- Indexes for table `case_investigation_tasks`
--
ALTER TABLE `case_investigation_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `idx_tasks_case` (`case_id`),
  ADD KEY `idx_tasks_assigned` (`assigned_to`),
  ADD KEY `idx_tasks_status` (`status`),
  ADD KEY `idx_tasks_due` (`due_date`),
  ADD KEY `fk_tasks_created_by` (`created_by`);

--
-- Indexes for table `case_investigation_timeline`
--
ALTER TABLE `case_investigation_timeline`
  ADD PRIMARY KEY (`id`),
  ADD KEY `milestone_id` (`milestone_id`),
  ADD KEY `completed_by` (`completed_by`),
  ADD KEY `idx_timeline_case` (`case_id`),
  ADD KEY `idx_timeline_date` (`activity_date`),
  ADD KEY `idx_timeline_type` (`activity_type`);

--
-- Indexes for table `case_milestones`
--
ALTER TABLE `case_milestones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_case_milestones_case` (`case_id`),
  ADD KEY `idx_case_milestones_achieved` (`is_achieved`);

--
-- Indexes for table `case_referrals`
--
ALTER TABLE `case_referrals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `from_station_id` (`from_station_id`),
  ADD KEY `to_station_id` (`to_station_id`),
  ADD KEY `referred_by` (`referred_by`),
  ADD KEY `idx_referral_case` (`case_id`),
  ADD KEY `idx_referral_status` (`status`);

--
-- Indexes for table `case_status_history`
--
ALTER TABLE `case_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `changed_by` (`changed_by`),
  ADD KEY `idx_case_status_history` (`case_id`);

--
-- Indexes for table `case_suspects`
--
ALTER TABLE `case_suspects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_case_suspect` (`case_id`,`suspect_id`),
  ADD KEY `idx_case_suspects_case` (`case_id`),
  ADD KEY `idx_case_suspects_suspect` (`suspect_id`);

--
-- Indexes for table `case_updates`
--
ALTER TABLE `case_updates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `idx_case_updates_case` (`case_id`),
  ADD KEY `idx_case_updates_date` (`update_date`);

--
-- Indexes for table `case_witnesses`
--
ALTER TABLE `case_witnesses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_case_witness` (`case_id`,`witness_id`),
  ADD KEY `idx_case_witnesses_case` (`case_id`),
  ADD KEY `idx_case_witnesses_witness` (`witness_id`);

--
-- Indexes for table `charges`
--
ALTER TABLE `charges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `charged_by` (`charged_by`),
  ADD KEY `idx_charges_case` (`case_id`),
  ADD KEY `idx_charges_suspect` (`suspect_id`);

--
-- Indexes for table `complainants`
--
ALTER TABLE `complainants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_complainant_person` (`person_id`);

--
-- Indexes for table `court_proceedings`
--
ALTER TABLE `court_proceedings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recorded_by` (`recorded_by`),
  ADD KEY `idx_court_case` (`case_id`),
  ADD KEY `idx_court_suspect` (`suspect_id`),
  ADD KEY `idx_court_date` (`court_date`);

--
-- Indexes for table `crime_categories`
--
ALTER TABLE `crime_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category_name` (`category_name`),
  ADD KEY `idx_crime_parent` (`parent_category_id`);

--
-- Indexes for table `custody_records`
--
ALTER TABLE `custody_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `case_id` (`case_id`),
  ADD KEY `released_by` (`released_by`),
  ADD KEY `idx_custody_suspect` (`suspect_id`),
  ADD KEY `idx_custody_status` (`custody_status`);

--
-- Indexes for table `districts`
--
ALTER TABLE `districts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `district_code` (`district_code`),
  ADD KEY `idx_district_division` (`division_id`);

--
-- Indexes for table `divisions`
--
ALTER TABLE `divisions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `division_code` (`division_code`),
  ADD KEY `idx_division_region` (`region_id`);

--
-- Indexes for table `duty_roster`
--
ALTER TABLE `duty_roster`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shift_id` (`shift_id`),
  ADD KEY `supervisor_id` (`supervisor_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_roster_officer` (`officer_id`),
  ADD KEY `idx_roster_date` (`duty_date`),
  ADD KEY `idx_roster_station` (`station_id`),
  ADD KEY `idx_roster_status` (`status`);

--
-- Indexes for table `duty_shifts`
--
ALTER TABLE `duty_shifts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `evidence`
--
ALTER TABLE `evidence`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `evidence_number` (`evidence_number`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `idx_evidence_case` (`case_id`),
  ADD KEY `idx_evidence_type` (`evidence_type`);

--
-- Indexes for table `evidence_custody_chain`
--
ALTER TABLE `evidence_custody_chain`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transferred_from` (`transferred_from`),
  ADD KEY `transferred_to` (`transferred_to`),
  ADD KEY `idx_custody_chain_evidence` (`evidence_id`);

--
-- Indexes for table `exhibits`
--
ALTER TABLE `exhibits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `exhibit_number` (`exhibit_number`),
  ADD KEY `seized_by` (`seized_by`),
  ADD KEY `idx_exhibit_case` (`case_id`),
  ADD KEY `idx_exhibit_number` (`exhibit_number`),
  ADD KEY `idx_exhibit_status` (`exhibit_status`);

--
-- Indexes for table `exhibit_movements`
--
ALTER TABLE `exhibit_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `moved_by` (`moved_by`),
  ADD KEY `received_by` (`received_by`),
  ADD KEY `idx_exhibit_movement` (`exhibit_id`);

--
-- Indexes for table `firearms`
--
ALTER TABLE `firearms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serial_number` (`serial_number`),
  ADD KEY `station_id` (`station_id`),
  ADD KEY `idx_firearm_serial` (`serial_number`),
  ADD KEY `idx_firearm_status` (`firearm_status`),
  ADD KEY `idx_firearm_holder` (`current_holder_id`);

--
-- Indexes for table `firearm_assignments`
--
ALTER TABLE `firearm_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `issued_by` (`issued_by`),
  ADD KEY `idx_firearm_assignment` (`firearm_id`),
  ADD KEY `idx_officer_firearm` (`officer_id`),
  ADD KEY `idx_assignment_date` (`issue_date`);

--
-- Indexes for table `incident_reports`
--
ALTER TABLE `incident_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `incident_number` (`incident_number`),
  ADD KEY `attending_officer_id` (`attending_officer_id`),
  ADD KEY `case_id` (`case_id`),
  ADD KEY `idx_incident_station` (`station_id`),
  ADD KEY `idx_incident_date` (`incident_date`),
  ADD KEY `idx_incident_type` (`incident_type`),
  ADD KEY `idx_incident_status` (`status`);

--
-- Indexes for table `informants`
--
ALTER TABLE `informants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `informant_code` (`informant_code`),
  ADD KEY `station_id` (`station_id`),
  ADD KEY `idx_informant_code` (`informant_code`),
  ADD KEY `idx_informant_handler` (`handler_officer_id`),
  ADD KEY `idx_informant_status` (`status`);

--
-- Indexes for table `informant_intelligence`
--
ALTER TABLE `informant_intelligence`
  ADD PRIMARY KEY (`id`),
  ADD KEY `handler_officer_id` (`handler_officer_id`),
  ADD KEY `idx_intel_informant` (`informant_id`),
  ADD KEY `idx_intel_case` (`case_id`),
  ADD KEY `idx_intel_date` (`intelligence_date`);

--
-- Indexes for table `intelligence_bulletins`
--
ALTER TABLE `intelligence_bulletins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bulletin_number` (`bulletin_number`),
  ADD KEY `issued_by` (`issued_by`),
  ADD KEY `idx_bulletin_number` (`bulletin_number`),
  ADD KEY `idx_bulletin_type` (`bulletin_type`),
  ADD KEY `idx_bulletin_priority` (`priority`),
  ADD KEY `idx_bulletin_status` (`status`),
  ADD KEY `idx_bulletin_valid` (`valid_from`,`valid_until`);

--
-- Indexes for table `intelligence_reports`
--
ALTER TABLE `intelligence_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `report_number` (`report_number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `reviewed_by` (`reviewed_by`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_intel_report_number` (`report_number`),
  ADD KEY `idx_intel_type` (`report_type`),
  ADD KEY `idx_intel_classification` (`classification_level`),
  ADD KEY `idx_intel_status` (`status`),
  ADD KEY `idx_intel_date` (`report_date`);

--
-- Indexes for table `intelligence_report_distribution`
--
ALTER TABLE `intelligence_report_distribution`
  ADD PRIMARY KEY (`id`),
  ADD KEY `distributed_to_district_id` (`distributed_to_district_id`),
  ADD KEY `distributed_to_division_id` (`distributed_to_division_id`),
  ADD KEY `distributed_to_region_id` (`distributed_to_region_id`),
  ADD KEY `distributed_to_unit_id` (`distributed_to_unit_id`),
  ADD KEY `distributed_by` (`distributed_by`),
  ADD KEY `acknowledged_by` (`acknowledged_by`),
  ADD KEY `idx_intel_dist_report` (`report_id`),
  ADD KEY `idx_intel_dist_station` (`distributed_to_station_id`);

--
-- Indexes for table `investigation_milestones`
--
ALTER TABLE `investigation_milestones`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `missing_persons`
--
ALTER TABLE `missing_persons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `report_number` (`report_number`),
  ADD KEY `station_id` (`station_id`),
  ADD KEY `investigating_officer_id` (`investigating_officer_id`),
  ADD KEY `case_id` (`case_id`),
  ADD KEY `idx_missing_report` (`report_number`),
  ADD KEY `idx_missing_status` (`status`),
  ADD KEY `idx_missing_date` (`last_seen_date`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `case_id` (`case_id`),
  ADD KEY `idx_notifications_user` (`user_id`),
  ADD KEY `idx_notifications_read` (`is_read`),
  ADD KEY `idx_notifications_created` (`created_at`);

--
-- Indexes for table `officers`
--
ALTER TABLE `officers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `service_number` (`service_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_officers_service_number` (`service_number`),
  ADD KEY `idx_officers_rank` (`rank_id`),
  ADD KEY `idx_officers_station` (`current_station_id`),
  ADD KEY `idx_officers_district` (`current_district_id`),
  ADD KEY `idx_officers_division` (`current_division_id`),
  ADD KEY `idx_officers_region` (`current_region_id`),
  ADD KEY `idx_officers_unit` (`current_unit_id`),
  ADD KEY `idx_officers_status` (`employment_status`),
  ADD KEY `idx_officers_first_name` (`first_name`),
  ADD KEY `idx_officers_last_name` (`last_name`);

--
-- Indexes for table `officer_biometrics`
--
ALTER TABLE `officer_biometrics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `captured_by` (`captured_by`),
  ADD KEY `idx_officer_biometric` (`officer_id`),
  ADD KEY `idx_officer_biometric_type` (`biometric_type`),
  ADD KEY `idx_officer_biometric_status` (`verification_status`);

--
-- Indexes for table `officer_commendations`
--
ALTER TABLE `officer_commendations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_commendations_officer` (`officer_id`),
  ADD KEY `idx_commendations_date` (`award_date`);

--
-- Indexes for table `officer_disciplinary_records`
--
ALTER TABLE `officer_disciplinary_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `case_id` (`case_id`),
  ADD KEY `recorded_by` (`recorded_by`),
  ADD KEY `idx_disciplinary_officer` (`officer_id`),
  ADD KEY `idx_disciplinary_status` (`status`),
  ADD KEY `idx_disciplinary_date` (`incident_date`);

--
-- Indexes for table `officer_leave_records`
--
ALTER TABLE `officer_leave_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_leave_officer` (`officer_id`),
  ADD KEY `idx_leave_dates` (`start_date`,`end_date`),
  ADD KEY `idx_leave_status` (`leave_status`);

--
-- Indexes for table `officer_postings`
--
ALTER TABLE `officer_postings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `district_id` (`district_id`),
  ADD KEY `division_id` (`division_id`),
  ADD KEY `region_id` (`region_id`),
  ADD KEY `posted_by` (`posted_by`),
  ADD KEY `idx_postings_officer` (`officer_id`),
  ADD KEY `idx_postings_station` (`station_id`),
  ADD KEY `idx_postings_current` (`is_current`),
  ADD KEY `idx_postings_dates` (`start_date`,`end_date`);

--
-- Indexes for table `officer_promotions`
--
ALTER TABLE `officer_promotions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `from_rank_id` (`from_rank_id`),
  ADD KEY `to_rank_id` (`to_rank_id`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_promotions_officer` (`officer_id`),
  ADD KEY `idx_promotions_date` (`promotion_date`);

--
-- Indexes for table `officer_training`
--
ALTER TABLE `officer_training`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_training_officer` (`officer_id`),
  ADD KEY `idx_training_type` (`training_type`),
  ADD KEY `idx_training_dates` (`start_date`,`end_date`);

--
-- Indexes for table `operations`
--
ALTER TABLE `operations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `operation_code` (`operation_code`),
  ADD KEY `station_id` (`station_id`),
  ADD KEY `case_id` (`case_id`),
  ADD KEY `idx_operation_code` (`operation_code`),
  ADD KEY `idx_operation_date` (`operation_date`),
  ADD KEY `idx_operation_status` (`operation_status`),
  ADD KEY `idx_operation_commander` (`operation_commander_id`);

--
-- Indexes for table `operation_officers`
--
ALTER TABLE `operation_officers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_operation_team` (`operation_id`),
  ADD KEY `idx_officer_operations` (`officer_id`);

--
-- Indexes for table `patrol_incidents`
--
ALTER TABLE `patrol_incidents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `case_id` (`case_id`),
  ADD KEY `idx_patrol_incident` (`patrol_id`);

--
-- Indexes for table `patrol_logs`
--
ALTER TABLE `patrol_logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `patrol_number` (`patrol_number`),
  ADD KEY `patrol_leader_id` (`patrol_leader_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `idx_patrol_station` (`station_id`),
  ADD KEY `idx_patrol_date` (`start_time`),
  ADD KEY `idx_patrol_status` (`patrol_status`);

--
-- Indexes for table `patrol_officers`
--
ALTER TABLE `patrol_officers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_patrol_officers` (`patrol_id`),
  ADD KEY `idx_officer_patrols` (`officer_id`);

--
-- Indexes for table `persons`
--
ALTER TABLE `persons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `contact` (`contact`),
  ADD UNIQUE KEY `ghana_card_number` (`ghana_card_number`),
  ADD UNIQUE KEY `passport_number` (`passport_number`),
  ADD UNIQUE KEY `drivers_license` (`drivers_license`),
  ADD KEY `idx_person_first_name` (`first_name`),
  ADD KEY `idx_person_last_name` (`last_name`),
  ADD KEY `idx_person_ghana_card` (`ghana_card_number`),
  ADD KEY `idx_person_contact` (`contact`),
  ADD KEY `idx_person_passport` (`passport_number`),
  ADD KEY `idx_person_drivers_license` (`drivers_license`),
  ADD KEY `idx_person_criminal_record` (`has_criminal_record`),
  ADD KEY `idx_person_wanted` (`is_wanted`),
  ADD KEY `idx_person_risk` (`risk_level`);

--
-- Indexes for table `person_alerts`
--
ALTER TABLE `person_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `issued_by` (`issued_by`),
  ADD KEY `idx_alert_person` (`person_id`),
  ADD KEY `idx_alert_type` (`alert_type`),
  ADD KEY `idx_alert_active` (`is_active`),
  ADD KEY `idx_alert_priority` (`alert_priority`);

--
-- Indexes for table `person_aliases`
--
ALTER TABLE `person_aliases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_alias_person` (`person_id`),
  ADD KEY `idx_alias_name` (`alias_name`);

--
-- Indexes for table `person_criminal_history`
--
ALTER TABLE `person_criminal_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_history_person` (`person_id`),
  ADD KEY `idx_history_case` (`case_id`),
  ADD KEY `idx_history_type` (`involvement_type`),
  ADD KEY `idx_history_date` (`case_date`);

--
-- Indexes for table `person_relationships`
--
ALTER TABLE `person_relationships`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_relationship` (`person_id_1`,`person_id_2`,`relationship_type`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_person_1` (`person_id_1`),
  ADD KEY `idx_person_2` (`person_id_2`);

--
-- Indexes for table `police_ranks`
--
ALTER TABLE `police_ranks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rank_name` (`rank_name`),
  ADD UNIQUE KEY `rank_level` (`rank_level`);

--
-- Indexes for table `public_complaints`
--
ALTER TABLE `public_complaints`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `complaint_number` (`complaint_number`),
  ADD KEY `station_id` (`station_id`),
  ADD KEY `investigating_officer_id` (`investigating_officer_id`),
  ADD KEY `idx_complaint_number` (`complaint_number`),
  ADD KEY `idx_complaint_officer` (`officer_complained_against`),
  ADD KEY `idx_complaint_status` (`complaint_status`),
  ADD KEY `idx_complaint_date` (`complaint_date`);

--
-- Indexes for table `public_intelligence_tips`
--
ALTER TABLE `public_intelligence_tips`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tip_number` (`tip_number`),
  ADD KEY `case_id` (`case_id`),
  ADD KEY `intelligence_report_id` (`intelligence_report_id`),
  ADD KEY `idx_tip_number` (`tip_number`),
  ADD KEY `idx_tip_status` (`verification_status`),
  ADD KEY `idx_tip_assigned` (`assigned_to`),
  ADD KEY `idx_tip_date` (`received_at`);

--
-- Indexes for table `regions`
--
ALTER TABLE `regions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `region_name` (`region_name`),
  ADD UNIQUE KEY `region_code` (`region_code`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `sensitive_data_access_log`
--
ALTER TABLE `sensitive_data_access_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sensitive_user` (`user_id`),
  ADD KEY `idx_sensitive_table` (`table_name`),
  ADD KEY `idx_sensitive_time` (`access_time`),
  ADD KEY `idx_sensitive_granted` (`access_granted`);

--
-- Indexes for table `statements`
--
ALTER TABLE `statements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `suspect_id` (`suspect_id`),
  ADD KEY `witness_id` (`witness_id`),
  ADD KEY `complainant_id` (`complainant_id`),
  ADD KEY `recorded_by` (`recorded_by`),
  ADD KEY `idx_statements_case` (`case_id`),
  ADD KEY `idx_statements_type` (`statement_type`),
  ADD KEY `fk_statement_cancelled_by` (`cancelled_by`),
  ADD KEY `idx_statement_status` (`status`),
  ADD KEY `idx_parent_statement` (`parent_statement_id`);

--
-- Indexes for table `stations`
--
ALTER TABLE `stations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `station_code` (`station_code`),
  ADD KEY `idx_station_district` (`district_id`),
  ADD KEY `idx_station_division` (`division_id`),
  ADD KEY `idx_station_region` (`region_id`);

--
-- Indexes for table `surveillance_officers`
--
ALTER TABLE `surveillance_officers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `officer_id` (`officer_id`),
  ADD KEY `idx_surveillance_team` (`surveillance_id`);

--
-- Indexes for table `surveillance_operations`
--
ALTER TABLE `surveillance_operations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `operation_code` (`operation_code`),
  ADD KEY `operation_commander_id` (`operation_commander_id`),
  ADD KEY `station_id` (`station_id`),
  ADD KEY `case_id` (`case_id`),
  ADD KEY `intelligence_report_id` (`intelligence_report_id`),
  ADD KEY `idx_surveillance_code` (`operation_code`),
  ADD KEY `idx_surveillance_status` (`operation_status`),
  ADD KEY `idx_surveillance_date` (`start_date`);

--
-- Indexes for table `suspects`
--
ALTER TABLE `suspects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_suspect_person` (`person_id`),
  ADD KEY `idx_suspect_status` (`current_status`);

--
-- Indexes for table `suspect_biometrics`
--
ALTER TABLE `suspect_biometrics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `captured_by` (`captured_by`),
  ADD KEY `idx_biometric_suspect` (`suspect_id`),
  ADD KEY `idx_biometric_type` (`biometric_type`),
  ADD KEY `idx_biometric_status` (`verification_status`);

--
-- Indexes for table `suspect_status_history`
--
ALTER TABLE `suspect_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `case_id` (`case_id`),
  ADD KEY `changed_by` (`changed_by`),
  ADD KEY `idx_suspect_status_history` (`suspect_id`);

--
-- Indexes for table `temporary_permissions`
--
ALTER TABLE `temporary_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `granted_by` (`granted_by`),
  ADD KEY `revoked_by` (`revoked_by`),
  ADD KEY `idx_temp_perm_user` (`user_id`),
  ADD KEY `idx_temp_perm_expires` (`expires_at`),
  ADD KEY `idx_temp_perm_active` (`is_active`);

--
-- Indexes for table `threat_assessments`
--
ALTER TABLE `threat_assessments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `assessment_number` (`assessment_number`),
  ADD KEY `assessed_by` (`assessed_by`),
  ADD KEY `reviewed_by` (`reviewed_by`),
  ADD KEY `idx_threat_number` (`assessment_number`),
  ADD KEY `idx_threat_level` (`threat_level`),
  ADD KEY `idx_threat_type` (`threat_type`),
  ADD KEY `idx_threat_status` (`status`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_units_type` (`unit_type_id`),
  ADD KEY `idx_units_station` (`station_id`),
  ADD KEY `idx_units_district` (`district_id`),
  ADD KEY `idx_units_division` (`division_id`),
  ADD KEY `idx_units_region` (`region_id`),
  ADD KEY `idx_units_parent` (`parent_unit_id`),
  ADD KEY `idx_units_active` (`is_active`),
  ADD KEY `fk_units_head_officer` (`unit_head_officer_id`);

--
-- Indexes for table `unit_officer_assignments`
--
ALTER TABLE `unit_officer_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `idx_unit_assignments_unit` (`unit_id`),
  ADD KEY `idx_unit_assignments_officer` (`officer_id`),
  ADD KEY `idx_unit_assignments_current` (`is_current`),
  ADD KEY `idx_unit_assignments_dates` (`start_date`,`end_date`);

--
-- Indexes for table `unit_types`
--
ALTER TABLE `unit_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unit_type_name` (`unit_type_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `service_number` (`service_number`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `idx_users_station` (`station_id`),
  ADD KEY `idx_users_district` (`district_id`),
  ADD KEY `idx_users_division` (`division_id`),
  ADD KEY `idx_users_region` (`region_id`),
  ADD KEY `idx_users_status` (`status`),
  ADD KEY `idx_users_reset_token` (`password_reset_token`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `idx_session_token` (`session_token`),
  ADD KEY `idx_user_sessions` (`user_id`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `registration_number` (`registration_number`),
  ADD KEY `idx_vehicle_registration` (`registration_number`),
  ADD KEY `idx_vehicle_status` (`vehicle_status`),
  ADD KEY `idx_vehicle_case` (`case_id`);

--
-- Indexes for table `witnesses`
--
ALTER TABLE `witnesses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_witness_person` (`person_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ammunition_stock`
--
ALTER TABLE `ammunition_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `arrests`
--
ALTER TABLE `arrests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `asset_movements`
--
ALTER TABLE `asset_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bail_records`
--
ALTER TABLE `bail_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cases`
--
ALTER TABLE `cases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `case_assignments`
--
ALTER TABLE `case_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `case_crimes`
--
ALTER TABLE `case_crimes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_documents`
--
ALTER TABLE `case_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_investigation_checklist`
--
ALTER TABLE `case_investigation_checklist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `case_investigation_tasks`
--
ALTER TABLE `case_investigation_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_investigation_timeline`
--
ALTER TABLE `case_investigation_timeline`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_milestones`
--
ALTER TABLE `case_milestones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `case_referrals`
--
ALTER TABLE `case_referrals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_status_history`
--
ALTER TABLE `case_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `case_suspects`
--
ALTER TABLE `case_suspects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `case_updates`
--
ALTER TABLE `case_updates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `case_witnesses`
--
ALTER TABLE `case_witnesses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `charges`
--
ALTER TABLE `charges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `complainants`
--
ALTER TABLE `complainants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `court_proceedings`
--
ALTER TABLE `court_proceedings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `crime_categories`
--
ALTER TABLE `crime_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custody_records`
--
ALTER TABLE `custody_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `districts`
--
ALTER TABLE `districts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `divisions`
--
ALTER TABLE `divisions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `duty_roster`
--
ALTER TABLE `duty_roster`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `duty_shifts`
--
ALTER TABLE `duty_shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `evidence`
--
ALTER TABLE `evidence`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `evidence_custody_chain`
--
ALTER TABLE `evidence_custody_chain`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exhibits`
--
ALTER TABLE `exhibits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exhibit_movements`
--
ALTER TABLE `exhibit_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `firearms`
--
ALTER TABLE `firearms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `firearm_assignments`
--
ALTER TABLE `firearm_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `incident_reports`
--
ALTER TABLE `incident_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `informants`
--
ALTER TABLE `informants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `informant_intelligence`
--
ALTER TABLE `informant_intelligence`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `intelligence_bulletins`
--
ALTER TABLE `intelligence_bulletins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `intelligence_reports`
--
ALTER TABLE `intelligence_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `intelligence_report_distribution`
--
ALTER TABLE `intelligence_report_distribution`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `investigation_milestones`
--
ALTER TABLE `investigation_milestones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `missing_persons`
--
ALTER TABLE `missing_persons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `officers`
--
ALTER TABLE `officers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `officer_biometrics`
--
ALTER TABLE `officer_biometrics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `officer_commendations`
--
ALTER TABLE `officer_commendations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `officer_disciplinary_records`
--
ALTER TABLE `officer_disciplinary_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `officer_leave_records`
--
ALTER TABLE `officer_leave_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `officer_postings`
--
ALTER TABLE `officer_postings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `officer_promotions`
--
ALTER TABLE `officer_promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `officer_training`
--
ALTER TABLE `officer_training`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `operations`
--
ALTER TABLE `operations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `operation_officers`
--
ALTER TABLE `operation_officers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patrol_incidents`
--
ALTER TABLE `patrol_incidents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patrol_logs`
--
ALTER TABLE `patrol_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patrol_officers`
--
ALTER TABLE `patrol_officers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `persons`
--
ALTER TABLE `persons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `person_alerts`
--
ALTER TABLE `person_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `person_aliases`
--
ALTER TABLE `person_aliases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `person_criminal_history`
--
ALTER TABLE `person_criminal_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `person_relationships`
--
ALTER TABLE `person_relationships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `police_ranks`
--
ALTER TABLE `police_ranks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `public_complaints`
--
ALTER TABLE `public_complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `public_intelligence_tips`
--
ALTER TABLE `public_intelligence_tips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `regions`
--
ALTER TABLE `regions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `sensitive_data_access_log`
--
ALTER TABLE `sensitive_data_access_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `statements`
--
ALTER TABLE `statements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `stations`
--
ALTER TABLE `stations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `surveillance_officers`
--
ALTER TABLE `surveillance_officers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `surveillance_operations`
--
ALTER TABLE `surveillance_operations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suspects`
--
ALTER TABLE `suspects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `suspect_biometrics`
--
ALTER TABLE `suspect_biometrics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suspect_status_history`
--
ALTER TABLE `suspect_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `temporary_permissions`
--
ALTER TABLE `temporary_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `threat_assessments`
--
ALTER TABLE `threat_assessments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `unit_officer_assignments`
--
ALTER TABLE `unit_officer_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `unit_types`
--
ALTER TABLE `unit_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `witnesses`
--
ALTER TABLE `witnesses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ammunition_stock`
--
ALTER TABLE `ammunition_stock`
  ADD CONSTRAINT `ammunition_stock_ibfk_1` FOREIGN KEY (`station_id`) REFERENCES `stations` (`id`);

--
-- Constraints for table `arrests`
--
ALTER TABLE `arrests`
  ADD CONSTRAINT `arrests_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `arrests_ibfk_2` FOREIGN KEY (`suspect_id`) REFERENCES `suspects` (`id`),
  ADD CONSTRAINT `arrests_ibfk_3` FOREIGN KEY (`arresting_officer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `assets_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`);

--
-- Constraints for table `asset_movements`
--
ALTER TABLE `asset_movements`
  ADD CONSTRAINT `asset_movements_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `asset_movements_ibfk_2` FOREIGN KEY (`moved_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `audit_logs_ibfk_2` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`),
  ADD CONSTRAINT `audit_logs_ibfk_3` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`),
  ADD CONSTRAINT `audit_logs_ibfk_4` FOREIGN KEY (`suspect_id`) REFERENCES `suspects` (`id`),
  ADD CONSTRAINT `audit_logs_ibfk_5` FOREIGN KEY (`evidence_id`) REFERENCES `evidence` (`id`);

--
-- Constraints for table `bail_records`
--
ALTER TABLE `bail_records`
  ADD CONSTRAINT `bail_records_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bail_records_ibfk_2` FOREIGN KEY (`suspect_id`) REFERENCES `suspects` (`id`),
  ADD CONSTRAINT `bail_records_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `cases`
--
ALTER TABLE `cases`
  ADD CONSTRAINT `cases_ibfk_1` FOREIGN KEY (`complainant_id`) REFERENCES `complainants` (`id`),
  ADD CONSTRAINT `cases_ibfk_2` FOREIGN KEY (`station_id`) REFERENCES `stations` (`id`),
  ADD CONSTRAINT `cases_ibfk_3` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`),
  ADD CONSTRAINT `cases_ibfk_4` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`),
  ADD CONSTRAINT `cases_ibfk_5` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`),
  ADD CONSTRAINT `cases_ibfk_6` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `case_assignments`
--
ALTER TABLE `case_assignments`
  ADD CONSTRAINT `case_assignments_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `case_assignments_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `case_assignments_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `case_crimes`
--
ALTER TABLE `case_crimes`
  ADD CONSTRAINT `case_crimes_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `case_crimes_ibfk_2` FOREIGN KEY (`crime_category_id`) REFERENCES `crime_categories` (`id`),
  ADD CONSTRAINT `case_crimes_ibfk_3` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `case_documents`
--
ALTER TABLE `case_documents`
  ADD CONSTRAINT `case_documents_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `case_documents_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `case_investigation_checklist`
--
ALTER TABLE `case_investigation_checklist`
  ADD CONSTRAINT `case_investigation_checklist_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `case_investigation_checklist_ibfk_2` FOREIGN KEY (`completed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `case_investigation_tasks`
--
ALTER TABLE `case_investigation_tasks`
  ADD CONSTRAINT `case_investigation_tasks_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `case_investigation_tasks_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `officers` (`id`),
  ADD CONSTRAINT `case_investigation_tasks_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_tasks_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `case_investigation_timeline`
--
ALTER TABLE `case_investigation_timeline`
  ADD CONSTRAINT `case_investigation_timeline_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `case_investigation_timeline_ibfk_2` FOREIGN KEY (`milestone_id`) REFERENCES `investigation_milestones` (`id`),
  ADD CONSTRAINT `case_investigation_timeline_ibfk_3` FOREIGN KEY (`completed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `case_milestones`
--
ALTER TABLE `case_milestones`
  ADD CONSTRAINT `case_milestones_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `case_milestones_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `case_referrals`
--
ALTER TABLE `case_referrals`
  ADD CONSTRAINT `case_referrals_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `case_referrals_ibfk_2` FOREIGN KEY (`from_station_id`) REFERENCES `stations` (`id`),
  ADD CONSTRAINT `case_referrals_ibfk_3` FOREIGN KEY (`to_station_id`) REFERENCES `stations` (`id`),
  ADD CONSTRAINT `case_referrals_ibfk_4` FOREIGN KEY (`referred_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `case_status_history`
--
ALTER TABLE `case_status_history`
  ADD CONSTRAINT `case_status_history_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `case_status_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `case_suspects`
--
ALTER TABLE `case_suspects`
  ADD CONSTRAINT `case_suspects_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `case_suspects_ibfk_2` FOREIGN KEY (`suspect_id`) REFERENCES `suspects` (`id`);

--
-- Constraints for table `case_updates`
--
ALTER TABLE `case_updates`
  ADD CONSTRAINT `case_updates_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `case_updates_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `case_witnesses`
--
ALTER TABLE `case_witnesses`
  ADD CONSTRAINT `case_witnesses_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `case_witnesses_ibfk_2` FOREIGN KEY (`witness_id`) REFERENCES `witnesses` (`id`);

--
-- Constraints for table `charges`
--
ALTER TABLE `charges`
  ADD CONSTRAINT `charges_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `charges_ibfk_2` FOREIGN KEY (`suspect_id`) REFERENCES `suspects` (`id`),
  ADD CONSTRAINT `charges_ibfk_3` FOREIGN KEY (`charged_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `complainants`
--
ALTER TABLE `complainants`
  ADD CONSTRAINT `complainants_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`);

--
-- Constraints for table `court_proceedings`
--
ALTER TABLE `court_proceedings`
  ADD CONSTRAINT `court_proceedings_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `court_proceedings_ibfk_2` FOREIGN KEY (`suspect_id`) REFERENCES `suspects` (`id`),
  ADD CONSTRAINT `court_proceedings_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `crime_categories`
--
ALTER TABLE `crime_categories`
  ADD CONSTRAINT `crime_categories_ibfk_1` FOREIGN KEY (`parent_category_id`) REFERENCES `crime_categories` (`id`);

--
-- Constraints for table `custody_records`
--
ALTER TABLE `custody_records`
  ADD CONSTRAINT `custody_records_ibfk_1` FOREIGN KEY (`suspect_id`) REFERENCES `suspects` (`id`),
  ADD CONSTRAINT `custody_records_ibfk_2` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `custody_records_ibfk_3` FOREIGN KEY (`released_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `districts`
--
ALTER TABLE `districts`
  ADD CONSTRAINT `districts_ibfk_1` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`);

--
-- Constraints for table `divisions`
--
ALTER TABLE `divisions`
  ADD CONSTRAINT `divisions_ibfk_1` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`);

--
-- Constraints for table `duty_roster`
--
ALTER TABLE `duty_roster`
  ADD CONSTRAINT `duty_roster_ibfk_1` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`),
  ADD CONSTRAINT `duty_roster_ibfk_2` FOREIGN KEY (`station_id`) REFERENCES `stations` (`id`),
  ADD CONSTRAINT `duty_roster_ibfk_3` FOREIGN KEY (`shift_id`) REFERENCES `duty_shifts` (`id`),
  ADD CONSTRAINT `duty_roster_ibfk_4` FOREIGN KEY (`supervisor_id`) REFERENCES `officers` (`id`),
  ADD CONSTRAINT `duty_roster_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `evidence`
--
ALTER TABLE `evidence`
  ADD CONSTRAINT `evidence_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evidence_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `evidence_custody_chain`
--
ALTER TABLE `evidence_custody_chain`
  ADD CONSTRAINT `evidence_custody_chain_ibfk_1` FOREIGN KEY (`evidence_id`) REFERENCES `evidence` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evidence_custody_chain_ibfk_2` FOREIGN KEY (`transferred_from`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `evidence_custody_chain_ibfk_3` FOREIGN KEY (`transferred_to`) REFERENCES `users` (`id`);

--
-- Constraints for table `exhibits`
--
ALTER TABLE `exhibits`
  ADD CONSTRAINT `exhibits_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`),
  ADD CONSTRAINT `exhibits_ibfk_2` FOREIGN KEY (`seized_by`) REFERENCES `officers` (`id`);

--
-- Constraints for table `exhibit_movements`
--
ALTER TABLE `exhibit_movements`
  ADD CONSTRAINT `exhibit_movements_ibfk_1` FOREIGN KEY (`exhibit_id`) REFERENCES `exhibits` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exhibit_movements_ibfk_2` FOREIGN KEY (`moved_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `exhibit_movements_ibfk_3` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `firearms`
--
ALTER TABLE `firearms`
  ADD CONSTRAINT `firearms_ibfk_1` FOREIGN KEY (`current_holder_id`) REFERENCES `officers` (`id`),
  ADD CONSTRAINT `firearms_ibfk_2` FOREIGN KEY (`station_id`) REFERENCES `stations` (`id`);

--
-- Constraints for table `firearm_assignments`
--
ALTER TABLE `firearm_assignments`
  ADD CONSTRAINT `firearm_assignments_ibfk_1` FOREIGN KEY (`firearm_id`) REFERENCES `firearms` (`id`),
  ADD CONSTRAINT `firearm_assignments_ibfk_2` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`),
  ADD CONSTRAINT `firearm_assignments_ibfk_3` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `incident_reports`
--
ALTER TABLE `incident_reports`
  ADD CONSTRAINT `incident_reports_ibfk_1` FOREIGN KEY (`station_id`) REFERENCES `stations` (`id`),
  ADD CONSTRAINT `incident_reports_ibfk_2` FOREIGN KEY (`attending_officer_id`) REFERENCES `officers` (`id`),
  ADD CONSTRAINT `incident_reports_ibfk_3` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`);

--
-- Constraints for table `informants`
--
ALTER TABLE `informants`
  ADD CONSTRAINT `informants_ibfk_1` FOREIGN KEY (`handler_officer_id`) REFERENCES `officers` (`id`),
  ADD CONSTRAINT `informants_ibfk_2` FOREIGN KEY (`station_id`) REFERENCES `stations` (`id`);

--
-- Constraints for table `informant_intelligence`
--
ALTER TABLE `informant_intelligence`
  ADD CONSTRAINT `informant_intelligence_ibfk_1` FOREIGN KEY (`informant_id`) REFERENCES `informants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `informant_intelligence_ibfk_2` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`),
  ADD CONSTRAINT `informant_intelligence_ibfk_3` FOREIGN KEY (`handler_officer_id`) REFERENCES `officers` (`id`);

--
-- Constraints for table `intelligence_bulletins`
--
ALTER TABLE `intelligence_bulletins`
  ADD CONSTRAINT `intelligence_bulletins_ibfk_1` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `intelligence_reports`
--
ALTER TABLE `intelligence_reports`
  ADD CONSTRAINT `intelligence_reports_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `intelligence_reports_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `intelligence_reports_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `intelligence_report_distribution`
--
ALTER TABLE `intelligence_report_distribution`
  ADD CONSTRAINT `intelligence_report_distribution_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `intelligence_reports` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `intelligence_report_distribution_ibfk_2` FOREIGN KEY (`distributed_to_station_id`) REFERENCES `stations` (`id`),
  ADD CONSTRAINT `intelligence_report_distribution_ibfk_3` FOREIGN KEY (`distributed_to_district_id`) REFERENCES `districts` (`id`),
  ADD CONSTRAINT `intelligence_report_distribution_ibfk_4` FOREIGN KEY (`distributed_to_division_id`) REFERENCES `divisions` (`id`),
  ADD CONSTRAINT `intelligence_report_distribution_ibfk_5` FOREIGN KEY (`distributed_to_region_id`) REFERENCES `regions` (`id`),
  ADD CONSTRAINT `intelligence_report_distribution_ibfk_6` FOREIGN KEY (`distributed_to_unit_id`) REFERENCES `units` (`id`),
  ADD CONSTRAINT `intelligence_report_distribution_ibfk_7` FOREIGN KEY (`distributed_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `intelligence_report_distribution_ibfk_8` FOREIGN KEY (`acknowledged_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `missing_persons`
--
ALTER TABLE `missing_persons`
  ADD CONSTRAINT `missing_persons_ibfk_1` FOREIGN KEY (`station_id`) REFERENCES `stations` (`id`),
  ADD CONSTRAINT `missing_persons_ibfk_2` FOREIGN KEY (`investigating_officer_id`) REFERENCES `officers` (`id`),
  ADD CONSTRAINT `missing_persons_ibfk_3` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `officers`
--
ALTER TABLE `officers`
  ADD CONSTRAINT `fk_officers_district` FOREIGN KEY (`current_district_id`) REFERENCES `districts` (`id`),
  ADD CONSTRAINT `fk_officers_division` FOREIGN KEY (`current_division_id`) REFERENCES `divisions` (`id`),
  ADD CONSTRAINT `fk_officers_region` FOREIGN KEY (`current_region_id`) REFERENCES `regions` (`id`),
  ADD CONSTRAINT `fk_officers_station` FOREIGN KEY (`current_station_id`) REFERENCES `stations` (`id`),
  ADD CONSTRAINT `fk_officers_unit` FOREIGN KEY (`current_unit_id`) REFERENCES `units` (`id`),
  ADD CONSTRAINT `officers_ibfk_1` FOREIGN KEY (`rank_id`) REFERENCES `police_ranks` (`id`),
  ADD CONSTRAINT `officers_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `officer_biometrics`
--
ALTER TABLE `officer_biometrics`
  ADD CONSTRAINT `officer_biometrics_ibfk_1` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `officer_biometrics_ibfk_2` FOREIGN KEY (`captured_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `officer_commendations`
--
ALTER TABLE `officer_commendations`
  ADD CONSTRAINT `officer_commendations_ibfk_1` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `officer_disciplinary_records`
--
ALTER TABLE `officer_disciplinary_records`
  ADD CONSTRAINT `officer_disciplinary_records_ibfk_1` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `officer_disciplinary_records_ibfk_2` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`),
  ADD CONSTRAINT `officer_disciplinary_records_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `officer_leave_records`
--
ALTER TABLE `officer_leave_records`
  ADD CONSTRAINT `officer_leave_records_ibfk_1` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `officer_leave_records_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `officer_postings`
--
ALTER TABLE `officer_postings`
  ADD CONSTRAINT `officer_postings_ibfk_1` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `officer_postings_ibfk_2` FOREIGN KEY (`station_id`) REFERENCES `stations` (`id`),
  ADD CONSTRAINT `officer_postings_ibfk_3` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`),
  ADD CONSTRAINT `officer_postings_ibfk_4` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`),
  ADD CONSTRAINT `officer_postings_ibfk_5` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`),
  ADD CONSTRAINT `officer_postings_ibfk_6` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `officer_promotions`
--
ALTER TABLE `officer_promotions`
  ADD CONSTRAINT `officer_promotions_ibfk_1` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `officer_promotions_ibfk_2` FOREIGN KEY (`from_rank_id`) REFERENCES `police_ranks` (`id`),
  ADD CONSTRAINT `officer_promotions_ibfk_3` FOREIGN KEY (`to_rank_id`) REFERENCES `police_ranks` (`id`),
  ADD CONSTRAINT `officer_promotions_ibfk_4` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `officer_training`
--
ALTER TABLE `officer_training`
  ADD CONSTRAINT `officer_training_ibfk_1` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `operations`
--
ALTER TABLE `operations`
  ADD CONSTRAINT `operations_ibfk_1` FOREIGN KEY (`operation_commander_id`) REFERENCES `officers` (`id`),
  ADD CONSTRAINT `operations_ibfk_2` FOREIGN KEY (`station_id`) REFERENCES `stations` (`id`),
  ADD CONSTRAINT `operations_ibfk_3` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`);

--
-- Constraints for table `operation_officers`
--
ALTER TABLE `operation_officers`
  ADD CONSTRAINT `operation_officers_ibfk_1` FOREIGN KEY (`operation_id`) REFERENCES `operations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `operation_officers_ibfk_2` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`);

--
-- Constraints for table `patrol_incidents`
--
ALTER TABLE `patrol_incidents`
  ADD CONSTRAINT `patrol_incidents_ibfk_1` FOREIGN KEY (`patrol_id`) REFERENCES `patrol_logs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `patrol_incidents_ibfk_2` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`);

--
-- Constraints for table `patrol_logs`
--
ALTER TABLE `patrol_logs`
  ADD CONSTRAINT `patrol_logs_ibfk_1` FOREIGN KEY (`station_id`) REFERENCES `stations` (`id`),
  ADD CONSTRAINT `patrol_logs_ibfk_2` FOREIGN KEY (`patrol_leader_id`) REFERENCES `officers` (`id`),
  ADD CONSTRAINT `patrol_logs_ibfk_3` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`);

--
-- Constraints for table `patrol_officers`
--
ALTER TABLE `patrol_officers`
  ADD CONSTRAINT `patrol_officers_ibfk_1` FOREIGN KEY (`patrol_id`) REFERENCES `patrol_logs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `patrol_officers_ibfk_2` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`);

--
-- Constraints for table `person_alerts`
--
ALTER TABLE `person_alerts`
  ADD CONSTRAINT `person_alerts_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `person_alerts_ibfk_2` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `person_aliases`
--
ALTER TABLE `person_aliases`
  ADD CONSTRAINT `person_aliases_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `person_criminal_history`
--
ALTER TABLE `person_criminal_history`
  ADD CONSTRAINT `person_criminal_history_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `person_criminal_history_ibfk_2` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`);

--
-- Constraints for table `person_relationships`
--
ALTER TABLE `person_relationships`
  ADD CONSTRAINT `person_relationships_ibfk_1` FOREIGN KEY (`person_id_1`) REFERENCES `persons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `person_relationships_ibfk_2` FOREIGN KEY (`person_id_2`) REFERENCES `persons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `person_relationships_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `public_complaints`
--
ALTER TABLE `public_complaints`
  ADD CONSTRAINT `public_complaints_ibfk_1` FOREIGN KEY (`officer_complained_against`) REFERENCES `officers` (`id`),
  ADD CONSTRAINT `public_complaints_ibfk_2` FOREIGN KEY (`station_id`) REFERENCES `stations` (`id`),
  ADD CONSTRAINT `public_complaints_ibfk_3` FOREIGN KEY (`investigating_officer_id`) REFERENCES `officers` (`id`);

--
-- Constraints for table `public_intelligence_tips`
--
ALTER TABLE `public_intelligence_tips`
  ADD CONSTRAINT `public_intelligence_tips_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `public_intelligence_tips_ibfk_2` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`),
  ADD CONSTRAINT `public_intelligence_tips_ibfk_3` FOREIGN KEY (`intelligence_report_id`) REFERENCES `intelligence_reports` (`id`);

--
-- Constraints for table `sensitive_data_access_log`
--
ALTER TABLE `sensitive_data_access_log`
  ADD CONSTRAINT `sensitive_data_access_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `statements`
--
ALTER TABLE `statements`
  ADD CONSTRAINT `fk_statement_cancelled_by` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_statement_parent` FOREIGN KEY (`parent_statement_id`) REFERENCES `statements` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `statements_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `statements_ibfk_2` FOREIGN KEY (`suspect_id`) REFERENCES `suspects` (`id`),
  ADD CONSTRAINT `statements_ibfk_3` FOREIGN KEY (`witness_id`) REFERENCES `witnesses` (`id`),
  ADD CONSTRAINT `statements_ibfk_4` FOREIGN KEY (`complainant_id`) REFERENCES `complainants` (`id`),
  ADD CONSTRAINT `statements_ibfk_5` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `stations`
--
ALTER TABLE `stations`
  ADD CONSTRAINT `stations_ibfk_1` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`);

--
-- Constraints for table `surveillance_officers`
--
ALTER TABLE `surveillance_officers`
  ADD CONSTRAINT `surveillance_officers_ibfk_1` FOREIGN KEY (`surveillance_id`) REFERENCES `surveillance_operations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `surveillance_officers_ibfk_2` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`);

--
-- Constraints for table `surveillance_operations`
--
ALTER TABLE `surveillance_operations`
  ADD CONSTRAINT `surveillance_operations_ibfk_1` FOREIGN KEY (`operation_commander_id`) REFERENCES `officers` (`id`),
  ADD CONSTRAINT `surveillance_operations_ibfk_2` FOREIGN KEY (`station_id`) REFERENCES `stations` (`id`),
  ADD CONSTRAINT `surveillance_operations_ibfk_3` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`),
  ADD CONSTRAINT `surveillance_operations_ibfk_4` FOREIGN KEY (`intelligence_report_id`) REFERENCES `intelligence_reports` (`id`);

--
-- Constraints for table `suspects`
--
ALTER TABLE `suspects`
  ADD CONSTRAINT `suspects_person_fk` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `suspect_biometrics`
--
ALTER TABLE `suspect_biometrics`
  ADD CONSTRAINT `suspect_biometrics_ibfk_1` FOREIGN KEY (`suspect_id`) REFERENCES `suspects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `suspect_biometrics_ibfk_2` FOREIGN KEY (`captured_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `suspect_status_history`
--
ALTER TABLE `suspect_status_history`
  ADD CONSTRAINT `suspect_status_history_ibfk_1` FOREIGN KEY (`suspect_id`) REFERENCES `suspects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `suspect_status_history_ibfk_2` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `suspect_status_history_ibfk_3` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `temporary_permissions`
--
ALTER TABLE `temporary_permissions`
  ADD CONSTRAINT `temporary_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `temporary_permissions_ibfk_2` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `temporary_permissions_ibfk_3` FOREIGN KEY (`revoked_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `threat_assessments`
--
ALTER TABLE `threat_assessments`
  ADD CONSTRAINT `threat_assessments_ibfk_1` FOREIGN KEY (`assessed_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `threat_assessments_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `units`
--
ALTER TABLE `units`
  ADD CONSTRAINT `fk_units_head_officer` FOREIGN KEY (`unit_head_officer_id`) REFERENCES `officers` (`id`),
  ADD CONSTRAINT `units_ibfk_1` FOREIGN KEY (`unit_type_id`) REFERENCES `unit_types` (`id`),
  ADD CONSTRAINT `units_ibfk_2` FOREIGN KEY (`station_id`) REFERENCES `stations` (`id`),
  ADD CONSTRAINT `units_ibfk_3` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`),
  ADD CONSTRAINT `units_ibfk_4` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`),
  ADD CONSTRAINT `units_ibfk_5` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`),
  ADD CONSTRAINT `units_ibfk_6` FOREIGN KEY (`parent_unit_id`) REFERENCES `units` (`id`);

--
-- Constraints for table `unit_officer_assignments`
--
ALTER TABLE `unit_officer_assignments`
  ADD CONSTRAINT `fk_unit_assignments_officer` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `unit_officer_assignments_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `unit_officer_assignments_ibfk_2` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_district` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`),
  ADD CONSTRAINT `fk_users_division` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`),
  ADD CONSTRAINT `fk_users_region` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`),
  ADD CONSTRAINT `fk_users_station` FOREIGN KEY (`station_id`) REFERENCES `stations` (`id`),
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`);

--
-- Constraints for table `witnesses`
--
ALTER TABLE `witnesses`
  ADD CONSTRAINT `witnesses_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
