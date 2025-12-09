<?php
// Блок 3 модуля Прихід товару - Таблиця приходів товарів
require_once __DIR__ . '/../logic/index.php';

$receipts = getGoodsReceipts($mysqli);
?>
<div class="goods-receipt-block">
    <div style="padding: 16px;">
        <h4 style="margin: 0 0 12px; font-size: 14px; font-weight: 600;">Список приходів товарів</h4>
        <?php if (empty($receipts)): ?>
            <p style="color: #6b7280; font-size: 13px;">Приходи ще не створені.</p>
        <?php else: ?>
            <div class="goods-receipt-table-container">
                <table class="goods-receipt-table">
                    <thead>
                        <tr>
                            <th>№</th>
                            <th>Номер накладної</th>
                            <th>Постачальник</th>
                            <th>Кількість позицій</th>
                            <th>Сума приходу</th>
                            <th>Створено</th>
                            <th>Дії</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = 1;
                        foreach ($receipts as $receipt): 
                        ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td><?php echo htmlspecialchars($receipt['receipt_number'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($receipt['supplier_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo $receipt['items_count']; ?></td>
                                <td><?php echo number_format($receipt['total_amount'], 2, '.', ' '); ?> грн</td>
                                <td><?php echo date('d.m.Y H:i', strtotime($receipt['created_at'])); ?></td>
                                <td>
                                    <button type="button" class="goods-receipt-btn-view" onclick="viewReceipt(<?php echo $receipt['id']; ?>)">
                                        Перегляд накладної
                                    </button>
                                    <button type="button" class="goods-receipt-btn-edit" onclick="editReceipt(<?php echo $receipt['id']; ?>)">
                                        Редагувати
                                    </button>
                                    <button type="button" class="goods-receipt-btn-delete" onclick="deleteReceipt(<?php echo $receipt['id']; ?>)">
                                        Видалити
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

<!-- Модальне вікно для перегляду накладної -->
<div id="viewReceiptModal" class="goods-receipt-modal">
    <div class="goods-receipt-modal-content goods-receipt-modal-content-large">
        <div class="goods-receipt-modal-header">
            <h3>Накладна приходу</h3>
            <span class="goods-receipt-modal-close" onclick="closeViewReceiptModal()">&times;</span>
        </div>
        <div class="goods-receipt-modal-body">
            <div id="viewReceiptContent">
                <p style="text-align: center; padding: 20px;">Завантаження...</p>
            </div>
        </div>
    </div>
</div>

<script>
window.viewReceipt = function(receiptId) {
    const modal = document.getElementById('viewReceiptModal');
    modal.style.display = 'flex';
    
    // Показуємо індикатор завантаження
    document.getElementById('viewReceiptContent').innerHTML = '<p style="text-align: center; padding: 20px;">Завантаження...</p>';
    
    fetch(`/modules/goods_receipt/logic/handle_receipt_action.php?action=get&id=${receiptId}`)
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Receipt data:', data); // Діагностика
            console.log('Receipt items:', data.receipt?.items); // Діагностика товарів
            if (data.success && data.receipt) {
                const receipt = data.receipt;
                let html = `
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                            <div><strong>Номер накладної:</strong> ${escapeHtml(receipt.receipt_number)}</div>
                            <div><strong>Дата:</strong> ${new Date(receipt.created_at).toLocaleDateString('uk-UA')}</div>
                        </div>
                        <div style="margin-bottom: 12px;"><strong>Постачальник:</strong> ${escapeHtml(receipt.supplier_name)}</div>
                        <div style="margin-bottom: 20px;"><strong>Загальна сума:</strong> ${parseFloat(receipt.total_amount).toFixed(2)} грн</div>
                    </div>
                `;
                
                // Перевіряємо, чи є товари
                console.log('Items check:', {
                    hasItems: !!receipt.items,
                    isArray: Array.isArray(receipt.items),
                    length: receipt.items?.length,
                    items: receipt.items
                });
                
                if (receipt.items && Array.isArray(receipt.items) && receipt.items.length > 0) {
                    html += `
                        <table class="goods-receipt-table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background-color: #f3f4f6;">
                                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #e5e7eb;">№</th>
                                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #e5e7eb;">Товар</th>
                                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #e5e7eb;">Артикул</th>
                                    <th style="padding: 12px; text-align: right; border-bottom: 2px solid #e5e7eb;">Кількість</th>
                                    <th style="padding: 12px; text-align: right; border-bottom: 2px solid #e5e7eb;">Ціна</th>
                                    <th style="padding: 12px; text-align: right; border-bottom: 2px solid #e5e7eb;">Сума</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    
                    receipt.items.forEach((item, index) => {
                        console.log('Rendering item:', item);
                        html += `
                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                <td style="padding: 12px;">${index + 1}</td>
                                <td style="padding: 12px;">${escapeHtml(item.product_name || '')}</td>
                                <td style="padding: 12px;">${escapeHtml(item.product_article || '')}</td>
                                <td style="padding: 12px; text-align: right;">${parseFloat(item.quantity || 0).toFixed(3)}</td>
                                <td style="padding: 12px; text-align: right;">${parseFloat(item.unit_price || 0).toFixed(2)} грн</td>
                                <td style="padding: 12px; text-align: right;">${parseFloat(item.total_price || 0).toFixed(2)} грн</td>
                            </tr>
                        `;
                    });
                    
                    html += `
                            </tbody>
                        </table>
                    `;
                } else {
                    html += '<p style="color: #6b7280; text-align: center; padding: 20px;">Товари не знайдені в накладній. Можливо, товари не були збережені при створенні приходу.</p>';
                }
                
                document.getElementById('viewReceiptContent').innerHTML = html;
            } else {
                document.getElementById('viewReceiptContent').innerHTML = '<p style="color: #dc2626; text-align: center; padding: 20px;">Помилка завантаження накладної</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('viewReceiptContent').innerHTML = '<p style="color: #dc2626; text-align: center; padding: 20px;">Помилка при завантаженні даних</p>';
        });
};

window.closeViewReceiptModal = function() {
    document.getElementById('viewReceiptModal').style.display = 'none';
};

window.editReceipt = function(receiptId) {
    // TODO: Реалізувати редагування приходу
    alert('Функція редагування приходу буде реалізована пізніше.');
};

window.deleteReceipt = function(receiptId) {
    if (!confirm('Ви впевнені, що хочете видалити цей прихід? Товар буде повернено зі складу.')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', receiptId);

    fetch('/modules/goods_receipt/logic/handle_receipt_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Помилка при видаленні приходу');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Помилка при відправці запиту');
    });
};

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
