<?php
// Функція для отримання складу за ID з користувачами

function getWarehouse($mysqli, $warehouseId) {
    $stmt = $mysqli->prepare('
        SELECT 
            id,
            name,
            identification_number,
            description,
            has_scheme,
            created_at,
            updated_at
        FROM warehouses
        WHERE id = ?
    ');
    
    if ($stmt) {
        $stmt->bind_param('i', $warehouseId);
        $stmt->execute();
        $result = $stmt->get_result();
        $warehouse = $result->fetch_assoc();
        $stmt->close();
        
        if ($warehouse) {
            // Отримуємо користувачів для цього складу
            $usersStmt = $mysqli->prepare('
                SELECT 
                    u.id,
                    u.full_name,
                    u.login
                FROM warehouse_users wu
                INNER JOIN access_users u ON wu.user_id = u.id
                WHERE wu.warehouse_id = ?
                ORDER BY u.full_name
            ');
            $usersStmt->bind_param('i', $warehouseId);
            $usersStmt->execute();
            $usersResult = $usersStmt->get_result();
            
            $users = [];
            while ($userRow = $usersResult->fetch_assoc()) {
                $users[] = $userRow;
            }
            $usersStmt->close();
            
            $warehouse['users'] = $users;
        }
        
        return $warehouse;
    }
    return null;
}



