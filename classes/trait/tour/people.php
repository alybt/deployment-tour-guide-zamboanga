<?php

trait PeopleTrait {

    public function addPeople($numberofpeople_maximum, $numberofpeople_based, $currency, $forAdult, $forChild, $forYoungAdult, $forSenior, $forPWD, $includeMeal, $mealFee, $transportFee, $discount, $db) {
        try {

            $pricing_ID = $this->addPricing($currency, $forAdult, $forChild, $forYoungAdult, $forSenior, $forPWD, $includeMeal, $mealFee, $transportFee, $discount, $db);

                if (!$pricing_ID){
                    return false;
                }


            $sql = "INSERT INTO tour_package (pricing_ID, numberofpeople_maximum, numberofpeople_based)
                    VALUES (:pricing_ID, :max, :based)";

            $query = $db->prepare($sql);
            $query->bindParam(':based', $numberofpeople_based );
            $query->bindParam(':max', $numberofpeople_maximum );
            $query->bindParam(':pricing_ID', $pricing_ID);
            $query->execute();
            return $db->lastInsertId();


            
        } catch (PDOException $e) {
            error_log("Error in addGetPeople: " . $e->getMessage());
            return false;
        }
    }

    public function updatePeople($tourpackage_ID, $numberofpeople_maximum, $numberofpeople_based, $pricing_ID, $currency, $forAdult, $forChild, $forYoungAdult, $forSenior, $forPWD, $includeMeal, $mealFee, $transportFee, $discount, $db) {
        try {

            $result = $this->updatePricing($pricing_ID, $currency, $forAdult, $forChild, $forYoungAdult, $forSenior, $forPWD, $includeMeal, $mealFee, $transportFee, $discount, $db);

                if (!$result){
                    return false;
                }

            $sql = "UPDATE tour_package SET
                        pricing_ID = :pricing_ID,
                        numberofpeople_maximum = :max,
                        numberofpeople_based = :based
                    WHERE tourpackage_ID = :tourpackage_ID";
            $query = $db->prepare($sql);
            $query->bindParam(':based', $numberofpeople_based );
            $query->bindParam(':max', $numberofpeople_maximum );
            $query->bindParam(':pricing_ID', $pricing_ID);
            $query->bindParam(':tourpackage_ID', $tourpackage_ID);
            
            return $query->execute();


            
        } catch (PDOException $e) {
            error_log("Error in addGetPeople: " . $e->getMessage());
            return false;
        }
    }

    // updatePricing($pricing_ID ,$currency, $forAdult, $forChild, $forYoungAdult, $forSenior, $forPWD, $includeMeal, $mealFee, $transportFee, $discount, $db)

    public function getPeopleByID($peopleID) {
        $db = $this->connect();
        $sql = "SELECT * FROM tour_package WHERE tourpackage_ID = :peopleID";
        $query = $db->prepare($sql);
        $query->bindParam(':peopleID', $peopleID);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function deletePeopleByID($tourpackage_ID, $db){
        $sql = "DELETE FROM tour_package WHERE tourpackage_ID = :tourpackage_ID";
        
        try {
            $query = $db->prepare($sql);
            $query->bindParam(":tourpackage_ID", $tourpackage_ID);
            
            if ($query->execute()) {
                return true;
            }
            error_log("Pricing Delete Error: " . print_r($query->errorInfo(), true));
            return false;
        } catch (PDOException $e) {
            error_log("Pricing Delete Exception: " . $e->getMessage());
            return false;
        }
    }

    // public function getPricingIDInNumberOfPeopleByPeopleID($people_ID){
    //     $db = $this->connect();
    //     $sql = "SELECT pricing_ID FROM tour_package WHERE tourpackage_ID = :people_ID";
    //     $query = $db->prepare($sql);
    //     $query->bindParam(':people_ID', $people_ID);
    //     $query->execute();
    //     return $query->fetch(PDO::FETCH_ASSOC);
    // }

    // public function addGetPeople($numberofpeople_maximum, $numberofpeople_based, $currency, $forAdult, $forChild, $forYoungAdult, $forSenior, $forPWD, $includeMeal, $mealFee, $transportFee, $discount, $db) {
    //     try {

    //         $pricing_ID = $this->addPricing($currency, $forAdult, $forChild, $forYoungAdult, $forSenior, $forPWD, $includeMeal, $mealFee, $transportFee, $discount, $db);

    //             if (!$pricing_ID){
    //                 return false;
    //             }

    //         $sql = "SELECT tourpackage_ID FROM tour_package WHERE numberofpeople_maximum = :max
    //                 AND numberofpeople_based = :based AND pricing_ID = :pricing_ID";
    //         $query = $db->prepare($sql);
    //         $query->bindParam(':based', $numberofpeople_based );
    //         $query->bindParam(':max', $numberofpeople_maximum );
    //         $query->bindParam(':pricing_ID', $pricing_ID);
    //         $query->execute();
    //         $result = $query->fetch();
            
    //             if ($result) {
    //                 return $result['tourpackage_ID'];
    //             }

    //         $sql = "INSERT INTO tour_package (pricing_ID, numberofpeople_maximum, numberofpeople_based)
    //                 VALUES (:pricing_ID, :max, :based)";

    //         $query = $db->prepare($sql);
    //         $query->bindParam(':based', $numberofpeople_based );
    //         $query->bindParam(':max', $numberofpeople_maximum );
    //         $query->bindParam(':pricing_ID', $pricing_ID);
    //         $query->execute();
    //         $result = $query->fetch();

    //             if ($result) {
    //                 return $db->lastInsertId();
    //             }

            
    //     } catch (PDOException $e) {
    //         error_log("Error in addGetPeople: " . $e->getMessage());
    //         return false;
    //     }
    // }
}
