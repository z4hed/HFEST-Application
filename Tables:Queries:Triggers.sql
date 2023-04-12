--*******************************************--
-----------------------------------------------
----------------TABLE CREATIONS----------------
-----------------------------------------------
--*******************************************--

CREATE TABLE employee (
    ID INT PRIMARY KEY,
    first_name VARCHAR(255),
    last_name VARCHAR(255),
    date_of_birth DATE,
    phone INT,
    address VARCHAR(255),
    city VARCHAR(255),
    province VARCHAR(255),
    postal_code VARCHAR(45),
    citizenship VARCHAR(255),
    email VARCHAR(255),
    -- modifications made here for medicare
    medicare INT NOT NULL,
    UNIQUE (medicare),
    -- modifications made here for role
    role ENUM('nurse', 'doctor', 'cashier', 'pharmacist', 'receptionist', 'administrative personnel', 'security personnel', 'regular employee') NOT NULL
);

CREATE TABLE facility (
    ID INT PRIMARY KEY,
    name VARCHAR(255),
    address VARCHAR(255),
    city VARCHAR(255),
    province VARCHAR(255),
    postal_code VARCHAR(45),
    phone INT,
    web_address VARCHAR(255),
    -- modifications made here for type of facility
    type ENUM('hospital', 'CLSC', 'clinic', 'pharmacy', 'special installment'),
    capacity INT,
    -- modifications made here for manager
    manager VARCHAR(255) NOT NULL,
    -- modifications made here for current employee count
    current_employee_count INT DEFAULT 0
);

CREATE TABLE vaccination (
    ID INT PRIMARY KEY,
    name VARCHAR(255),
    -- modifications made here for type
    type ENUM('Pfizer', 'Moderna', 'AstraZeneca', 'Johnson & Johnson', 'Other') NOT NULL,
    date_of_vaccine DATE,
    location VARCHAR(255),
    -- modifications made here for dose number
    dose INT CHECK (dose >= 1),
    employee_ID INT,
    FOREIGN KEY (employee_ID) REFERENCES employee(ID),
    facility_ID INT,
    FOREIGN KEY (facility_ID) REFERENCES facility(ID)
);

CREATE TABLE infection (
    ID INT PRIMARY KEY,
    employee_ID INT,
    FOREIGN KEY (employee_ID) REFERENCES employee(ID),
    facility_ID INT,
    FOREIGN KEY (facility_ID) REFERENCES facility(ID),
    -- modifications made here for type
    type ENUM('COVID-19', 'SARS-Cov-2 Variant', 'Other') NOT NULL,
    date_of_infection DATE
);

CREATE TABLE workHistory (
    ID INT PRIMARY KEY,
    employee_ID INT,
    FOREIGN KEY (employee_ID) REFERENCES employee(ID),
    facility_ID INT,
    FOREIGN KEY (facility_ID) REFERENCES facility(ID),
    start_date DATE,
    end_date DATE,
    -- modifications made here for unique insertion of work history
    UNIQUE (employee_ID, facility_ID, start_date)
);

--modifications made here to create new schedule table 
CREATE TABLE schedule (
    ID INT PRIMARY KEY,
    employee_ID INT,
    FOREIGN KEY (employee_ID) REFERENCES employee(ID),
    facility_ID INT,
    FOREIGN KEY (facility_ID) REFERENCES facility(ID),
    date DATE,
    start_time TIME,
    end_time TIME
);


--*******************************************--
-----------------------------------------------
---------------TRIGGER CREATIONS---------------
-----------------------------------------------
--*******************************************--


-- Create triggers to update employee count in each facility to make sure
-- capacity is not reached. Error message if we try to insert an employee
-- when facility is at capacity
CREATE TRIGGER update_employee_count_after_insert
AFTER INSERT ON workHistory
FOR EACH ROW
BEGIN
DECLARE current_count INT;
    IF NEW.end_date IS NULL THEN
        SELECT current_employee_count INTO current_count
        FROM facility
        WHERE ID = NEW.facility_ID;

        IF current_count >= (SELECT capacity FROM facility WHERE ID = NEW.facility_ID) THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Facility capacity reached. Cannot add more employees.';
        ELSE
            UPDATE facility
            SET current_employee_count = current_count + 1
            WHERE ID = NEW.facility_ID;
        END IF;
    END IF;
END;

CREATE TRIGGER update_employee_count_after_update
AFTER UPDATE ON workHistory
FOR EACH ROW
BEGIN
    DECLARE current_count INT;
    IF OLD.end_date IS NULL AND NEW.end_date IS NOT NULL THEN
        

        SELECT current_employee_count INTO current_count
        FROM facility
        WHERE ID = NEW.facility_ID;

        UPDATE facility
        SET current_employee_count = current_count - 1
        WHERE ID = NEW.facility_ID;
    END IF;
END;


CREATE TRIGGER update_employee_count_after_delete
AFTER DELETE ON workHistory
FOR EACH ROW
BEGIN
    UPDATE facility
    SET current_employee_count = current_employee_count - 1
    WHERE ID = OLD.facility_ID;
END;

