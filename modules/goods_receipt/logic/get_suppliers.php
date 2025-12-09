<?php
// Функція для отримання всіх постачальників

function getSuppliers($mysqli) {
    $result = $mysqli->query('SELECT id, name, information, notes, created_at, updated_at FROM suppliers ORDER BY created_at DESC');
    $suppliers = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $suppliers[] = $row;
        }
        $result->free();
    }
    return $suppliers;
}



