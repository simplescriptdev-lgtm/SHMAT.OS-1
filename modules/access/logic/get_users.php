<?php
// Функція для отримання всіх користувачів

function getAccessUsers($mysqli) {
    $result = $mysqli->query('SELECT id, full_name, login, password_hash, notes, created_at, updated_at FROM access_users ORDER BY created_at DESC');
    $users = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $result->free();
    }
    return $users;
}



