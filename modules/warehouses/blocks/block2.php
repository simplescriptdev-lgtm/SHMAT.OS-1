<?php
// Блок 2 модуля Управління складами - Таблиця складів
require_once __DIR__ . '/../logic/index.php';

$warehouses = getWarehouses($mysqli);
$allUsers = getWarehouseUsers($mysqli);
?>
<div class="warehouses-block">
    <div style="padding: 16px;">
        <h4 style="margin: 0 0 12px; font-size: 14px; font-weight: 600;">Список складів</h4>
        <?php if (empty($warehouses)): ?>
            <p style="color: #6b7280; font-size: 13px;">Склади ще не додані.</p>
        <?php else: ?>
            <div class="warehouses-table-container">
                <table class="warehouses-table">
                    <thead>
                        <tr>
                            <th>№</th>
                            <th>Назва складу</th>
                            <th>Ідентифікаційний номер</th>
                            <th>Опис складу</th>
                            <th>Схема реалізації</th>
                            <th>Створено</th>
                            <th>Відповідальні особи</th>
                            <th>Дії</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = 1;
                        foreach ($warehouses as $warehouse): 
                        ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td><?php echo htmlspecialchars($warehouse['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($warehouse['identification_number'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($warehouse['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo $warehouse['has_scheme'] ? 'З сектором та рядом' : 'Без схеми'; ?></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($warehouse['created_at'])); ?></td>
                                <td>
                                    <?php if (!empty($warehouse['users'])): ?>
                                        <?php foreach ($warehouse['users'] as $user): ?>
                                            <div style="margin-bottom: 4px;"><?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span style="color: #6b7280;">Немає</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="warehouses-btn-edit" onclick="editWarehouse(<?php echo $warehouse['id']; ?>)">
                                        Редагувати
                                    </button>
                                    <button type="button" class="warehouses-btn-delete" onclick="deleteWarehouse(<?php echo $warehouse['id']; ?>)">
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

<!-- Модальне вікно для редагування складу -->
<div id="editWarehouseModal" class="warehouses-modal">
    <div class="warehouses-modal-content">
        <div class="warehouses-modal-header">
            <h3>Редагувати склад</h3>
            <span class="warehouses-modal-close" onclick="closeEditWarehouseModal()">&times;</span>
        </div>
        <div class="warehouses-modal-body">
            <form id="editWarehouseForm" onsubmit="handleUpdateWarehouse(event)">
                <input type="hidden" id="editWarehouseId" name="id">
                
                <div class="warehouses-form-field">
                    <label for="editWarehouseName">Назва складу *</label>
                    <input type="text" id="editWarehouseName" name="name" required placeholder="Введіть назву складу">
                </div>

                <div class="warehouses-form-field">
                    <label for="editWarehouseIdentificationNumber">Ідентифікаційний номер складу *</label>
                    <input type="text" id="editWarehouseIdentificationNumber" name="identification_number" required placeholder="Введіть ідентифікаційний номер">
                </div>

                <div class="warehouses-form-field">
                    <label for="editWarehouseDescription">Опис складу (нотатки)</label>
                    <textarea id="editWarehouseDescription" name="description" rows="3" placeholder="Введіть опис складу"></textarea>
                </div>

                <div class="warehouses-form-field">
                    <label for="editWarehouseHasScheme" style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" id="editWarehouseHasScheme" name="has_scheme" value="1" style="width: auto; cursor: pointer;">
                        <span>Склад має схему реалізації (ряд та сектор)</span>
                    </label>
                </div>

                <div class="warehouses-form-field">
                    <label>Відповідальні особи</label>
                    <div class="warehouses-user-select-container">
                        <div class="warehouses-user-select-wrapper">
                            <input type="text" id="editWarehouseUserSearch" placeholder="Пошук користувача..." oninput="filterEditUsers(this.value)" onfocus="showEditUserDropdown()" onkeydown="handleEditUserSearchKeydown(event)" autocomplete="off">
                            <div id="editWarehouseUserDropdown" class="warehouses-user-dropdown" style="display: none;"></div>
                        </div>
                        <button type="button" class="warehouses-btn-add-user" onclick="addSelectedEditUser()">Додати</button>
                    </div>
                    <div id="editWarehouseSelectedUsersContainer">
                        <div id="editWarehouseSelectedUsers" class="warehouses-selected-users"></div>
                    </div>
                </div>

                <div class="warehouses-modal-actions">
                    <button type="button" class="warehouses-btn-secondary" onclick="closeEditWarehouseModal()">Скасувати</button>
                    <button type="submit" class="warehouses-btn-primary">Зберегти зміни</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Глобальні змінні для редагування
let editSelectedUsers = [];
let editFilteredUsers = [];
let editSelectedUserId = null;
let editSelectedUserName = '';

window.editWarehouse = function(warehouseId) {
    const modal = document.getElementById('editWarehouseModal');
    modal.style.display = 'flex';
    
    fetch(`/modules/warehouses/logic/handle_warehouse_action.php?action=get&id=${warehouseId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.warehouse) {
                const warehouse = data.warehouse;
                document.getElementById('editWarehouseId').value = warehouse.id;
                document.getElementById('editWarehouseName').value = warehouse.name;
                document.getElementById('editWarehouseIdentificationNumber').value = warehouse.identification_number;
                document.getElementById('editWarehouseDescription').value = warehouse.description || '';
                document.getElementById('editWarehouseHasScheme').checked = warehouse.has_scheme == 1;
                
                editSelectedUsers = warehouse.users || [];
                updateEditUserDropdown();
                updateEditSelectedUsers();
                document.getElementById('editWarehouseName').focus();
            } else {
                alert('Помилка завантаження даних складу');
                closeEditWarehouseModal();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Помилка при завантаженні даних');
            closeEditWarehouseModal();
        });
};

window.closeEditWarehouseModal = function() {
    document.getElementById('editWarehouseModal').style.display = 'none';
    document.getElementById('editWarehouseForm').reset();
    editSelectedUsers = [];
    editFilteredUsers = [];
    updateEditUserDropdown();
    updateEditSelectedUsers();
};

window.filterEditUsers = function(searchTerm) {
    const term = searchTerm.toLowerCase().trim();
    const allUsers = <?php echo json_encode($allUsers, JSON_UNESCAPED_UNICODE); ?>;
    
    // Не скидаємо editSelectedUserId при введенні тексту
    if (term === '') {
        editFilteredUsers = allUsers.filter(user => 
            !editSelectedUsers.some(su => su.id === user.id)
        );
    } else {
        editFilteredUsers = allUsers.filter(user => 
            !editSelectedUsers.some(su => su.id === user.id) &&
            (user.full_name.toLowerCase().includes(term) || user.login.toLowerCase().includes(term))
        );
        // Якщо знайдено точний збіг, автоматично встановлюємо editSelectedUserId
        const exactMatch = allUsers.find(user => 
            !editSelectedUsers.some(su => su.id === user.id) &&
            (user.full_name.toLowerCase() === term || user.login.toLowerCase() === term)
        );
        if (exactMatch) {
            editSelectedUserId = exactMatch.id;
            editSelectedUserName = exactMatch.full_name;
        }
    }
    updateEditUserDropdown();
    
    // Показуємо випадаючий список, якщо є результати
    const dropdown = document.getElementById('editWarehouseUserDropdown');
    if (editFilteredUsers.length > 0 && term.length > 0) {
        if (dropdown) dropdown.style.display = 'block';
    } else if (term.length === 0) {
        if (dropdown) dropdown.style.display = 'none';
    }
};

window.showEditUserDropdown = function() {
    const dropdown = document.getElementById('editWarehouseUserDropdown');
    if (dropdown && editFilteredUsers.length > 0) {
        dropdown.style.display = 'block';
    }
};

window.updateEditUserDropdown = function() {
    const dropdown = document.getElementById('editWarehouseUserDropdown');
    if (!dropdown) return;
    
    const allUsers = <?php echo json_encode($allUsers, JSON_UNESCAPED_UNICODE); ?>;
    editFilteredUsers = allUsers.filter(user => 
        !editSelectedUsers.some(su => su.id === user.id)
    );
    
    if (editFilteredUsers.length === 0) {
        dropdown.innerHTML = '<div class="warehouses-user-dropdown-item" style="padding: 10px; color: #6b7280; text-align: center;">Користувачі не знайдені</div>';
        return;
    }
    
    dropdown.innerHTML = editFilteredUsers.map(user => `
        <div class="warehouses-user-dropdown-item" onclick="selectEditUser(${user.id}, '${user.full_name.replace(/'/g, "\\'")}')">
            <strong>${escapeHtml(user.full_name)}</strong>
            <span style="color: #6b7280; font-size: 12px;">${escapeHtml(user.login)}</span>
        </div>
    `).join('');
};

window.selectEditUser = function(userId, userName) {
    editSelectedUserId = userId;
    editSelectedUserName = userName;
    document.getElementById('editWarehouseUserSearch').value = userName;
    setTimeout(() => {
        document.getElementById('editWarehouseUserDropdown').style.display = 'none';
    }, 200);
};

window.addSelectedEditUser = function() {
    const searchInput = document.getElementById('editWarehouseUserSearch');
    const searchValue = searchInput ? searchInput.value.trim() : '';
    const allUsers = <?php echo json_encode($allUsers, JSON_UNESCAPED_UNICODE); ?>;
    
    let userToAdd = null;
    
    // Спочатку перевіряємо, чи є вибраний користувач з випадаючого списку
    if (editSelectedUserId) {
        const user = allUsers.find(u => u.id === editSelectedUserId);
        if (user && !editSelectedUsers.some(su => su.id === user.id)) {
            userToAdd = user;
        }
    }
    
    // Якщо не знайдено через editSelectedUserId, шукаємо за введеним текстом
    if (!userToAdd && searchValue) {
        // Спочатку шукаємо точний збіг
        let foundUser = allUsers.find(u => 
            (u.full_name.toLowerCase() === searchValue.toLowerCase() || 
             u.login.toLowerCase() === searchValue.toLowerCase()) &&
            !editSelectedUsers.some(su => su.id === u.id)
        );
        
        // Якщо точного збігу немає, шукаємо частковий збіг
        if (!foundUser) {
            foundUser = allUsers.find(u => 
                (u.full_name.toLowerCase().includes(searchValue.toLowerCase()) || 
                 u.login.toLowerCase().includes(searchValue.toLowerCase())) &&
                !editSelectedUsers.some(su => su.id === u.id)
            );
        }
        
        if (foundUser) {
            userToAdd = foundUser;
        }
    }
    
    // Додаємо користувача, якщо знайдено
    if (userToAdd) {
        editSelectedUsers.push(userToAdd);
        updateEditSelectedUsers();
        updateEditUserDropdown();
        if (searchInput) {
            searchInput.value = '';
        }
        editSelectedUserId = null;
        editSelectedUserName = '';
        setTimeout(() => {
            const dropdown = document.getElementById('editWarehouseUserDropdown');
            if (dropdown) dropdown.style.display = 'none';
        }, 200);
    } else {
        if (!searchValue) {
            alert('Виберіть користувача зі списку або введіть ім\'я/логін.');
        } else {
            alert('Користувача не знайдено. Виберіть зі списку або введіть правильне ім\'я/логін.');
        }
    }
};

window.handleEditUserSearchKeydown = function(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        addSelectedEditUser();
    }
};

window.removeEditSelectedUser = function(userId) {
    editSelectedUsers = editSelectedUsers.filter(u => u.id !== userId);
    updateEditSelectedUsers();
    updateEditUserDropdown();
};

window.updateEditSelectedUsers = function() {
    const container = document.getElementById('editWarehouseSelectedUsers');
    const containerWrapper = document.getElementById('editWarehouseSelectedUsersContainer');
    if (!container) return;
    
    if (editSelectedUsers.length === 0) {
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
            ${editSelectedUsers.map(user => `
                <div class="warehouses-selected-user-item">
                    <span>${escapeHtml(user.full_name)}</span>
                    <button type="button" class="warehouses-btn-remove-user" onclick="removeEditSelectedUser(${user.id})">&times;</button>
                </div>
            `).join('')}
        </div>
    `;
};

window.handleUpdateWarehouse = function(event) {
    event.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('id', document.getElementById('editWarehouseId').value);
    formData.append('name', document.getElementById('editWarehouseName').value.trim());
    formData.append('identification_number', document.getElementById('editWarehouseIdentificationNumber').value.trim());
    formData.append('description', document.getElementById('editWarehouseDescription').value.trim());
    formData.append('has_scheme', document.getElementById('editWarehouseHasScheme').checked ? '1' : '0');
    
    editSelectedUsers.forEach(user => {
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
            closeEditWarehouseModal();
            location.reload();
        } else {
            alert(data.message || 'Помилка при оновленні складу');
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

window.deleteWarehouse = function(warehouseId) {
    if (!confirm('Ви впевнені, що хочете видалити цей склад?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', warehouseId);

    fetch('/modules/warehouses/logic/handle_warehouse_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Помилка при видаленні складу');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Помилка при відправці запиту');
    });
};

// Закрити модальне вікно редагування при кліку поза ним
document.addEventListener('click', function(event) {
    const editModal = document.getElementById('editWarehouseModal');
    if (editModal && event.target === editModal) {
        closeEditWarehouseModal();
    }
    
    // Закрити випадаючий список при кліку поза ним
    const dropdown = document.getElementById('editWarehouseUserDropdown');
    const searchInput = document.getElementById('editWarehouseUserSearch');
    if (dropdown && searchInput && !dropdown.contains(event.target) && event.target !== searchInput) {
        setTimeout(() => {
            if (dropdown) dropdown.style.display = 'none';
        }, 200);
    }
});
</script>
