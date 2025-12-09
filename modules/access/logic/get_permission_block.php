<?php
// Отримання одного блоку дозволів за ID
function getPermissionBlock($mysqli, $id) {
    $stmt = $mysqli->prepare("SELECT id, name, notes, created_at FROM permission_blocks WHERE id = ?");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $block = $result->fetch_assoc();
    $stmt->close();
    return $block;
}
?>



