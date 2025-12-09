<?php
// Відхилення переміщення товарів між складами
function rejectTransfer($mysqli, $transferId, $userId) {
    if ($transferId <= 0) {
        return ['success' => false, 'message' => 'Невірний ID переміщення'];
    }
    
    try {
        // Оновлюємо статус переміщення
        $updateStmt = $mysqli->prepare("
            UPDATE warehouse_transfers
            SET status = 'rejected', approved_by_user_id = ?, updated_at = NOW()
            WHERE id = ? AND status = 'pending'
        ");
        $updateStmt->bind_param("ii", $userId, $transferId);
        $updateStmt->execute();
        
        if ($updateStmt->affected_rows === 0) {
            $updateStmt->close();
            return ['success' => false, 'message' => 'Переміщення не знайдено або вже оброблено'];
        }
        
        $updateStmt->close();
        return ['success' => true, 'message' => 'Переміщення відхилено'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Помилка відхилення переміщення: ' . $e->getMessage()];
    }
}
?>



