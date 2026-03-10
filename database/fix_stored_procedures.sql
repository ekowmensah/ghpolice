-- ==============================
-- FIX STORED PROCEDURES COLLATION
-- ==============================
-- Recreate stored procedures with explicit collation
-- ==============================

USE ghpims;

DROP PROCEDURE IF EXISTS sp_register_person;
DROP PROCEDURE IF EXISTS sp_check_person_criminal_record;

DELIMITER $$

-- Procedure to register or update person (prevents duplicates)
CREATE PROCEDURE sp_register_person(
    IN p_first_name VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    IN p_middle_name VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    IN p_last_name VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    IN p_gender VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    IN p_date_of_birth DATE,
    IN p_contact VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    IN p_email VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    IN p_address TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    IN p_ghana_card VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    IN p_passport VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    IN p_drivers_license VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    OUT p_person_id INT,
    OUT p_is_duplicate BOOLEAN,
    OUT p_duplicate_message TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
)
BEGIN
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

-- Procedure to check if person exists and return criminal history (INSTANT CRIME CHECK)
CREATE PROCEDURE sp_check_person_criminal_record(
    IN p_ghana_card VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    IN p_contact VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    IN p_passport VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    IN p_drivers_license VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    IN p_first_name VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    IN p_last_name VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
)
BEGIN
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

DELIMITER ;

SELECT 'Stored procedures recreated with correct collation!' as status;
