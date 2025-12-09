<?php
// Блок 2 модального вікна - Пошук товарів
?>
<div class="goods-receipt-modal-block">
    <div class="goods-receipt-modal-block-header">
        <h4>Пошук товарів</h4>
    </div>
    <div class="goods-receipt-modal-block-content">
        <div class="goods-receipt-search-wrapper">
            <input type="text" id="productSearch" class="goods-receipt-search-input" 
                   placeholder="Введіть назву, артикул або бренд товару..." 
                   oninput="searchProducts(this.value)" 
                   autocomplete="off">
            <div id="productSearchResults" class="goods-receipt-search-results"></div>
        </div>
    </div>
</div>
