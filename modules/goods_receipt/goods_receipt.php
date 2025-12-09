<?php
// Головний файл модуля "Прихід товару", який збирає вкладки докупи.

require_once __DIR__ . '/logic/index.php';

$activeTab = isset($_GET['tab']) ? (int) $_GET['tab'] : 1;
if ($activeTab < 1 || $activeTab > 4) {
    $activeTab = 1;
}

// Масив назв вкладок
$tabs = [
    1 => 'Прихід товару',
    2 => 'Накладні',
    3 => 'Постачальники',
    4 => 'Звіти',
];

// Підключаємо стилі модуля
echo '<link rel="stylesheet" href="/modules/goods_receipt/goods_receipt.css">';
?>

<div class="top-label">Прихід товару</div>
<div class="panel-header" style="text-align:left;margin-bottom:12px;">
    <div class="panel-title" style="font-size:17px;">Модуль приходу товару</div>
</div>

<div class="goods-receipt-tabs">
    <?php foreach ($tabs as $tabNumber => $tabTitle): ?>
        <?php
        $isActive = $tabNumber === $activeTab;
        $tabUrl = '?page=goods_receipt&tab=' . $tabNumber;
        ?>
        <a href="<?php echo htmlspecialchars($tabUrl, ENT_QUOTES, 'UTF-8'); ?>"
           class="goods-receipt-tab<?php echo $isActive ? ' goods-receipt-tab--active' : ''; ?>">
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



