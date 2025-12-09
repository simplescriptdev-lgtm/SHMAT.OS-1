<?php
// Вкладка 4: збирає 6 блоків докупи
?>
<div class="access-content">
    <?php
    for ($i = 19; $i <= 24; $i++) {
        $blockFile = __DIR__ . '/../blocks/block' . $i . '.php';
        if (is_file($blockFile)) {
            require $blockFile;
        }
    }
    ?>
</div>

