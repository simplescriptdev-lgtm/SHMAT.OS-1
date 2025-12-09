<script>
// Глобальні змінні для переміщення
let currentTransferProductId = null;
let currentTransferProductName = null;
let currentTransferAvailableQuantity = 0;
let currentTransferWarehouseId = <?php echo $warehouseId; ?>;

// Відкриття модального вікна для вказання кількості
window.openTransferProductModal = function(productId, productName, availableQuantity, warehouseId) {
    console.log('openTransferProductModal викликано:', productId, productName, availableQuantity);
    
    currentTransferProductId = productId;
    currentTransferProductName = productName;
    currentTransferAvailableQuantity = parseFloat(availableQuantity);
    currentTransferWarehouseId = warehouseId;
    
    const nameEl = document.getElementById('transferProductName');
    const qtyEl = document.getElementById('transferAvailableQuantity');
    const inputEl = document.getElementById('transferQuantity');
    
    if (nameEl) nameEl.textContent = productName;
    if (qtyEl) qtyEl.textContent = parseFloat(availableQuantity).toFixed(3);
    if (inputEl) {
        inputEl.value = '1';
        inputEl.max = availableQuantity;
    }
    
    let modal = document.getElementById('transferProductModal');
    if (!modal) {
        console.warn('Модальне вікно не знайдено за ID, шукаємо в DOM...');
        modal = document.querySelector('#transferProductModal');
    }
    
    if (!modal) {
        console.error('Модальне вікно transferProductModal не знайдено!');
        alert('Помилка: модальне вікно не знайдено. Перевірте консоль браузера (F12).');
        return;
    }
    
    // Переміщуємо модальне вікно в body, якщо воно ще не там
    if (modal.parentElement !== document.body) {
        console.log('Переміщуємо модальне вікно transferProductModal в body');
        document.body.appendChild(modal);
    }
    
    // Відкриваємо модальне вікно
    modal.style.cssText = 'display: flex !important; position: fixed !important; z-index: 99999 !important; left: 0 !important; top: 0 !important; width: 100vw !important; height: 100vh !important; background-color: rgba(0, 0, 0, 0.5) !important; align-items: center !important; justify-content: center !important; overflow: auto !important; box-sizing: border-box !important; margin: 0 !important; padding: 0 !important;';
    
    const modalContent = modal.querySelector('.warehouse-modal-content');
    if (modalContent) {
        modalContent.style.cssText = 'background-color: #ffffff !important; border-radius: 16px !important; padding: 24px !important; max-width: 500px !important; width: 90% !important; max-height: 90vh !important; overflow-y: auto !important; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important; position: relative !important; z-index: 100000 !important; margin: auto !important;';
    }
    
    console.log('Модальне вікно transferProductModal відкрито');
};

// Закриття модального вікна для вказання кількості
if (typeof window.closeTransferProductModal === 'undefined') {
    window.closeTransferProductModal = function() {
        const modal = document.getElementById('transferProductModal');
        if (modal) {
            modal.style.setProperty('display', 'none', 'important');
        }
        currentTransferProductId = null;
        currentTransferProductName = null;
        currentTransferAvailableQuantity = 0;
    };
}

// Додавання товару до кошика переміщення
window.addToTransferCart = function() {
    const quantity = parseFloat(document.getElementById('transferQuantity').value);
    
    if (!quantity || quantity <= 0) {
        alert('Введіть коректну кількість');
        return;
    }
    
    if (quantity > currentTransferAvailableQuantity) {
        alert('Кількість не може перевищувати доступну');
        return;
    }
    
    console.log('Додавання товару до кошика:', {
        productId: currentTransferProductId,
        quantity: quantity,
        warehouseId: currentTransferWarehouseId
    });
    
    // Відправляємо AJAX запит для додавання до кошика
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/modules/transfers/logic/handle_transfer_action.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    console.log('Відповідь сервера при додаванні до кошика:', response);
                    if (response.success) {
                        closeTransferProductModal();
                        // Оновлюємо бейдж після невеликої затримки, щоб сервер встиг оновити сесію
                        setTimeout(function() {
                            updateTransferCartBadge();
                        }, 100);
                        alert('Товар додано до кошика переміщення');
                    } else {
                        alert('Помилка: ' + (response.message || 'Невідома помилка'));
                    }
                } catch (e) {
                    console.error('Помилка парсингу відповіді:', e);
                    console.error('Відповідь сервера:', xhr.responseText);
                    alert('Помилка обробки відповіді сервера');
                }
            } else {
                console.error('Помилка HTTP:', xhr.status);
                alert('Помилка з\'єднання з сервером');
            }
        }
    };
    xhr.send('action=add_to_cart&product_id=' + currentTransferProductId + '&quantity=' + quantity + '&warehouse_id=' + currentTransferWarehouseId);
};

