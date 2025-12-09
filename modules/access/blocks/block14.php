<?php
// Блок 14 модуля Доступи - Заголовок "БЛОКИ ДОЗВОЛІВ" з кнопками
require_once __DIR__ . '/../logic/index.php';
?>
<div class="access-block">
    <div style="padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px;">
            <div>
                <h2 style="margin: 0 0 8px; font-size: 20px; font-weight: 600; color: #111827;">БЛОКИ ДОЗВОЛІВ</h2>
                <p style="margin: 0; font-size: 14px; color: #6b7280; line-height: 1.5;">
                    Структуруйте функціонал за блоками. Кожен блок об'єднує пов'язані доступи.
                </p>
            </div>
            <div style="display: flex; gap: 12px;">
                <button type="button" class="access-btn-primary" onclick="openCreatePermissionBlockModal()" style="background: linear-gradient(135deg, #1e40af, #1e3a8a);">
                    Створити блок дозволів
                </button>
                <button type="button" class="access-btn-primary" onclick="openCreatePermissionModal()" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                    Створити доступ
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Модальне вікно для створення блоку дозволів -->
<div id="createPermissionBlockModal" class="access-modal">
    <div class="access-modal-content">
        <div class="access-modal-header">
            <h3>Створити блок дозволів</h3>
            <span class="access-modal-close" onclick="closeCreatePermissionBlockModal()">&times;</span>
        </div>
        <div class="access-modal-body">
            <form id="createPermissionBlockForm" onsubmit="handleCreatePermissionBlock(event)">
                <div class="access-form-field">
                    <label for="blockName">Назва блоку *</label>
                    <input type="text" id="blockName" name="name" required placeholder="Введіть назву блоку">
                </div>
                <div class="access-form-field">
                    <label for="blockNotes">Нотатки</label>
                    <textarea id="blockNotes" name="notes" rows="3" placeholder="Введіть нотатки про блок"></textarea>
                </div>
                <div class="access-modal-actions">
                    <button type="button" class="access-btn-secondary" onclick="closeCreatePermissionBlockModal()">Закрити</button>
                    <button type="submit" class="access-btn-primary">Створити</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальне вікно для редагування блоку дозволів -->
<div id="editPermissionBlockModal" class="access-modal">
    <div class="access-modal-content">
        <div class="access-modal-header">
            <h3>Редагувати блок дозволів</h3>
            <span class="access-modal-close" onclick="closeEditPermissionBlockModal()">&times;</span>
        </div>
        <div class="access-modal-body">
            <form id="editPermissionBlockForm" onsubmit="handleUpdatePermissionBlock(event)">
                <input type="hidden" id="editBlockId" name="id">
                <div class="access-form-field">
                    <label for="editBlockName">Назва блоку *</label>
                    <input type="text" id="editBlockName" name="name" required placeholder="Введіть назву блоку">
                </div>
                <div class="access-form-field">
                    <label for="editBlockNotes">Нотатки</label>
                    <textarea id="editBlockNotes" name="notes" rows="3" placeholder="Введіть нотатки про блок"></textarea>
                </div>
                <div class="access-modal-actions">
                    <button type="button" class="access-btn-secondary" onclick="closeEditPermissionBlockModal()">Закрити</button>
                    <button type="submit" class="access-btn-primary">Оновити</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальне вікно для створення доступу -->
