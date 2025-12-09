<?php
// Блок 9 модуля Доступи - Матриця доступів користувача (заголовок та перший блок дозволів)
require_once __DIR__ . '/../logic/index.php';

$selectedUserId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;

if ($selectedUserId <= 0) {
    echo '<div class="access-block"><div style="padding: 24px; text-align: center; color: #6b7280;">Оберіть користувача для налаштування доступів</div></div>';
    return;
}

// Отримуємо всі блоки дозволів
$blocks = getPermissionBlocks($mysqli);
$userPermissions = getUserPermissions($mysqli, $selectedUserId);
?>
<div class="access-block">
    <div style="padding: 24px;">
        <h2 style="margin: 0 0 8px; font-size: 18px; font-weight: 600; color: #111827;">МАТРИЦЯ ДОСТУПІВ КОРИСТУВАЧА</h2>
        <p style="margin: 0 0 24px; font-size: 14px; color: #6b7280; line-height: 1.5;">
            Поставте галочки біля функцій, які будуть доступні обраному користувачу.
        </p>
        
        <?php if (empty($blocks)): ?>
            <div style="text-align: center; padding: 40px 20px; color: #6b7280; font-size: 14px; background: #f9fafb; border-radius: 12px;">
                <p style="margin: 0;">Немає створених блоків дозволів. Створіть блоки дозволів у вкладці "Дозволи".</p>
            </div>
        <?php else: ?>
            <?php 
            $blockIndex = 0;
            foreach ($blocks as $block): 
                $blockIndex++;
                // Отримуємо доступи для цього блоку
                $permissions = [];
                $sql = "
                    SELECT id, name, code, notes
                    FROM permissions
                    WHERE block_id = ?
                    ORDER BY code ASC
                ";
                $stmt = $mysqli->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("i", $block['id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) {
                        $permissions[] = $row;
                    }
                    $stmt->close();
                }
            ?>
                <div style="margin-bottom: 24px; padding: 20px; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h3 style="margin: 0; font-size: 15px; font-weight: 600; color: #111827;"><?php echo htmlspecialchars($block['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <span style="font-size: 12px; color: #6b7280; background: #f3f4f6; padding: 4px 8px; border-radius: 6px;"><?php echo $blockIndex; ?></span>
                    </div>
                    
                    <?php if (empty($permissions)): ?>
                        <div style="text-align: center; padding: 20px; color: #9ca3af; font-size: 13px; background: #f9fafb; border-radius: 8px;">
                            <p style="margin: 0;">У цьому блоці немає доступів</p>
                        </div>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: linear-gradient(135deg, #f9fafb, #f3f4f6);">
                                        <th style="padding: 10px 14px; text-align: left; font-size: 12px; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb;">ID</th>
                                        <th style="padding: 10px 14px; text-align: left; font-size: 12px; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb;">Назва доступу</th>
                                        <th style="padding: 10px 14px; text-align: left; font-size: 12px; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb;">Код (ідентифікатор)</th>
                                        <th style="padding: 10px 14px; text-align: center; font-size: 12px; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb;">Доступ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($permissions as $permission): 
                                        $isChecked = in_array($permission['id'], $userPermissions);
                                    ?>
                                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#ffffff'">
                                            <td style="padding: 12px 14px; font-size: 13px; color: #111827;"><?php echo $permission['id']; ?></td>
                                            <td style="padding: 12px 14px; font-size: 13px; color: #111827; font-weight: 500;"><?php echo htmlspecialchars($permission['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td style="padding: 12px 14px; font-size: 13px; color: #1d4ed8; font-weight: 500;"><?php echo htmlspecialchars($permission['code'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td style="padding: 12px 14px; text-align: center;">
                                                <input 
                                                    type="checkbox" 
                                                    class="permission-checkbox" 
                                                    data-permission-id="<?php echo $permission['id']; ?>"
                                                    <?php echo $isChecked ? 'checked' : ''; ?>
                                                    style="width: 18px; height: 18px; cursor: pointer; accent-color: #3b82f6;"
                                                >
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <div style="margin-top: 24px; text-align: right;">
                <button 
                    type="button" 
                    onclick="saveUserPermissions()" 
                    class="access-btn-primary"
                    style="background: linear-gradient(135deg, #1e40af, #1e3a8a); padding: 12px 24px; border-radius: 8px; border: none; color: #ffffff; font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.15s ease;"
                    onmouseover="this.style.background='linear-gradient(135deg, #1e3a8a, #1e293b)'"
                    onmouseout="this.style.background='linear-gradient(135deg, #1e40af, #1e3a8a)'"
                >
                    Зберегти
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
window.saveUserPermissions = function() {
    const checkboxes = document.querySelectorAll('.permission-checkbox');
    const permissionIds = [];
    
    checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
            permissionIds.push(parseInt(checkbox.getAttribute('data-permission-id')));
        }
    });
    
    const userId = <?php echo $selectedUserId; ?>;
    
    const formData = new FormData();
    formData.append('action', 'save_user_permissions');
    formData.append('user_id', userId);
    formData.append('permission_ids', JSON.stringify(permissionIds));
    
    const saveBtn = event.target;
    const originalText = saveBtn.textContent;
    saveBtn.disabled = true;
    saveBtn.textContent = 'Збереження...';
    
    fetch('/modules/access/logic/handle_permission_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Доступи успішно збережено');
            location.reload();
        } else {
            alert(data.message || 'Помилка при збереженні доступів');
            saveBtn.disabled = false;
            saveBtn.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Помилка при відправці запиту');
        saveBtn.disabled = false;
        saveBtn.textContent = originalText;
    });
};
</script>
