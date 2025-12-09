<?php
// Блок 8 модуля Доступи - Вибір користувача для налаштування доступів
require_once __DIR__ . '/../logic/index.php';

$selectedUserId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : (isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0);
$users = getAccessUsers($mysqli);
?>
<div class="access-block">
    <div style="padding: 24px;">
        <h2 style="margin: 0 0 8px; font-size: 18px; font-weight: 600; color: #111827;">ОБРАТИ КОРИСТУВАЧА</h2>
        <p style="margin: 0 0 20px; font-size: 14px; color: #6b7280; line-height: 1.5;">
            Оберіть користувача, щоб налаштувати йому доступи до блоків та функцій.
        </p>
        
        <div style="display: flex; gap: 12px; align-items: flex-end;">
            <div style="flex: 1; max-width: 400px;">
                <label for="userIdSelect" style="display: block; margin-bottom: 6px; font-size: 13px; font-weight: 500; color: #374151;">Користувач</label>
                <select 
                    id="userIdSelect" 
                    onchange="applyUserSelection()"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; background: #ffffff; cursor: pointer; transition: border-color 0.15s ease; appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 4 5%22><path fill=%22%23666%22 d=%22M2 0L0 2h4zm0 5L0 3h4z%22/></svg>'); background-repeat: no-repeat; background-position: right 12px center; background-size: 12px; padding-right: 36px;"
                    onfocus="this.style.borderColor='#3b82f6'; this.style.outline='none';"
                    onblur="this.style.borderColor='#d1d5db';"
                >
                    <option value="">-- Оберіть користувача --</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>" <?php echo $selectedUserId == $user['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?> (ID: <?php echo $user['id']; ?>, Логін: <?php echo htmlspecialchars($user['login'], ENT_QUOTES, 'UTF-8'); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <?php if ($selectedUserId > 0): 
            $selectedUser = null;
            foreach ($users as $user) {
                if ($user['id'] == $selectedUserId) {
                    $selectedUser = $user;
                    break;
                }
            }
        ?>
            <?php if ($selectedUser): ?>
                <div style="margin-top: 20px; padding: 16px; background: #eff6ff; border-radius: 8px; border-left: 4px solid #3b82f6;">
                    <div style="font-size: 14px; font-weight: 500; color: #1e40af; margin-bottom: 4px;">
                        Обраний користувач:
                    </div>
                    <div style="font-size: 15px; font-weight: 600; color: #111827;">
                        <?php echo htmlspecialchars($selectedUser['full_name'], ENT_QUOTES, 'UTF-8'); ?> 
                        <span style="font-weight: 400; color: #6b7280;">(ID: <?php echo $selectedUser['id']; ?>)</span>
                    </div>
                </div>
            <?php else: ?>
                <div style="margin-top: 20px; padding: 16px; background: #fef2f2; border-radius: 8px; border-left: 4px solid #ef4444;">
                    <div style="font-size: 14px; color: #dc2626;">
                        Користувач з ID <?php echo $selectedUserId; ?> не знайдено.
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
window.applyUserSelection = function() {
    const select = document.getElementById('userIdSelect');
    const userId = select.value;
    
    if (!userId || parseInt(userId) <= 0) {
        return;
    }
    
    // Оновлюємо URL з параметром user_id
    const url = new URL(window.location.href);
    url.searchParams.set('user_id', userId);
    window.location.href = url.toString();
};
</script>
