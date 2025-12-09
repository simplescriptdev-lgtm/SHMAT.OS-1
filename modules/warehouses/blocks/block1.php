<?php
// Блок 1 модуля Управління складами - Кнопка "Додати склад"
require_once __DIR__ . '/../logic/index.php';

// Отримуємо всіх користувачів для випадаючого списку
$allUsers = getWarehouseUsers($mysqli);
?>
<div class="warehouses-block">
    <div style="padding: 16px;">
        <button type="button" class="warehouses-btn-primary" onclick="openCreateWarehouseModal()">
            Додати склад
        </button>
    </div>
</div>

<!-- Модальне вікно для створення складу -->
<div id="createWarehouseModal" class="warehouses-modal">
    <div class="warehouses-modal-content">
        <div class="warehouses-modal-header">
            <h3>Додати склад</h3>
            <span class="warehouses-modal-close" onclick="closeCreateWarehouseModal()">&times;</span>
        </div>
        <div class="warehouses-modal-body">
            <form id="createWarehouseForm" onsubmit="handleCreateWarehouse(event)">
                <div class="warehouses-form-field">
                    <label for="warehouseName">Назва складу *</label>
                    <input type="text" id="warehouseName" name="name" required placeholder="Введіть назву складу">
                </div>

                <div class="warehouses-form-field">
                    <label for="warehouseIdentificationNumber">Ідентифікаційний номер складу *</label>
                    <input type="text" id="warehouseIdentificationNumber" name="identification_number" required placeholder="Введіть ідентифікаційний номер">
                </div>

                <div class="warehouses-form-field">
                    <label for="warehouseDescription">Опис складу (нотатки)</label>
                    <textarea id="warehouseDescription" name="description" rows="3" placeholder="Введіть опис складу"></textarea>
                </div>

                <div class="warehouses-form-field">
                    <label for="warehouseHasScheme" style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" id="warehouseHasScheme" name="has_scheme" value="1" style="width: auto; cursor: pointer;">
                        <span>Склад має схему реалізації (ряд та сектор)</span>
                    </label>
                </div>

                <div class="warehouses-form-field">
                    <label>Відповідальні особи</label>
                    <div class="warehouses-user-select-container">
                        <div class="warehouses-user-select-wrapper">
                            <input type="text" id="warehouseUserSearch" placeholder="Пошук користувача..." oninput="filterUsers(this.value)" onfocus="showUserDropdown()" onkeydown="handleUserSearchKeydown(event)" autocomplete="off">
                            <div id="warehouseUserDropdown" class="warehouses-user-dropdown" style="display: none;"></div>
                        </div>
                        <button type="button" class="warehouses-btn-add-user" onclick="addSelectedUser()">Додати</button>
                    </div>
                    <div id="warehouseSelectedUsersContainer">
                        <div id="warehouseSelectedUsers" class="warehouses-selected-users"></div>
                    </div>
                </div>

                <div class="warehouses-modal-actions">
                    <button type="button" class="warehouses-btn-secondary" onclick="closeCreateWarehouseModal()">Закрити</button>
                    <button type="submit" class="warehouses-btn-primary">Зберегти</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Глобальні змінні для роботи з користувачами
let allUsersList = <?php echo json_encode($allUsers, JSON_UNESCAPED_UNICODE); ?>;
let selectedUsers = [];
let filteredUsers = [];

// Ініціалізація
document.addEventListener('DOMContentLoaded', function() {
    filteredUsers = [...allUsersList];
    updateUserDropdown();
});

window.openCreateWarehouseModal = function() {
    const modal = document.getElementById('createWarehouseModal');
    if (modal) {
        modal.style.display = 'flex';
        selectedUsers = [];
        filteredUsers = [...allUsersList];
        updateSelectedUsers();
        updateUserDropdown();
        document.getElementById('warehouseName').focus();
    }
};

