<?php
// Видалення доступу
function deletePermission($mysqli, $id) {
    $stmt = $mysqli->prepare("DELETE FROM permissions WHERE id = ?");
    if (!$stmt) {
        return ['success' => false, 'message' => 'Помилка підготовки запиту: ' . $mysqli->error];
    }
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $stmt->close();
        return ['success' => true, 'message' => 'Доступ успішно видалено'];
    } else {
        $error = $stmt->error;
        $stmt->close();
        return ['success' => false, 'message' => 'Помилка видалення доступу: ' . $error];
    }
}
?>



