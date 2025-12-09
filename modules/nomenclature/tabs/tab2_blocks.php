<?php
// Вкладка 2: збирає 6 блоків докупи
?>
<div class="nomenclature-content">
    <?php
    for ($i = 7; $i <= 12; $i++) {
        $blockFile = __DIR__ . '/../blocks/block' . $i . '.php';
        if (is_file($blockFile)) {
            require $blockFile;
        }
    }
    ?>
</div>



