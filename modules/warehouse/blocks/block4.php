<?php
// Блок 4 модуля Склад - Таблиця товарів на складі
require_once __DIR__ . '/../logic/index.php';

// Отримуємо ID складу з GET параметра або з глобальної змінної
$warehouseId = isset($_GET['id']) ? (int) $_GET['id'] : (isset($GLOBALS['current_warehouse_id']) ? (int) $GLOBALS['current_warehouse_id'] : 0);

$stock = [];

if ($warehouseId > 0) {
    $stock = getWarehouseStock($mysqli, $warehouseId);
}
?>
<div class="warehouse-block">
    <div style="padding: 20px;">
        <h4 style="margin: 0 0 16px; font-size: 16px; font-weight: 600; color: #111827;">Товари на складі</h4>
        <?php if (empty($stock)): ?>
            <div style="text-align: center; padding: 40px 20px; color: #6b7280; font-size: 14px; background: #f9fafb; border-radius: 12px;">
                <p style="margin: 0;">На складі немає товарів.</p>
            </div>
        <?php else: ?>
            <div class="warehouse-table-container">
                <table class="warehouse-table">
                    <thead>
                        <tr>
                            <th>№</th>
                            <th>Назва товару</th>
                            <th>Артикул</th>
                            <th>Виробник</th>
                            <th>Категорія</th>
                            <th>Підкатегорія</th>
                            <?php
                            // Перевіряємо, чи склад має схему
                            $warehouse = getWarehouseById($mysqli, $warehouseId);
                            if ($warehouse && $warehouse['has_scheme']):
                            ?>
                                <th>Сектор\Ряд</th>
                            <?php endif; ?>
                            <th>Кількість одиниць</th>
                            <th>Дії</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = 1;
                        foreach ($stock as $item): 
                        ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td><?php echo htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($item['product_article'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($item['product_brand'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($item['category_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($item['subcategory_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <?php if ($warehouse && $warehouse['has_scheme']): ?>
                                    <td>
                                        <?php 
                                        $location = [];
                                        if (!empty($item['sector'])) {
                                            $location[] = 'Сектор: ' . htmlspecialchars($item['sector'], ENT_QUOTES, 'UTF-8');
                                        }
                                        if (!empty($item['row_number'])) {
                                            $location[] = 'Ряд: ' . htmlspecialchars($item['row_number'], ENT_QUOTES, 'UTF-8');
                                        }
                                        echo !empty($location) ? implode(', ', $location) : '-';
                                        ?>
                                    </td>
                                <?php endif; ?>
                                <td><?php echo number_format($item['quantity'], 3, '.', ' '); ?></td>
                                <td style="text-align: center;">
                                    <button type="button" class="warehouse-transfer-btn" onclick="openTransferProductModal(<?php echo $item['product_id']; ?>, '<?php echo htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8'); ?>', <?php echo $item['quantity']; ?>, <?php echo $warehouseId; ?>)">
                                        Перемістити товар
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
