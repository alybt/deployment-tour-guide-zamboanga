-- Get the actual country_ID for Philippines (don't assume it's 161)
SET @ph_country_id = (SELECT country_ID FROM Country WHERE country_name = 'Philippines' LIMIT 1);

-- Insert Philippine Regions using the actual country_ID
INSERT INTO Region (region_name, country_ID) VALUES
('Region I: Ilocos Region', @ph_country_id),
('Region II: Cagayan Valley', @ph_country_id),
('Region III: Central Luzon', @ph_country_id),
('Region IV-A: CALABARZON', @ph_country_id),
('Region IV-B: MIMAROPA', @ph_country_id),
('Region V: Bicol Region', @ph_country_id),
('Region VI: Western Visayas', @ph_country_id),
('Region VII: Central Visayas', @ph_country_id),
('Region VIII: Eastern Visayas', @ph_country_id),
('Region IX: Zamboanga Peninsula', @ph_country_id),
('Region X: Northern Mindanao', @ph_country_id),
('Region XI: Davao Region', @ph_country_id),
('Region XII: SOCCSKSARGEN', @ph_country_id),
('Region XIII: Caraga', @ph_country_id),
('NCR: National Capital Region', @ph_country_id),
('CAR: Cordillera Administrative Region', @ph_country_id),
('BARMM: Bangsamoro Autonomous Region in Muslim Mindanao', @ph_country_id);