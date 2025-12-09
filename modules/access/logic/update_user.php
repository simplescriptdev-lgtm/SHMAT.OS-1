<?php
// Функція для оновлення користувача

function updateAccessUser($mysqli, $id, $fullName, $login, $password = null, $notes = null) {
    if (empty(trim($fullName))) {
        return ['success' => false, 'message' => 'ПІБ користувача не може бути порожнім.'];
    }

    if (empty(trim($login))) {
        return ['success' => false, 'message' => 'Логін користувача не може бути порожнім.'];
    }

    $fullName = trim($fullName);
    $login = trim($login);
    $notes = $notes ? trim($notes) : null;

    // Перевірка на унікальність логіну (крім поточного користувача)
    $checkStmt = $mysqli->prepare('SELECT id FROM access_users WHERE login = ? AND id != ? LIMIT 1');
    if ($checkStmt) {
        $checkStmt->bind_param('si', $login, $id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        if ($result->num_rows > 0) {
            $checkStmt->close();
            return ['success' => false, 'message' => 'Користувач з таким логіном вже існує.'];
        }
        $checkStmt->close();
    }

    if ($password && !empty(trim($password))) {
        $passwordHash = password_hash(trim($password), PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare('UPDATE access_users SET full_name = ?, login = ?, password_hash = ?, notes = ? WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('ssssi', $fullName, $login, $passwordHash, $notes, $id);
        }
    } else {
        $stmt = $mysqli->prepare('UPDATE access_users SET full_name = ?, login = ?, notes = ? WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('sssi', $fullName, $login, $notes, $id);
        }
    }

    if ($stmt) {
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Користувача успішно оновлено.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Помилка при оновленні користувача: ' . $mysqli->error];
        }
    } else {
        return ['success' => false, 'message' => 'Помилка підготовки запиту: ' . $mysqli->error];
    }
}



