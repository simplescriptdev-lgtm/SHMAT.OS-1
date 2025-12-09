<?php
// Функція для видалення підкатегорії

function deleteSubcategory($mysqli, $id) {
    $stmt = $mysqli->prepare('DELETE FROM product_subcategories WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Підкатегорію успішно видалено.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Помилка при видаленні підкатегорії: ' . $mysqli->error];
        }
    } else {
        return ['success' => false, 'message' => 'Помилка підготовки запиту: ' . $mysqli->error];
    }
}



