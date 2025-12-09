<?php
// Блок 15 модуля Доступи - Список блоків дозволів
require_once __DIR__ . '/../logic/index.php';

$blocks = getPermissionBlocks($mysqli);
?>
<div class="access-block">
    <div style="padding: 24px;">
        <h3 style="margin: 0 0 20px; font-size: 16px; font-weight: 600; color: #111827;">СПИСОК БЛОКІВ ДОЗВОЛІВ</h3>
        
        <?php if (empty($blocks)): ?>
            <div style="text-align: center; padding: 40px 20px; color: #6b7280; font-size: 14px; background: #f9fafb; border-radius: 12px;">
                <p style="margin: 0;">Немає створених блоків дозволів. Створіть перший блок, натиснувши кнопку "Створити блок дозволів".</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; background: #ffffff; border-radius: 12px; overflow: hidden;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #f9fafb, #f3f4f6);">
                            <th style="padding: 12px 16px; text-align: left; font-size: 13px; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb;">ID</th>
                            <th style="padding: 12px 16px; text-align: left; font-size: 13px; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb;">Назва блока</th>
                            <th style="padding: 12px 16px; text-align: left; font-size: 13px; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb;">Нотатки</th>
                            <th style="padding: 12px 16px; text-align: left; font-size: 13px; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb;">Створено</th>
                            <th style="padding: 12px 16px; text-align: right; font-size: 13px; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb;">Дії</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($blocks as $index => $block): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#ffffff'">
                                <td style="padding: 14px 16px; font-size: 13px; color: #111827;"><?php echo $block['id']; ?></td>
                                <td style="padding: 14px 16px; font-size: 13px; color: #111827; font-weight: 500;"><?php echo htmlspecialchars($block['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td style="padding: 14px 16px; font-size: 13px; color: #6b7280;"><?php echo !empty($block['notes']) ? htmlspecialchars($block['notes'], ENT_QUOTES, 'UTF-8') : '—'; ?></td>
                                <td style="padding: 14px 16px; font-size: 13px; color: #6b7280;"><?php echo date('Y-m-d\TH:i', strtotime($block['created_at'])); ?></td>
                                <td style="padding: 14px 16px; text-align: right;">
                                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                        <button type="button" onclick="openEditPermissionBlockModal(<?php echo $block['id']; ?>)" style="border-radius: 6px; border: 1px solid #3b82f6; padding: 6px 12px; font-size: 12px; font-weight: 500; cursor: pointer; background: #ffffff; color: #3b82f6; transition: background 0.15s ease;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='#ffffff'">
                                            Редагувати
                                        </button>
                                        <button type="button" onclick="deletePermissionBlock(<?php echo $block['id']; ?>)" style="border-radius: 6px; border: 1px solid #ef4444; padding: 6px 12px; font-size: 12px; font-weight: 500; cursor: pointer; background: #ffffff; color: #ef4444; transition: background 0.15s ease;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='#ffffff'">
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
</div>
