<?php
// Функція для видалення користувача

function deleteAccessUser($mysqli, $id) {
    $stmt = $mysqli->prepare('DELETE FROM access_users WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Користувача успішно видалено.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Помилка при видаленні користувача: ' . $mysqli->error];
        }
    } else {
        return ['success' => false, 'message' => 'Помилка підготовки запиту: ' . $mysqli->error];
    }
}



