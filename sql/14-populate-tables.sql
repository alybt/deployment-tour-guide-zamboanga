
INSERT INTO CategoryRefund_Name (categoryrefundname_name) VALUES
('Change of Plans'),
('Personal Emergencies'),
('Financial Issues'),
('Mistaken Booking'),
('Double Booking'),
('Change in Group Size'),
('Travel Issues'),
('Found a Better Deal'),
('Change of Destination'),
('Unmet Expectations'),
('Provider Cancellation'),
('Overbooking'),
('Service Quality Issues'),
('Error in Pricing or Package'),
('Delayed Confirmations'),
('Bad Weather/Natural Disasters'),
('Government Restrictions'),
('Transportation Shutdowns'),
('Health Crises'),
('Political or Security Issues'),
('Payment Failed or Charged Twice'),
('Booking Confirmation Not Received'),
('Miscommunication Between System'),
('Unsafe Conditions at Tour Site'),
('Guide Unavailability'),
('Health Issues'),
('Family Emergency'),
('Scheduling Conflict'),
('Transportation Problems'),
('Unforeseen Circumstances'),
('Personal Leave or Rest Day'),
('Lack of Participants'),
('Venue or Partner Closure'),
('Permit or License Issues'),
('Transportation / Equipment Unavailable'),
('Scheduling Error'),
('Staff Shortage'),
('No Show'),
('Invalid Booking Details'),
('Violation of Terms'),
('Tourist Requested Cancellation');

-- 2️⃣ Assign reasons to roles in Category_Refund
-- Tourist reasons (role_ID = 3)
INSERT INTO Category_Refund (categoryrefundname_ID, role_ID)
SELECT categoryrefundname_ID, 3 FROM CategoryRefund_Name
WHERE categoryrefundname_name IN (
    'Change of Plans',
    'Personal Emergencies',
    'Financial Issues',
    'Mistaken Booking',
    'Double Booking',
    'Change in Group Size',
    'Travel Issues',
    'Found a Better Deal',
    'Change of Destination',
    'Unmet Expectations',
    'Payment Failed or Charged Twice',
    'Booking Confirmation Not Received'
);

-- Tour Guide reasons (role_ID = 2)
INSERT INTO Category_Refund (categoryrefundname_ID, role_ID)
SELECT categoryrefundname_ID, 2 FROM CategoryRefund_Name
WHERE categoryrefundname_name IN (
    'Guide Unavailability',
    'Health Issues',
    'Family Emergency',
    'Scheduling Conflict',
    'Transportation Problems',
    'Unforeseen Circumstances',
    'Personal Leave or Rest Day',
    'Lack of Participants',
    'Venue or Partner Closure',
    'Permit or License Issues',
    'Transportation / Equipment Unavailable',
    'Scheduling Error',
    'Staff Shortage',
    'No Show'
);

-- Admin reasons (role_ID = 1)
INSERT INTO Category_Refund (categoryrefundname_ID, role_ID)
SELECT categoryrefundname_ID, 1 FROM CategoryRefund_Name
WHERE categoryrefundname_name IN (
    'Provider Cancellation',
    'Overbooking',
    'Service Quality Issues',
    'Error in Pricing or Package',
    'Delayed Confirmations',
    'Bad Weather/Natural Disasters',
    'Government Restrictions',
    'Transportation Shutdowns',
    'Health Crises',
    'Political or Security Issues',
    'Miscommunication Between System',
    'Unsafe Conditions at Tour Site',
    'Invalid Booking Details',
    'Violation of Terms',
    'Tourist Requested Cancellation'
);

INSERT INTO Companion_Category(companion_category_name) VALUES ('Infant'), ('Child'), ('Young Adult'), ('Adult'), ('Senior'), ('PWD');