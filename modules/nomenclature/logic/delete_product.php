<?php
// Функція для видалення товару

function deleteProduct($mysqli, $id) {
    $stmt = $mysqli->prepare('DELETE FROM products WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Товар успішно видалено.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Помилка при видаленні товару: ' . $mysqli->error];
        }
    } else {
        return ['success' => false, 'message' => 'Помилка підготовки запиту: ' . $mysqli->error];
    }
}