// Відкриття модального вікна корзини переміщення
window.openTransferCart = function() {
    console.log('=== openTransferCart викликано ===');
    
    let modal = document.getElementById('transferCartModal');
    console.log('Модальне вікно до пошуку:', modal);
    
    // Якщо модальне вікно не знайдено, спробуємо знайти його в усіх можливих місцях
    if (!modal) {
        console.warn('Модальне вікно не знайдено за ID, шукаємо в DOM...');
        modal = document.querySelector('#transferCartModal');
        if (!modal) {
            modal = document.querySelector('.warehouse-modal[id="transferCartModal"]');
        }
    }
    
    console.log('Модальне вікно після пошуку:', modal);
    
    if (!modal) {
        console.error('Модальне вікно transferCartModal не знайдено!');
        alert('Помилка: модальне вікно не знайдено. Перевірте консоль браузера (F12).');
        return;
    }
    
    // Переміщуємо модальне вікно в body, якщо воно ще не там
    if (modal.parentElement !== document.body) {
        console.log('Переміщуємо модальне вікно в body');
        document.body.appendChild(modal);
    }
    
    // Завантажуємо дані
    loadTransferCart();
    loadWarehousesForTransfer();
    
    // Відкриваємо модальне вікно
    console.log('Встановлюємо display: flex');
    modal.style.cssText = 'display: flex !important; position: fixed !important; z-index: 99999 !important; left: 0 !important; top: 0 !important; width: 100vw !important; height: 100vh !important; background-color: rgba(0, 0, 0, 0.5) !important; align-items: center !important; justify-content: center !important; overflow: auto !important; box-sizing: border-box !important; margin: 0 !important; padding: 0 !important;';
    
    // Переконаємося, що контент модального вікна правильно стилізований
    const modalContent = modal.querySelector('.warehouse-modal-content');
    if (modalContent) {
        modalContent.style.cssText = 'background-color: #ffffff !important; border-radius: 16px !important; padding: 24px !important; max-width: 600px !important; width: 90% !important; max-height: 90vh !important; overflow-y: auto !important; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important; position: relative !important; z-index: 100000 !important; margin: auto !important;';
    }
    
    console.log('Модальне вікно відкрито, display:', modal.style.display);
    console.log('Computed style:', window.getComputedStyle(modal).display);
};

// Закриття модального вікна корзини переміщення
if (typeof window.closeTransferCartModal === 'undefined') {
    window.closeTransferCartModal = function() {
        const modal = document.getElementById('transferCartModal');
        if (modal) {
            modal.style.setProperty('display', 'none', 'important');
        }
    };
}

// Завантаження кошика переміщення
function loadTransferCart() {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/modules/transfers/logic/handle_transfer_action.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    displayTransferCartItems(response.items || []);
                }
            } catch (e) {
                console.error('Помилка парсингу відповіді:', e);
            }
        }
    };
    xhr.send('action=get_cart');
}

