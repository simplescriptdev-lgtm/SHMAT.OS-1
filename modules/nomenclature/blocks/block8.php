<?php
// Блок 8 модуля Номенклатура товару - Таблиця категорій з підкатегоріями
require_once __DIR__ . '/../logic/index.php';

$categories = getCategories($mysqli);
?>
<div class="nomenclature-block">
    <div style="padding: 16px;">
        <h4 style="margin: 0 0 12px; font-size: 14px; font-weight: 600;">Список категорій</h4>
        <?php if (empty($categories)): ?>
            <p style="color: #6b7280; font-size: 13px;">Категорії ще не додані.</p>
        <?php else: ?>
            <div class="nomenclature-table-container">
                <table class="nomenclature-table">
                    <thead>
                        <tr>
                            <th>Номер п\п</th>
                            <th>Назва категорії</th>
                            <th>Створено</th>
                            <th>Дії</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = 1;
                        foreach ($categories as $category): 
                            $subcategories = getSubcategories($mysqli, $category['id']);
                        ?>
                            <tr class="category-row">
                                <td><?php echo $counter++; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                </td>
                                <td><?php echo date('d.m.Y H:i', strtotime($category['created_at'])); ?></td>
                                <td>
                                    <button type="button" class="nomenclature-btn-add-sub" onclick="openCreateSubcategoryModal(<?php echo $category['id']; ?>)">
                                        Додати підкатегорії
                                    </button>
                                    <button type="button" class="nomenclature-btn-edit" onclick="openEditCategoryModal(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?>')">
                                        Редагувати
                                    </button>
                                    <button type="button" class="nomenclature-btn-delete" onclick="deleteCategory(<?php echo $category['id']; ?>)">
                                        Видалити
                                    </button>
                                </td>
                            </tr>
                            <?php if (!empty($subcategories)): ?>
                                <tr class="subcategories-row">
                                    <td></td>
                                    <td colspan="3">
                                        <div class="subcategories-container">
                                            <?php foreach ($subcategories as $subcategory): ?>
                                                <div class="subcategory-item">
                                                    <span class="subcategory-name"><?php echo htmlspecialchars($subcategory['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <div class="subcategory-actions">
                                                        <button type="button" class="nomenclature-btn-edit-small" onclick="openEditSubcategoryModal(<?php echo $subcategory['id']; ?>, '<?php echo htmlspecialchars($subcategory['name'], ENT_QUOTES, 'UTF-8'); ?>')">
                                                            Редагувати
                                                        </button>
                                                        <button type="button" class="nomenclature-btn-delete-small" onclick="deleteSubcategory(<?php echo $subcategory['id']; ?>)">
                                                            Видалити
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Модальне вікно для створення підкатегорії -->
<div id="createSubcategoryModal" class="nomenclature-modal">
    <div class="nomenclature-modal-content">
        <div class="nomenclature-modal-header">
            <h3>Додати підкатегорію</h3>
            <span class="nomenclature-modal-close" onclick="closeCreateSubcategoryModal()">&times;</span>
        </div>
        <div class="nomenclature-modal-body">
            <form id="createSubcategoryForm" onsubmit="handleCreateSubcategory(event)">
                <input type="hidden" id="createSubcategoryCategoryId" name="category_id">
                <div class="nomenclature-form-field">
                    <label for="subcategoryName">Назва підкатегорії</label>
                    <input type="text" id="subcategoryName" name="name" required placeholder="Введіть назву підкатегорії">
                </div>
                <div class="nomenclature-modal-actions">
                    <button type="button" class="nomenclature-btn-secondary" onclick="closeCreateSubcategoryModal()">Скасувати</button>
                    <button type="submit" class="nomenclature-btn-primary">Зберегти</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальне вікно для редагування категорії -->
<div id="editCategoryModal" class="nomenclature-modal">
    <div class="nomenclature-modal-content">
        <div class="nomenclature-modal-header">
            <h3>Редагувати категорію</h3>
            <span class="nomenclature-modal-close" onclick="closeEditCategoryModal()">&times;</span>
        </div>
        <div class="nomenclature-modal-body">
            <form id="editCategoryForm" onsubmit="handleUpdateCategory(event)">
                <input type="hidden" id="editCategoryId" name="id">
                <div class="nomenclature-form-field">
                    <label for="editCategoryName">Назва категорії</label>
                    <input type="text" id="editCategoryName" name="name" required placeholder="Введіть назву категорії">
                </div>
                <div class="nomenclature-modal-actions">
                    <button type="button" class="nomenclature-btn-secondary" onclick="closeEditCategoryModal()">Скасувати</button>
                    <button type="submit" class="nomenclature-btn-primary">Оновити назву</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальне вікно для редагування підкатегорії -->
<div id="editSubcategoryModal" class="nomenclature-modal">
    <div class="nomenclature-modal-content">
        <div class="nomenclature-modal-header">
            <h3>Редагувати підкатегорію</h3>
            <span class="nomenclature-modal-close" onclick="closeEditSubcategoryModal()">&times;</span>
        </div>
        <div class="nomenclature-modal-body">
            <form id="editSubcategoryForm" onsubmit="handleUpdateSubcategory(event)">
                <input type="hidden" id="editSubcategoryId" name="id">
                <div class="nomenclature-form-field">
                    <label for="editSubcategoryName">Назва підкатегорії</label>
                    <input type="text" id="editSubcategoryName" name="name" required placeholder="Введіть назву підкатегорії">
                </div>
                <div class="nomenclature-modal-actions">
                    <button type="button" class="nomenclature-btn-secondary" onclick="closeEditSubcategoryModal()">Скасувати</button>
                    <button type="submit" class="nomenclature-btn-primary">Оновити назву</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreateSubcategoryModal(categoryId) {
    document.getElementById('createSubcategoryCategoryId').value = categoryId;
    document.getElementById('createSubcategoryModal').style.display = 'flex';
    document.getElementById('subcategoryName').focus();
}

function closeCreateSubcategoryModal() {
    document.getElementById('createSubcategoryModal').style.display = 'none';
    document.getElementById('createSubcategoryForm').reset();
}

function handleCreateSubcategory(event) {
    event.preventDefault();
    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('category_id', document.getElementById('createSubcategoryCategoryId').value);
    formData.append('name', document.getElementById('subcategoryName').value);

    fetch('/modules/nomenclature/logic/handle_subcategory_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeCreateSubcategoryModal();
            location.reload();
        } else {
            alert(data.message || 'Помилка при створенні підкатегорії');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Помилка при відправці запиту');
    });
}

function openEditCategoryModal(id, name) {
    document.getElementById('editCategoryId').value = id;
    document.getElementById('editCategoryName').value = name;
    document.getElementById('editCategoryModal').style.display = 'flex';
    document.getElementById('editCategoryName').focus();
}

function closeEditCategoryModal() {
    document.getElementById('editCategoryModal').style.display = 'none';
    document.getElementById('editCategoryForm').reset();
}

function handleUpdateCategory(event) {
    event.preventDefault();
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('id', document.getElementById('editCategoryId').value);
    formData.append('name', document.getElementById('editCategoryName').value);

    fetch('/modules/nomenclature/logic/handle_category_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeEditCategoryModal();
            location.reload();
        } else {
            alert(data.message || 'Помилка при оновленні категорії');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Помилка при відправці запиту');
    });
}

