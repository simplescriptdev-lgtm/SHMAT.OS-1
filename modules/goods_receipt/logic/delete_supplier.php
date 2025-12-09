<?php
// Функція для видалення постачальника

function deleteSupplier($mysqli, $id) {
    $stmt = $mysqli->prepare('DELETE FROM suppliers WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Постачальника успішно видалено.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Помилка при видаленні постачальника: ' . $mysqli->error];
        }
    } else {
        return ['success' => false, 'message' => 'Помилка підготовки запиту: ' . $mysqli->error];
    }
}