window.closeCreateWarehouseModal = function() {
    const modal = document.getElementById('createWarehouseModal');
    if (modal) {
        modal.style.display = 'none';
    }
    const form = document.getElementById('createWarehouseForm');
    if (form) {
        form.reset();
        selectedUsers = [];
        filteredUsers = [...allUsersList];
        updateSelectedUsers();
        updateUserDropdown();
    }
};

window.filterUsers = function(searchTerm) {
    const term = searchTerm.toLowerCase().trim();
    
    // Не скидаємо selectedUserId при введенні тексту, щоб можна було додати вибраного користувача
    // Скидаємо тільки якщо поле повністю очищено
    if (term === '') {
        // Не скидаємо selectedUserId тут, щоб зберегти вибір
        filteredUsers = allUsersList.filter(user => 
            !selectedUsers.some(su => su.id === user.id)
        );
    } else {
        filteredUsers = allUsersList.filter(user => 
            !selectedUsers.some(su => su.id === user.id) &&
            (user.full_name.toLowerCase().includes(term) || user.login.toLowerCase().includes(term))
        );
        // Якщо знайдено точний збіг, автоматично встановлюємо selectedUserId
        const exactMatch = allUsersList.find(user => 
            !selectedUsers.some(su => su.id === user.id) &&
            (user.full_name.toLowerCase() === term || user.login.toLowerCase() === term)
        );
        if (exactMatch) {
            selectedUserId = exactMatch.id;
            selectedUserName = exactMatch.full_name;
        }
    }
    updateUserDropdown();
    
    // Показуємо випадаючий список, якщо є результати
    if (filteredUsers.length > 0 && term.length > 0) {
        showUserDropdown();
    } else if (term.length === 0) {
        hideUserDropdown();
    }
};

window.showUserDropdown = function() {
    const dropdown = document.getElementById('warehouseUserDropdown');
    if (dropdown) {
        if (filteredUsers.length > 0) {
            dropdown.style.display = 'block';
        } else {
            dropdown.style.display = 'none';
        }
    }
};

window.hideUserDropdown = function() {
    setTimeout(() => {
        const dropdown = document.getElementById('warehouseUserDropdown');
        if (dropdown) {
            dropdown.style.display = 'none';
        }
    }, 200);
};

window.updateUserDropdown = function() {
    const dropdown = document.getElementById('warehouseUserDropdown');
    if (!dropdown) return;
    
    if (filteredUsers.length === 0) {
        dropdown.innerHTML = '<div class="warehouses-user-dropdown-item" style="padding: 10px; color: #6b7280; text-align: center;">Користувачі не знайдені</div>';
        return;
    }
    
    dropdown.innerHTML = filteredUsers.map(user => `
        <div class="warehouses-user-dropdown-item" onclick="selectUser(${user.id}, '${user.full_name.replace(/'/g, "\\'")}')">
            <strong>${escapeHtml(user.full_name)}</strong>
            <span style="color: #6b7280; font-size: 12px;">${escapeHtml(user.login)}</span>
        </div>
    `).join('');
};

let selectedUserId = null;
let selectedUserName = '';

window.selectUser = function(userId, userName) {
    selectedUserId = userId;
    selectedUserName = userName;
    const searchInput = document.getElementById('warehouseUserSearch');
    if (searchInput) {
        searchInput.value = userName;
    }
    hideUserDropdown();
};

