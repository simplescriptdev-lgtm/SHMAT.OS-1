<?php
// Блок 2 модуля Прихід товару - Кнопка "Створити прихід"
require_once __DIR__ . '/../logic/index.php';
?>
<div class="goods-receipt-block">
    <div style="padding: 16px;">
        <button type="button" class="goods-receipt-btn-primary" onclick="openCreateReceiptModal()">
            Створити прихід
        </button>
    </div>
</div>

<?php require __DIR__ . '/../modal/create_receipt_modal.php'; ?>
