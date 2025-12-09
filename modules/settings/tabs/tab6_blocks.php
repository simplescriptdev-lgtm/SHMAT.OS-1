<?php
// Вкладка 6: блоки для роботи з логотипом
?>
<div class="settings-content">
    <div class="settings-block">
        <div class="settings-block-inner">
            <div class="settings-logo-upload">
                <h4>Завантаження логотипу</h4>
                <p>Прямокутне зображення, формат PNG / JPG / WEBP, до 2 МБ.</p>

                <?php if (!empty($logoUploadError)): ?>
                    <div class="settings-alert settings-alert--error">
                        <?php echo htmlspecialchars($logoUploadError, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($logoUploadSuccess)): ?>
                    <div class="settings-alert settings-alert--success">
                        <?php echo htmlspecialchars($logoUploadSuccess, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="/modules/settings/logic/upload_logo.php" enctype="multipart/form-data">
                    <div class="settings-file-input">
                        <input type="file" id="logo_rect" name="logo_rect" accept="image/png,image/jpeg,image/webp" required>
                    </div>
                    <button type="submit" class="settings-upload-btn">Завантажити логотип</button>
                </form>
            </div>
            <div class="settings-logo-preview">
                <h4>Поточний логотип</h4>
                <div class="settings-logo-preview-image">
                    <img src="/public/logo.php" alt="Поточний логотип">
                </div>
            </div>
        </div>
    </div>

    <?php for ($i = 2; $i <= 5; $i++): ?>
        <div class="settings-block"></div>
    <?php endfor; ?>
</div>


