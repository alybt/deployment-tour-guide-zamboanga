<?php

trait ToGuideTrait{

    public function initializeConversationGuide(int $guide_ID, int $tourist_ID): ?int {
        $db = $this->connect();
        $db->beginTransaction(); 
        
        try {
            $sql = "SELECT account_ID FROM Guide WHERE guide_ID = :guide_ID";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':guide_ID', $guide_ID, PDO::PARAM_INT);
            $stmt->execute();
            $guide_row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$guide_row) {
                $db->rollBack(); 
                return null;
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

    public function fetchConversation($tourist_ID){
         $db = $this->connect();
        $db->beginTransaction(); 
        $conversations = [];
        try {
            $sql_convos = "SELECT
                    c.conversation_ID,
                    CASE
                        WHEN c.user1_account_ID = :current_id THEN c.user2_account_ID
                        ELSE c.user1_account_ID
                    END AS correspondent_account_ID,
                    m.message_content AS last_message_content,
                    m.sent_at AS last_message_time,
                    (SELECT COUNT(*) 
                        FROM Message 
                        WHERE conversation_ID = c.conversation_ID 
                          AND sender_account_ID != :current_id 
                          AND is_read = 0) AS unread_count,
                    g.guide_ID,
                    ai.account_profilepic AS avatar,
                    ni.name_first,
                    ni.name_last
                FROM Conversation c
                LEFT JOIN Message m ON m.message_ID = c.last_message_ID
                JOIN Account_Info aicor ON aicor.account_ID = 
                    (CASE WHEN c.user1_account_ID = :current_id THEN c.user2_account_ID ELSE c.user1_account_ID END)
                LEFT JOIN Guide g ON g.account_ID = aicor.account_ID
                JOIN User_Login ul ON aicor.user_ID = ul.user_ID
                JOIN Person p ON ul.person_ID = p.person_ID
                LEFT JOIN Name_Info ni ON p.name_ID = ni.name_ID
                LEFT JOIN Account_Info ai ON ai.account_ID = aicor.account_ID
                WHERE c.user1_account_ID = :current_id OR c.user2_account_ID = :current_id
                ORDER BY COALESCE(m.sent_at, c.updated_at) DESC";
            $stmt = $db->prepare($sql_convos);
            $stmt->execute([':current_id' => $tourist_ID]);
            $raw_conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($raw_conversations as $conv) {
                $conversations[] = [
                    'conversation_ID' => (int)$conv['conversation_ID'],
                    'guide_ID' => isset($conv['guide_ID']) ? (int)$conv['guide_ID'] : null,
                    'name' => trim(($conv['name_first'] ?? '') . ' ' . ($conv['name_last'] ?? '')),
                    'avatar' => $conv['avatar'] ?? null,
                    'last_message_content' => $conv['last_message_content'] ?? '',
                    'last_message_time' => $conv['last_message_time'] ?? null,
                    'unread_count' => (int)($conv['unread_count'] ?? 0),
                ];
            }

            $db->commit();

        } catch (PDOException $e) {
            $db->rollBack(); 
            error_log("fetchConversation: " . $e->getMessage());
        }
        
        return $conversations;
    }

    public function getConversationWithGuide(int $guide_ID, int $tourist_ID): ?array {
        $db = $this->connect();
        
        try {
            $sql = "SELECT account_ID FROM Guide WHERE guide_ID = :guide_ID";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':guide_ID', $guide_ID, PDO::PARAM_INT);
            $stmt->execute();
            $guide_row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$guide_row) {
                return null;
            }

            $guide_account_ID = $guide_row['account_ID'];

            $sql = "SELECT conversation_ID FROM Conversation 
                    WHERE (user1_account_ID = :tourist AND user2_account_ID = :guide)
                       OR (user1_account_ID = :guide AND user2_account_ID = :tourist)";
            $stmt = $db->prepare($sql);
            $stmt->execute([':tourist' => $tourist_ID, ':guide' => $guide_account_ID]);
            $convo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$convo) {
                $sql = "INSERT INTO Conversation (user1_account_ID, user2_account_ID) 
                        VALUES (:tourist, :guide)";
                $stmt = $db->prepare($sql);
                $stmt->execute([':tourist' => $tourist_ID, ':guide' => $guide_account_ID]);
                $conversation_ID = $db->lastInsertId();
            } else {
                $conversation_ID = $convo['conversation_ID'];
            }

            $sql = "SELECT 
                        g.guide_ID,
                        ai.account_ID,
                        ai.account_profilepic,
                        ni.name_first,
                        ni.name_last,
                        ci.contactinfo_email
                    FROM Guide g
                    JOIN Account_Info ai ON g.account_ID = ai.account_ID
                    JOIN User_Login ul ON ai.user_ID = ul.user_ID
                    JOIN Person p ON ul.person_ID = p.person_ID
                    LEFT JOIN Name_Info ni ON p.name_ID = ni.name_ID
                    LEFT JOIN Contact_Info ci ON p.contactinfo_ID = ci.contactinfo_ID
                    WHERE g.guide_ID = :guide_ID";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':guide_ID', $guide_ID, PDO::PARAM_INT);
            $stmt->execute();
            $guide_details = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$guide_details) {
                return null;
            }

            $sql = "SELECT 
                        m.message_ID,
                        m.sender_account_ID,
                        m.message_content,
                        m.sent_at
                    FROM Message m
                    WHERE m.conversation_ID = :conversation_ID
                    ORDER BY m.sent_at ASC";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':conversation_ID', $conversation_ID, PDO::PARAM_INT);
            $stmt->execute();
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'conversation_ID' => $conversation_ID,
                'guide_ID' => $guide_details['guide_ID'],
                'guide_name' => trim(($guide_details['name_first'] ?? '') . ' ' . ($guide_details['name_last'] ?? '')),
                'guide_photo' => $guide_details['account_profilepic'] ?? null,
                'guide_email' => $guide_details['contactinfo_email'] ?? null,
                'is_online' => false,
                'messages' => $messages
            ];

        } catch (PDOException $e) {
            error_log("getConversationWithGuide: " . $e->getMessage());
            return null;
        }
    }

}

?>