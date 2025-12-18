<?php

trait NameInfoTrait{

    // $name_first, $name_second, $name_middle, $name_last, $name_suffix,
    public function addgetNameInfo($name_first, $name_second, $name_middle, $name_last, $name_suffix, $db) {
        if (!($db instanceof PDO)) {
            $this->setLastError("addgetNameInfo: \$db is not PDO");
            return false;
        }

        $sql_check = "SELECT user_ID FROM user_login
                    WHERE name_first = :firstname
                        AND name_second <=> :secondname
                        AND name_middle <=> :middlename
                        AND name_last   = :lastname
                        AND name_suffix <=> :suffix";
        $q = $db->prepare($sql_check);
        $q->execute([
            ':firstname'   => $name_first,
            ':secondname'  => $name_second,
            ':middlename'  => $name_middle,
            ':lastname'    => $name_last,
            ':suffix'      => $name_suffix,
        ]);

        if ($row = $q->fetch(PDO::FETCH_ASSOC)) {
            return $row['user_ID'];
        }

        $sql_insert = "INSERT INTO user_login (name_first, name_second, name_middle, name_last, name_suffix)
                    VALUES (:firstname, :secondname, :middlename, :lastname, :suffix)";
        $q = $db->prepare($sql_insert);
        $q->execute([
            ':firstname'   => $name_first,
            ':secondname'  => $name_second,
            ':middlename'  => $name_middle,
            ':lastname'    => $name_last,
            ':suffix'      => $name_suffix,
        ]);

        return $q->rowCount() ? $db->lastInsertId() : false;
    }

    public function renameNameSmart($user_ID, $firstname, $middlename, $lastname, $suffix){
        $db = $this->connect();

        // 1. Get current user_ID of this person
        $sql = "SELECT user_ID FROM user_login WHERE person_id = :person_id";
        $q = $db->prepare($sql);
        $q->bindParam(":person_id", $user_ID);
        $q->execute();
        $current = $q->fetch(PDO::FETCH_ASSOC);

        if(!$current){
            return false;
        }

        $current_name_id = $current['user_ID'];

        // 2. Count how many people use this user_ID
        $sql_count = "SELECT COUNT(*) AS total FROM user_login WHERE user_ID = :user_ID";
        $q_count = $db->prepare($sql_count);
        $q_count->bindParam(":user_ID", $current_name_id);
        $q_count->execute();
        $count = $q_count->fetch(PDO::FETCH_ASSOC)['total'];

        // 3. Check if the NEW NAME already exists
        $sql_check = "SELECT user_ID FROM user_login
                    WHERE name_first = :firstname AND name_middle = :middlename 
                    AND name_last = :lastname AND name_suffix = :suffix";
        $q_check = $db->prepare($sql_check);
        $q_check->bindParam(":firstname", $firstname);
        $q_check->bindParam(":middlename", $middlename);
        $q_check->bindParam(":lastname", $lastname);
        $q_check->bindParam(":suffix", $suffix);
        $q_check->execute();
        $existing = $q_check->fetch(PDO::FETCH_ASSOC);

        // ---- CASE A: New name already exists → just link to it
        if($existing){
            $new_name_id = $existing["user_ID"];

            $sql_update = "UPDATE user_login SET user_ID = :new_name_id WHERE person_id = :person_id";
            $q_update = $db->prepare($sql_update);
            $q_update->bindParam(":new_name_id", $new_name_id);
            $q_update->bindParam(":person_id", $user_ID);
            $q_update->execute();

            return $new_name_id;
        }

        // ---- CASE B: Only this person uses the current name → safe to rename
        if($count == 1){
            $sql_update_name = "UPDATE user_login 
                                SET name_first = :firstname, name_middle = :middlename, name_last = :lastname, name_suffix = :suffix
                                WHERE user_ID = :user_ID";
            $q_update_name = $db->prepare($sql_update_name);
            $q_update_name->bindParam(":firstname", $firstname);
            $q_update_name->bindParam(":middlename", $middlename);
            $q_update_name->bindParam(":lastname", $lastname);
            $q_update_name->bindParam(":suffix", $suffix);
            $q_update_name->bindParam(":user_ID", $current_name_id);
            $q_update_name->execute();

            return $current_name_id;
        }

        // ---- CASE C: More than 1 person uses the name → create new one
        $sql_insert = "INSERT INTO user_login (name_first, name_middle, name_last, name_suffix)
                    VALUES (:firstname, :middlename, :lastname, :suffix)";
        $q_insert = $db->prepare($sql_insert);
        $q_insert->bindParam(":firstname", $firstname);
        $q_insert->bindParam(":middlename", $middlename);
        $q_insert->bindParam(":lastname", $lastname);
        $q_insert->bindParam(":suffix", $suffix);
        $q_insert->execute();

        $new_name_ID = $db->lastInsertId();

        $sql_update_person = "UPDATE user_login SET user_ID = :new_name_ID WHERE user_ID = :user_ID";
        $q_update_person = $db->prepare($sql_update_person);
        $q_update_person->bindParam(":new_name_ID", $new_name_ID);
        $q_update_person->bindParam(":user_ID", $user_ID);
        $q_update_person->execute();

        return $new_name_ID;
    }

    public function deleteName($user_ID){
        $sql = "DELETE FROM user_login WHERE user_ID = :id";
        $query = $this->connect()->prepare($sql);
        $query->bindParam(":id", $user_ID);

        return $query->execute();
    }

    public function updateNameInfo($user_ID, $name_first,  $name_second, $name_middle, 
        $name_last, $name_suffix,$db) {
        
        try {
            $sql_count = "SELECT COUNT(DISTINCT user_ID) AS person_count
                FROM person WHERE user_ID = :user_ID ";

            $q_count = $db->prepare($sql_count);
            $q_count->execute([':user_ID' => $user_ID]);
            $person_count = (int) $q_count->fetchColumn();

            if ($person_count > 1) {
                echo "Name ID {$user_ID} is shared by {$person_count} people. Creating new user_ID for this person.\n";
                $user_ID = $this->addgetNameInfo($name_first, $name_second, $name_middle, $name_last, $name_suffix, $db);

                return $user_ID;

            } else {
                echo "Reusing existing name ID: {$user_ID} (Linked to {$person_count} person).\n";
                $sql_insert = "UPDATE user_login SET
                    name_first = :firstname,
                    name_second = :secondname,
                    name_middle = :middlename,
                    name_last = :lastname,
                    name_suffix = :suffix
                    WHERE user_ID = :user_ID";
                $q_insert = $db->prepare($sql_insert);
                $q_insert->bindParam(":firstname", $name_first);
                $q_insert->bindParam(":secondname", $name_second);
                $q_insert->bindParam(":middlename", $name_middle);
                $q_insert->bindParam(":lastname", $name_last);
                $q_insert->bindParam(":suffix", $name_suffix);
                $existing = $q_check->fetch(PDO::FETCH_ASSOC);
                if ($q_insert->execute()) {
                    return $db->lastInsertId();
                } else {
                    return false;
                }
                
            }

        } catch (PDOException $e) {
            if (method_exists($this, 'setLastError')) {
                $this->setLastError("Name Update error: " . $e->getMessage());
            }
            error_log("Update Name Error: " . $e->getMessage());
            return false;
        }
        
    }


}