window.addSelectedUser = function() {
    const searchInput = document.getElementById('warehouseUserSearch');
    const searchValue = searchInput ? searchInput.value.trim() : '';
    
    let userToAdd = null;
    
    // Спочатку перевіряємо, чи є вибраний користувач з випадаючого списку
    if (selectedUserId) {
        const user = allUsersList.find(u => u.id === selectedUserId);
        if (user && !selectedUsers.some(su => su.id === user.id)) {
            userToAdd = user;
        }
    }
    
    // Якщо не знайдено через selectedUserId, шукаємо за введеним текстом
    if (!userToAdd && searchValue) {
        // Спочатку шукаємо точний збіг
        let foundUser = allUsersList.find(u => 
            (u.full_name.toLowerCase() === searchValue.toLowerCase() || 
             u.login.toLowerCase() === searchValue.toLowerCase()) &&
            !selectedUsers.some(su => su.id === u.id)
        );
        
        // Якщо точного збігу немає, шукаємо частковий збіг
        if (!foundUser) {
            foundUser = allUsersList.find(u => 
                (u.full_name.toLowerCase().includes(searchValue.toLowerCase()) || 
                 u.login.toLowerCase().includes(searchValue.toLowerCase())) &&
                !selectedUsers.some(su => su.id === u.id)
            );
        }
        
        if (foundUser) {
            userToAdd = foundUser;
        }
    }
    
    // Додаємо користувача, якщо знайдено
    if (userToAdd) {
        selectedUsers.push(userToAdd);
        updateSelectedUsers();
        updateUserDropdown();
        if (searchInput) {
            searchInput.value = '';
        }
        selectedUserId = null;
        selectedUserName = '';
        hideUserDropdown();
    } else {
        if (!searchValue) {
            alert('Виберіть користувача зі списку або введіть ім\'я/логін.');
        } else {
            alert('Користувача не знайдено. Виберіть зі списку або введіть правильне ім\'я/логін.');
        }
    }
};

window.handleUserSearchKeydown = function(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        addSelectedUser();
    }
};

window.removeSelectedUser = function(userId) {
    selectedUsers = selectedUsers.filter(u => u.id !== userId);
    updateSelectedUsers();
    updateUserDropdown();
};

window.updateSelectedUsers = function() {
    const container = document.getElementById('warehouseSelectedUsers');
    const containerWrapper = document.getElementById('warehouseSelectedUsersContainer');
    if (!container) return;
    
    if (selectedUsers.length === 0) {
        container.innerHTML = '';
        if (containerWrapper) {
            containerWrapper.style.display = 'none';
        }
        return;
    }
    
    // Показуємо контейнер, якщо є вибрані користувачі
    if (containerWrapper) {
        containerWrapper.style.display = 'block';
    }
    
    // Додаємо заголовок та список користувачів
    container.innerHTML = `
        <div style="margin-bottom: 8px; font-size: 12px; font-weight: 600; color: #374151;">Відповідальні особи:</div>
        <div class="warehouses-selected-users-list">
            ${selectedUsers.map(user => `
                <div class="warehouses-selected-user-item">
                    <span>${escapeHtml(user.full_name)}</span>
                    <button type="button" class="warehouses-btn-remove-user" onclick="removeSelectedUser(${user.id})">&times;</button>
                </div>
            `).join('')}
        </div>
    `;
};

window.handleCreateWarehouse = function(event) {
    event.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('name', document.getElementById('warehouseName').value.trim());
    formData.append('identification_number', document.getElementById('warehouseIdentificationNumber').value.trim());
    formData.append('description', document.getElementById('warehouseDescription').value.trim());
    formData.append('has_scheme', document.getElementById('warehouseHasScheme').checked ? '1' : '0');
    
    selectedUsers.forEach(user => {
        formData.append('user_ids[]', user.id);
    });

    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Збереження...';

    fetch('/modules/warehouses/logic/handle_warehouse_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeCreateWarehouseModal();
            location.reload();
        } else {
            alert(data.message || 'Помилка при створенні складу');
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

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Закрити модальне вікно при кліку поза ним
document.addEventListener('click', function(event) {
    const modal = document.getElementById('createWarehouseModal');
    if (modal && event.target === modal) {
        closeCreateWarehouseModal();
    }
    
    // Закрити випадаючий список при кліку поза ним
    const dropdown = document.getElementById('warehouseUserDropdown');
    const searchInput = document.getElementById('warehouseUserSearch');
    if (dropdown && searchInput && !dropdown.contains(event.target) && event.target !== searchInput) {
        hideUserDropdown();
    }
});
</script>
