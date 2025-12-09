<?php
// Блок 2 модуля Доступи - Таблиця користувачів
require_once __DIR__ . '/../logic/index.php';

$users = getAccessUsers($mysqli);
?>
<div class="access-block">
    <div style="padding: 16px;">
        <h4 style="margin: 0 0 12px; font-size: 14px; font-weight: 600;">Список користувачів</h4>
        <?php if (empty($users)): ?>
            <p style="color: #6b7280; font-size: 13px;">Користувачі ще не додані.</p>
        <?php else: ?>
            <div class="access-table-container">
                <table class="access-table">
                    <thead>
                        <tr>
                            <th>№</th>
                            <th>ПІБ користувача</th>
                            <th>Логін</th>
                            <th>Пароль</th>
                            <th>Створено</th>
                            <th>Нотатки</th>
                            <th>Дії</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = 1;
                        foreach ($users as $user): 
                        ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td><?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($user['login'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>••••••••</td>
                                <td><?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($user['notes'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <button type="button" class="access-btn-edit" onclick="editUser(<?php echo $user['id']; ?>)">
                                        Редагувати
                                    </button>
                                    <button type="button" class="access-btn-delete" onclick="deleteUser(<?php echo $user['id']; ?>)">
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

<!-- Модальне вікно для редагування користувача -->
<div id="editUserModal" class="access-modal">
    <div class="access-modal-content">
        <div class="access-modal-header">
            <h3>Редагувати користувача</h3>
            <span class="access-modal-close" onclick="closeEditUserModal()">&times;</span>
        </div>
        <div class="access-modal-body">
            <form id="editUserForm" onsubmit="handleUpdateUser(event)">
                <input type="hidden" id="editUserId" name="id">
                
                <div class="access-form-field">
                    <label for="editUserFullName">ПІБ користувача *</label>
                    <input type="text" id="editUserFullName" name="full_name" required placeholder="Введіть ПІБ користувача">
                </div>

                <div class="access-form-field">
                    <label for="editUserLogin">Логін користувача *</label>
                    <input type="text" id="editUserLogin" name="login" required placeholder="Введіть логін користувача">
                </div>

                <div class="access-form-field">
                    <label for="editUserPassword">Новий пароль (залиште порожнім, щоб не змінювати)</label>
                    <input type="password" id="editUserPassword" name="password" placeholder="Введіть новий пароль">
                </div>

                <div class="access-form-field">
                    <label for="editUserNotes">Нотатки</label>
                    <textarea id="editUserNotes" name="notes" rows="3" placeholder="Введіть нотатки про користувача"></textarea>
                </div>

                <div class="access-modal-actions">
                    <button type="button" class="access-btn-secondary" onclick="closeEditUserModal()">Скасувати</button>
                    <button type="submit" class="access-btn-primary">Зберегти зміни</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
window.editUser = function(userId) {
    const modal = document.getElementById('editUserModal');
    modal.style.display = 'flex';
    
    fetch(`/modules/access/logic/handle_user_action.php?action=get&id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.user) {
                document.getElementById('editUserId').value = data.user.id;
                document.getElementById('editUserFullName').value = data.user.full_name;
                document.getElementById('editUserLogin').value = data.user.login;
                document.getElementById('editUserNotes').value = data.user.notes || '';
                document.getElementById('editUserFullName').focus();
            } else {
                alert('Помилка завантаження даних користувача');
                closeEditUserModal();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Помилка при завантаженні даних');
            closeEditUserModal();
        });
};

window.closeEditUserModal = function() {
    document.getElementById('editUserModal').style.display = 'none';
    document.getElementById('editUserForm').reset();
};

window.handleUpdateUser = function(event) {
    event.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('id', document.getElementById('editUserId').value);
    formData.append('full_name', document.getElementById('editUserFullName').value.trim());
    formData.append('login', document.getElementById('editUserLogin').value.trim());
    
    const password = document.getElementById('editUserPassword').value;
    if (password) {
        formData.append('password', password);
    }
    
    formData.append('notes', document.getElementById('editUserNotes').value.trim());

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
            closeEditUserModal();
            location.reload();
        } else {
            alert(data.message || 'Помилка при оновленні користувача');
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

window.deleteUser = function(userId) {
    if (!confirm('Ви впевнені, що хочете видалити цього користувача?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', userId);

    fetch('/modules/access/logic/handle_user_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Помилка при видаленні користувача');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Помилка при відправці запиту');
    });
};

// Закрити модальне вікно редагування при кліку поза ним
document.addEventListener('click', function(event) {
    const editModal = document.getElementById('editUserModal');
    if (editModal && event.target === editModal) {
        closeEditUserModal();
    }
});
</script>
