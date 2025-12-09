<?php
// Головний файл модуля "Налаштування", який збирає вкладки докупи.

require_once __DIR__ . '/logic/index.php';

// Повідомлення про завантаження логотипу
$logoUploadError = isset($_SESSION['logo_upload_error']) ? $_SESSION['logo_upload_error'] : null;
$logoUploadSuccess = isset($_SESSION['logo_upload_success']) ? $_SESSION['logo_upload_success'] : null;
unset($_SESSION['logo_upload_error'], $_SESSION['logo_upload_success']);

$activeTab = isset($_GET['tab']) ? (int) $_GET['tab'] : 1;
if ($activeTab < 1 || $activeTab > 6) {
    $activeTab = 1;
}

// Масив назв вкладок (можна буде розширити)
$tabs = [
    1 => 'Загальні',
    2 => 'Користувачі',
    3 => 'Сповіщення',
    4 => 'Доступи',
    5 => 'Інтеграції',
    6 => 'Логотип',
];

// Підключаємо стилі модуля
echo '<link rel="stylesheet" href="/modules/settings/settings.css">';
?>

<div class="top-label">Налаштування</div>
<div class="panel-header" style="text-align:left;margin-bottom:12px;">
    <div class="panel-title" style="font-size:17px;">Модуль налаштувань</div>
</div>

<div class="settings-tabs">
    <?php foreach ($tabs as $tabNumber => $tabTitle): ?>
        <?php
        $isActive = $tabNumber === $activeTab;
        $tabUrl = '?page=settings&tab=' . $tabNumber;
        ?>
        <a href="<?php echo htmlspecialchars($tabUrl, ENT_QUOTES, 'UTF-8'); ?>"
           class="settings-tab<?php echo $isActive ? ' settings-tab--active' : ''; ?>">
            <?php echo htmlspecialchars($tabTitle, ENT_QUOTES, 'UTF-8'); ?>
        </a>
    <?php endforeach; ?>
</div>

<?php
// Підключаємо файл вкладки, який у свою чергу підключає 5 блоків
$tabFile = __DIR__ . '/tabs/tab' . $activeTab . '.php';
if (is_file($tabFile)) {
    require $tabFile;
}

