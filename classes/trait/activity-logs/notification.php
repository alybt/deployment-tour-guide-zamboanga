<?php 

trait NotificationTrait{

    public function touristNotification(int $tourist_ID): array {
        $db = $this->connect();
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
        $sql = "SELECT 
                    al.activity_ID,
                    al.account_ID,
                    al.action_ID,
                    al.activity_description,
                    al.activity_timestamp,
                    a.action_name,
                    COALESCE(av.activity_isViewed, 0) AS is_viewed 
                FROM 
                    `activity_log` al 
                INNER JOIN 
                    `action` a ON al.action_ID = a.action_ID
                LEFT JOIN
                    `activity_view` av 
                    ON al.activity_ID = av.activity_ID AND al.account_ID = av.account_ID
                WHERE 
                    al.account_ID = :touristID 
                    AND a.action_name NOT IN ('Logout', 'Login', 'Change Account Into Tourist')
                ORDER BY 
                    al.activity_timestamp DESC";

        try {
            $query = $db->prepare($sql);
            
            // Correctly bind the parameter with the expected name
            $query->bindParam(':touristID', $tourist_ID, PDO::PARAM_INT);

            $query->execute();
            
            // Fetch all results as an associative array
            return $query->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("FATAL touristNotification error: Failed to fetch notifications for ID {$tourist_ID}. Details: " . $e->getMessage());
            return []; // Return an empty array on failure
        }
    }
 
