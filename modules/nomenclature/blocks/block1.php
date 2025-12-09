<?php
// Блок 1 модуля Номенклатура товару - Кнопка "Додати товар"
require_once __DIR__ . '/../logic/index.php';

$categories = getCategories($mysqli);
?>
<div class="nomenclature-block">
    <div style="padding: 16px;">
        <button type="button" class="nomenclature-btn-primary" onclick="openCreateProductModal()">
            Додати товар
        </button>
    </div>
</div>

<!-- Модальне вікно для створення товару -->
<div id="createProductModal" class="nomenclature-modal">
    <div class="nomenclature-modal-content">
        <div class="nomenclature-modal-header">
            <h3>Додати товар</h3>
            <span class="nomenclature-modal-close" onclick="closeCreateProductModal()">&times;</span>
        </div>
        <div class="nomenclature-modal-body">
            <form id="createProductForm" onsubmit="handleCreateProduct(event)" enctype="multipart/form-data">
                <div class="nomenclature-form-field">
                    <label for="productName">Назва товару *</label>
                    <input type="text" id="productName" name="name" required placeholder="Введіть назву товару">
                </div>

                <div class="nomenclature-form-field">
                    <label for="productArticle">Артикул товару *</label>
                    <input type="text" id="productArticle" name="article" required placeholder="Введіть артикул товару">
                </div>

                <div class="nomenclature-form-field">
                    <label for="productBrand">Бренд товару *</label>
                    <input type="text" id="productBrand" name="brand" required placeholder="Введіть бренд товару">
                </div>

                <div class="nomenclature-form-field">
                    <label for="productCategory">Категорія *</label>
                    <select id="productCategory" name="category_id" required onchange="loadSubcategories(this.value)">
                        <option value="">Виберіть категорію</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="nomenclature-form-field">
                    <label for="productSubcategory">Підкатегорія</label>
                    <select id="productSubcategory" name="subcategory_id">
                        <option value="">Виберіть підкатегорію (необов'язково)</option>
                    </select>
                </div>

                <div class="nomenclature-form-field">
                    <label for="productImages">Фотографії товару (до 3 шт.)</label>
                    <input type="file" id="productImages" name="images[]" accept="image/png,image/jpeg,image/webp" multiple>
                    <small style="color: #6b7280; font-size: 11px; display: block; margin-top: 4px;">Можна завантажити до 3 фотографій</small>
                </div>

                <div class="nomenclature-modal-actions">
                    <button type="button" class="nomenclature-btn-secondary" onclick="closeCreateProductModal()">Закрити</button>
                    <button type="submit" class="nomenclature-btn-primary">Зберегти</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Переконаємося, що функції доступні глобально
window.openCreateProductModal = function() {
    const modal = document.getElementById('createProductModal');
    if (modal) {
        modal.style.display = 'flex';
        const nameInput = document.getElementById('productName');
        if (nameInput) {
            setTimeout(() => nameInput.focus(), 100);
        }
    } else {
        console.error('Модальне вікно не знайдено');
        alert('Помилка: модальне вікно не знайдено');
    }
};

window.closeCreateProductModal = function() {
    const modal = document.getElementById('createProductModal');
    if (modal) {
        modal.style.display = 'none';
    }
    const form = document.getElementById('createProductForm');
    if (form) {
        form.reset();
    }
    const subcategorySelect = document.getElementById('productSubcategory');
    if (subcategorySelect) {
        subcategorySelect.innerHTML = '<option value="">Виберіть підкатегорію (необов\'язково)</option>';
    }
};

window.loadSubcategories = function(categoryId) {
    const subcategorySelect = document.getElementById('productSubcategory');
    if (!subcategorySelect) return;
    
    subcategorySelect.innerHTML = '<option value="">Завантаження...</option>';
    
    if (!categoryId) {
        subcategorySelect.innerHTML = '<option value="">Виберіть підкатегорію (необов\'язково)</option>';
        return;
    }

    fetch(`/modules/nomenclature/logic/get_subcategories.php?category_id=${categoryId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Помилка завантаження підкатегорій');
            }
            return response.json();
        })
        .then(data => {
            subcategorySelect.innerHTML = '<option value="">Виберіть підкатегорію (необов\'язково)</option>';
            if (data && Array.isArray(data) && data.length > 0) {
                data.forEach(subcategory => {
                    const option = document.createElement('option');
                    option.value = subcategory.id;
                    option.textContent = subcategory.name;
                    subcategorySelect.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            subcategorySelect.innerHTML = '<option value="">Помилка завантаження</option>';
        });
};

window.handleCreateProduct = function(event) {
    event.preventDefault();
    
    // Валідація форми
    const name = document.getElementById('productName').value.trim();
    const article = document.getElementById('productArticle').value.trim();
    const brand = document.getElementById('productBrand').value.trim();
    const categoryId = document.getElementById('productCategory').value;

    if (!name || !article || !brand || !categoryId) {
        alert('Будь ласка, заповніть всі обов\'язкові поля');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('name', name);
    formData.append('article', article);
    formData.append('brand', brand);
    formData.append('category_id', categoryId);
    
    const subcategoryId = document.getElementById('productSubcategory').value;
    if (subcategoryId) {
        formData.append('subcategory_id', subcategoryId);
    }

    const imagesInput = document.getElementById('productImages');
    if (imagesInput && imagesInput.files.length > 0) {
        for (let i = 0; i < Math.min(imagesInput.files.length, 3); i++) {
            formData.append('images[]', imagesInput.files[i]);
        }
    }

    // Показуємо індикатор завантаження
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Збереження...';

    fetch('/modules/nomenclature/logic/handle_product_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Помилка сервера: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            closeCreateProductModal();
            location.reload();
        } else {
            alert(data.message || 'Помилка при створенні товару');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Помилка при відправці запиту: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
}

// Закрити модальне вікно при кліку поза ним
document.addEventListener('click', function(event) {
    const modal = document.getElementById('createProductModal');
    if (modal && event.target === modal) {
        window.closeCreateProductModal();
    }
});

// Додаткова перевірка при завантаженні сторінки
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.querySelector('.nomenclature-btn-primary[onclick*="openCreateProductModal"]');
    const modal = document.getElementById('createProductModal');
    
    if (btn && !modal) {
        console.error('Кнопка знайдена, але модальне вікно відсутнє');
    } else if (!btn) {
        console.error('Кнопка "Додати товар" не знайдена');
    } else {
        console.log('Модальне вікно та кнопка успішно ініціалізовані');
    }
});
</script>
