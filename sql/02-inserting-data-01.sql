
-- Insert default roles
INSERT INTO Role(role_name) VALUES 
('Admin'),
('Tour Guide'),
('Tourist')
ON DUPLICATE KEY UPDATE role_name = VALUES(role_name);

-- Insert default payment methods
INSERT INTO Method_Category (methodcategory_name, methodcategory_type, methodcategory_processing_fee) VALUES
('Credit Card', 'card', 2.50),
('Debit Card', 'card', 2.50),
('GCash', 'ewallet', 1.00),
('PayMaya', 'ewallet', 1.00),
('Bank Transfer', 'bank', 0.00),
('Cash', 'cash', 0.00)
ON DUPLICATE KEY UPDATE methodcategory_name = VALUES(methodcategory_name);

INSERT IGNORE INTO Tour_Spots(spots_name, spots_description, spots_category, spots_address, spots_googlelink) VALUES 
('Great Santa Cruz Island (Pink Sand Beach)', 'Famous for its unique pink-hued sand, which gets its color from crushed red organ pipe corals mixing with the white sand. Its a great spot for swimming, picnicking, and has a mangrove lagoon tour.', 'Beach', 'Zamboanga City', 'https://maps.app.goo.gl/3SR4NzSbEoCMeu689'), 
('Fort Pilar','A 17th-century military defense fortress built by the Spanish. It is now a Latin American-style outdoor shrine dedicated to the Our Lady of the Pillar and houses the National Museum Western-Southern Mindanao Regional Museum.','Historical','N.S. Valderosa St., Zamboanga City','https://maps.app.goo.gl/KfRWjCMRfhtMSn9Z7'), 
('Paseo del Mar','A vibrant seaside promenade near Fort Pilar, popular for strolling, enjoying the sunset, and serving as the jump-off point for Great Santa Cruz Island. Its also a great spot to try local food, like the famous Knickerbocker dessert.','Entertainment','N S Valderosa St. (Right beside Fort Pilar), Zone IV, Zamboanga City','https://maps.app.goo.gl/yfL5eojhbs3hdWqV8'), 
('Yakan Weaving Village (or Yakan Weaving Center)','A place to witness the artistry of the Yakan indigenous people, who are renowned for their intricate, vibrant, hand-woven textiles and crafts. You can buy their products here.','Cultural','Upper Calarian, Labuan - Limpapa National Road, Zamboanga City','https://maps.app.goo.gl/YXzJViyeq3mPAsmy8'), 
('Pasonanca Park','A sprawling urban park featuring a Boy Scout camp, a public swimming pool, a Tree House (which used to host guests of the former mayor), and the El Museo de Zamboanga.','Nature','Pasonanca Road, Brgy. Pasonanca, Zamboanga City','https://maps.app.goo.gl/cP7Bdk9aMBVhL19X6'), 
('Merloquet Falls','A beautiful two-tiered waterfall located outside the city center, known for its unique, stair-like rock formations.','Nature','Brgy. Sibulao, Zamboanga City (Approximately 1-2 hours travel from the city proper)','https://maps.app.goo.gl/AQab8f5XXX68wsbV7'), 
('Once Islas','A cluster of 11 islands (though only a few are open to the public) offering island hopping, pristine beaches, and eco-cultural tourism experiences, such as Bisaya-Bisaya and Baung-Baung Islands.','Beach','Panubigan Ferry Terminal, Brgy. Panubigan (The designated jump-off point for the islands)','https://maps.app.goo.gl/Y4DoN4487Pi3DQ5q9'), 
('Zamboanga City Hall','A beautiful, well-preserved colonial-era building with historical significance, often included in a city walking tour.','Historical','N S Valderosa St., Zone IV, Zamboanga City','https://maps.app.goo.gl/T2yXcvBQaj1NBZrU6'), 
('Taluksangay Mosque','The oldest mosque in the Zamboanga Peninsula (built in 1885), distinguished by its distinctive red domes and recognized as a significant center for the propagation of Islam.','Religious','Brgy. Taluksangay, Zamboanga City','https://maps.app.goo.gl/tyEbMvVsNan8aeDR7'), 
('Metropolitan Cathedral of the Immaculate Conception','The main Catholic cathedral in the city, known for its distinct, modern architectural design.','Religious','La Purisima St., Zamboanga City','https://maps.app.goo.gl/vU5hH8E3MMMHbn7g7');

