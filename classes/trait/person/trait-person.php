<?php

require_once "trait-name-info.php";
require_once "trait-phone.php";
require_once "trait-address.php";
require_once "trait-emergency.php";
require_once "trait-contact-info.php";
trait PersonTrait {

    // Check if user_login with same name and birthdate exists
    public function checkPersonExists($name_first, $name_second, $name_middle, $name_last, $name_suffix, 
                                     $person_dateofbirth, $person_gender) {
        $sql = "SELECT COUNT(*) AS total FROM user_login n  
                WHERE n.name_first = :name_first 
                AND (n.name_second = :name_second OR (n.name_second IS NULL AND :name_second IS NULL)) 
                AND (n.name_middle = :name_middle OR (n.name_middle IS NULL AND :name_middle IS NULL)) 
                AND n.name_last = :name_last 
                AND (n.name_suffix = :name_suffix OR (n.name_suffix IS NULL AND :name_suffix IS NULL)) 
                AND p.person_DateOfBirth = :person_dateofbirth
                AND p.person_Gender = :person_gender";
        
        $query = $this->connect()->prepare($sql);
        $query->bindParam(":name_first", $name_first);
        $query->bindParam(":name_second", $name_second);
        $query->bindParam(":name_middle", $name_middle);
        $query->bindParam(":name_last", $name_last);
        $query->bindParam(":name_suffix", $name_suffix);
        $query->bindParam(":person_dateofbirth", $person_dateofbirth);
        $query->bindParam(":person_gender", $person_gender);
        
        if ($query->execute()) {
            $record = $query->fetch();
            return $record["total"] > 0;
        }
        return false;
    }

    // Add Person
    public function addgetPerson(
    $name_first, $name_second, $name_middle, $name_last, $name_suffix,
    $houseno, $street, $barangay,
    $country_ID, $phone_number,
    $emergency_name, $emergency_country_ID, $emergency_phonenumber, $emergency_relationship,
    $contactinfo_email,
    $person_nationality, $person_gender, $person_dateofbirth,
    $db ) {
        if (!($db instanceof PDO)) {
            $this->setLastError("addgetPerson: \$db is not PDO");
            return false;
        }

        $user_ID = $this->addgetNameInfo($name_first, $name_second, $name_middle, $name_last, $name_suffix, $db);
        $contactinfo_ID = $this->addgetContact_Info(
            $houseno, $street, $barangay,
            $country_ID, $phone_number,
            $emergency_name, $emergency_country_ID, $emergency_phonenumber, $emergency_relationship,
            $contactinfo_email,
            $db
        );

        if (!$user_ID || !$contactinfo_ID) {
            $this->setLastError($this->getLastError() ?: "Name or Contact missing");
            return false;
        }

        $sql = "INSERT INTO user_login (user_ID, person_Nationality, person_Gender, person_DateOfBirth, contactinfo_ID)
                VALUES (:user_ID, :person_nationality, :person_gender, :person_dateofbirth, :contactinfo_ID)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(":user_ID", $user_ID, PDO::PARAM_INT);
        $stmt->bindParam(":person_nationality", $person_nationality);
        $stmt->bindParam(":person_gender", $person_gender);
        $stmt->bindParam(":person_dateofbirth", $person_dateofbirth);
        $stmt->bindParam(":contactinfo_ID", $contactinfo_ID, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $db->lastInsertId();
        }

        $this->setLastError("Person insert failed");
        return false;
    }

    // Delete Person
    public function deletePerson($user_ID){
        $db = $this->connect();
        $db->beginTransaction();

        try {
            $sql = "SELECT user_ID, contactinfo_ID FROM user_login WHERE user_ID = :user_ID";
            $query = $db->prepare($sql);
            $query->bindParam(":user_ID", $user_ID);
            $query->execute();
            $data = $query->fetch(PDO::FETCH_ASSOC);

            if (!$data) return false;

            $user_ID = $data['user_ID'];
            $contactinfo_ID = $data['contactinfo_ID'];

            $sql_delete = "DELETE FROM user_login WHERE user_ID = :user_ID";
            $query_delete = $db->prepare($sql_delete);
            $query_delete->bindParam(":user_ID", $user_ID);
            $query_delete->execute();

            $sql_count_name = "SELECT COUNT(*) AS total FROM user_login WHERE user_ID = :user_ID";
            $query_name = $db->prepare($sql_count_name);
            $query_name->bindParam(":user_ID", $user_ID);
            $query_name->execute();
            if ($query_name->fetch(PDO::FETCH_ASSOC)['total'] == 0){
                $this->deleteName($user_ID);
            }

            $sql_count_contact = "SELECT COUNT(*) AS total FROM user_login WHERE contactinfo_ID = :contactinfo_ID";
            $query_contact = $db->prepare($sql_count_contact);
            $query_contact->bindParam(":contactinfo_ID", $contactinfo_ID);
            $query_contact->execute();
            if ($query_contact->fetch(PDO::FETCH_ASSOC)['total'] == 0){
                $this->deleteContactInfoSafe($contactinfo_ID);
            }

            return $db->lastInsertId();
            

        } catch (PDOException $e){
            
            error_log("Delete Person Error: " . $e->getMessage());
            return false;
        }
    }

