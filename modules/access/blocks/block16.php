<?php
// Блок 16 модуля Доступи - Список доступів, згрупованих за блоками
require_once __DIR__ . '/../logic/index.php';

$permissions = getPermissions($mysqli);
?>
<div class="access-block">
    <div style="padding: 24px;">
        <h3 style="margin: 0 0 8px; font-size: 16px; font-weight: 600; color: #111827;">СПИСОК ДОСТУПІВ</h3>
        <p style="margin: 0 0 20px; font-size: 14px; color: #6b7280; line-height: 1.5;">
            Усі створені доступи, згруповані за блоками. Тут можна швидко побачити, до якого блоку належить кожен доступ.
        </p>
        
        <?php if (empty($permissions)): ?>
            <div style="text-align: center; padding: 40px 20px; color: #6b7280; font-size: 14px; background: #f9fafb; border-radius: 12px;">
                <p style="margin: 0;">Немає створених доступів. Створіть перший доступ, натиснувши кнопку "Створити доступ".</p>
            </div>
        <?php else: ?>
            <?php foreach ($permissions as $blockId => $blockData): ?>
                <div style="margin-bottom: 32px; padding: 20px; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h4 style="margin: 0; font-size: 15px; font-weight: 600; color: #111827;">Блок: <?php echo htmlspecialchars($blockData['block_name'], ENT_QUOTES, 'UTF-8'); ?></h4>
                        <span style="font-size: 12px; color: #6b7280;">ID блоку: <?php echo $blockId; ?></span>
                    </div>
                    
                    <?php if (empty($blockData['items'])): ?>
                        <div style="text-align: center; padding: 20px; color: #9ca3af; font-size: 13px; background: #f9fafb; border-radius: 8px;">
                            <p style="margin: 0;">У цьому блоці немає доступів</p>
                        </div>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: linear-gradient(135deg, #f9fafb, #f3f4f6);">
                                        <th style="padding: 10px 14px; text-align: left; font-size: 12px; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb;">ID</th>
                                        <th style="padding: 10px 14px; text-align: left; font-size: 12px; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb;">НАЗВА ДОСТУПУ</th>
                                        <th style="padding: 10px 14px; text-align: left; font-size: 12px; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb;">КОД ДОСТУПУ</th>
                                        <th style="padding: 10px 14px; text-align: left; font-size: 12px; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb;">НОТАТКИ</th>
                                        <th style="padding: 10px 14px; text-align: left; font-size: 12px; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb;">СТВОРЕНО</th>
                                        <th style="padding: 10px 14px; text-align: right; font-size: 12px; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb;">ДІЇ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($blockData['items'] as $permission): ?>
                                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#ffffff'">
                                            <td style="padding: 12px 14px; font-size: 13px; color: #111827;"><?php echo $permission['id']; ?></td>
                                            <td style="padding: 12px 14px; font-size: 13px; color: #111827; font-weight: 500;"><?php echo htmlspecialchars($permission['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td style="padding: 12px 14px; font-size: 13px; color: #1d4ed8; font-weight: 500;"><?php echo htmlspecialchars($permission['code'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td style="padding: 12px 14px; font-size: 13px; color: #6b7280;"><?php echo !empty($permission['notes']) ? htmlspecialchars($permission['notes'], ENT_QUOTES, 'UTF-8') : 'немає'; ?></td>
                                            <td style="padding: 12px 14px; font-size: 13px; color: #6b7280;"><?php echo date('Y-m-d\TH:i', strtotime($permission['created_at'])); ?></td>
                                            <td style="padding: 12px 14px; text-align: right;">
                                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                                    <button type="button" onclick="openEditPermissionModal(<?php echo $permission['id']; ?>)" style="border-radius: 6px; border: 1px solid #3b82f6; padding: 6px 12px; font-size: 12px; font-weight: 500; cursor: pointer; background: #ffffff; color: #3b82f6; transition: background 0.15s ease;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='#ffffff'">
                                                        Редагувати
                                                    </button>
                                                    <button type="button" onclick="deletePermission(<?php echo $permission['id']; ?>)" style="border-radius: 6px; border: 1px solid #ef4444; padding: 6px 12px; font-size: 12px; font-weight: 500; cursor: pointer; background: #ffffff; color: #ef4444; transition: background 0.15s ease;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='#ffffff'">
                                                        Видалити
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