--Trigger for covid infection cancel assignments
CREATE TRIGGER cancel_assignments
AFTER INSERT ON infection
FOR EACH ROW
BEGIN
    IF NEW.type = 'COVID-19' AND (SELECT role FROM employee WHERE ID = NEW.employee_ID) IN ('doctor', 'nurse') THEN
        UPDATE schedule
        SET start_time = NULL, end_time = NULL
        WHERE employee_ID = NEW.employee_ID AND facility_ID = NEW.facility_ID AND date BETWEEN NEW.date_of_infection AND DATE_ADD(NEW.date_of_infection, INTERVAL 14 DAY);
    END IF;
END;

-- create trigger to check if administrative personnel is manager
CREATE TRIGGER check_administrative_personnel
BEFORE INSERT ON employee
FOR EACH ROW
BEGIN
    DECLARE facility_manager INT;
    IF NEW.role = 'administrative personnel' THEN
        
        SET facility_manager = (
            SELECT employee_ID FROM workHistory 
            WHERE facility_ID = (
                SELECT ID FROM facility WHERE manager = CONCAT(NEW.first_name, ' ', NEW.last_name)
            ) AND end_date IS NULL
        );
        IF NEW.ID <> facility_manager THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Error: The employee must be the manager of a facility to have the role of administrative personnel.';
        END IF;
    END IF;
END;

