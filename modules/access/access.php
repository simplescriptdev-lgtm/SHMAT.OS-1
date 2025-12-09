<?php
// Головний файл модуля "Доступи", який збирає вкладки докупи.

require_once __DIR__ . '/logic/index.php';

$activeTab = isset($_GET['tab']) ? (int) $_GET['tab'] : 1;
if ($activeTab < 1 || $activeTab > 5) {
    $activeTab = 1;
}

// Масив назв вкладок
$tabs = [
    1 => 'Користувачі',
    2 => 'Матриця дозволів',
    3 => 'Дозволи',
    4 => 'Групи',
    5 => 'Логи доступу',
];

// Підключаємо стилі модуля
echo '<link rel="stylesheet" href="/modules/access/access.css">';
?>

<div class="top-label">Доступи</div>
<div class="panel-header" style="text-align:left;margin-bottom:12px;">
    <div class="panel-title" style="font-size:17px;">Модуль управління доступами</div>
</div>

<div class="access-tabs">
    <?php foreach ($tabs as $tabNumber => $tabTitle): ?>
        <?php
        $isActive = $tabNumber === $activeTab;
        $tabUrl = '?page=access&tab=' . $tabNumber;
        ?>
        <a href="<?php echo htmlspecialchars($tabUrl, ENT_QUOTES, 'UTF-8'); ?>"
           class="access-tab<?php echo $isActive ? ' access-tab--active' : ''; ?>">
            <?php echo htmlspecialchars($tabTitle, ENT_QUOTES, 'UTF-8'); ?>
        </a>
    <?php endforeach; ?>
</div>

<?php
// Підключаємо файл вкладки, який у свою чергу підключає 6 блоків
$tabFile = __DIR__ . '/tabs/tab' . $activeTab . '.php';
if (is_file($tabFile)) {
    require $tabFile;
}

