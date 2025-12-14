-- Sample Name Info
INSERT INTO Name_Info (name_first, name_last)
VALUES 
('Alice', 'Admin'),
('Greg', 'Guide'),
('Tina', 'Tourist');

-- Sample Address (Barangay_ID should exist — assume ID = 1)
INSERT INTO Address_Info (address_houseno, address_street, barangay_ID)
VALUES
('123', 'Main Street', 1),
('456', 'Palm Avenue', 1),
('789', 'Sunset Blvd', 1);

-- Sample Phone (Country_ID should exist — assume ID = 1)
INSERT INTO Phone_Number (country_ID, phone_number)
VALUES
(1, '9123456789'),
(1, '9234567890'),
(1, '9345678901');

-- Sample Emergency Info
INSERT INTO Emergency_Info (emergency_Name, emergency_Relationship)
VALUES 
('Bob Admin', 'Brother'),
('Megan Guide', 'Sister'),
('Lara Tourist', 'Friend');

-- Sample Contact Info
INSERT INTO Contact_Info (address_ID, phone_ID, contactinfo_email, emergency_ID)
VALUES
(1, 1, 'alice.admin@email.com', 1),
(2, 2, 'greg.guide@email.com', 2),
(3, 3, 'tina.tourist@email.com', 3);

-- Now link them in Person
INSERT INTO Person (name_ID, contactinfo_ID, person_Nationality, person_Gender, person_DateOfBirth)
VALUES
(1, 1, 'Filipino', 'Female', '1985-05-10'),
(2, 2, 'Filipino', 'Male', '1990-07-20'),
(3, 3, 'Filipino', 'Female', '2000-11-15');


INSERT INTO User_Login (person_ID, user_username, user_password)
VALUES
(1, 'admin01', 'admin123'),
(2, 'guide01', 'guide123'),
(3, 'tourist01', 'tourist123');
-- Admin
INSERT INTO Account_Info (user_ID, role_ID, account_status)
VALUES (1, 1, 'Active');

-- Guide
INSERT INTO Account_Info (user_ID, role_ID, account_status)
VALUES (2, 2, 'Active');

-- Tourist
INSERT INTO Account_Info (user_ID, role_ID, account_status)
VALUES (3, 3, 'Active');

INSERT INTO Admin (account_ID)
VALUES (1);
INSERT INTO Guide_License (
    lisence_number, lisence_created_date, lisence_issued_date, lisence_issued_by, 
    lisence_expiry_date, lisence_verification_status, lisence_status
)
VALUES (
    'GL-2025-001', '2025-01-01', '2025-01-05', 'Department of Tourism',
    '2026-01-05', 'Verified', 'Active'
);

INSERT INTO Guide (account_ID, lisence_ID)
VALUES (2, 1);

INSERT INTO Guide_Languages (guide_ID, languages_ID)
VALUES (1, 1), (1, 3);
