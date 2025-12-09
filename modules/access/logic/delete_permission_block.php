<?php
// Видалення блоку дозволів
function deletePermissionBlock($mysqli, $id) {
    $stmt = $mysqli->prepare("DELETE FROM permission_blocks WHERE id = ?");
    if (!$stmt) {
        return ['success' => false, 'message' => 'Помилка підготовки запиту: ' . $mysqli->error];
    }
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $stmt->close();
        return ['success' => true, 'message' => 'Блок дозволів успішно видалено'];
    } else {
        $error = $stmt->error;
        $stmt->close();
        return ['success' => false, 'message' => 'Помилка видалення блоку: ' . $error];
    }
}
?>



