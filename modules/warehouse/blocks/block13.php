<?php
// Блок 3 вкладки "Переміщення" - Активні переміщення, що очікують підтвердження
require_once __DIR__ . '/../logic/index.php';
require_once __DIR__ . '/../../transfers/logic/index.php';

// Отримуємо ID складу з GET параметра або з глобальної змінної
$warehouseId = isset($_GET['id']) ? (int) $_GET['id'] : (isset($GLOBALS['current_warehouse_id']) ? (int) $GLOBALS['current_warehouse_id'] : 0);

$pendingTransfers = [];

if ($warehouseId > 0) {
    $pendingTransfers = getPendingTransfers($mysqli, $warehouseId);
}
?>
<div class="warehouse-block">
    <div style="padding: 20px;">
        <h4 style="margin: 0 0 16px; font-size: 16px; font-weight: 600; color: #111827;">Переміщення, що очікують підтвердження</h4>
        <?php if (empty($pendingTransfers)): ?>
            <div style="text-align: center; padding: 40px 20px; color: #6b7280; font-size: 14px; background: #f9fafb; border-radius: 12px;">
                <p style="margin: 0;">Немає активних переміщень, що очікують підтвердження.</p>
            </div>
        <?php else: ?>
            <?php foreach ($pendingTransfers as $transfer): ?>
                <div style="margin-bottom: 20px; padding: 20px; background: #ffffff; border: 2px solid #10b981; border-radius: 12px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px;">
                        <div>
                            <h5 style="margin: 0 0 8px; font-size: 15px; font-weight: 600; color: #111827;">
                                Переміщення зі складу "<?php echo htmlspecialchars($transfer['from_warehouse_name'], ENT_QUOTES, 'UTF-8'); ?>"
                            </h5>
                            <p style="margin: 0; font-size: 13px; color: #6b7280;">
                                На склад "<?php echo htmlspecialchars($transfer['to_warehouse_name'], ENT_QUOTES, 'UTF-8'); ?>"
                            </p>
                            <p style="margin: 4px 0 0; font-size: 12px; color: #9ca3af;">
                                Створено: <?php echo date('d.m.Y H:i', strtotime($transfer['created_at'])); ?>
                            </p>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button type="button" class="warehouse-transfer-approve-btn" onclick="approveTransfer(<?php echo $transfer['id']; ?>)">
                                Підтвердити
                            </button>
                            <button type="button" class="warehouse-transfer-reject-btn" onclick="rejectTransfer(<?php echo $transfer['id']; ?>)">
                                Відхилити
                            </button>
                        </div>
                    </div>
                    
                    <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb;">
                        <h6 style="margin: 0 0 12px; font-size: 13px; font-weight: 600; color: #374151;">Товари:</h6>
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f9fafb;">
                                    <th style="padding: 8px; text-align: left; font-size: 12px; font-weight: 600;">Товар</th>
                                    <th style="padding: 8px; text-align: center; font-size: 12px; font-weight: 600;">Кількість</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transfer['items'] as $item): ?>
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 10px; font-size: 13px;">
                                            <?php echo htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8'); ?>
                                            <?php if (!empty($item['product_article'])): ?>
                                                <span style="color: #6b7280; font-size: 12px;">(<?php echo htmlspecialchars($item['product_article'], ENT_QUOTES, 'UTF-8'); ?>)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 10px; text-align: center; font-size: 13px; font-weight: 500;">
                                            <?php echo number_format($item['quantity'], 3, '.', ' '); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
window.approveTransfer = function(transferId) {
    if (!confirm('Ви впевнені, що хочете підтвердити це переміщення? Товар буде переміщено між складами.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'approve_transfer');
    formData.append('transfer_id', transferId);
    
    fetch('/modules/transfers/logic/handle_transfer_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Переміщення успішно підтверджено.');
            location.reload();
        } else {
            alert(data.message || 'Помилка при підтвердженні переміщення.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Помилка при відправці запиту');
    });
};

window.rejectTransfer = function(transferId) {
    if (!confirm('Ви впевнені, що хочете відхилити це переміщення?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'reject_transfer');
    formData.append('transfer_id', transferId);
    
    fetch('/modules/transfers/logic/handle_transfer_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Переміщення відхилено.');
            location.reload();
        } else {
            alert(data.message || 'Помилка при відхиленні переміщення.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Помилка при відправці запиту');
    });
};
</script>
