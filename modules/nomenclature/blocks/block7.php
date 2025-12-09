<?php
// Блок 7 модуля Номенклатура товару - Кнопка "Додати категорії"
require_once __DIR__ . '/../logic/index.php';
?>
<div class="nomenclature-block">
    <div style="padding: 16px;">
        <button type="button" class="nomenclature-btn-primary" onclick="openCreateCategoryModal()">
            Додати категорію
        </button>
    </div>
</div>

<!-- Модальне вікно для створення категорії -->
<div id="createCategoryModal" class="nomenclature-modal">
    <div class="nomenclature-modal-content">
        <div class="nomenclature-modal-header">
            <h3>Додати категорію</h3>
            <span class="nomenclature-modal-close" onclick="closeCreateCategoryModal()">&times;</span>
        </div>
        <div class="nomenclature-modal-body">
            <form id="createCategoryForm" onsubmit="handleCreateCategory(event)">
                <div class="nomenclature-form-field">
                    <label for="categoryName">Назва категорії</label>
                    <input type="text" id="categoryName" name="name" required placeholder="Введіть назву категорії">
                </div>
                <div class="nomenclature-modal-actions">
                    <button type="button" class="nomenclature-btn-secondary" onclick="closeCreateCategoryModal()">Скасувати</button>
                    <button type="submit" class="nomenclature-btn-primary">Створити</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreateCategoryModal() {
    document.getElementById('createCategoryModal').style.display = 'flex';
    document.getElementById('categoryName').focus();
}

function closeCreateCategoryModal() {
    document.getElementById('createCategoryModal').style.display = 'none';
    document.getElementById('createCategoryForm').reset();
}

function handleCreateCategory(event) {
    event.preventDefault();
    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('name', document.getElementById('categoryName').value);

    fetch('/modules/nomenclature/logic/handle_category_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeCreateCategoryModal();
            location.reload();
        } else {
            alert(data.message || 'Помилка при створенні категорії');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Помилка при відправці запиту');
    });
}

// Закрити модальне вікно при кліку поза ним
window.onclick = function(event) {
    const modal = document.getElementById('createCategoryModal');
    if (event.target === modal) {
        closeCreateCategoryModal();
    }
}
</script>
