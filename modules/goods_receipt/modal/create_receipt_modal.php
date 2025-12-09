<?php
// Модальне вікно для створення приходу товару
require_once __DIR__ . '/../logic/index.php';

$suppliers = getSuppliers($mysqli);
?>
<!-- Модальне вікно для створення приходу -->
<div id="createReceiptModal" class="goods-receipt-modal">
    <div class="goods-receipt-modal-content goods-receipt-modal-content-large">
        <div class="goods-receipt-modal-header">
            <h3>Створити прихід</h3>
            <span class="goods-receipt-modal-close" onclick="closeCreateReceiptModal()">&times;</span>
        </div>
        <div class="goods-receipt-modal-body">
            <div class="goods-receipt-modal-blocks">
                <?php require __DIR__ . '/modal_blocks/block1.php'; ?>
                <?php require __DIR__ . '/modal_blocks/block2.php'; ?>
                <?php require __DIR__ . '/modal_blocks/block3.php'; ?>
                <?php require __DIR__ . '/modal_blocks/block4.php'; ?>
            </div>
            <div class="goods-receipt-modal-actions">
                <button type="button" class="goods-receipt-btn-secondary" onclick="closeCreateReceiptModal()">Закрити</button>
                <button type="button" class="goods-receipt-btn-primary" onclick="handleCreateReceipt()">Створити прихід</button>
            </div>
        </div>
    </div>
</div>

<script>
let selectedProducts = [];
let allProductsList = [];

window.openCreateReceiptModal = function() {
    const modal = document.getElementById('createReceiptModal');
    if (modal) {
        modal.style.display = 'flex';
        selectedProducts = [];
        allProductsList = [];
        updateSelectedProductsList();
        loadAllProducts();
        document.getElementById('receiptSupplier').focus();
    }
};

window.closeCreateReceiptModal = function() {
    const modal = document.getElementById('createReceiptModal');
    if (modal) {
        modal.style.display = 'none';
    }
    selectedProducts = [];
    allProductsList = [];
    document.getElementById('receiptSupplier').value = '';
    document.getElementById('productSearch').value = '';
    updateSelectedProductsList();
    clearProductSearch();
};

