<?php
// Функція для створення нового постачальника

function createSupplier($mysqli, $name, $information = null, $notes = null) {
    if (empty(trim($name))) {
        return ['success' => false, 'message' => 'Назва постачальника не може бути порожньою.'];
    }

    $name = trim($name);
    $information = $information ? trim($information) : null;
    $notes = $notes ? trim($notes) : null;

    $stmt = $mysqli->prepare('INSERT INTO suppliers (name, information, notes) VALUES (?, ?, ?)');
    if ($stmt) {
        $stmt->bind_param('sss', $name, $information, $notes);
        if ($stmt->execute()) {
            $supplierId = $mysqli->insert_id;
            $stmt->close();
            return ['success' => true, 'message' => 'Постачальника успішно створено.', 'id' => $supplierId];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Помилка при створенні постачальника: ' . $mysqli->error];
        }
    } else {
        return ['success' => false, 'message' => 'Помилка підготовки запиту: ' . $mysqli->error];
    }
}



