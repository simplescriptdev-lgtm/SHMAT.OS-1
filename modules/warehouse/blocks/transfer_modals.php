<?php
// Модальні вікна для переміщення товарів
require_once __DIR__ . '/../logic/index.php';
$warehouseId = isset($_GET['id']) ? (int) $_GET['id'] : (isset($GLOBALS['current_warehouse_id']) ? (int) $GLOBALS['current_warehouse_id'] : 0);
$warehouse = getWarehouseById($mysqli, $warehouseId);
?>

<!-- Модальне вікно для вказання кількості товару -->
<div id="transferProductModal" class="warehouse-modal" style="display: none !important;">
    <div class="warehouse-modal-content">
        <div class="warehouse-modal-header">
            <h3>Перемістити товар</h3>
            <span class="warehouse-modal-close" onclick="closeTransferProductModal()">&times;</span>
        </div>
        <div class="warehouse-modal-body">
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 500; color: #111827;">Назва товару:</label>
                <div id="transferProductName" style="padding: 8px 12px; background: #f9fafb; border-radius: 8px; color: #111827;"></div>
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 500; color: #111827;">Доступна кількість:</label>
                <div id="transferAvailableQuantity" style="padding: 8px 12px; background: #f9fafb; border-radius: 8px; color: #111827;"></div>
            </div>
            <div style="margin-bottom: 20px;">
                <label for="transferQuantity" style="display: block; margin-bottom: 6px; font-weight: 500; color: #111827;">Кількість для переміщення:</label>
                <input type="number" id="transferQuantity" step="0.001" min="0.001" value="1" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="warehouse-btn-secondary" onclick="closeTransferProductModal()">Скасувати</button>
                <button type="button" class="warehouse-btn-primary" onclick="addToTransferCart()">Додати</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальне вікно корзини переміщення -->
<div id="transferCartModal" class="warehouse-modal" style="display: none !important;">
    <div class="warehouse-modal-content" style="max-width: 600px;">
        <div class="warehouse-modal-header">
            <h3>Корзина переміщення</h3>
            <span class="warehouse-modal-close" onclick="closeTransferCartModal()">&times;</span>
        </div>
        <div class="warehouse-modal-body">
            <div id="transferCartItems" style="margin-bottom: 20px;">
                <!-- Товари будуть додані через JavaScript -->
            </div>
            <div style="margin-bottom: 16px; padding: 12px; background: #eff6ff; border-radius: 8px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 500; color: #111827;">Склад відправлення:</label>
                <div id="transferFromWarehouse" style="font-weight: 600; color: #1d4ed8;">
                    <?php echo htmlspecialchars($warehouse['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                </div>
            </div>
            <div style="margin-bottom: 20px;">
                <label for="transferToWarehouse" style="display: block; margin-bottom: 6px; font-weight: 500; color: #111827;">Склад призначення:</label>
                <select id="transferToWarehouse" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                    <option value="">Оберіть склад...</option>
                    <!-- Склади будуть завантажені через JavaScript -->
                </select>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="warehouse-btn-secondary" onclick="closeTransferCartModal()">Закрити</button>
                <button type="button" class="warehouse-btn-primary" onclick="createTransfer()">Перемістити</button>
            </div>
        </div>
    </div>
</div>