// Відображення товарів у кошику
function displayTransferCartItems(items) {
    const container = document.getElementById('transferCartItems');
    
    if (!items || items.length === 0) {
        container.innerHTML = '<p style="text-align: center; color: #6b7280; padding: 20px;">Кошик порожній</p>';
        return;
    }
    
    let html = '<div style="max-height: 300px; overflow-y: auto;">';
    items.forEach(function(item) {
        html += '<div style="display: flex; align-items: center; justify-content: space-between; padding: 12px; background: #f9fafb; border-radius: 8px; margin-bottom: 10px;">';
        html += '<div style="flex: 1;">';
        html += '<div style="font-weight: 500; color: #111827; margin-bottom: 4px;">' + escapeHtml(item.product_name) + '</div>';
        html += '<div style="font-size: 12px; color: #6b7280;">Артикул: ' + escapeHtml(item.product_article || '') + '</div>';
        html += '</div>';
        html += '<div style="display: flex; align-items: center; gap: 8px; margin-left: 16px;">';
        html += '<button type="button" onclick="updateCartQuantity(' + item.product_id + ', -0.001)" style="width: 28px; height: 28px; border: 1px solid #d1d5db; background: #fff; border-radius: 6px; cursor: pointer; font-size: 16px;">−</button>';
        html += '<input type="number" id="cart_qty_' + item.product_id + '" value="' + parseFloat(item.quantity).toFixed(3) + '" step="0.001" min="0.001" style="width: 80px; padding: 4px 8px; border: 1px solid #d1d5db; border-radius: 6px; text-align: center; font-size: 13px;" onchange="updateCartQuantity(' + item.product_id + ', 0)">';
        html += '<button type="button" onclick="updateCartQuantity(' + item.product_id + ', 0.001)" style="width: 28px; height: 28px; border: 1px solid #d1d5db; background: #fff; border-radius: 6px; cursor: pointer; font-size: 16px;">+</button>';
        html += '<button type="button" onclick="removeFromCart(' + item.product_id + ')" style="margin-left: 8px; padding: 4px 12px; background: #ef4444; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 12px;">Видалити</button>';
        html += '</div>';
        html += '</div>';
    });
    html += '</div>';
    container.innerHTML = html;
}

// Оновлення кількості товару в кошику
if (typeof window.updateCartQuantity === 'undefined') {
    window.updateCartQuantity = function(productId, delta) {
        const input = document.getElementById('cart_qty_' + productId);
        if (!input) return;
        
        let newQuantity = parseFloat(input.value);
        if (delta !== 0) {
            newQuantity += delta;
        }
        
        if (newQuantity <= 0) {
            newQuantity = 0.001;
        }
        
        input.value = newQuantity.toFixed(3);
        
        // Відправляємо AJAX запит для оновлення
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/modules/transfers/logic/handle_transfer_action.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        updateTransferCartBadge();
                    }
                } catch (e) {
                    console.error('Помилка парсингу відповіді:', e);
                }
            }
        };
        xhr.send('action=update_cart_item&product_id=' + productId + '&quantity=' + newQuantity);
    };
}

// Видалення товару з кошика
if (typeof window.removeFromCart === 'undefined') {
    window.removeFromCart = function(productId) {
        if (!confirm('Видалити товар з кошика?')) {
            return;
        }
        
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/modules/transfers/logic/handle_transfer_action.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        loadTransferCart();
                        updateTransferCartBadge();
                    }
                } catch (e) {
                    console.error('Помилка парсингу відповіді:', e);
                }
            }
        };
        xhr.send('action=remove_from_cart&product_id=' + productId);
    };
}

// Завантаження списку складів для переміщення
function loadWarehousesForTransfer() {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/modules/transfers/logic/handle_transfer_action.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    const select = document.getElementById('transferToWarehouse');
                    if (select) {
                        select.innerHTML = '<option value="">Оберіть склад...</option>';
                        response.warehouses.forEach(function(warehouse) {
                            if (warehouse.id != currentTransferWarehouseId) {
                                const option = document.createElement('option');
                                option.value = warehouse.id;
                                option.textContent = warehouse.name + ' (' + warehouse.identification_number + ')';
                                select.appendChild(option);
                            }
                        });
                    }
                }
            } catch (e) {
                console.error('Помилка парсингу відповіді:', e);
                console.error('Відповідь сервера:', xhr.responseText);
            }
        }
    };
    xhr.send('action=get_all_warehouses');
}

