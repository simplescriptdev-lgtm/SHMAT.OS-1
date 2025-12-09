<?php
// Функція для створення нового користувача

function createAccessUser($mysqli, $fullName, $login, $password, $notes = null) {
    if (empty(trim($fullName))) {
        return ['success' => false, 'message' => 'ПІБ користувача не може бути порожнім.'];
    }

    if (empty(trim($login))) {
        return ['success' => false, 'message' => 'Логін користувача не може бути порожнім.'];
    }

    if (empty(trim($password))) {
        return ['success' => false, 'message' => 'Пароль користувача не може бути порожнім.'];
    }

    $fullName = trim($fullName);
    $login = trim($login);
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $notes = $notes ? trim($notes) : null;

    // Перевірка на унікальність логіну
    $checkStmt = $mysqli->prepare('SELECT id FROM access_users WHERE login = ? LIMIT 1');
    if ($checkStmt) {
        $checkStmt->bind_param('s', $login);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        if ($result->num_rows > 0) {
            $checkStmt->close();
            return ['success' => false, 'message' => 'Користувач з таким логіном вже існує.'];
        }
        $checkStmt->close();
    }

    $stmt = $mysqli->prepare('INSERT INTO access_users (full_name, login, password_hash, notes) VALUES (?, ?, ?, ?)');
    if ($stmt) {
        $stmt->bind_param('ssss', $fullName, $login, $passwordHash, $notes);
        if ($stmt->execute()) {
            $userId = $mysqli->insert_id;
            $stmt->close();
            return ['success' => true, 'message' => 'Користувача успішно створено.', 'id' => $userId];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Помилка при створенні користувача: ' . $mysqli->error];
        }
    } else {
        return ['success' => false, 'message' => 'Помилка підготовки запиту: ' . $mysqli->error];
    }
}



