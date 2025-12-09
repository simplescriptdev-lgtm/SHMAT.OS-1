<?php
// Головний файл модуля "Управління складами", який збирає вкладки докупи.

require_once __DIR__ . '/logic/index.php';

$activeTab = isset($_GET['tab']) ? (int) $_GET['tab'] : 1;
if ($activeTab < 1 || $activeTab > 5) {
    $activeTab = 1;
}

// Масив назв вкладок
$tabs = [
    1 => 'Склади',
    2 => 'Залишки',
    3 => 'Переміщення',
    4 => 'Інвентаризація',
    5 => 'Звіти',
];

// Підключаємо стилі модуля
echo '<link rel="stylesheet" href="/modules/warehouses/warehouses.css">';
?>

<div class="top-label">Управління складами</div>
<div class="panel-header" style="text-align:left;margin-bottom:12px;">
    <div class="panel-title" style="font-size:17px;">Модуль управління складами</div>
</div>

<div class="warehouses-tabs">
    <?php foreach ($tabs as $tabNumber => $tabTitle): ?>
        <?php
        $isActive = $tabNumber === $activeTab;
        $tabUrl = '?page=warehouses&tab=' . $tabNumber;
        ?>
        <a href="<?php echo htmlspecialchars($tabUrl, ENT_QUOTES, 'UTF-8'); ?>"
           class="warehouses-tab<?php echo $isActive ? ' warehouses-tab--active' : ''; ?>">
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