// Створення переміщення
if (typeof window.createTransfer === 'undefined') {
    window.createTransfer = function() {
        const toWarehouseId = document.getElementById('transferToWarehouse').value;
        
        if (!toWarehouseId) {
            alert('Оберіть склад призначення');
            return;
        }
        
        if (!confirm('Створити переміщення?')) {
            return;
        }
        
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/modules/transfers/logic/handle_transfer_action.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        alert('Переміщення створено. Очікується підтвердження від складу призначення.');
                        closeTransferCartModal();
                        updateTransferCartBadge();
                        // Оновлюємо сторінку для відображення нових даних
                        window.location.reload();
                    } else {
                        alert('Помилка: ' + (response.message || 'Невідома помилка'));
                    }
                } catch (e) {
                    console.error('Помилка парсингу відповіді:', e);
                    alert('Помилка обробки відповіді сервера');
                }
            }
        };
        xhr.send('action=create_transfer&to_warehouse_id=' + toWarehouseId + '&from_warehouse_id=' + currentTransferWarehouseId);
    };
}

// Оновлення бейджа кошика
function updateTransferCartBadge() {
    console.log('Оновлення бейджа кошика переміщення');
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/modules/transfers/logic/handle_transfer_action.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                console.log('Відповідь сервера для бейджа:', response);
                if (response.success) {
                    const badge = document.getElementById('transferCartBadge');
                    if (badge) {
                        const count = response.count || 0;
                        console.log('Кількість товарів у кошику:', count);
                        if (count > 0) {
                            badge.textContent = count;
                            badge.style.display = 'inline-flex';
                            badge.style.visibility = 'visible';
                            console.log('Бейдж оновлено, показуємо:', count);
                        } else {
                            badge.style.display = 'none';
                            badge.style.visibility = 'hidden';
                            console.log('Бейдж приховано, кошик порожній');
                        }
                    } else {
                        console.error('Бейдж transferCartBadge не знайдено!');
                    }
                } else {
                    console.error('Помилка отримання кількості товарів:', response.message);
                }
            } catch (e) {
                console.error('Помилка парсингу відповіді:', e);
                console.error('Відповідь сервера:', xhr.responseText);
            }
        } else if (xhr.readyState === 4) {
            console.error('Помилка HTTP:', xhr.status);
        }
    };
    xhr.send('action=get_cart_count');
}

// Функція для екранування HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Закриття модальних вікон при кліку поза ними
document.addEventListener('click', function(event) {
    const transferProductModal = document.getElementById('transferProductModal');
    const transferCartModal = document.getElementById('transferCartModal');
    
    if (transferProductModal && event.target === transferProductModal) {
        closeTransferProductModal();
    }
    if (transferCartModal && event.target === transferCartModal) {
        closeTransferCartModal();
    }
});

// Переміщуємо модальні вікна в body при завантаженні сторінки
function initializeTransferModals() {
    console.log('Ініціалізація модальних вікон переміщення');
    
    // Переміщуємо модальні вікна в body, щоб вони не були обмежені батьківським контейнером
    const transferProductModal = document.getElementById('transferProductModal');
    const transferCartModal = document.getElementById('transferCartModal');
    
    console.log('transferProductModal:', transferProductModal);
    console.log('transferCartModal:', transferCartModal);
    
    if (transferProductModal && transferProductModal.parentElement !== document.body) {
        document.body.appendChild(transferProductModal);
        console.log('transferProductModal переміщено в body');
    }
    
    if (transferCartModal && transferCartModal.parentElement !== document.body) {
        document.body.appendChild(transferCartModal);
        console.log('transferCartModal переміщено в body');
    }
    
    updateTransferCartBadge();
}

// Викликаємо ініціалізацію при завантаженні DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        initializeTransferModals();
        attachTransferCartButton();
    });
} else {
    // DOM вже завантажено
    initializeTransferModals();
    attachTransferCartButton();
}

// Прикріплюємо обробник події до кнопки корзини переміщення
function attachTransferCartButton() {
    const btn = document.getElementById('transferCartBtn');
    if (btn) {
        console.log('Кнопка transferCartBtn знайдена, прикріплюємо обробник');
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Клік по кнопці transferCartBtn');
            if (typeof window.openTransferCart === 'function') {
                window.openTransferCart();
            } else {
                console.error('Функція openTransferCart не визначена');
                alert('Помилка: функція не завантажена');
            }
        });
    } else {
        console.error('Кнопка transferCartBtn не знайдена');
    }
}
</script>
