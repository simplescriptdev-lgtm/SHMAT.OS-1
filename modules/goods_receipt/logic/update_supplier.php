<?php
// Функція для оновлення постачальника

function updateSupplier($mysqli, $id, $name, $information = null, $notes = null) {
    if (empty(trim($name))) {
        return ['success' => false, 'message' => 'Назва постачальника не може бути порожньою.'];
    }

    $name = trim($name);
    $information = $information ? trim($information) : null;
    $notes = $notes ? trim($notes) : null;

    $stmt = $mysqli->prepare('UPDATE suppliers SET name = ?, information = ?, notes = ? WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('sssi', $name, $information, $notes, $id);
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Постачальника успішно оновлено.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Помилка при оновленні постачальника: ' . $mysqli->error];
        }
    } else {
        return ['success' => false, 'message' => 'Помилка підготовки запиту: ' . $mysqli->error];
    }
}



