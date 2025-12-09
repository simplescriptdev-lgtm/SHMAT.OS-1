<?php
// Блок 13 модуля Прихід товару - Кнопка "Створити постачальника"
require_once __DIR__ . '/../logic/index.php';
?>
<div class="goods-receipt-block">
    <div style="padding: 16px;">
        <button type="button" class="goods-receipt-btn-primary" onclick="openCreateSupplierModal()">
            Створити постачальника
        </button>
    </div>
</div>

<!-- Модальне вікно для створення постачальника -->
<div id="createSupplierModal" class="goods-receipt-modal">
    <div class="goods-receipt-modal-content">
        <div class="goods-receipt-modal-header">
            <h3>Створити постачальника</h3>
            <span class="goods-receipt-modal-close" onclick="closeCreateSupplierModal()">&times;</span>
        </div>
        <div class="goods-receipt-modal-body">
            <form id="createSupplierForm" onsubmit="handleCreateSupplier(event)">
                <div class="goods-receipt-form-field">
                    <label for="supplierName">Назва постачальника *</label>
                    <input type="text" id="supplierName" name="name" required placeholder="Введіть назву постачальника">
                </div>

                <div class="goods-receipt-form-field">
                    <label for="supplierInformation">Інформація про постачальника</label>
                    <textarea id="supplierInformation" name="information" rows="4" placeholder="Введіть інформацію про постачальника"></textarea>
                </div>

                <div class="goods-receipt-form-field">
                    <label for="supplierNotes">Нотатки</label>
                    <textarea id="supplierNotes" name="notes" rows="3" placeholder="Введіть нотатки"></textarea>
                </div>

                <div class="goods-receipt-modal-actions">
                    <button type="button" class="goods-receipt-btn-secondary" onclick="closeCreateSupplierModal()">Закрити</button>
                    <button type="submit" class="goods-receipt-btn-primary">Зберегти</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
window.openCreateSupplierModal = function() {
    const modal = document.getElementById('createSupplierModal');
    if (modal) {
        modal.style.display = 'flex';
        document.getElementById('supplierName').focus();
    }
};

window.closeCreateSupplierModal = function() {
    const modal = document.getElementById('createSupplierModal');
    if (modal) {
        modal.style.display = 'none';
    }
    const form = document.getElementById('createSupplierForm');
    if (form) {
        form.reset();
    }
};

window.handleCreateSupplier = function(event) {
    event.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('name', document.getElementById('supplierName').value.trim());
    formData.append('information', document.getElementById('supplierInformation').value.trim());
    formData.append('notes', document.getElementById('supplierNotes').value.trim());

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
            closeCreateSupplierModal();
            location.reload();
        } else {
            alert(data.message || 'Помилка при створенні постачальника');
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
}

// Закрити модальне вікно при кліку поза ним
document.addEventListener('click', function(event) {
    const modal = document.getElementById('createSupplierModal');
    if (modal && event.target === modal) {
        closeCreateSupplierModal();
    }
});
</script>
