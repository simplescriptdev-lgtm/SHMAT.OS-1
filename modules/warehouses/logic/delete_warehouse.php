<?php
// Функція для видалення складу

function deleteWarehouse($mysqli, $id) {
    $stmt = $mysqli->prepare('DELETE FROM warehouses WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Склад успішно видалено.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Помилка при видаленні складу: ' . $mysqli->error];
        }
    } else {
        return ['success' => false, 'message' => 'Помилка підготовки запиту: ' . $mysqli->error];
    }
}



