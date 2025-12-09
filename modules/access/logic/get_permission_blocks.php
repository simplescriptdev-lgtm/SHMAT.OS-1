<?php
// Отримання всіх блоків дозволів
function getPermissionBlocks($mysqli) {
    $blocks = [];
    $sql = "SELECT id, name, notes, created_at FROM permission_blocks ORDER BY created_at DESC";
    $result = $mysqli->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $blocks[] = $row;
        }
        $result->free();
    }
    return $blocks;
}
?>



