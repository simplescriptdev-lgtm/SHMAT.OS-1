<?php
// Блок 1 модуля Доступи - Кнопка "Додати користувача"
require_once __DIR__ . '/../logic/index.php';
?>
<div class="access-block">
    <div style="padding: 16px;">
        <button type="button" class="access-btn-primary" onclick="openCreateUserModal()">
            Додати користувача
        </button>
    </div>
</div>

<!-- Модальне вікно для створення користувача -->
<div id="createUserModal" class="access-modal">
    <div class="access-modal-content">
        <div class="access-modal-header">
            <h3>Додати користувача</h3>
            <span class="access-modal-close" onclick="closeCreateUserModal()">&times;</span>
        </div>
        <div class="access-modal-body">
            <form id="createUserForm" onsubmit="handleCreateUser(event)">
                <div class="access-form-field">
                    <label for="userFullName">ПІБ користувача *</label>
                    <input type="text" id="userFullName" name="full_name" required placeholder="Введіть ПІБ користувача">
                </div>

                <div class="access-form-field">
                    <label for="userLogin">Логін користувача *</label>
                    <input type="text" id="userLogin" name="login" required placeholder="Введіть логін користувача">
                </div>

                <div class="access-form-field">
                    <label for="userPassword">Пароль користувача *</label>
                    <input type="password" id="userPassword" name="password" required placeholder="Введіть пароль користувача">
                </div>

                <div class="access-form-field">
                    <label for="userNotes">Нотатки</label>
                    <textarea id="userNotes" name="notes" rows="3" placeholder="Введіть нотатки про користувача"></textarea>
                </div>

                <div class="access-modal-actions">
                    <button type="button" class="access-btn-secondary" onclick="closeCreateUserModal()">Закрити</button>
                    <button type="submit" class="access-btn-primary">Зберегти</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
window.openCreateUserModal = function() {
    const modal = document.getElementById('createUserModal');
    if (modal) {
        modal.style.display = 'flex';
        document.getElementById('userFullName').focus();
    }
};

window.closeCreateUserModal = function() {
    const modal = document.getElementById('createUserModal');
    if (modal) {
        modal.style.display = 'none';
    }
    const form = document.getElementById('createUserForm');
    if (form) {
        form.reset();
    }
};

window.handleCreateUser = function(event) {
    event.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('full_name', document.getElementById('userFullName').value.trim());
    formData.append('login', document.getElementById('userLogin').value.trim());
    formData.append('password', document.getElementById('userPassword').value);
    formData.append('notes', document.getElementById('userNotes').value.trim());

    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Збереження...';

    fetch('/modules/access/logic/handle_user_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeCreateUserModal();
            location.reload();
        } else {
            alert(data.message || 'Помилка при створенні користувача');
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
    const modal = document.getElementById('createUserModal');
    if (modal && event.target === modal) {
        closeCreateUserModal();
    }
});
</script>