<div id="createPermissionModal" class="access-modal">
    <div class="access-modal-content">
        <div class="access-modal-header">
            <h3>Створити доступ</h3>
            <span class="access-modal-close" onclick="closeCreatePermissionModal()">&times;</span>
        </div>
        <div class="access-modal-body">
            <form id="createPermissionForm" onsubmit="handleCreatePermission(event)">
                <div class="access-form-field">
                    <label for="permissionBlockId">Блок дозволів *</label>
                    <select id="permissionBlockId" name="block_id" required>
                        <option value="">Оберіть блок</option>
                        <?php
                        $blocks = getPermissionBlocks($mysqli);
                        foreach ($blocks as $block):
                        ?>
                            <option value="<?php echo $block['id']; ?>"><?php echo htmlspecialchars($block['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="access-form-field">
                    <label for="permissionName">Назва доступу *</label>
                    <input type="text" id="permissionName" name="name" required placeholder="Введіть назву доступу">
                </div>
                <div class="access-form-field">
                    <label for="permissionCode">Код доступу *</label>
                    <input type="text" id="permissionCode" name="code" required placeholder="Введіть код доступу (наприклад, 101)">
                </div>
                <div class="access-form-field">
                    <label for="permissionNotes">Нотатки</label>
                    <textarea id="permissionNotes" name="notes" rows="3" placeholder="Введіть нотатки про доступ"></textarea>
                </div>
                <div class="access-modal-actions">
                    <button type="button" class="access-btn-secondary" onclick="closeCreatePermissionModal()">Закрити</button>
                    <button type="submit" class="access-btn-primary">Створити</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальне вікно для редагування доступу -->
<div id="editPermissionModal" class="access-modal">
    <div class="access-modal-content">
        <div class="access-modal-header">
            <h3>Редагувати доступ</h3>
            <span class="access-modal-close" onclick="closeEditPermissionModal()">&times;</span>
        </div>
        <div class="access-modal-body">
            <form id="editPermissionForm" onsubmit="handleUpdatePermission(event)">
                <input type="hidden" id="editPermissionId" name="id">
                <div class="access-form-field">
                    <label for="editPermissionBlockId">Блок дозволів *</label>
                    <select id="editPermissionBlockId" name="block_id" required>
                        <option value="">Оберіть блок</option>
                        <?php
                        $blocks = getPermissionBlocks($mysqli);
                        foreach ($blocks as $block):
                        ?>
                            <option value="<?php echo $block['id']; ?>"><?php echo htmlspecialchars($block['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="access-form-field">
                    <label for="editPermissionName">Назва доступу *</label>
                    <input type="text" id="editPermissionName" name="name" required placeholder="Введіть назву доступу">
                </div>
                <div class="access-form-field">
                    <label for="editPermissionCode">Код доступу *</label>
                    <input type="text" id="editPermissionCode" name="code" required placeholder="Введіть код доступу">
                </div>
                <div class="access-form-field">
                    <label for="editPermissionNotes">Нотатки</label>
                    <textarea id="editPermissionNotes" name="notes" rows="3" placeholder="Введіть нотатки про доступ"></textarea>
                </div>
                <div class="access-modal-actions">
                    <button type="button" class="access-btn-secondary" onclick="closeEditPermissionModal()">Закрити</button>
                    <button type="submit" class="access-btn-primary">Оновити</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Функції для роботи з блоками дозволів
window.openCreatePermissionBlockModal = function() {
    const modal = document.getElementById('createPermissionBlockModal');
    if (modal) {
        modal.style.display = 'flex';
        document.getElementById('blockName').focus();
    }
};

window.closeCreatePermissionBlockModal = function() {
    const modal = document.getElementById('createPermissionBlockModal');
    if (modal) {
        modal.style.display = 'none';
    }
    const form = document.getElementById('createPermissionBlockForm');
    if (form) {
        form.reset();
    }
};

window.handleCreatePermissionBlock = function(event) {
    event.preventDefault();
    const formData = new FormData();
    formData.append('action', 'create_permission_block');
    formData.append('name', document.getElementById('blockName').value.trim());
    formData.append('notes', document.getElementById('blockNotes').value.trim());
    
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Створення...';
    
    fetch('/modules/access/logic/handle_permission_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeCreatePermissionBlockModal();
            location.reload();
        } else {
            alert(data.message || 'Помилка при створенні блоку');
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

window.openEditPermissionBlockModal = function(blockId) {
    fetch('/modules/access/logic/handle_permission_action.php?action=get_permission_block&id=' + blockId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.block) {
                document.getElementById('editBlockId').value = data.block.id;
                document.getElementById('editBlockName').value = data.block.name;
                document.getElementById('editBlockNotes').value = data.block.notes || '';
                document.getElementById('editPermissionBlockModal').style.display = 'flex';
            } else {
                alert('Не вдалося завантажити дані блоку');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Помилка при завантаженні даних');
        });
};

window.closeEditPermissionBlockModal = function() {
    document.getElementById('editPermissionBlockModal').style.display = 'none';
    document.getElementById('editPermissionBlockForm').reset();
};

window.handleUpdatePermissionBlock = function(event) {
    event.preventDefault();
    const formData = new FormData();
    formData.append('action', 'update_permission_block');
    formData.append('id', document.getElementById('editBlockId').value);
    formData.append('name', document.getElementById('editBlockName').value.trim());
    formData.append('notes', document.getElementById('editBlockNotes').value.trim());
    
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Оновлення...';
    
    fetch('/modules/access/logic/handle_permission_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeEditPermissionBlockModal();
            location.reload();
        } else {
            alert(data.message || 'Помилка при оновленні блоку');
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

window.deletePermissionBlock = function(blockId) {
    if (!confirm('Ви впевнені, що хочете видалити цей блок дозволів? Всі доступи цього блоку також будуть видалені.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete_permission_block');
    formData.append('id', blockId);
    
    fetch('/modules/access/logic/handle_permission_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Помилка при видаленні блоку');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Помилка при відправці запиту');
    });
};

// Функції для роботи з доступами
window.openCreatePermissionModal = function() {
    const modal = document.getElementById('createPermissionModal');
    if (modal) {
        modal.style.display = 'flex';
        document.getElementById('permissionName').focus();
    }
};

window.closeCreatePermissionModal = function() {
    const modal = document.getElementById('createPermissionModal');
    if (modal) {
        modal.style.display = 'none';
    }
    const form = document.getElementById('createPermissionForm');
    if (form) {
        form.reset();
    }
};

window.handleCreatePermission = function(event) {
    event.preventDefault();
    const formData = new FormData();
    formData.append('action', 'create_permission');
    formData.append('block_id', document.getElementById('permissionBlockId').value);
    formData.append('name', document.getElementById('permissionName').value.trim());
    formData.append('code', document.getElementById('permissionCode').value.trim());
    formData.append('notes', document.getElementById('permissionNotes').value.trim());
    
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Створення...';
    
    fetch('/modules/access/logic/handle_permission_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeCreatePermissionModal();
            location.reload();
        } else {
            alert(data.message || 'Помилка при створенні доступу');
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

window.openEditPermissionModal = function(permissionId) {
    fetch('/modules/access/logic/handle_permission_action.php?action=get_permission&id=' + permissionId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.permission) {
                document.getElementById('editPermissionId').value = data.permission.id;
                document.getElementById('editPermissionBlockId').value = data.permission.block_id;
                document.getElementById('editPermissionName').value = data.permission.name;
                document.getElementById('editPermissionCode').value = data.permission.code;
                document.getElementById('editPermissionNotes').value = data.permission.notes || '';
                document.getElementById('editPermissionModal').style.display = 'flex';
            } else {
                alert('Не вдалося завантажити дані доступу');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Помилка при завантаженні даних');
        });
};

window.closeEditPermissionModal = function() {
    document.getElementById('editPermissionModal').style.display = 'none';
    document.getElementById('editPermissionForm').reset();
};

window.handleUpdatePermission = function(event) {
    event.preventDefault();
    const formData = new FormData();
    formData.append('action', 'update_permission');
    formData.append('id', document.getElementById('editPermissionId').value);
    formData.append('block_id', document.getElementById('editPermissionBlockId').value);
    formData.append('name', document.getElementById('editPermissionName').value.trim());
    formData.append('code', document.getElementById('editPermissionCode').value.trim());
    formData.append('notes', document.getElementById('editPermissionNotes').value.trim());
    
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Оновлення...';
    
    fetch('/modules/access/logic/handle_permission_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeEditPermissionModal();
            location.reload();
        } else {
            alert(data.message || 'Помилка при оновленні доступу');
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

window.deletePermission = function(permissionId) {
    if (!confirm('Ви впевнені, що хочете видалити цей доступ?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete_permission');
    formData.append('id', permissionId);
    
    fetch('/modules/access/logic/handle_permission_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Помилка при видаленні доступу');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Помилка при відправці запиту');
    });
};

// Закрити модальні вікна при кліку поза ними
document.addEventListener('click', function(event) {
    const modals = ['createPermissionBlockModal', 'editPermissionBlockModal', 'createPermissionModal', 'editPermissionModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal && event.target === modal) {
            if (modalId === 'createPermissionBlockModal') closeCreatePermissionBlockModal();
            if (modalId === 'editPermissionBlockModal') closeEditPermissionBlockModal();
            if (modalId === 'createPermissionModal') closeCreatePermissionModal();
            if (modalId === 'editPermissionModal') closeEditPermissionModal();
        }
    });
});
</script>