function openEditSubcategoryModal(id, name) {
    document.getElementById('editSubcategoryId').value = id;
    document.getElementById('editSubcategoryName').value = name;
    document.getElementById('editSubcategoryModal').style.display = 'flex';
    document.getElementById('editSubcategoryName').focus();
}

function closeEditSubcategoryModal() {
    document.getElementById('editSubcategoryModal').style.display = 'none';
    document.getElementById('editSubcategoryForm').reset();
}

function handleUpdateSubcategory(event) {
    event.preventDefault();
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('id', document.getElementById('editSubcategoryId').value);
    formData.append('name', document.getElementById('editSubcategoryName').value);

    fetch('/modules/nomenclature/logic/handle_subcategory_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeEditSubcategoryModal();
            location.reload();
        } else {
            alert(data.message || 'Помилка при оновленні підкатегорії');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Помилка при відправці запиту');
    });
}

function deleteCategory(id) {
    if (!confirm('Ви впевнені, що хочете видалити цю категорію? Всі підкатегорії також будуть видалені.')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch('/modules/nomenclature/logic/handle_category_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Помилка при видаленні категорії');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Помилка при відправці запиту');
    });
}

function deleteSubcategory(id) {
    if (!confirm('Ви впевнені, що хочете видалити цю підкатегорію?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch('/modules/nomenclature/logic/handle_subcategory_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Помилка при видаленні підкатегорії');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Помилка при відправці запиту');
    });
}

// Закрити модальні вікна при кліку поза ними
window.onclick = function(event) {
    const modals = ['createSubcategoryModal', 'editCategoryModal', 'editSubcategoryModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            if (modalId === 'createSubcategoryModal') closeCreateSubcategoryModal();
            if (modalId === 'editCategoryModal') closeEditCategoryModal();
            if (modalId === 'editSubcategoryModal') closeEditSubcategoryModal();
        }
    });
}
</script>
