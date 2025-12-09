<?php
// Функція для отримання всіх користувачів (для випадаючого списку)

function getWarehouseUsers($mysqli, $excludeUserIds = []) {
    $users = [];
    
    if (empty($excludeUserIds) || !is_array($excludeUserIds)) {
        $result = $mysqli->query('SELECT id, full_name, login FROM access_users ORDER BY full_name');
    } else {
        // Фільтруємо тільки числові ID
        $excludeUserIds = array_filter(array_map('intval', $excludeUserIds));
        
        if (empty($excludeUserIds)) {
            $result = $mysqli->query('SELECT id, full_name, login FROM access_users ORDER BY full_name');
        } else {
            $placeholders = implode(',', array_fill(0, count($excludeUserIds), '?'));
            $stmt = $mysqli->prepare("SELECT id, full_name, login FROM access_users WHERE id NOT IN ($placeholders) ORDER BY full_name");
            if ($stmt) {
                $types = str_repeat('i', count($excludeUserIds));
                $stmt->bind_param($types, ...$excludeUserIds);
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                return $users;
            }
        }
    }
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        if (isset($stmt)) {
            $stmt->close();
        } else {
            $result->free();
        }
    }
    
    return $users;
}

