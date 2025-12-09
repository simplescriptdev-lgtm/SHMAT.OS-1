<?php
// Функція для отримання користувача за ID

function getAccessUser($mysqli, $userId) {
    $stmt = $mysqli->prepare('SELECT id, full_name, login, password_hash, notes, created_at, updated_at FROM access_users WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }
    return null;
}