INSERT INTO Languages (language_name) VALUES
('English'),
('Filipino'),
('Chavacano');

INSERT INTO Tour_Spots_Images (spots_ID,spotsimage_PATH) VALUES 
('1','assets/img/tour-spots/great-santa-cruz-island/1.jpg'),
('1','assets/img/tour-spots/great-santa-cruz-island/2.jpg'),
('1','assets/img/tour-spots/great-santa-cruz-island/3.jpg'),
('1','assets/img/tour-spots/great-santa-cruz-island/4.jpg'),
('1','assets/img/tour-spots/great-santa-cruz-island/5.jpg'),
('1','assets/img/tour-spots/great-santa-cruz-island/6.jpg'),
('1','assets/img/tour-spots/great-santa-cruz-island/7.jpg'),
('1','assets/img/tour-spots/great-santa-cruz-island/8.jpg'),
('1','assets/img/tour-spots/great-santa-cruz-island/9.jpg'),
('1','assets/img/tour-spots/great-santa-cruz-island/10.jpg'),
('1','assets/img/tour-spots/great-santa-cruz-island/11.jpg'),
('1','assets/img/tour-spots/great-santa-cruz-island/12.jpg'),
('1','assets/img/tour-spots/great-santa-cruz-island/13.jpg'),
('1','assets/img/tour-spots/great-santa-cruz-island/14.jpg'),
('1','assets/img/tour-spots/great-santa-cruz-island/15.jpg'),
('1','assets/img/tour-spots/great-santa-cruz-island/16.jpg'),

('2','assets/img/tour-spots/fort-pilar/1.jpg'),
('2','assets/img/tour-spots/fort-pilar/2.jpg'),
('2','assets/img/tour-spots/fort-pilar/3.jpg'),
('2','assets/img/tour-spots/fort-pilar/4.jpg'),
('2','assets/img/tour-spots/fort-pilar/5.jpg'),
('2','assets/img/tour-spots/fort-pilar/6.jpg'),
('2','assets/img/tour-spots/fort-pilar/7.jpg'),
('2','assets/img/tour-spots/fort-pilar/8.jpg'),
('2','assets/img/tour-spots/fort-pilar/9.jpg'),
('2','assets/img/tour-spots/fort-pilar/10.jpg'),
('2','assets/img/tour-spots/fort-pilar/11.jpg'),
('2','assets/img/tour-spots/fort-pilar/12.jpg'),
('2','assets/img/tour-spots/fort-pilar/13.jpg'),
('2','assets/img/tour-spots/fort-pilar/14.jpg'),
('2','assets/img/tour-spots/fort-pilar/15.jpg'),
('2','assets/img/tour-spots/fort-pilar/16.jpg'),
('2','assets/img/tour-spots/fort-pilar/17.jpg'),

('3','assets/img/tour-spots/paseo-del-mar/1.jpg'),
('3','assets/img/tour-spots/paseo-del-mar/2.jpg'),
('3','assets/img/tour-spots/paseo-del-mar/3.jpg'),
('3','assets/img/tour-spots/paseo-del-mar/4.jpg'),
('3','assets/img/tour-spots/paseo-del-mar/5.jpg'),
('3','assets/img/tour-spots/paseo-del-mar/6.jpg'),
('3','assets/img/tour-spots/paseo-del-mar/7.jpg'),
('3','assets/img/tour-spots/paseo-del-mar/8.jpg'),
('3','assets/img/tour-spots/paseo-del-mar/9.jpg'),
('3','assets/img/tour-spots/paseo-del-mar/10.jpg'),
('3','assets/img/tour-spots/paseo-del-mar/11.jpg'),
('3','assets/img/tour-spots/paseo-del-mar/12.jpg'),


