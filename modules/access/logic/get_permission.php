<?php
// Отримання одного доступу за ID
function getPermission($mysqli, $id) {
    $stmt = $mysqli->prepare("SELECT id, block_id, name, code, notes, created_at FROM permissions WHERE id = ?");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $permission = $result->fetch_assoc();
    $stmt->close();
    return $permission;
}
?>



