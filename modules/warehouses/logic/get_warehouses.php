<?php
// Функція для отримання всіх складів з їх користувачами

function getWarehouses($mysqli) {
    $result = $mysqli->query('
        SELECT 
            w.id,
            w.name,
            w.identification_number,
            w.description,
            w.has_scheme,
            w.created_at,
            w.updated_at
        FROM warehouses w
        ORDER BY w.created_at DESC
    ');
    
    $warehouses = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $warehouseId = $row['id'];
            
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
            
            $row['users'] = $users;
            $warehouses[] = $row;
        }
        $result->free();
    }
    return $warehouses;
}