window.handleCreateReceipt = function() {
    const supplierId = document.getElementById('receiptSupplier').value;
    if (!supplierId || supplierId === '') {
        alert('Необхідно вибрати постачальника.');
        return;
    }

    if (selectedProducts.length === 0) {
        alert('Необхідно додати хоча б один товар до приходу.');
        return;
    }

    // Перевіряємо, чи всі товари мають ціну та кількість
    for (let i = 0; i < selectedProducts.length; i++) {
        const product = selectedProducts[i];
        const priceInput = document.getElementById('price_' + product.id);
        const quantityInput = document.getElementById('quantity_' + product.id);
        
        if (!priceInput || !priceInput.value || parseFloat(priceInput.value) <= 0) {
            alert('Необхідно вказати ціну для товару: ' + product.name);
            return;
        }
        
        if (!quantityInput || !quantityInput.value || parseFloat(quantityInput.value) <= 0) {
            alert('Необхідно вказати кількість для товару: ' + product.name);
            return;
        }

        product.unit_price = parseFloat(priceInput.value);
        product.quantity = parseFloat(quantityInput.value);
        product.product_id = product.id; // Додаємо product_id для PHP
        
        // Отримуємо сектор та ряд, якщо вони є
        const sectorInput = document.getElementById('sector_' + product.id);
        const rowInput = document.getElementById('row_' + product.id);
        if (sectorInput && sectorInput.value) {
            product.sector = sectorInput.value.trim();
        }
        if (rowInput && rowInput.value) {
            product.row = rowInput.value.trim();
        }
    }

    // Формуємо масив товарів для відправки (тільки необхідні поля)
    const itemsToSend = selectedProducts.map(product => ({
        product_id: product.id,
        quantity: product.quantity,
        unit_price: product.unit_price,
        sector: product.sector || null,
        row: product.row || null
    }));

    // Діагностика: виводимо дані, які відправляються
    console.log('Відправляємо дані:', {
        supplier_id: supplierId,
        items: itemsToSend
    });

    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('supplier_id', supplierId);
    formData.append('items', JSON.stringify(itemsToSend));

    const submitBtn = document.querySelector('#createReceiptModal .goods-receipt-btn-primary');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Створення...';

    fetch('/modules/goods_receipt/logic/handle_receipt_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeCreateReceiptModal();
            location.reload();
        } else {
            alert(data.message || 'Помилка при створенні приходу');
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

window.searchProducts = function(searchTerm) {
    if (searchTerm.length < 1) {
        clearProductSearch();
        return;
    }

    const excludeIds = selectedProducts.map(p => p.id);
    const excludeIdsParam = encodeURIComponent(JSON.stringify(excludeIds));
    
    fetch(`/modules/goods_receipt/logic/handle_receipt_action.php?action=search_products&term=${encodeURIComponent(searchTerm)}&exclude_ids=${excludeIdsParam}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayProductSearchResults(data.products);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
};

window.displayProductSearchResults = function(products) {
    const resultsContainer = document.getElementById('productSearchResults');
    if (!resultsContainer) return;

    if (products.length === 0) {
        resultsContainer.innerHTML = '<div style="padding: 10px; color: #6b7280; text-align: center;">Товари не знайдені</div>';
        resultsContainer.style.display = 'block';
        return;
    }

    resultsContainer.innerHTML = products.map(product => `
        <div class="goods-receipt-search-result-item">
            <div class="goods-receipt-search-result-info">
                <strong>${escapeHtml(product.name)}</strong>
                <span style="color: #6b7280; font-size: 12px;">Арт: ${escapeHtml(product.article || '')} | ${escapeHtml(product.brand || '')}</span>
            </div>
            <button type="button" class="goods-receipt-btn-add-to-receipt" onclick="addProductToReceipt(${product.id}, '${escapeHtml(product.name).replace(/'/g, "\\'")}', '${escapeHtml(product.article || '').replace(/'/g, "\\'")}', '${escapeHtml(product.brand || '').replace(/'/g, "\\'")}')">
                Додати у прихід
            </button>
        </div>
    `).join('');
    resultsContainer.style.display = 'block';
};

window.clearProductSearch = function() {
    const resultsContainer = document.getElementById('productSearchResults');
    if (resultsContainer) {
        resultsContainer.style.display = 'none';
        resultsContainer.innerHTML = '';
    }
};

window.addProductToReceipt = function(productId, productName, productArticle, productBrand) {
    // Перевіряємо, чи товар вже додано
    if (selectedProducts.some(p => p.id === productId)) {
        return;
    }

    selectedProducts.push({
        id: productId,
        name: productName,
        article: productArticle,
        brand: productBrand
    });

    updateSelectedProductsList();
    clearProductSearch();
    document.getElementById('productSearch').value = '';
    
    // Прокручуємо до списку вибраних товарів
    const selectedList = document.getElementById('selectedProductsList');
    if (selectedList) {
        selectedList.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
};

window.removeProductFromReceipt = function(productId) {
    if (!confirm('Видалити цей товар з приходу?')) {
        return;
    }

    selectedProducts = selectedProducts.filter(p => p.id !== productId);
    updateSelectedProductsList();
    
    // Оновлюємо список всіх товарів, щоб видалений товар знову з'явився
    loadAllProducts();
};

window.updateSelectedProductsList = function() {
    const container = document.getElementById('selectedProductsList');
    if (!container) return;

    if (selectedProducts.length === 0) {
        container.innerHTML = '<p style="color: #6b7280; font-size: 13px; padding: 16px; text-align: center;">Товари ще не додані</p>';
        return;
    }

    const hasSchemeInput = document.getElementById('warehouseHasScheme');
    const hasScheme = hasSchemeInput && hasSchemeInput.value === '1';

    container.innerHTML = selectedProducts.map((product, index) => {
        return `
            <div class="goods-receipt-selected-product-item">
                <div class="goods-receipt-selected-product-header">
                    <div>
                        <strong>${escapeHtml(product.name)}</strong>
                        <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                            Арт: ${escapeHtml(product.article || '')} | ${escapeHtml(product.brand || '')}
                        </div>
                    </div>
                    <button type="button" class="goods-receipt-btn-remove-product" onclick="removeProductFromReceipt(${product.id})">
                        Видалити
                    </button>
                </div>
                <div class="goods-receipt-selected-product-fields">
                    <div class="goods-receipt-field-group">
                        <label>Ціна за одиницю *</label>
                        <input type="number" id="price_${product.id}" step="0.01" min="0" required placeholder="0.00" onchange="updateProductTotal(${product.id})">
                    </div>
                    <div class="goods-receipt-field-group">
                        <label>Кількість *</label>
                        <input type="number" id="quantity_${product.id}" step="0.001" min="0.001" required placeholder="1" onchange="updateProductTotal(${product.id})">
                    </div>
                    ${hasScheme ? `
                        <div class="goods-receipt-field-group">
                            <label>Сектор</label>
                            <input type="text" id="sector_${product.id}" placeholder="Сектор">
                        </div>
                        <div class="goods-receipt-field-group">
                            <label>Ряд</label>
                            <input type="text" id="row_${product.id}" placeholder="Ряд">
                        </div>
                    ` : ''}
                    <div class="goods-receipt-field-group">
                        <label>Сума</label>
                        <input type="text" id="total_${product.id}" readonly value="0.00" style="background: #f3f4f6;">
                    </div>
                </div>
            </div>
        `;
    }).join('');
};

window.updateProductTotal = function(productId) {
    const priceInput = document.getElementById('price_' + productId);
    const quantityInput = document.getElementById('quantity_' + productId);
    const totalInput = document.getElementById('total_' + productId);
    
    if (priceInput && quantityInput && totalInput) {
        const price = parseFloat(priceInput.value) || 0;
        const quantity = parseFloat(quantityInput.value) || 0;
        const total = (price * quantity).toFixed(2);
        totalInput.value = total;
    }
};

window.loadAllProducts = function() {
    const excludeIds = selectedProducts.map(p => p.id);
    const excludeIdsParam = encodeURIComponent(JSON.stringify(excludeIds));
    
    fetch(`/modules/goods_receipt/logic/handle_receipt_action.php?action=get_all_products&limit=20&offset=0&exclude_ids=${excludeIdsParam}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allProductsList = data.products;
                displayAllProducts();
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
};

window.displayAllProducts = function() {
    const container = document.getElementById('allProductsList');
    if (!container) return;

    if (allProductsList.length === 0) {
        container.innerHTML = '<p style="color: #6b7280; font-size: 13px; padding: 16px; text-align: center;">Товари не знайдені</p>';
        return;
    }

    container.innerHTML = allProductsList.map(product => `
        <div class="goods-receipt-all-product-item">
            <div>
                <strong>${escapeHtml(product.name)}</strong>
                <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                    Арт: ${escapeHtml(product.article || '')} | ${escapeHtml(product.brand || '')}
                </div>
            </div>
            <button type="button" class="goods-receipt-btn-add-to-receipt" onclick="addProductToReceipt(${product.id}, '${escapeHtml(product.name).replace(/'/g, "\\'")}', '${escapeHtml(product.article || '').replace(/'/g, "\\'")}', '${escapeHtml(product.brand || '').replace(/'/g, "\\'")}')">
                Додати у прихід
            </button>
        </div>
    `).join('');
};

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Закрити модальне вікно при кліку поза ним
document.addEventListener('click', function(event) {
    const modal = document.getElementById('createReceiptModal');
    if (modal && event.target === modal) {
        closeCreateReceiptModal();
    }
    
    // Закрити результати пошуку при кліку поза ними
    const searchResults = document.getElementById('productSearchResults');
    if (searchResults && !event.target.closest('#productSearch') && !event.target.closest('#productSearchResults')) {
        clearProductSearch();
    }
});
</script>

