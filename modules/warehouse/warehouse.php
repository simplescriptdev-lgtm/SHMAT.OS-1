<?php
// Головний файл модуля окремого складу, який збирає вкладки докупи.

require_once __DIR__ . '/logic/index.php';
require_once __DIR__ . '/../access/logic/index.php';

// Отримуємо ID складу з GET параметра
$warehouseId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($warehouseId <= 0) {
    header('Location: ?page=warehouses');
    exit;
}

// Перевіряємо доступ до складу
if (!hasAccessToWarehouse($mysqli, $warehouseId)) {
    echo '<div style="padding: 40px; text-align: center; color: #dc2626;"><h2>Доступ заборонено</h2><p>У вас немає доступу до цього складу.</p></div>';
    exit;
}

// Отримуємо інформацію про склад
$warehouse = getWarehouseById($mysqli, $warehouseId);

if (!$warehouse) {
    header('Location: ?page=warehouses');
    exit;
}

// Передаємо warehouseId у глобальну змінну для використання в блоках
$GLOBALS['current_warehouse_id'] = $warehouseId;

$activeTab = isset($_GET['tab']) ? (int) $_GET['tab'] : 1;
if ($activeTab < 1 || $activeTab > 6) {
    $activeTab = 1;
}

// Масив назв вкладок
$tabs = [
    1 => 'Огляд',
    2 => 'Залишки',
    3 => 'Переміщення',
    4 => 'Інвентаризація',
    5 => 'Звіти',
    6 => 'Налаштування',
];

// Підключаємо стилі модуля
echo '<link rel="stylesheet" href="/modules/warehouse/warehouse.css">';
?>

<div class="top-label"><?php echo htmlspecialchars($warehouse['name'], ENT_QUOTES, 'UTF-8'); ?></div>
<div class="panel-header" style="text-align:left;margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;">
    <div class="panel-title" style="font-size:17px;"><?php echo htmlspecialchars($warehouse['identification_number'], ENT_QUOTES, 'UTF-8'); ?></div>
    <button type="button" id="transferCartBtn" class="warehouse-transfer-cart-btn">
        Корзина переміщення
        <span id="transferCartBadge" class="warehouse-transfer-cart-badge" style="display:none;">0</span>
    </button>
</div>

<div class="warehouse-tabs">
    <?php foreach ($tabs as $tabNumber => $tabTitle): ?>
        <?php
        $isActive = $tabNumber === $activeTab;
        $tabUrl = '?page=warehouse&id=' . $warehouseId . '&tab=' . $tabNumber;
        ?>
        <a href="<?php echo htmlspecialchars($tabUrl, ENT_QUOTES, 'UTF-8'); ?>"
           class="warehouse-tab<?php echo $isActive ? ' warehouse-tab--active' : ''; ?>">
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
?>

<!-- Модальні вікна для переміщення (винесені за межі основного контенту) -->
<?php require __DIR__ . '/blocks/transfer_modals.php'; ?>
<?php require __DIR__ . '/blocks/transfer_scripts.php'; ?>