('4','assets/img/tour-spots/yakan-weaving-village/1.jpg'),
('4','assets/img/tour-spots/yakan-weaving-village/2.jpg'),
('4','assets/img/tour-spots/yakan-weaving-village/3.jpg'),
('4','assets/img/tour-spots/yakan-weaving-village/4.jpg'),
('4','assets/img/tour-spots/yakan-weaving-village/5.jpg'),
('4','assets/img/tour-spots/yakan-weaving-village/6.jpg'),
('4','assets/img/tour-spots/yakan-weaving-village/7.jpg'),
('4','assets/img/tour-spots/yakan-weaving-village/1-1.png'),
('4','assets/img/tour-spots/yakan-weaving-village/1-2.png'),

('5','assets/img/tour-spots/pasonanca-park/1.jpg'),
('5','assets/img/tour-spots/pasonanca-park/2.jpg'),
('5','assets/img/tour-spots/pasonanca-park/3.jpg'),
('5','assets/img/tour-spots/pasonanca-park/4.jpg'),
('5','assets/img/tour-spots/pasonanca-park/5.jpg'),
('5','assets/img/tour-spots/pasonanca-park/6.jpg'),
('5','assets/img/tour-spots/pasonanca-park/7.jpg'),
('5','assets/img/tour-spots/pasonanca-park/8.jpg'),

('6','assets/img/tour-spots/merloquet-falls/1.jpg'),
('6','assets/img/tour-spots/merloquet-falls/2.jpg'),
('6','assets/img/tour-spots/merloquet-falls/3.jpg'),
('6','assets/img/tour-spots/merloquet-falls/4.jpg'),
('6','assets/img/tour-spots/merloquet-falls/5.jpg'),
('6','assets/img/tour-spots/merloquet-falls/6.jpg'),
('6','assets/img/tour-spots/merloquet-falls/7.jpg'),
('6','assets/img/tour-spots/merloquet-falls/8.jpg'),


('7','assets/img/tour-spots/11-islands/1.jpg'),
('7','assets/img/tour-spots/11-islands/2.jpg'),
('7','assets/img/tour-spots/11-islands/3.jpg'),
('7','assets/img/tour-spots/11-islands/4.jpg'),
('7','assets/img/tour-spots/11-islands/5.jpg'),
('7','assets/img/tour-spots/11-islands/6.jpg'),
('7','assets/img/tour-spots/11-islands/7.jpg'),
('7','assets/img/tour-spots/11-islands/8.jpg'),

('8','assets/img/tour-spots/zamboanga-city-hall/1.jpg'),
('8','assets/img/tour-spots/zamboanga-city-hall/2.jpg'),
('8','assets/img/tour-spots/zamboanga-city-hall/3.jpg'),
('8','assets/img/tour-spots/zamboanga-city-hall/4.jpg'),
('8','assets/img/tour-spots/zamboanga-city-hall/5.jpg'),
('8','assets/img/tour-spots/zamboanga-city-hall/6.jpg'),
('8','assets/img/tour-spots/zamboanga-city-hall/7.jpg'),
('8','assets/img/tour-spots/zamboanga-city-hall/8.jpg'),
('8','assets/img/tour-spots/zamboanga-city-hall/9.jpg'),

('9','assets/img/tour-spots/taluksangay-mosque/1.jpg'),
('9','assets/img/tour-spots/taluksangay-mosque/2.jpg'),

('10','assets/img/tour-spots/metropolitan-cathedral-of-the-immaculate-conception/1.jpg'),
('10','assets/img/tour-spots/metropolitan-cathedral-of-the-immaculate-conception/2.jpg'),
('10','assets/img/tour-spots/metropolitan-cathedral-of-the-immaculate-conception/3.jpg');