-- create trigger to make sure there are no conflicts in schedule
CREATE TRIGGER no_schedule_conflicts_trigger
BEFORE INSERT ON schedule
FOR EACH ROW
BEGIN
    IF EXISTS (
        SELECT employee_ID, date, start_time, end_time 
        FROM schedule 
        WHERE employee_ID = NEW.employee_ID
            AND date = NEW.date
            AND ((start_time <= NEW.start_time AND end_time > NEW.start_time) 
                 OR (start_time < NEW.end_time AND end_time >= NEW.end_time))
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Schedule conflicts with existing schedule.';
    END IF;
END;

--create trigger to check if employee who has new schedule also has valid work history in facility
CREATE TRIGGER check_valid_workhistory
BEFORE INSERT ON schedule
FOR EACH ROW
BEGIN
    IF NOT EXISTS (
        SELECT * FROM workHistory 
        WHERE employee_ID = NEW.employee_ID
            AND facility_ID = NEW.facility_ID
            AND start_date <= NEW.date 
            AND (end_date IS NULL OR end_date >= NEW.date)
    ) THEN
        SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Invalid workhistory for employee.';
    END IF;
END;

-- create trigger to check that schedule start time is smaller than end time
CREATE TRIGGER check_start_time_before_end_time
BEFORE INSERT ON schedule
FOR EACH ROW
BEGIN
    IF NEW.start_time >= NEW.end_time THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Start time must be before end time';
    END IF;
END;

-- create trigger to make sure each employee with 2 schedules in 1 day
-- has at least 1 hour break in between
CREATE TRIGGER min_schedule_interval_trigger
BEFORE INSERT ON schedule
FOR EACH ROW
BEGIN
  IF EXISTS (
      SELECT 1 FROM schedule s2
      WHERE s2.employee_ID = NEW.employee_ID
      AND s2.ID <> NEW.ID
      AND s2.date = NEW.date
      AND (
          (NEW.start_time BETWEEN s2.start_time AND s2.end_time) 
          OR (NEW.end_time BETWEEN s2.start_time AND s2.end_time) 
          OR (NEW.start_time <= s2.start_time AND NEW.end_time >= s2.end_time)
      )
      AND ABS(TIMESTAMPDIFF(MINUTE, s2.end_time, NEW.start_time)) < 60
  ) THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'The minimum schedule interval has not been met.';
  END IF;
END;

-- create trigger to not schedule employees with covid for 2 weeks
CREATE TRIGGER tr_schedule_two_weeks_gap
BEFORE INSERT ON schedule
FOR EACH ROW
BEGIN
    IF EXISTS (
        SELECT * FROM infection 
        WHERE employee_ID = NEW.employee_ID 
        AND type IN ('COVID-19', 'SARS-Cov-2 Variant')
        AND date_of_infection BETWEEN NEW.date AND DATE_ADD(NEW.date, INTERVAL 14 DAY)
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'At least 2 weeks gap after infection is required';
    END IF;
END;

-- create trigger to not schedule employees not vaccinated within 6 months
CREATE TRIGGER tr_schedule_vaccinated_within_six_months
BEFORE INSERT ON schedule
FOR EACH ROW
BEGIN
    IF NOT EXISTS (
        SELECT * FROM vaccination
        WHERE vaccination.employee_ID = NEW.employee_ID
            AND vaccination.date_of_vaccine >= DATE_SUB(NEW.date, INTERVAL 6 MONTH)
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Employee must be vaccinated within six months';
    END IF;
END;


--*******************************************--
-----------------------------------------------
-------------POPULATION OF TABLES--------------
-----------------------------------------------
--*******************************************--


INSERT INTO employee (ID, first_name, last_name, date_of_birth, phone, address, city, province, postal_code, citizenship, email, medicare, role)
VALUES
(1, 'John', 'Doe', '1990-01-01', 1234567890, '123 Main St', 'Toronto', 'ON', 'A1B 2C3', 'Canada', 'johndoe@email.com', 123456780, 'doctor'),
(2, 'Jane', 'Doe', '1992-02-02', 234567890, '456 Maple Ave', 'Montreal', 'QC', 'D4E 5F6', 'Canada', 'janedoe@email.com', 234567893, 'nurse'),
(3, 'Bob', 'Smith', '1995-03-03', 345678901, '789 Oak St', 'Vancouver', 'BC', 'G7H 8I9', 'Canada', 'bobsmith@email.com', 345678904, 'cashier'),
(4, 'Samantha', 'Johnson', '1998-04-04', 456789012, '321 Elm St', 'Ottawa', 'ON', 'J1K 2L3', 'Canada', 'sjohnson@email.com', 456789015, 'pharmacist'),
(5, 'William', 'Garcia', '2000-05-05', 567890123, '654 Pine Ave', 'Calgary', 'AB', 'M2N 3P4', 'Canada', 'wgarcia@email.com', 567890158, 'receptionist'),
(6, 'Emily', 'Davis', '1989-06-06', 678901234, '987 Cedar St', 'Winnipeg', 'MB', 'R3T 4X2', 'Canada', 'edavis@email.com', 678901239, 'administrative personnel'),
(7, 'Michael', 'Taylor', '1991-07-07', 789012345, '246 Birch St', 'Halifax', 'NS', 'B2V 1B1', 'Canada', 'mtaylor@email.com', 789012342, 'security personnel'),
(8, 'Stephanie', 'Anderson', '1994-08-08', 890123456, '135 Oak St', 'Edmonton', 'AB', 'T5J 3Z8', 'Canada', 'sanderson@email.com', 890120453, 'regular employee'),
(9, 'David', 'Lee', '1997-09-09', 901234567, '864 Maple Ave', 'Regina', 'SK', 'S4P 3Y2', 'Canada', 'dlee@email.com', 901234563, 'doctor'),
(10, 'Melissa', 'Baker', '1999-10-10', 123450987, '753 Pine Ave', 'Victoria', 'BC', 'V8W 1W2', 'Canada', 'mbaker@email.com', 123450986, 'nurse'),
(11, 'Christopher', 'Gonzalez', '1988-11-11', 234561098, '159 Elm St', 'Quebec City', 'QC', 'G1R 5E5', 'Canada', 'cgonzalez@email.com', 234561094, 'cashier'),
(12, 'Ashley', 'Brown', '1992-12-12', 345678901, '246 Maple St', 'Toronto', 'ON', 'M1H 3G8', 'Canada', 'abrown@email.com', 345678903, 'cashier'),
(13, 'Daniel', 'Johnson', '1995-01-13', 456789012, '753 Cedar Ave', 'Montreal', 'QC', 'H3A 0B8', 'Canada', 'djohnson@email.com', 456789027, 'pharmacist'),
(14, 'Amanda', 'Williams', '1998-02-14', 567890123, '951 Oak St', 'Vancouver', 'BC', 'V6B 2W5', 'Canada', 'awilliams@email.com', 567890127, 'receptionist'),
(15, 'Brandon', 'Davis', '2000-03-15', 678901234, '159 Pine St', 'Ottawa', 'ON', 'K2P 0B2', 'Canada', 'bdavis@email.com', 678901230, 'administrative personnel'),
(16, 'Sophia', 'Jackson', '1989-04-16', 789012345, '357 Cedar Ave', 'Calgary', 'AB', 'T2P 4K8', 'Canada', 'sjackson@email.com', 789012349, 'security personnel'),
(17, 'William', 'Gonzalez', '1991-05-17', 890123456, '852 Oak St', 'Winnipeg', 'MB', 'R3B 2G6', 'Canada', 'wgonzalez@email.com', 890123453, 'regular employee'),
(18, 'Mia', 'Anderson', '1994-06-18', 901234567, '357 Maple St', 'Halifax', 'NS', 'B3K 2A7', 'Canada', 'manderson@email.com', 901234557, 'doctor'),
(19, 'Gabriel', 'Wilson', '1997-07-19', 123450987, '753 Pine Ave', 'Edmonton', 'AB', 'T5J 3M6', 'Canada', 'gwilson@email.com', 123460987, 'nurse'),
(20, 'Madison', 'Taylor', '1999-08-20', 234561098, '159 Cedar St', 'Regina', 'SK', 'S4S 0A4', 'Canada', 'mtaylor@email.com', 234551098, 'cashier'),
(21, 'Ethan', 'Johnson', '1988-09-21', 345672109, '753 Oak St', 'Victoria', 'BC', 'V8W 1W3', 'Canada', 'ejohnson@email.com', 345682109, 'pharmacist'),
(22, 'Olivia', 'Brown', '1993-10-22', 456789012, '246 Cedar St', 'Toronto', 'ON', 'M5H 2N2', 'Canada', 'obrown@email.com', 456789714, 'nurse'),
(23, 'Jacob', 'Garcia', '1996-11-23', 567890123, '753 Maple Ave', 'Montreal', 'QC', 'H3B 4G7', 'Canada', 'jgarcia@email.com', 567892123, 'doctor'),
(24, 'Isabella', 'Martinez', '1999-12-24', 678901234, '951 Pine St', 'Vancouver', 'BC', 'V6C 0B8', 'Canada', 'imartinez@email.com', 678941234, 'cashier'),
(25, 'Noah', 'Robinson', '2001-01-25', 789012345, '357 Cedar Ave', 'Ottawa', 'ON', 'K1N 9G4', 'Canada', 'nrobinson@email.com', 789052345, 'pharmacist'),
(26, 'Emily', 'Clark', '1990-02-26', 890123456, '852 Oak St', 'Calgary', 'AB', 'T2P 5C5', 'Canada', 'eclark@email.com', 890523456, 'receptionist'),
(27, 'Liam', 'Rodriguez', '1992-03-27', 901234567, '159 Cedar St', 'Winnipeg', 'MB', 'R3C 3J3', 'Canada', 'lrodriguez@email.com', 901244567, 'regular employee'),
(28, 'Sofia', 'Lewis', '1995-04-28', 123450987, '357 Pine Ave', 'Halifax', 'NS', 'B3L 1K7', 'Canada', 'slewis@email.com', 123450987, 'doctor'),
(29, 'Michael', 'Lee', '1998-05-29', 234561098, '753 Maple St', 'Edmonton', 'AB', 'T5J 1X6', 'Canada', 'mlee@email.com', 234761098, 'nurse'),
(30, 'Charlotte', 'Wright', '2000-06-30', 345672109, '159 Oak St', 'Regina', 'SK', 'S4N 0A9', 'Canada', 'cwright@email.com', 345872109, 'cashier'),
(31, 'Abigail', 'Adams', '1992-07-01', 456789012, '246 Maple St', 'Toronto', 'ON', 'M5H 2N2', 'Canada', 'aadams@email.com', 456799018, 'administrative personnel'),
(32, 'Ethan', 'Jackson', '1993-08-02', 567890123, '753 Pine Ave', 'Montreal', 'QC', 'H3B 4G7', 'Canada', 'ejackson@email.com', 567890023, 'administrative personnel'),
(33, 'Madison', 'Johnson', '1994-09-03', 678901234, '951 Oak St', 'Vancouver', 'BC', 'V6C 0B8', 'Canada', 'mjohnson@email.com', 678101254, 'administrative personnel'),
(34, 'William', 'Smith', '1995-10-04', 789012345, '357 Maple St', 'Ottawa', 'ON', 'K1N 9G4', 'Canada', 'wsmith@email.com', 789012145, 'administrative personnel'),
(35, 'Olivia', 'Martinez', '1980-04-15', 112233445, '123 Elm St', 'Vancouver', 'BC', 'V5K 0A1', 'Canada', 'omartinez@email.com', 111223345, 'doctor'),
(36, 'Sophia', 'Brown', '1982-06-10', 223344556, '456 Birch St', 'Calgary', 'AB', 'T2P 1J3', 'Canada', 'sbrown@email.com', 222334456, 'doctor'),
(37, 'Ava', 'Green', '1984-08-05', 334455667, '789 Cedar Ave', 'Toronto', 'ON', 'M4V 1E8', 'Canada', 'agreen@email.com', 333445567, 'doctor'),
(38, 'Isabella', 'Turner', '1986-10-20', 445566778, '321 Oak St', 'Montreal', 'QC', 'H3G 1J1', 'Canada', 'iturner@email.com', 444556678, 'doctor'),
(39, 'Mia', 'Harris', '1988-12-25', 556677889, '654 Pine St', 'Ottawa', 'ON', 'K1P 5P6', 'Canada', 'mharris@email.com', 555667789, 'doctor'),
(40, 'Emma', 'Wilson', '1990-02-18', 667788990, '23 Maple St', 'Edmonton', 'AB', 'T5H 2H1', 'Canada', 'ewilson@email.com', 666778890, 'nurse'),
(41, 'Lucy', 'Thompson', '1992-03-22', 778899001, '47 Beech St', 'Winnipeg', 'MB', 'R3C 0Y1', 'Canada', 'lthompson@email.com', 777889901, 'nurse'),
(42, 'Grace', 'Anderson', '1994-07-28', 889900112, '1096 Willow Rd', 'Halifax', 'NS', 'B3H 2L1', 'Canada', 'ganderson@email.com', 888990012, 'nurse'),
(43, 'Lily', 'Taylor', '1996-11-12', 990011223, '2889 Cherry Ave', 'Victoria', 'BC', 'V8T 3W3', 'Canada', 'ltaylor@email.com', 999001123, 'nurse'),
(44, 'Chloe', 'Jackson', '1998-01-30', 112233334, '72 Walnut St', 'Quebec City', 'QC', 'G1K 3W3', 'Canada', 'cjackson@email.com', 111223334, 'nurse');

DELETE FROM employee;
DELETE FROM schedule;
DELETE FROM facility;
DELETE FROM vaccination;
DELETE FROM infection;
DELETE FROM workHistory;

INSERT INTO facility (ID, name, address, city, province, postal_code, phone, web_address, type, capacity, manager, current_employee_count) VALUES
(1, 'Green Hospital', '1234 Green Street', 'Montreal', 'Quebec', 'H1A 1A1', 514555123, 'www.greenhospital.com', 'hospital', 20, 'William Smith', 0),
(2, 'Red CLSC', '5678 Red Street', 'Montreal', 'Quebec', 'H2B 2B2', 514555567, 'www.redclsc.com', 'CLSC', 20, 'Madison Johnson', 0),
(3, 'Blue Clinic', '9012 Blue Street', 'Montreal', 'Quebec', 'H3C 3C3', 514555901, 'www.blueclinic.com', 'clinic', 10, 'Ethan Jackson', 0),
(4, 'Yellow Pharmacy', '3456 Yellow Street', 'Montreal', 'Quebec', 'H4D 4D4', 514555345, 'www.yellowpharmacy.com', 'pharmacy', 10, 'Abigail Adams', 0),
(5, 'Green Installment', '7890 Green Street', 'Montreal', 'Quebec', 'H5E 5E5', 514555789, 'www.greeninstallment.com', 'special installment', 10, 'Brandon Davis', 0),
(6, 'Red Hospital', '1234 Red Street', 'Toronto', 'Ontario', 'M1A 1A1', 416555123, 'www.redhospital.com', 'hospital', 10, 'Emily Davis', 0);

INSERT INTO vaccination (ID, name, type, date_of_vaccine, location, dose, employee_ID, facility_ID) VALUES
(1, 'John Doe', 'Pfizer', '2023-02-01', 'Green Hospital', 1, 1, 1),
(2, 'John Doe', 'Pfizer', '2023-03-01', 'Green Hospital', 2, 1, 1),
(3, 'Jane Doe', 'Moderna', '2023-02-15', 'Red CLSC', 1, 2, 2),
(4, 'Bob Smith', 'AstraZeneca', '2022-06-01', 'Blue Clinic', 1, 3, 3),
(5, 'Bob Smith', 'AstraZeneca', '2022-08-01', 'Blue Clinic', 2, 3, 3),
(6, 'Samantha Johnson', 'Johnson & Johnson', '2022-10-01', 'Yellow Pharmacy', 1, 4, 4),
(7, 'William Garcia', 'Pfizer', '2023-01-01', 'Green Installment', 1, 5, 5),
(8, 'William Garcia', 'Pfizer', '2023-01-30', 'Green Installment', 2, 5, 5),
(9, 'Emily Davis', 'Moderna', '2023-03-15', 'Red Hospital', 1, 6, 6),
(10, 'Michael Taylor', 'AstraZeneca', '2023-02-01', 'Red Hospital', 1, 7, 6),
(11, 'Stephanie Anderson', 'Pfizer', '2022-12-15', 'Red Hospital', 1, 8, 6),
(12, 'Stephanie Anderson', 'Pfizer', '2023-01-15', 'Yellow Pharmacy', 2, 8, 4),
(13, 'David Lee', 'Johnson & Johnson', '2023-02-28', 'Blue Clinic', 1, 9, 3),
(14, 'Melissa Baker', 'Pfizer', '2023-03-01', 'Red CLSC', 1, 10, 2),
(15, 'Christopher Gonzalez', 'Moderna', '2022-12-01', 'Green Hospital', 1, 11, 1),
(16, 'Ashley Brown', 'AstraZeneca', '2022-11-01', 'Green Installment', 1, 12, 5),
(17, 'Ashley Brown', 'AstraZeneca', '2022-12-01', 'Red Hospital', 2, 12, 6),
(18, 'Daniel Johnson', 'Pfizer', '2022-11-15', 'Yellow Pharmacy', 1, 13, 4),
(19, 'Amanda Williams', 'Moderna', '2022-12-15', 'Red CLSC', 1, 14, 2),
(20, 'Brandon Davis', 'Johnson & Johnson', '2023-03-15', 'Yellow Pharmacy', 1, 15, 4),
(21, 'Mia Anderson', 'Pfizer', '2022-09-10', 'Green Hospital', 1, 18, 1),
(22, 'Gabriel Wilson', 'Moderna', '2022-09-15', 'Red CLSC', 1, 19, 2),
(23, 'Madison Taylor', 'AstraZeneca', '2022-09-20', 'Blue Clinic', 1, 20, 3),
(24, 'Ethan Johnson', 'Johnson & Johnson', '2022-09-25', 'Yellow Pharmacy', 1, 21, 4),
(25, 'Olivia Brown', 'Pfizer', '2022-10-01', 'Green Installment', 1, 22, 5),
(26, 'Jacob Garcia', 'Moderna', '2022-10-05', 'Red Hospital', 1, 23, 6),
(27, 'Isabella Martinez', 'AstraZeneca', '2022-10-10', 'Green Hospital', 1, 24, 1),
(28, 'Noah Robinson', 'Johnson & Johnson', '2022-10-15', 'Red CLSC', 1, 25, 2),
(29, 'Emily Clark', 'Pfizer', '2022-10-20', 'Blue Clinic', 1, 26, 3),
(30, 'Liam Rodriguez', 'Moderna', '2022-10-25', 'Yellow Pharmacy', 1, 27, 4),
(31, 'Sofia Lewis', 'AstraZeneca', '2022-11-01', 'Green Installment', 1, 28, 5),
(32, 'Michael Lee', 'Johnson & Johnson', '2022-11-05', 'Red Hospital', 1, 29, 6),
(33, 'Charlotte Wright', 'Pfizer', '2022-11-10', 'Green Hospital', 1, 30, 1),
(34, 'Abigail Adams', 'Moderna', '2022-11-15', 'Red CLSC', 1, 31, 2),
(35, 'Ethan Jackson', 'AstraZeneca', '2022-11-20', 'Blue Clinic', 1, 32, 3),
(36, 'Madison Johnson', 'Johnson & Johnson', '2022-11-25', 'Yellow Pharmacy', 1, 33, 4),
(37, 'William Smith', 'Pfizer', '2022-12-01', 'Green Installment', 1, 34, 5),
(38, 'Emma Wilson', 'Pfizer', '2023-02-15', 'Green Hospital', 1, 40, 1),
(39, 'Emma Wilson', 'Pfizer', '2023-03-15', 'Green Hospital', 2, 40, 1),
(40, 'Lucy Thompson', 'Moderna', '2023-02-20', 'Red CLSC', 1, 41, 2),
(41, 'Lucy Thompson', 'Moderna', '2023-03-20', 'Red CLSC', 2, 41, 2),
(42, 'Grace Anderson', 'AstraZeneca', '2022-11-01', 'Blue Clinic', 1, 42, 3),
(43, 'Grace Anderson', 'AstraZeneca', '2022-12-01', 'Blue Clinic', 2, 42, 3),
(44, 'Lily Taylor', 'Pfizer', '2023-01-14', 'Yellow Hospital', 1, 43, 4),
(45, 'Lily Taylor', 'Pfizer', '2023-02-14', 'Yellow Hospital', 2, 43, 4),
(46, 'Chloe Jackson', 'Moderna', '2023-03-05', 'Red CLSC', 1, 44, 5);



INSERT INTO workHistory (id, employee_id, facility_id, start_date, end_date)
VALUES
  (1, 1, 1, '2020-03-01', '2022-01-20'),
  (2, 1, 1, '2022-02-01', NULL),
  (3, 2, 1, '2020-06-10', NULL),
  (4, 3, 2, '2021-04-15', NULL),
  (5, 4, 2, '2020-08-20', NULL),
  (6, 5, 2, '2021-01-05', NULL),
  (7, 7, 2, '2020-11-01', '2021-09-30'),
  (8, 7, 2, '2021-11-01', '2022-09-30'),
  (9, 7, 2, '2023-02-15', NULL),
  (10, 8, 2, '2022-01-01', NULL),
  (11, 9, 1, '2020-05-01', '2021-04-30'),
  (12, 9, 1, '2021-05-15', NULL),
  (13, 10, 1, '2021-07-01', NULL),
  (14, 11, 1, '2020-11-11', '2022-05-20'),
  (15, 11, 3, '2022-11-11', NULL),
  (16, 12, 3, '2021-05-01', NULL),
  (17, 13, 3, '2020-02-14', '2021-05-30'),
  (18, 13, 3, '2022-02-14', NULL),
  (19, 14, 3, '2020-12-12', '2021-11-15'),
  (20, 14, 3, '2022-03-15', NULL),
  (21, 16, 3, '2020-10-16', NULL),
  (22, 17, 4, '2021-05-17', '2023-02-28'),
  (23, 18, 4, '2020-08-08', NULL),
  (24, 19, 4, '2020-09-09', NULL),
  (25, 20, 5, '2020-11-20', NULL),
  (26, 21, 5, '2021-07-01', NULL),
  (27, 22, 5, '2021-01-01', NULL),
  (28, 23, 6, '2020-05-15', NULL),
  (29, 24, 6, '2021-04-04', '2022-03-03'),
  (30, 25, 6, '2020-10-10', '2021-09-09'),
  (31, 26, 1, '2020-06-01', '2020-12-31'),
  (32, 27, 1, '2020-10-01', '2021-06-30'),
  (33, 28, 1, '2021-07-01', NULL),
  (34, 29, 2, '2020-09-01', '2021-02-28'),
  (35, 29, 2, '2021-09-01', '2022-02-28'),
  (36, 29, 1, '2022-09-01', NULL),
  (37, 30, 3, '2021-03-01', '2021-08-31'),
  (38, 30, 3, '2022-03-01', NULL),
  (39, 24, 1, '2023-02-01', NULL),
  (40, 25, 1, '2022-09-01', NULL),
  (41, 26, 1, '2022-01-01', NULL),
  (42, 27, 1, '2022-02-01', NULL),
  (43, 6, 6, '2022-04-01', NULL),
  (44, 15, 5, '2022-10-01', NULL),
  (45, 31, 4, '2022-07-01', NULL),
  (46, 32, 3, '2023-01-01', NULL),
  (47, 33, 2, '2023-01-01', NULL),
  (48, 34, 1, '2023-02-01', NULL),
  (49, 35, 5, '2022-10-01', NULL),
  (50, 36, 4, '2022-07-01', NULL),
  (51, 37, 3, '2023-01-01', NULL),
  (52, 38, 2, '2023-01-01', NULL),
  (53, 39, 1, '2023-02-01', NULL),
  (54, 40, 6, '2021-01-01', NULL),
  (55, 41, 5, '2021-01-15', NULL),
  (56, 42, 5, '2021-05-10', NULL),
  (57, 43, 5, '2021-01-07', NULL),
  (58, 44, 5, '2021-03-01', NULL);


INSERT INTO schedule (ID, employee_ID, facility_ID, date, start_time, end_time)
VALUES
  (1, 1, 1, '2023-04-03', '09:00:00', '17:00:00'),
  (2, 1, 1, '2023-04-04', '09:00:00', '17:00:00'),
  (3, 1, 1, '2023-04-05', '09:00:00', '17:00:00'),
  (4, 2, 1, '2023-04-03', '13:00:00', '21:00:00'),
  (5, 2, 1, '2023-04-04', '13:00:00', '21:00:00'),
  (6, 2, 1, '2023-04-05', '13:00:00', '21:00:00'),
  (7, 9, 1, '2023-04-06', '08:00:00', '16:00:00'),
  (8, 9, 1, '2023-04-07', '08:00:00', '16:00:00'),
  (9, 9, 1, '2023-04-03', '08:00:00', '16:00:00'),
  (10, 10, 1, '2023-04-10', '10:00:00', '18:00:00'),
  (11, 10, 1, '2023-04-11', '10:00:00', '18:00:00'),
  (12, 10, 1, '2023-04-12', '10:00:00', '18:00:00'),
  (13, 1, 1, '2023-04-06', '09:00:00', '17:00:00'),
  (14, 1, 1, '2023-04-07', '09:00:00', '17:00:00'),
  (15, 1, 1, '2023-04-08', '09:00:00', '17:00:00'),
  (16, 10, 1, '2023-04-03', '09:00:00', '17:00:00'),
  (17, 28, 1, '2023-04-03', '09:00:00', '17:00:00'),
  (18, 29, 1, '2023-04-03', '09:00:00', '17:00:00'),
  (19, 24, 1, '2023-04-07', '09:00:00', '17:00:00'),
  (20, 25, 1, '2023-04-08', '09:00:00', '17:00:00'),
  (21, 26, 1, '2023-04-03', '09:00:00', '17:00:00'),
  (22, 27, 1, '2023-04-03', '09:00:00', '17:00:00'),
  (23, 34, 1, '2023-04-03', '09:00:00', '17:00:00');
  

INSERT INTO infection (ID, employee_ID, facility_ID, type, date_of_infection)
VALUES (1, 35, 5, 'COVID-19', '2023-04-01'),
       (2, 36, 4, 'COVID-19', '2023-04-02'),
       (3, 37, 3, 'COVID-19', '2023-04-01'),
       (4, 38, 2, 'COVID-19', '2023-04-02'),
       (5, 39, 1, 'COVID-19', '2023-04-03'),
       (6, 40, 1, 'COVID-19', '2021-11-20'),
        (7, 40, 1, 'COVID-19', '2021-12-10'),
        (8, 41, 2, 'COVID-19', '2021-09-05'),
        (9, 41, 2, 'COVID-19', '2021-10-10'),
        (10, 41, 2, 'COVID-19', '2021-11-30'),
        (11, 42, 3, 'COVID-19', '2021-08-01'),
        (12, 42, 3, 'COVID-19', '2021-09-10'),
        (13, 42, 3, 'COVID-19', '2021-10-25'),
        (14, 43, 4, 'COVID-19', '2021-07-20'),
        (15, 43, 4, 'COVID-19', '2021-08-15'),
        (16, 43, 4, 'COVID-19', '2021-09-30'),
        (17, 44, 5, 'COVID-19', '2021-06-05'),
        (18, 44, 5, 'COVID-19', '2021-07-10'),
        (19, 44, 5, 'COVID-19', '2021-08-20');


--*******************************************--
-----------------------------------------------
-------------------QUERIES---------------------
-----------------------------------------------
--*******************************************--

-- 6
SELECT
    f.name AS facility_name,
    f.address AS facility_address,
    f.city AS facility_city,
    f.province AS facility_province,
    f.postal_code AS facility_postal_code,
    f.phone AS facility_phone,
    f.web_address AS facility_web_address,
    f.type AS facility_type,
    f.capacity AS facility_capacity,
    f.manager AS facility_manager,
    f.current_employee_count AS facility_employee_count
FROM
    facility f
ORDER BY
    f.province ASC,
    f.city ASC,
    f.type ASC,
    f.current_employee_count ASC;


-- 7
SELECT e.first_name, e.last_name, wh.start_date, e.date_of_birth, e.medicare,
       e.phone, e.address, e.city, e.province, e.postal_code, e.citizenship, e.email
FROM employee e
JOIN workHistory wh ON e.ID = wh.employee_ID
WHERE wh.facility_ID = 1 -- change id of facility depending
  AND wh.end_date IS NULL
ORDER BY e.role ASC, e.first_name ASC, e.last_name ASC;

-- 8
SELECT
    f.name AS facility_name,
    s.date AS day_of_year,
    s.start_time AS start_time,
    s.end_time AS end_time
FROM
    schedule s
JOIN facility f ON s.facility_ID = f.ID
WHERE
-- here the employee and dates are chosen, change as you like
    s.employee_ID = 1 AND
    s.date BETWEEN '2021-09-06' AND '2023-04-15'
ORDER BY
    f.name ASC,
    s.date ASC,
    s.start_time ASC;

--9
SELECT e.first_name, e.last_name, i.date_of_infection, f.name AS facility_name
FROM employee e
JOIN infection i ON e.ID = i.employee_ID
JOIN workHistory wh ON e.ID = wh.employee_ID
JOIN facility f ON wh.facility_ID = f.ID
WHERE e.role = 'doctor'
    AND i.type = 'COVID-19'
    AND i.date_of_infection BETWEEN DATE_SUB(CURDATE(), INTERVAL 2 WEEK) AND CURDATE()
    AND (wh.end_date IS NULL OR wh.end_date > CURDATE())
ORDER BY f.name ASC, e.first_name ASC;

-- 11
SELECT DISTINCT e.first_name, e.last_name, e.role
FROM employee e
JOIN schedule s ON e.ID = s.employee_ID
WHERE s.facility_ID = 1 -- change id of facility depending
    AND s.date BETWEEN DATE_SUB(CURDATE(), INTERVAL 2 WEEK) AND CURDATE()
    AND (e.role = 'doctor' OR e.role = 'nurse')
ORDER BY e.role ASC, e.first_name ASC;

-- 12
SELECT
    e.role AS employee_role,
    SUM(TIMESTAMPDIFF(HOUR, s.start_time, s.end_time)) AS total_hours
FROM
    schedule s
JOIN employee e ON s.employee_ID = e.ID
WHERE
    -- here the facility id and dates are chosen, change as you like
    s.facility_ID = 1 AND
    s.date BETWEEN '2021-01-01' AND '2023-04-15'
GROUP BY
    e.role
ORDER BY
    e.role ASC;


-- 13
SELECT f.province, f.name AS facility_name, f.capacity, COUNT(i.employee_ID) AS infected_employees
FROM facility f
LEFT JOIN workHistory wh ON f.ID = wh.facility_ID
LEFT JOIN employee e ON wh.employee_ID = e.ID
LEFT JOIN infection i ON e.ID = i.employee_ID
    AND i.type = 'COVID-19'
    AND i.date_of_infection BETWEEN DATE_SUB(CURDATE(), INTERVAL 2 WEEK) AND CURDATE()
GROUP BY f.ID
ORDER BY f.province ASC, infected_employees ASC;

--14
SELECT
    e.first_name AS doctor_first_name,
    e.last_name AS doctor_last_name,
    e.city AS doctor_city,
    COUNT(DISTINCT wh.facility_ID) AS total_facilities
FROM
    employee e
JOIN workHistory wh ON e.ID = wh.employee_ID
JOIN facility f ON wh.facility_ID = f.ID
WHERE
    e.role = 'doctor' AND
    f.province = 'Quebec' AND
    wh.end_date IS NULL
GROUP BY
    e.ID
ORDER BY
    e.city ASC,
    total_facilities DESC;

-- 15
WITH nurse_schedule AS (
    SELECT e.ID, e.first_name, e.last_name, e.date_of_birth, e.email, 
           MIN(wh.start_date) AS first_day_of_work_as_nurse, 
           SUM(TIMESTAMPDIFF(HOUR, s.start_time, s.end_time)) AS total_hours_scheduled
    FROM employee e
    JOIN workHistory wh ON e.ID = wh.employee_ID AND e.role = 'nurse'
    JOIN schedule s ON e.ID = s.employee_ID
    GROUP BY e.ID
),
max_hours AS (
    SELECT MAX(total_hours_scheduled) AS max_hours_scheduled
    FROM nurse_schedule
)
SELECT ns.first_name, ns.last_name, ns.first_day_of_work_as_nurse, ns.date_of_birth, ns.email, ns.total_hours_scheduled
FROM nurse_schedule ns
JOIN max_hours mh ON ns.total_hours_scheduled = mh.max_hours_scheduled;

-- 16
SELECT
    e.first_name,
    e.last_name,
    MIN(wh.start_date) AS first_day_of_work,
    e.role,
    e.date_of_birth,
    e.email,
    COUNT(DISTINCT i.ID) as infection_count
FROM
    employee e
JOIN workHistory wh ON e.ID = wh.employee_ID
JOIN infection i ON e.ID = i.employee_ID
WHERE
    e.role IN ('nurse', 'doctor') AND
    wh.end_date IS NULL AND
    i.type = 'COVID-19'
GROUP BY
    e.ID
HAVING
    COUNT(DISTINCT i.ID) >= 3
ORDER BY
    e.role ASC,
    e.first_name ASC,
    e.last_name ASC;


-- 17
SELECT
    e.first_name,
    e.last_name,
    MIN(wh.start_date) AS first_day_of_work,
    e.role,
    e.date_of_birth,
    e.email,
    SUM(TIMESTAMPDIFF(HOUR, s.start_time, s.end_time)) AS total_hours_scheduled
FROM
    employee e
JOIN workHistory wh ON e.ID = wh.employee_ID
JOIN schedule s ON e.ID = s.employee_ID
LEFT JOIN infection i ON e.ID = i.employee_ID AND i.type = 'COVID-19'
WHERE
    e.role IN ('nurse', 'doctor') AND
    wh.end_date IS NULL
GROUP BY
    e.ID
HAVING
    COUNT(DISTINCT i.ID) = 0
ORDER BY
    e.role ASC,
    e.first_name ASC,
    e.last_name ASC;