    public function markTouristNotificationsAsViewed(int $tourist_ID): bool {
        $db = $this->connect();
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            // Step 1: Insert missing activity_view records for activities that don't have them yet
            $insertSql = "INSERT INTO activity_view (activity_ID, account_ID, activity_isViewed)
                          SELECT al.activity_ID, al.account_ID, 1
                          FROM activity_log al
                          INNER JOIN action a ON al.action_ID = a.action_ID
                          WHERE al.account_ID = :touristID
                          AND a.action_name NOT IN ('Logout', 'Login', 'Change Account Into Tourist')
                          AND NOT EXISTS (
                              SELECT 1 FROM activity_view av 
                              WHERE av.activity_ID = al.activity_ID 
                              AND av.account_ID = al.account_ID
                          )
                          ON DUPLICATE KEY UPDATE activity_isViewed = 1";
            
            $insertQuery = $db->prepare($insertSql);
            $insertQuery->bindParam(':touristID', $tourist_ID, PDO::PARAM_INT);
            $insertQuery->execute();
            error_log("INSERT missing activity_view records executed");

            // Step 2: Update existing activity_view records to mark as viewed
            $updateSql = "UPDATE activity_view av
                          INNER JOIN activity_log al 
                              ON al.activity_ID = av.activity_ID 
                              AND al.account_ID = av.account_ID
                          INNER JOIN action a 
                              ON al.action_ID = a.action_ID
                          SET av.activity_isViewed = 1
                          WHERE al.account_ID = :touristID
                          AND a.action_name NOT IN ('Logout', 'Login', 'Change Account Into Tourist')";

            $updateQuery = $db->prepare($updateSql);
            $updateQuery->bindParam(':touristID', $tourist_ID, PDO::PARAM_INT);
            $updateQuery->execute();
            error_log("UPDATE activity_view records executed");
            
            return true;
        } catch (Exception $e) {
            error_log("FATAL markTouristNotificationsAsViewed error: " . $e->getMessage());
            return false;
        }
    }

    public function markSingleNotificationAsViewed(int $activity_ID, int $account_ID): bool {
        $db = $this->connect();
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        error_log("markSingleNotificationAsViewed - Activity ID: $activity_ID, Account ID: $account_ID");

        // First, check if the activity_view record exists
        $checkSql = "SELECT activity_ID FROM activity_view 
                     WHERE activity_ID = :activity_ID 
                     AND account_ID = :account_ID";
        
        try {
            $checkQuery = $db->prepare($checkSql);
            $checkQuery->bindParam(':activity_ID', $activity_ID, PDO::PARAM_INT);
            $checkQuery->bindParam(':account_ID', $account_ID, PDO::PARAM_INT);
            $checkQuery->execute();
            
            $exists = $checkQuery->fetch(PDO::FETCH_ASSOC);
            error_log("activity_view record exists: " . ($exists ? 'YES' : 'NO'));

            if ($exists) {
                // Update existing record
                $updateSql = "UPDATE activity_view 
                              SET activity_isViewed = 1
                              WHERE activity_ID = :activity_ID 
                              AND account_ID = :account_ID";
                
                $updateQuery = $db->prepare($updateSql);
                $updateQuery->bindParam(':activity_ID', $activity_ID, PDO::PARAM_INT);
                $updateQuery->bindParam(':account_ID', $account_ID, PDO::PARAM_INT);
                $result = $updateQuery->execute();
                error_log("UPDATE result: " . ($result ? 'TRUE' : 'FALSE'));
            } else {
                // Insert new record
                $insertSql = "INSERT INTO activity_view (activity_ID, account_ID, activity_isViewed)
                              VALUES (:activity_ID, :account_ID, 1)";
                
                $insertQuery = $db->prepare($insertSql);
                $insertQuery->bindParam(':activity_ID', $activity_ID, PDO::PARAM_INT);
                $insertQuery->bindParam(':account_ID', $account_ID, PDO::PARAM_INT);
                $result = $insertQuery->execute();
                error_log("INSERT result: " . ($result ? 'TRUE' : 'FALSE'));
            }
            
            return true;
        } catch (Exception $e) {
            error_log("FATAL markSingleNotificationAsViewed error: " . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    public function guideNotification(int $guide_account_ID): array {
        $db = $this->connect();
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
        
        $sql = "SELECT 
                    al.activity_ID,
                    al.account_ID as tourist_account_ID,
                    al.action_ID,
                    al.activity_description,
                    al.activity_timestamp,
                    a.action_name,
                    COALESCE(av.activity_isViewed, 0) AS is_viewed,
                    
                    -- Extract booking_ID from description
                    CAST(SUBSTRING_INDEX(al.activity_description, ' book ', -1) AS UNSIGNED) as booking_ID,
                    
                    -- Booking details
                    b.booking_status,
                    b.booking_date,
                    b.booking_startDate,
                    b.booking_endDate,
                    b.booking_totalPrice,
                    
                    -- Tourist details (the one who made the booking)
                    acc_tourist.account_nickname AS tourist_nickname,
                    CONCAT(ni_tourist.name_first, ' ', ni_tourist.name_last) AS tourist_fullname,
                    acc_tourist.account_profilepic AS tourist_profile_pic,
                    ci_tourist.contactinfo_email AS tourist_email,
                    pn_tourist.phone_number AS tourist_phone,
                    
                    -- Tour package details (if applicable)
                    tp.tourPackage_name,
                    tp.tourPackage_description,
                    tp.tourPackage_price
                    
                FROM 
                    `Booking` b
                
                -- Join with activity log to get tourist activities
                INNER JOIN
                    `activity_log` al ON CAST(SUBSTRING_INDEX(al.activity_description, ' book ', -1) AS UNSIGNED) = b.booking_ID
                INNER JOIN 
                    `action` a ON al.action_ID = a.action_ID
                LEFT JOIN
                    `activity_view` av 
                    ON al.activity_ID = av.activity_ID AND :guideID = av.account_ID
                
                -- Get Tourist Account Info (the tourist who created the booking)
                LEFT JOIN
                    `account_info` acc_tourist ON al.account_ID = acc_tourist.account_ID
                LEFT JOIN
                    `user_login` ul_tourist ON acc_tourist.user_ID = ul_tourist.user_ID
                LEFT JOIN
                    `contact_info` ci_tourist ON p_tourist.contactinfo_ID = ci_tourist.contactinfo_ID
                LEFT JOIN
                    `phone_number` pn_tourist ON ci_tourist.phone_ID = pn_tourist.phone_ID
                
                -- Get Tour Package Info
                LEFT JOIN
                    `tourpackage` tp ON b.tourPackage_ID = tp.tourPackage_ID
                    
                WHERE 
                    b.account_ID = :guideID
                    AND a.action_name NOT IN ('Logout', 'Login', 'Change Account Into Tourist')
                    AND al.activity_description LIKE '% book %'
                ORDER BY 
                    al.activity_timestamp DESC";

        try {
            $query = $db->prepare($sql);
            $query->bindParam(':guideID', $guide_account_ID, PDO::PARAM_INT);
            $query->execute();
            
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            
            // Process results to add user-friendly messages
            foreach ($results as &$row) {
                $row['formatted_message'] = $this->formatGuideNotificationMessage($row);
                $row['time_ago'] = $this->timeAgo($row['activity_timestamp']);
            }
            
            return $results;

        } catch (Exception $e) {
            error_log("FATAL guideNotification error: Failed to fetch notifications for Guide ID {$guide_account_ID}. Details: " . $e->getMessage());
            return [];
        }
    } 

    private function formatGuideNotificationMessage(array $notification): string {
        $action = $notification['action_name'] ?? '';
        $touristName = $notification['tourist_nickname'] ?? $notification['tourist_fullname'] ?? 'A tourist';
        $tourPackageName = $notification['tourPackage_name'] ?? 'your service';
        $bookingId = $notification['booking_ID'] ?? '';
        
        switch ($action) {
            case 'Booking Created':
            case 'New Booking':
                return "{$touristName} created a new booking (#{$bookingId}) for {$tourPackageName}";
            
            case 'Booking Cancelled':
                return "{$touristName} cancelled booking #{$bookingId}";
            
            case 'Booking Modified':
            case 'Booking Updated':
                return "{$touristName} modified booking #{$bookingId}";
            
            case 'Payment Made':
            case 'Payment Completed':
                return "{$touristName} completed payment for booking #{$bookingId}";
            
            case 'Review Submitted':
                return "{$touristName} left a review for booking #{$bookingId}";
            
            case 'Message Sent':
                return "{$touristName} sent you a message about booking #{$bookingId}";
            
            case 'Booking Inquiry':
                return "{$touristName} inquired about {$tourPackageName}";
            
            default:
                return "{$touristName} performed an action on booking #{$bookingId}";
        }
    }
}

?>