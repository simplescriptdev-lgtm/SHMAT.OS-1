<?php
// Блок 14 модуля Прихід товару - Таблиця постачальників
require_once __DIR__ . '/../logic/index.php';

$suppliers = getSuppliers($mysqli);
?>
<div class="goods-receipt-block">
    <div style="padding: 16px;">
        <h4 style="margin: 0 0 12px; font-size: 14px; font-weight: 600;">Список постачальників</h4>
        <?php if (empty($suppliers)): ?>
            <p style="color: #6b7280; font-size: 13px;">Постачальники ще не додані.</p>
        <?php else: ?>
            <div class="goods-receipt-table-container">
                <table class="goods-receipt-table">
                    <thead>
                        <tr>
                            <th>№</th>
                            <th>Назва постачальника</th>
                            <th>Інформація</th>
                            <th>Нотатки</th>
                            <th>Створено</th>
                            <th>Дії</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = 1;
                        foreach ($suppliers as $supplier): 
                        ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td><?php echo htmlspecialchars($supplier['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($supplier['information'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($supplier['notes'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($supplier['created_at'])); ?></td>
                                <td>
                                    <button type="button" class="goods-receipt-btn-edit" onclick="editSupplier(<?php echo $supplier['id']; ?>)">
                                        Редагувати
                                    </button>
                                    <button type="button" class="goods-receipt-btn-delete" onclick="deleteSupplier(<?php echo $supplier['id']; ?>)">
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

<!-- Модальне вікно для редагування постачальника -->
<div id="editSupplierModal" class="goods-receipt-modal">
    <div class="goods-receipt-modal-content">
        <div class="goods-receipt-modal-header">
            <h3>Редагувати постачальника</h3>
            <span class="goods-receipt-modal-close" onclick="closeEditSupplierModal()">&times;</span>
        </div>
        <div class="goods-receipt-modal-body">
            <form id="editSupplierForm" onsubmit="handleUpdateSupplier(event)">
                <input type="hidden" id="editSupplierId" name="id">
                
                <div class="goods-receipt-form-field">
                    <label for="editSupplierName">Назва постачальника *</label>
                    <input type="text" id="editSupplierName" name="name" required placeholder="Введіть назву постачальника">
                </div>

                <div class="goods-receipt-form-field">
                    <label for="editSupplierInformation">Інформація про постачальника</label>
                    <textarea id="editSupplierInformation" name="information" rows="4" placeholder="Введіть інформацію про постачальника"></textarea>
                </div>

                <div class="goods-receipt-form-field">
                    <label for="editSupplierNotes">Нотатки</label>
                    <textarea id="editSupplierNotes" name="notes" rows="3" placeholder="Введіть нотатки"></textarea>
                </div>

                <div class="goods-receipt-modal-actions">
                    <button type="button" class="goods-receipt-btn-secondary" onclick="closeEditSupplierModal()">Скасувати</button>
                    <button type="submit" class="goods-receipt-btn-primary">Зберегти зміни</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
window.editSupplier = function(supplierId) {
    const modal = document.getElementById('editSupplierModal');
    modal.style.display = 'flex';
    
    fetch(`/modules/goods_receipt/logic/handle_supplier_action.php?action=get&id=${supplierId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.supplier) {
                document.getElementById('editSupplierId').value = data.supplier.id;
                document.getElementById('editSupplierName').value = data.supplier.name;
                document.getElementById('editSupplierInformation').value = data.supplier.information || '';
                document.getElementById('editSupplierNotes').value = data.supplier.notes || '';
                document.getElementById('editSupplierName').focus();
            } else {
                alert('Помилка завантаження даних постачальника');
                closeEditSupplierModal();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Помилка при завантаженні даних');
            closeEditSupplierModal();
        });
};

window.closeEditSupplierModal = function() {
    document.getElementById('editSupplierModal').style.display = 'none';
    document.getElementById('editSupplierForm').reset();
};

window.handleUpdateSupplier = function(event) {
    event.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('id', document.getElementById('editSupplierId').value);
    formData.append('name', document.getElementById('editSupplierName').value.trim());
    formData.append('information', document.getElementById('editSupplierInformation').value.trim());
    formData.append('notes', document.getElementById('editSupplierNotes').value.trim());

    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Збереження...';

    fetch('/modules/goods_receipt/logic/handle_supplier_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeEditSupplierModal();
            location.reload();
        } else {
            alert(data.message || 'Помилка при оновленні постачальника');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Помилка при відправці запиту');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
};

window.deleteSupplier = function(supplierId) {
    if (!confirm('Ви впевнені, що хочете видалити цього постачальника?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', supplierId);

    fetch('/modules/goods_receipt/logic/handle_supplier_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Помилка при видаленні постачальника');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Помилка при відправці запиту');
    });
};

// Закрити модальне вікно редагування при кліку поза ним
document.addEventListener('click', function(event) {
    const editModal = document.getElementById('editSupplierModal');
    if (editModal && event.target === editModal) {
        closeEditSupplierModal();
    }
});
</script>
