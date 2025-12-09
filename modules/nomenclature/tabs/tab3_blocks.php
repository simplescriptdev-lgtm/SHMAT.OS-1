<?php
// Вкладка 3: збирає 6 блоків докупи
?>
<div class="nomenclature-content">
    <?php
    for ($i = 13; $i <= 18; $i++) {
        $blockFile = __DIR__ . '/../blocks/block' . $i . '.php';
        if (is_file($blockFile)) {
            require $blockFile;
        }
    }
    ?>
</div>



