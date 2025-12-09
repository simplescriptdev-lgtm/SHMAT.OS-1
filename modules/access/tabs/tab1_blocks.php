<?php
// Вкладка 1: збирає 6 блоків докупи
?>
<div class="access-content">
    <?php
    for ($i = 1; $i <= 6; $i++) {
        $blockFile = __DIR__ . '/../blocks/block' . $i . '.php';
        if (is_file($blockFile)) {
            require $blockFile;
        }
    }
    ?>
</div>