    // View Person
    public function viewPerson($user_ID){
        $db = $this->connect();

        $sql = "
            SELECT 
                p.user_ID, p.person_Nationality, p.person_Gender, p.person_DateOfBirth,
                n.name_first, n.name_second, n.name_middle, n.name_last, n.name_suffix,
                c.contactinfo_email,
                pn.phone_number,
                cc.country_codename AS phone_country_code,
                e.emergency_Name, e.emergency_Relationship,
                epn.phone_number AS emergency_phone_number,
                ecc.country_codename AS emergency_country_code,
                c.address_houseno, c.address_street,
                b.barangay_name, ct.city_name, pr.province_name, co.country_name
            FROM user_login p
            JOIN user_login n ON p.user_ID = n.user_ID
            JOIN contact_info c ON p.contactinfo_ID = c.contactinfo_ID
            LEFT JOIN phone_number pn ON c.phone_ID = pn.phone_ID
            LEFT JOIN country cc ON pn.country_ID = cc.country_ID 
            LEFT JOIN phone_number epn ON e.phone_ID = epn.phone_ID
            LEFT JOIN country ecc ON epn.country_ID = ecc.country_ID 
            LEFT JOIN barangay b ON c.barangay_ID = b.barangay_ID
            LEFT JOIN city ct ON b.city_ID = ct.city_ID
            LEFT JOIN province pr ON ct.province_ID = pr.province_ID
            LEFT JOIN country co ON pr.country_ID = co.country_ID
            WHERE user_ID = :user_ID
        ";

        $query = $db->prepare($sql);
        $query->bindParam(":user_ID", $user_ID);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    // Search People
    public function searchPersons($searchTerm){
        $db = $this->connect();
        $searchTerm = "%".$searchTerm."%";

        $sql = " SELECT 
                p.user_ID,
                CONCAT(n.name_first, ' ', n.name_last) AS full_name,
                c.contactinfo_email,
                pn.phone_number,
                c.address_street,
                ct.city_name,
                co.country_name
            FROM user_login p 
            JOIN contact_info c ON p.contactinfo_ID = c.contactinfo_ID
            LEFT JOIN phone_number pn ON c.phone_ID = pn.phone_ID 
            LEFT JOIN barangay b ON c.barangay_ID = b.barangay_ID
            LEFT JOIN city ct ON b.city_ID = ct.city_ID
            LEFT JOIN province pr ON ct.province_ID = pr.province_ID
            LEFT JOIN country co ON pr.country_ID = co.country_ID
            WHERE 
                n.name_first LIKE :search
                OR n.name_last LIKE :search
                OR pn.phone_number LIKE :search
                OR c.contactinfo_email LIKE :search
                OR co.country_name LIKE :search
                OR ct.city_name LIKE :search
            ORDER BY full_name ASC
        ";

        $query = $db->prepare($sql);
        $query->bindParam(":search", $searchTerm);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLanguages(){
        $sql = "SELECT * FROM languages";
        $db = $this->connect();
        $query = $db->prepare($sql);
        $query->execute();

        return $query->fetchAll();
    }

    public function updatePerson($user_ID, $name_first,  $name_second, $name_middle, 
        $name_last, $name_suffix,
        $houseno, $street, $barangay,
        $country_ID, $phone_number,
        $emergency_name, $emergency_country_ID, $emergency_phonenumber, $emergency_relationship,
        $contactinfo_email,
        $person_nationality, $person_gender, $person_dateofbirth, $db){
        try {
            $sql = "SELECT COUNT(*) as person_count FROM user_login 
                WHERE contactinfo_ID = :contactinfo_ID OR user_ID = :user_ID";
            $query->prepare($sql);
            $query->execute([':contactinfo_ID' => $contactinfo_ID], [':user_ID' => $user_ID]);
            $person_count = (int) $query->fetchColumn();

            if($person_count > 1){
                $user_ID = $this->updateNameInfo($user_ID, $name_first,  $name_second, $name_middle, 
                    $name_last, $name_suffix,$db);
                

            } else {

            }


        } catch (PDOException $e) {
            if (method_exists($this, 'setLastError')) {
                $this->setLastError("Person creation error: " . $e->getMessage());
            }
            error_log("Add Person Error: " . $e->getMessage());
            return false;
        }
    
            

    }
}
