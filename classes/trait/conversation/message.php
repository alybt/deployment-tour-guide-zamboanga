<?php

trait MessageTrait{

    public function addgetUsers($user1_ID, $user2_ID, $db){
        
            try {
                $sql = "SELECT conversation_ID 
                        FROM Conversation 
                        WHERE user1_account_ID = :user1 
                        AND user2_account_ID = :user2";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':user1',$user1_ID);
                $stmt->bindParam(':user2',$user2_ID);
                $stmt->execute();
                $conversation_ID = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$conversation_ID) {
                    $sql1 = "SELECT conversation_ID 
                            FROM Conversation 
                            WHERE user1_account_ID = :user1 
                            AND user2_account_ID = :user2";
                    $stmt1 = $db->prepare($sql);
                    $stmt1->bindParam(':user1',$user2_ID);
                    $stmt1->bindParam(':user2',$user1_ID);
                    $stmt1->execute();
                    $conversation_ID1 = $stmt1->fetch(PDO::FETCH_ASSOC);
                    $conversation_ID = $conversation_ID1;

                    if(!$conversation_ID){
                        $db->rollback();
                        return false;
                    }
                }
                

                $initial_account_ID = $guide_row['account_ID'];
                $current_id = $tourist_ID;

                $sql1 = "SELECT conversation_ID 
                        FROM Conversation 
                        WHERE (user1_account_ID = :user1 AND user2_account_ID = :user2)
                            OR (user1_account_ID = :user2 AND user2_account_ID = :user1)";
                $query1 = $db->prepare($sql1);
                
                $query1->execute([
                    ':user1' => $current_id,
                    ':user2' => $initial_account_ID
                ]);
                
                $existing_convo = $query1->fetch(PDO::FETCH_ASSOC); 
                
                if ($existing_convo) {
                    $db->rollBack(); 
                    return (int)$existing_convo['conversation_ID'];
                } else {
                    $stmt_insert = $db->prepare("INSERT INTO Conversation (user1_account_ID, user2_account_ID) VALUES (:user1, :user2)");
                    $stmt_insert->execute([':user1' => $current_id, ':user2' => $initial_account_ID]);
                    $new_convo_id = $db->lastInsertId();
                    
                    $db->commit(); 
                    
                    header("Location: inbox.php?conversation_id=" . $new_convo_id);
                    exit;
                }
                
            } catch (PDOException $e) {
                $db->rollBack(); 
                error_log("Error in initializeConversationGuide: " . $e->getMessage());
                return null;
            }
        }
    }



}



?>
