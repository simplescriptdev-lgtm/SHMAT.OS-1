<?php
// Вікно входу / панель технічного директора
require_once __DIR__ . '/../../config/bootstrap.php';

$isLoggedIn = !empty($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
$authError = isset($_SESSION['auth_error']) ? $_SESSION['auth_error'] : null;
unset($_SESSION['auth_error']); // показуємо помилку лише один раз

// Поточний логотип віддає спеціальний endpoint
$loginLogoPath = '/public/logo.php';
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Shmat OS – Авторизація</title>
    <style>
        :root {
            --bg-body: #f5f5f7;
            --bg-sidebar: #ffffff;
            --bg-surface: #ffffff;
            --border-subtle: #e5e7eb;
            --border-strong: #d1d5db;
            --text-main: #111827;
            --text-muted: #6b7280;
            --accent: #2563eb;
            --accent-soft: #eff4ff;
            --danger: #dc2626;
            --danger-soft: #fef2f2;
            --radius-lg: 16px;
            --radius-md: 12px;
            --radius-pill: 999px;
            --shadow-soft: 0 18px 40px rgba(15, 23, 42, 0.08);
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "SF Pro Text", "Segoe UI", sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
        }
        .app-shell {
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr);
            min-height: 100vh;
        }
        .sidebar {
            background-color: var(--bg-sidebar);
            border-right: 1px solid var(--border-subtle);
            padding: 18px 18px 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 4px 8px;
        }
        .logo-mark {
            width: 28px;
            height: 28px;
            border-radius: 11px;
            background: radial-gradient(circle at 0% 0%, #22c55e, #16a34a 55%, #0f766e 100%);
            box-shadow: 0 12px 30px rgba(34, 197, 94, 0.35);
        }
        .logo-text {
            display: flex;
            flex-direction: column;
        }
        .logo-text span:first-child {
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.02em;
        }
        .logo-text span:last-child {
            font-size: 11px;
            color: var(--text-muted);
        }
        .sidebar-section-title {
            margin-top: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: #9ca3af;
            padding: 0 8px;
        }
        .nav-list {
            list-style: none;
            margin: 4px 0 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .nav-item {
            border-radius: 10px;
            padding: 7px 10px;
            font-size: 13px;
            color: var(--text-muted);
            display: block;
            cursor: default;
        }
        .nav-item > a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }
        .nav-item--active {
            background-color: #eff6ff;
            color: #111827;
            font-weight: 500;
        }
        .nav-item-badge {
            font-size: 11px;
            border-radius: 999px;
            padding: 1px 7px;
            background: #e0f2fe;
            color: #0369a1;
        }
        .nav-item-toggle {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0 4px;
            margin: 0;
            color: inherit;
            font-size: 12px;
            transition: transform 0.2s ease;
        }
        .nav-item-toggle:hover {
            opacity: 0.7;
        }
        .nav-item-toggle.rotated {
            transform: rotate(180deg);
        }
        .nav-sublist {
            list-style: none;
            padding: 0;
            margin: 4px 0 0 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, padding 0.3s ease;
            width: 100%;
        }
        .nav-sublist.show {
            max-height: 500px;
            padding: 4px 0;
        }
        .nav-sublist li {
            padding: 4px 0;
            margin-left: 20px;
        }
        .nav-sublist a {
            text-decoration: none;
            color: inherit;
            font-size: 13px;
            display: block;
            padding: 4px 8px;
            border-radius: 6px;
            transition: background 0.15s ease;
        }
        .nav-sublist a:hover {
            background: #eff6ff;
        }
        .sidebar-footer {
            margin-top: auto;
            padding: 6px 8px 0;
        }
        .btn-logout {
            width: 100%;
            border-radius: var(--radius-pill);
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #b91c1c;
            font-size: 13px;
            padding: 7px 0;
            cursor: pointer;
        }
        .btn-logout:hover {
            background: #fee2e2;
        }

        .main {
            padding: 20px 28px 28px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .main-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .main-title {
            font-size: 20px;
            font-weight: 600;
        }
        .main-subtitle {
            margin-top: 2px;
            font-size: 13px;
            color: var(--text-muted);
        }
        .main-actions {
            display: flex;
            gap: 8px;
        }
        .chip {
            border-radius: var(--radius-pill);
            border: 1px solid var(--border-subtle);
            background: #f9fafb;
            font-size: 12px;
            padding: 6px 10px;
            color: var(--text-muted);
        }

        .content-grid,
        .content-grid--login-only {
            display: grid;
            grid-template-columns: minmax(0, 480px);
            justify-content: center;
            margin-top: 4vh;
        }
        .content-grid--login-only {
            min-height: 100vh;
            align-content: center;
        }
        .panel {
            background: var(--bg-surface);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-subtle);
            box-shadow: var(--shadow-soft);
            padding: 20px 22px 22px;
        }
        .panel--wide {
            width: 100%;
            max-width: none;
            margin: 0;
        }
        .panel-header {
            text-align: center;
            margin-bottom: 18px;
        }
        .panel-logo {
            margin-bottom: 10px;
        }
        .panel-logo img {
            max-width: 220px;
            height: auto;
            display: block;
            margin: 0 auto;
        }
        .panel-title {
            font-size: 18px;
            font-weight: 600;
        }
        .field-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            row-gap: 10px;
            margin-bottom: 14px;
        }
        label {
            display: block;
            font-size: 12px;
            margin-bottom: 4px;
            color: var(--text-muted);
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 8px 9px;
            border-radius: 10px;
            border: 1px solid var(--border-subtle);
            background: #f9fafb;
            color: var(--text-main);
            font-size: 13px;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #60a5fa;
            box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.25);
            background: #ffffff;
        }
        .btn-primary {
            width: 100%;
            border-radius: var(--radius-pill);
            border: none;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            font-size: 14px;
            font-weight: 500;
            padding: 9px 0;
            cursor: pointer;
            box-shadow: 0 12px 30px rgba(37, 99, 235, 0.35);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
        }
        .error {
            background: var(--danger-soft);
            border: 1px solid #fecaca;
            color: #b91c1c;
            font-size: 12px;
            padding: 8px 9px;
            border-radius: 9px;
            margin-bottom: 12px;
        }
        .status {
            background: #ecfdf3;
            border: 1px solid #bbf7d0;
            color: #166534;
            font-size: 12px;
            padding: 8px 9px;
            border-radius: 9px;
            margin-bottom: 12px;
        }
        .db-error {
            background: var(--danger-soft);
            border: 1px dashed #fecaca;
            color: #b91c1c;
            font-size: 12px;
            padding: 8px 9px;
            border-radius: 9px;
            margin-bottom: 10px;
        }
        .logout-form {
            margin-top: 12px;
            text-align: right;
        }
        .btn-secondary {
            border-radius: var(--radius-pill);
            border: 1px solid var(--border-strong);
            background: #f9fafb;
            color: var(--text-main);
            font-size: 13px;
            padding: 7px 14px;
            cursor: pointer;
        }
        .btn-secondary:hover {
            background: #f3f4f6;
        }
        @media (max-width: 840px) {
            .app-shell {
                grid-template-columns: minmax(0, 1fr);
            }
            .sidebar {
                display: none;
            }
            .main {
                padding-inline: 16px;
            }
            .content-grid {
                margin-top: 8vh;
            }
        }
    </style>
