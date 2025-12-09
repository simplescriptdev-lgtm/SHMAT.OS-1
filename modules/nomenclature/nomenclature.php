<?php
// Головний файл модуля "Номенклатура товару", який збирає вкладки докупи.

require_once __DIR__ . '/logic/index.php';

$activeTab = isset($_GET['tab']) ? (int) $_GET['tab'] : 1;
if ($activeTab < 1 || $activeTab > 5) {
    $activeTab = 1;
}

// Масив назв вкладок
$tabs = [
    1 => 'Каталог товарів',
    2 => 'Категорії',
    3 => 'Постачальники',
    4 => 'Склади',
    5 => 'Звіти',
];

// Підключаємо стилі модуля
echo '<link rel="stylesheet" href="/modules/nomenclature/nomenclature.css">';
?>

<div class="top-label">Номенклатура товару</div>
<div class="panel-header" style="text-align:left;margin-bottom:12px;">
    <div class="panel-title" style="font-size:17px;">Модуль номенклатури товару</div>
</div>

<div class="nomenclature-tabs">
    <?php foreach ($tabs as $tabNumber => $tabTitle): ?>
        <?php
        $isActive = $tabNumber === $activeTab;
        $tabUrl = '?page=nomenclature&tab=' . $tabNumber;
        ?>
        <a href="<?php echo htmlspecialchars($tabUrl, ENT_QUOTES, 'UTF-8'); ?>"
           class="nomenclature-tab<?php echo $isActive ? ' nomenclature-tab--active' : ''; ?>">
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

