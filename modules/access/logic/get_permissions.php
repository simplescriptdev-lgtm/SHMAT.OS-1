<?php
// Отримання всіх доступів, згрупованих за блоками
function getPermissions($mysqli) {
    $permissions = [];
    $sql = "
        SELECT 
            p.id,
            p.block_id,
            p.name,
            p.code,
            p.notes,
            p.created_at,
            pb.name as block_name
        FROM permissions p
        LEFT JOIN permission_blocks pb ON p.block_id = pb.id
        ORDER BY pb.id, p.created_at DESC
    ";
    $result = $mysqli->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $blockId = $row['block_id'];
            if (!isset($permissions[$blockId])) {
                $permissions[$blockId] = [
                    'block_id' => $blockId,
                    'block_name' => $row['block_name'],
                    'items' => []
                ];
            }
            $permissions[$blockId]['items'][] = $row;
        }
        $result->free();
    }
    return $permissions;
}
?>



