<?php
// Блок 1 модального вікна - Вибір постачальника
require_once __DIR__ . '/../../logic/index.php';
$suppliers = getSuppliers($mysqli);
?>
<div class="goods-receipt-modal-block">
    <div class="goods-receipt-modal-block-header">
        <h4>Вкажіть постачальника</h4>
    </div>
    <div class="goods-receipt-modal-block-content">
        <select id="receiptSupplier" class="goods-receipt-select" required>
            <option value="">Виберіть постачальника</option>
            <?php foreach ($suppliers as $supplier): ?>
                <option value="<?php echo $supplier['id']; ?>">
                    <?php echo htmlspecialchars($supplier['name'], ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
