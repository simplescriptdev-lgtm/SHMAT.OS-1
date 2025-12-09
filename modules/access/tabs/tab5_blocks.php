<?php
// Вкладка 5: збирає 6 блоків докупи
?>
<div class="access-content">
    <?php
    for ($i = 25; $i <= 30; $i++) {
        $blockFile = __DIR__ . '/../blocks/block' . $i . '.php';
        if (is_file($blockFile)) {
            require $blockFile;
        }
    }
    ?>
</div>