</head>
<body>
<?php if (!$isLoggedIn): ?>
    <div class="content-grid--login-only">
        <section class="panel">
            <div class="panel-header">
                <div class="panel-logo">
                    <img src="<?php echo htmlspecialchars($loginLogoPath, ENT_QUOTES, 'UTF-8'); ?>" alt="Shmat.OS">
                </div>
                <div class="panel-title">Вхід</div>
            </div>

            <?php if ($dbError): ?>
                <div class="db-error">
                    <?php echo htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <?php if ($authError): ?>
                <div class="error">
                    <?php echo htmlspecialchars($authError, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="login.php" autocomplete="off">
                <div class="field-grid">
                    <div>
                        <label for="login">Логін</label>
                        <input type="text" id="login" name="login" required>
                    </div>
                    <div>
                        <label for="password">Пароль</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                </div>
                <button type="submit" class="btn-primary">Увійти</button>
            </form>
        </section>
    </div>
<?php else: ?>
    <?php
    $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
    ?>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="sidebar-logo">
                <div class="logo-mark"></div>
                <div class="logo-text">
                    <span>Shmat Corporation</span>
                    <span>Internal portal</span>
                </div>
            </div>

            <div>
                <div class="sidebar-section-title">Навігація</div>
                <ul class="nav-list">
                    <?php
                    // Підключаємо функції перевірки доступу
                    require_once __DIR__ . '/../access/logic/index.php';
                    
                    // Dashboard - доступний тільки технічному директору
                    if (hasAccessToModule($mysqli, 'dashboard')):
                    ?>
                        <li class="nav-item<?php echo $page === 'dashboard' ? ' nav-item--active' : ''; ?>">
                            <a href="?page=dashboard" style="text-decoration:none;color:inherit;display:flex;justify-content:space-between;width:100%;">
                                <span>Dashboard</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php if (hasAccessToModule($mysqli, 'nomenclature')): ?>
                        <li class="nav-item<?php echo $page === 'nomenclature' ? ' nav-item--active' : ''; ?>">
                            <a href="?page=nomenclature" style="text-decoration:none;color:inherit;display:flex;justify-content:space-between;width:100%;">
                                <span>Номенклатура товару</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php if (hasAccessToModule($mysqli, 'goods_receipt')): ?>
                        <li class="nav-item<?php echo $page === 'goods_receipt' ? ' nav-item--active' : ''; ?>">
                            <a href="?page=goods_receipt" style="text-decoration:none;color:inherit;display:flex;justify-content:space-between;width:100%;">
                                <span>Прихід товару</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php
                    // Показуємо меню "Управління складами", якщо:
                    // 1. Користувач - технічний директор (має доступ до модуля)
                    // 2. АБО користувач має хоча б один склад (для перегляду своїх складів)
                    $showWarehousesMenu = false;
                    $warehouses = [];
                    
                    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'technical_director') {
                        // Технічний директор має доступ до модуля
                        $showWarehousesMenu = hasAccessToModule($mysqli, 'warehouses');
                        if ($showWarehousesMenu) {
                            require_once __DIR__ . '/../warehouses/logic/index.php';
                            $warehouses = getWarehouses($mysqli);
                        }
                    } else {
                        // Для звичайних користувачів перевіряємо, чи вони мають склади
                        $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
                        if ($userId > 0) {
                            $warehouses = getUserWarehouses($mysqli, $userId);
                            $showWarehousesMenu = !empty($warehouses);
                        }
                    }
                    
                    if ($showWarehousesMenu):
                    ?>
                        <li class="nav-item<?php echo $page === 'warehouses' ? ' nav-item--active' : ''; ?>">
                            <a href="<?php echo (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'technical_director') ? '?page=warehouses' : '#'; ?>" style="text-decoration:none;color:inherit;display:flex;justify-content:space-between;width:100%;">
                                <span>Управління складами</span>
                                <?php if (!empty($warehouses)): ?>
                                    <button type="button" class="nav-item-toggle" onclick="toggleWarehousesList(event)" style="background:none;border:none;cursor:pointer;padding:0;margin:0;color:inherit;font-size:12px;">▼</button>
                                <?php endif; ?>
                            </a>
                            <?php if (!empty($warehouses)): ?>
                                <ul class="nav-sublist" id="warehousesSublist">
                                    <?php foreach ($warehouses as $warehouse): ?>
                                        <li>
                                            <a href="?page=warehouse&id=<?php echo $warehouse['id']; ?>">
                                                <?php echo htmlspecialchars($warehouse['name'], ENT_QUOTES, 'UTF-8'); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </li>
                    <?php endif; ?>
                    
                    <?php if (hasAccessToModule($mysqli, 'access')): ?>
                        <li class="nav-item<?php echo $page === 'access' ? ' nav-item--active' : ''; ?>">
                            <a href="?page=access" style="text-decoration:none;color:inherit;display:flex;justify-content:space-between;width:100%;">
                                <span>Доступи</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php if (hasAccessToModule($mysqli, 'settings')): ?>
                        <li class="nav-item<?php echo $page === 'settings' ? ' nav-item--active' : ''; ?>">
                            <a href="?page=settings" style="text-decoration:none;color:inherit;display:flex;justify-content:space-between;width:100%;">
                                <span>Налаштування</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="sidebar-footer">
                <form method="post" action="logout.php">
                    <button type="submit" class="btn-logout">Вийти</button>
                </form>
            </div>
        </aside>

        <main class="main">
            <div class="main-header">
                <div>
                    <?php if ($page === 'settings'): ?>
                        <div class="main-title">Налаштування</div>
                        <div class="main-subtitle">Керування параметрами порталу Shmat OS.</div>
                    <?php elseif ($page === 'nomenclature'): ?>
                        <div class="main-title">Номенклатура товару</div>
                        <div class="main-subtitle">Модуль для роботи з переліком товарів.</div>
                    <?php elseif ($page === 'goods_receipt'): ?>
                        <div class="main-title">Прихід товару</div>
                        <div class="main-subtitle">Модуль для управління приходом товару на склад.</div>
                    <?php elseif ($page === 'warehouses'): ?>
                        <div class="main-title">Управління складами</div>
                        <div class="main-subtitle">Модуль для управління складами та залишками товарів.</div>
                    <?php elseif ($page === 'access'): ?>
                        <div class="main-title">Доступи</div>
                        <div class="main-subtitle">Модуль для управління доступами користувачів та ролями.</div>
                    <?php else: ?>
                        <div class="main-title">Панель технічного директора</div>
                        <div class="main-subtitle">Базова заглушка дашборду. Далі додамо модулі.</div>
                    <?php endif; ?>
                </div>
                <div class="main-actions">
                    <div class="chip">Бета-версія порталу</div>
                </div>
            </div>

            <?php
            // Підключаємо функції перевірки доступу (якщо ще не підключено)
            if (!function_exists('hasAccessToModule')) {
                require_once __DIR__ . '/../access/logic/index.php';
            }
            
            if ($page === 'settings'): 
                if (!hasAccessToModule($mysqli, 'settings')):
                    echo '<div style="padding: 40px; text-align: center; color: #dc2626;"><h2>Доступ заборонено</h2><p>У вас немає доступу до цього модуля.</p></div>';
                else:
            ?>
                <section class="panel panel--wide" style="margin-top:18px;">
                    <?php require __DIR__ . '/../settings/settings.php'; ?>
                </section>
            <?php 
                endif;
            elseif ($page === 'nomenclature'): 
                if (!hasAccessToModule($mysqli, 'nomenclature')):
                    echo '<div style="padding: 40px; text-align: center; color: #dc2626;"><h2>Доступ заборонено</h2><p>У вас немає доступу до цього модуля.</p></div>';
                else:
            ?>
                <section class="panel panel--wide" style="margin-top:18px;">
                    <?php 
                    try {
                        require __DIR__ . '/../nomenclature/nomenclature.php'; 
                    } catch (Exception $e) {
                        echo '<div style="padding: 20px; color: #dc2626;">Помилка завантаження модуля: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>';
                        error_log('Nomenclature module error: ' . $e->getMessage());
                    } catch (Error $e) {
                        echo '<div style="padding: 20px; color: #dc2626;">Помилка завантаження модуля: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>';
                        error_log('Nomenclature module fatal error: ' . $e->getMessage());
                    }
                    ?>
                </section>
            <?php 
                endif;
            elseif ($page === 'goods_receipt'): 
                if (!hasAccessToModule($mysqli, 'goods_receipt')):
                    echo '<div style="padding: 40px; text-align: center; color: #dc2626;"><h2>Доступ заборонено</h2><p>У вас немає доступу до цього модуля.</p></div>';
                else:
            ?>
                <section class="panel panel--wide" style="margin-top:18px;">
                    <?php require __DIR__ . '/../goods_receipt/goods_receipt.php'; ?>
                </section>
            <?php 
                endif;
            elseif ($page === 'warehouses'): 
                // Модуль "Управління складами" доступний тільки технічному директору
                // Звичайні користувачі не мають доступу до цього модуля (вони бачать тільки свої склади в меню)
                if (!hasAccessToModule($mysqli, 'warehouses')):
                    echo '<div style="padding: 40px; text-align: center; color: #dc2626;"><h2>Доступ заборонено</h2><p>У вас немає доступу до цього модуля.</p></div>';
                else:
            ?>
                <section class="panel panel--wide" style="margin-top:18px;">
                    <?php require __DIR__ . '/../warehouses/warehouses.php'; ?>
                </section>
            <?php 
                endif;
            elseif ($page === 'warehouse'): 
                $warehouseId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
                if ($warehouseId <= 0 || !hasAccessToWarehouse($mysqli, $warehouseId)):
                    echo '<div style="padding: 40px; text-align: center; color: #dc2626;"><h2>Доступ заборонено</h2><p>У вас немає доступу до цього складу.</p></div>';
                else:
            ?>
                <section class="panel panel--wide" style="margin-top:18px;">
                    <?php require __DIR__ . '/../warehouse/warehouse.php'; ?>
                </section>
            <?php 
                endif;
            elseif ($page === 'access'): 
                if (!hasAccessToModule($mysqli, 'access')):
                    echo '<div style="padding: 40px; text-align: center; color: #dc2626;"><h2>Доступ заборонено</h2><p>У вас немає доступу до цього модуля.</p></div>';
                else:
            ?>
                <section class="panel panel--wide" style="margin-top:18px;">
                    <?php require __DIR__ . '/../access/access.php'; ?>
                </section>
            <?php 
                endif;
            else: 
                if (!hasAccessToModule($mysqli, 'dashboard')):
                    echo '<div style="padding: 40px; text-align: center; color: #dc2626;"><h2>Доступ заборонено</h2><p>У вас немає доступу до цього модуля.</p></div>';
                else:
            ?>
                <div class="content-grid">
                    <section class="panel">
                        <div class="top-label">Панель</div>
                        <div class="panel-header">
                            <div class="panel-title">Статус доступу</div>
                        </div>

                        <div class="status">
                            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'technical_director'): ?>
                                Роль: <strong>Технічний директор</strong><br>
                            <?php else: ?>
                                Роль: <strong>Користувач</strong><br>
                            <?php endif; ?>
                            Логін: <strong><?php echo htmlspecialchars($_SESSION['user_login'] ?? '', ENT_QUOTES, 'UTF-8'); ?></strong><br>
                            <?php if (isset($_SESSION['user_full_name'])): ?>
                                ПІБ: <strong><?php echo htmlspecialchars($_SESSION['user_full_name'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                            <?php endif; ?>
                            Статус: залогінено в системі.
                        </div>

                        <div class="logout-form">
                            <form method="post" action="logout.php">
                                <button type="submit" class="btn-secondary">Вихід (розлогінитися)</button>
                            </form>
                        </div>
                    </section>
                </div>
            <?php 
                endif;
            endif; 
            ?>
        </main>
    </div>
<?php endif; ?>
    <script>
    function toggleWarehousesList(event) {
        event.preventDefault();
        event.stopPropagation();
        const sublist = document.getElementById('warehousesSublist');
        const button = event.target;
        if (sublist) {
            if (sublist.classList.contains('show')) {
                sublist.classList.remove('show');
                button.textContent = '▼';
                button.classList.remove('rotated');
            } else {
                sublist.classList.add('show');
                button.textContent = '▲';
                button.classList.add('rotated');
            }
        }
    }
    </script>
</body>
</html>


