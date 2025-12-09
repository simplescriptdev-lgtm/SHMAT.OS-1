<?php
// Функція для отримання всіх приходів товару

function getGoodsReceipts($mysqli) {
    $result = $mysqli->query('
        SELECT 
            gr.id,
            gr.receipt_number,
            gr.total_amount,
            gr.items_count,
            gr.created_at,
            s.name as supplier_name
        FROM goods_receipts gr
        INNER JOIN suppliers s ON gr.supplier_id = s.id
        ORDER BY gr.created_at DESC
    ');
    
    $receipts = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $receipts[] = $row;
        }
        $result->free();
    }
    return $receipts;
}



