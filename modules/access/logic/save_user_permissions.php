<?php
// Збереження призначених доступів для користувача
function saveUserPermissions($mysqli, $userId, $permissionIds) {
    if ($userId <= 0) {
        return ['success' => false, 'message' => 'Невірний ID користувача'];
    }
    
    // Починаємо транзакцію
    $mysqli->begin_transaction();
    
    try {
        // Видаляємо всі існуючі доступи користувача
        $deleteStmt = $mysqli->prepare("DELETE FROM user_permissions WHERE user_id = ?");
        if (!$deleteStmt) {
            throw new Exception('Помилка підготовки запиту для видалення: ' . $mysqli->error);
        }
        $deleteStmt->bind_param("i", $userId);
        $deleteStmt->execute();
        $deleteStmt->close();
        
        // Додаємо нові доступи
        if (!empty($permissionIds)) {
            $insertStmt = $mysqli->prepare("INSERT INTO user_permissions (user_id, permission_id) VALUES (?, ?)");
            if (!$insertStmt) {
                throw new Exception('Помилка підготовки запиту для додавання: ' . $mysqli->error);
            }
            
            foreach ($permissionIds as $permissionId) {
                $permissionId = (int) $permissionId;
                if ($permissionId > 0) {
                    $insertStmt->bind_param("ii", $userId, $permissionId);
                    if (!$insertStmt->execute()) {
                        throw new Exception('Помилка додавання доступу: ' . $mysqli->error);
                    }
                }
            }
            
            $insertStmt->close();
        }
        
        $mysqli->commit();
        return ['success' => true, 'message' => 'Доступи успішно збережено'];
    } catch (Exception $e) {
        $mysqli->rollback();
        return ['success' => false, 'message' => 'Помилка збереження доступів: ' . $e->getMessage()];
    }
}
?>



