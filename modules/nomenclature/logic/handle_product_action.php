<?php
// Обробник дій з товарами (створення, редагування, видалення, отримання)

require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/index.php';

header('Content-Type: application/json');

if ($dbError !== null) {
    echo json_encode(['success' => false, 'message' => 'Помилка бази даних.']);
    exit;
}

// Перевіряємо action з POST або GET
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

if ($action === 'create') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $article = isset($_POST['article']) ? trim($_POST['article']) : '';
    $brand = isset($_POST['brand']) ? trim($_POST['brand']) : '';
    $categoryId = isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0;
    $subcategoryId = isset($_POST['subcategory_id']) && !empty($_POST['subcategory_id']) ? (int) $_POST['subcategory_id'] : null;

    $result = createProduct($mysqli, $name, $article, $brand, $categoryId, $subcategoryId);
    
    if ($result['success'] && isset($result['id'])) {
        // Завантажуємо фотографії, якщо вони є
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            uploadProductImages($mysqli, $result['id'], $_FILES['images']);
        }
    }
    
    echo json_encode($result);
} elseif ($action === 'get') {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($id > 0) {
        $product = getProduct($mysqli, $id);
        if ($product) {
            echo json_encode(['success' => true, 'product' => $product]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Товар не знайдено.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Невірний ID товару.']);
    }
} elseif ($action === 'update') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $article = isset($_POST['article']) ? trim($_POST['article']) : '';
    $brand = isset($_POST['brand']) ? trim($_POST['brand']) : '';
    $categoryId = isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0;
    $subcategoryId = isset($_POST['subcategory_id']) && !empty($_POST['subcategory_id']) ? (int) $_POST['subcategory_id'] : null;

    if ($id > 0) {
        $result = updateProduct($mysqli, $id, $name, $article, $brand, $categoryId, $subcategoryId);
        
        if ($result['success']) {
            // Завантажуємо нові фотографії, якщо вони є
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                uploadProductImages($mysqli, $id, $_FILES['images']);
            }
        }
        
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Невірний ID товару.']);
    }
} elseif ($action === 'delete') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    if ($id > 0) {
        $result = deleteProduct($mysqli, $id);
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Невірний ID товару.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Невідома дія.']);
}
