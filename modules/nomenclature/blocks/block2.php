<?php
// Блок 2 модуля Номенклатура товару - Таблиця товарів
require_once __DIR__ . '/../logic/index.php';

$products = getProducts($mysqli);
$categories = getCategories($mysqli);
?>
<div class="nomenclature-block">
    <div style="padding: 16px;">
        <h4 style="margin: 0 0 12px; font-size: 14px; font-weight: 600;">Список товарів</h4>
        <?php if (empty($products)): ?>
            <p style="color: #6b7280; font-size: 13px;">Товари ще не додані.</p>
        <?php else: ?>
            <div class="nomenclature-table-container">
                <table class="nomenclature-table">
                    <thead>
                        <tr>
                            <th>Назва товару</th>
                            <th>Артикул</th>
                            <th>Бренд</th>
                            <th>Дії</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($product['article'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($product['brand'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <button type="button" class="nomenclature-btn-details" onclick="showProductDetails(<?php echo $product['id']; ?>)">
                                        Деталі
                                    </button>
                                    <button type="button" class="nomenclature-btn-edit" onclick="editProduct(<?php echo $product['id']; ?>)">
                                        Редагувати
                                    </button>
                                    <button type="button" class="nomenclature-btn-delete" onclick="deleteProduct(<?php echo $product['id']; ?>)">
                                        Видалити
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Модальне вікно для деталей товару -->
<div id="productDetailsModal" class="nomenclature-modal nomenclature-modal--large">
    <div class="nomenclature-modal-content nomenclature-modal-content--large">
        <div class="nomenclature-modal-header">
            <h3>Деталі товару</h3>
            <span class="nomenclature-modal-close" onclick="closeProductDetailsModal()">&times;</span>
        </div>
        <div class="nomenclature-modal-body" id="productDetailsContent">
            <div style="text-align: center; padding: 20px;">
                <p>Завантаження...</p>
            </div>
        </div>
    </div>
</div>

<!-- Модальне вікно для редагування товару -->
<div id="editProductModal" class="nomenclature-modal">
    <div class="nomenclature-modal-content">
        <div class="nomenclature-modal-header">
            <h3>Редагувати товар</h3>
            <span class="nomenclature-modal-close" onclick="closeEditProductModal()">&times;</span>
        </div>
        <div class="nomenclature-modal-body">
            <form id="editProductForm" onsubmit="handleUpdateProduct(event)" enctype="multipart/form-data">
                <input type="hidden" id="editProductId" name="id">
                
                <div class="nomenclature-form-field">
                    <label for="editProductName">Назва товару *</label>
                    <input type="text" id="editProductName" name="name" required placeholder="Введіть назву товару">
                </div>

                <div class="nomenclature-form-field">
                    <label for="editProductArticle">Артикул товару *</label>
                    <input type="text" id="editProductArticle" name="article" required placeholder="Введіть артикул товару">
                </div>

                <div class="nomenclature-form-field">
                    <label for="editProductBrand">Бренд товару *</label>
                    <input type="text" id="editProductBrand" name="brand" required placeholder="Введіть бренд товару">
                </div>

                <div class="nomenclature-form-field">
                    <label for="editProductCategory">Категорія *</label>
                    <select id="editProductCategory" name="category_id" required onchange="loadSubcategoriesForEdit(this.value)">
                        <option value="">Виберіть категорію</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="nomenclature-form-field">
                    <label for="editProductSubcategory">Підкатегорія</label>
                    <select id="editProductSubcategory" name="subcategory_id">
                        <option value="">Виберіть підкатегорію (необов'язково)</option>
                    </select>
                </div>

                <div class="nomenclature-form-field">
                    <label for="editProductImages">Додати нові фотографії (до 3 шт.)</label>
                    <input type="file" id="editProductImages" name="images[]" accept="image/png,image/jpeg,image/webp" multiple>
                    <small style="color: #6b7280; font-size: 11px; display: block; margin-top: 4px;">Можна завантажити до 3 нових фотографій</small>
                </div>

                <div class="nomenclature-modal-actions">
                    <button type="button" class="nomenclature-btn-secondary" onclick="closeEditProductModal()">Скасувати</button>
                    <button type="submit" class="nomenclature-btn-primary">Зберегти зміни</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentProductData = null;
let currentImageIndex = 0;

window.showProductDetails = function(productId) {
    const modal = document.getElementById('productDetailsModal');
    const content = document.getElementById('productDetailsContent');
    
    content.innerHTML = '<div style="text-align: center; padding: 20px;"><p>Завантаження...</p></div>';
    modal.style.display = 'flex';
    
    fetch(`/modules/nomenclature/logic/handle_product_action.php?action=get&id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.product) {
                currentProductData = data.product;
                currentImageIndex = 0;
                renderProductDetails(data.product);
            } else {
                content.innerHTML = '<div style="text-align: center; padding: 20px;"><p>Помилка завантаження даних товару</p></div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = '<div style="text-align: center; padding: 20px;"><p>Помилка при завантаженні даних</p></div>';
        });
};

function renderProductDetails(product) {
    const content = document.getElementById('productDetailsContent');
    const images = product.images || [];
    
    let imagesHtml = '';
    if (images.length > 0) {
        imagesHtml = `
            <div class="product-images-container">
                <div class="product-image-wrapper">
                    <button class="image-nav-btn image-nav-btn--prev" onclick="changeImage(-1)" ${images.length <= 1 ? 'style="display:none;"' : ''}>&#8249;</button>
                    <img id="productDetailImage" src="${images[0].file_path}" alt="Фотографія товару" class="product-detail-image">
                    <button class="image-nav-btn image-nav-btn--next" onclick="changeImage(1)" ${images.length <= 1 ? 'style="display:none;"' : ''}>&#8250;</button>
                </div>
                ${images.length > 1 ? `<div class="image-counter">${currentImageIndex + 1} / ${images.length}</div>` : ''}
            </div>
        `;
    } else {
        imagesHtml = '<div class="product-images-container"><p style="text-align: center; color: #6b7280;">Фотографії відсутні</p></div>';
    }
    
    content.innerHTML = `
        <div class="product-details-grid">
            <div class="product-details-section">
                <h4>Основна інформація</h4>
                <div class="product-detail-item">
                    <strong>Назва товару:</strong>
                    <span>${escapeHtml(product.name)}</span>
                </div>
                <div class="product-detail-item">
                    <strong>Артикул:</strong>
                    <span>${escapeHtml(product.article)}</span>
                </div>
                <div class="product-detail-item">
                    <strong>Бренд:</strong>
                    <span>${escapeHtml(product.brand)}</span>
                </div>
                <div class="product-detail-item">
                    <strong>Категорія:</strong>
                    <span>${escapeHtml(product.category_name || 'Не вказано')}</span>
                </div>
                <div class="product-detail-item">
                    <strong>Підкатегорія:</strong>
                    <span>${escapeHtml(product.subcategory_name || 'Не вказано')}</span>
                </div>
                <div class="product-detail-item">
                    <strong>Створено:</strong>
                    <span>${new Date(product.created_at).toLocaleString('uk-UA')}</span>
                </div>
                ${product.updated_at !== product.created_at ? `
                <div class="product-detail-item">
                    <strong>Оновлено:</strong>
                    <span>${new Date(product.updated_at).toLocaleString('uk-UA')}</span>
                </div>
                ` : ''}
            </div>
            <div class="product-details-section">
                ${imagesHtml}
            </div>
        </div>
    `;
}

window.changeImage = function(direction) {
    if (!currentProductData || !currentProductData.images || currentProductData.images.length === 0) return;
    
    currentImageIndex += direction;
    if (currentImageIndex < 0) {
        currentImageIndex = currentProductData.images.length - 1;
    } else if (currentImageIndex >= currentProductData.images.length) {
        currentImageIndex = 0;
    }
    
    const img = document.getElementById('productDetailImage');
    const counter = document.querySelector('.image-counter');
    
    if (img) {
        img.src = currentProductData.images[currentImageIndex].file_path;
    }
    if (counter) {
        counter.textContent = `${currentImageIndex + 1} / ${currentProductData.images.length}`;
    }
};

window.closeProductDetailsModal = function() {
    document.getElementById('productDetailsModal').style.display = 'none';
    currentProductData = null;
    currentImageIndex = 0;
};

window.editProduct = function(productId) {
    const modal = document.getElementById('editProductModal');
    modal.style.display = 'flex';
    
    fetch(`/modules/nomenclature/logic/handle_product_action.php?action=get&id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.product) {
                document.getElementById('editProductId').value = data.product.id;
                document.getElementById('editProductName').value = data.product.name;
                document.getElementById('editProductArticle').value = data.product.article;
                document.getElementById('editProductBrand').value = data.product.brand;
                document.getElementById('editProductCategory').value = data.product.category_id;
                
                // Завантажуємо підкатегорії
                loadSubcategoriesForEdit(data.product.category_id, data.product.subcategory_id);
            } else {
                alert('Помилка завантаження даних товару');
                closeEditProductModal();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Помилка при завантаженні даних');
            closeEditProductModal();
        });
};

window.loadSubcategoriesForEdit = function(categoryId, selectedSubcategoryId = null) {
    const subcategorySelect = document.getElementById('editProductSubcategory');
    if (!subcategorySelect) return;
    
    subcategorySelect.innerHTML = '<option value="">Завантаження...</option>';
    
    if (!categoryId) {
        subcategorySelect.innerHTML = '<option value="">Виберіть підкатегорію (необов\'язково)</option>';
        return;
    }

    fetch(`/modules/nomenclature/logic/get_subcategories.php?category_id=${categoryId}`)
        .then(response => response.json())
        .then(data => {
            subcategorySelect.innerHTML = '<option value="">Виберіть підкатегорію (необов\'язково)</option>';
            if (data && Array.isArray(data) && data.length > 0) {
                data.forEach(subcategory => {
                    const option = document.createElement('option');
                    option.value = subcategory.id;
                    option.textContent = subcategory.name;
                    if (selectedSubcategoryId && subcategory.id == selectedSubcategoryId) {
                        option.selected = true;
                    }
                    subcategorySelect.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            subcategorySelect.innerHTML = '<option value="">Помилка завантаження</option>';
        });
};

window.closeEditProductModal = function() {
    document.getElementById('editProductModal').style.display = 'none';
    document.getElementById('editProductForm').reset();
    document.getElementById('editProductSubcategory').innerHTML = '<option value="">Виберіть підкатегорію (необов\'язково)</option>';
};

window.handleUpdateProduct = function(event) {
    event.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('id', document.getElementById('editProductId').value);
    formData.append('name', document.getElementById('editProductName').value.trim());
    formData.append('article', document.getElementById('editProductArticle').value.trim());
    formData.append('brand', document.getElementById('editProductBrand').value.trim());
    formData.append('category_id', document.getElementById('editProductCategory').value);
    
    const subcategoryId = document.getElementById('editProductSubcategory').value;
    if (subcategoryId) {
        formData.append('subcategory_id', subcategoryId);
    }

    const imagesInput = document.getElementById('editProductImages');
    if (imagesInput && imagesInput.files.length > 0) {
        for (let i = 0; i < Math.min(imagesInput.files.length, 3); i++) {
            formData.append('images[]', imagesInput.files[i]);
        }
    }

    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Збереження...';

    fetch('/modules/nomenclature/logic/handle_product_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeEditProductModal();
            location.reload();
        } else {
            alert(data.message || 'Помилка при оновленні товару');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Помилка при відправці запиту');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
};

window.deleteProduct = function(productId) {
    if (!confirm('Ви впевнені, що хочете видалити цей товар?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', productId);

    fetch('/modules/nomenclature/logic/handle_product_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Помилка при видаленні товару');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Помилка при відправці запиту');
    });
};

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Закрити модальні вікна при кліку поза ними
document.addEventListener('click', function(event) {
    const detailsModal = document.getElementById('productDetailsModal');
    const editModal = document.getElementById('editProductModal');
    
    if (detailsModal && event.target === detailsModal) {
        closeProductDetailsModal();
    }
    if (editModal && event.target === editModal) {
        closeEditProductModal();
    }
});

// Навігація по фотографіях клавіатурою
document.addEventListener('keydown', function(event) {
    const detailsModal = document.getElementById('productDetailsModal');
    if (detailsModal && detailsModal.style.display === 'flex') {
        if (event.key === 'ArrowLeft') {
            changeImage(-1);
        } else if (event.key === 'ArrowRight') {
            changeImage(1);
        } else if (event.key === 'Escape') {
            closeProductDetailsModal();
        }
    }
});
</script>
