<?php
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$qty        = isset($_POST['qty']) ? (int) $_POST['qty'] : 1;
if ($qty < 1) {
    $qty = 1;
}
if ($qty > 99) {
    $qty = 99;
}

$stmt = $pdo->prepare('SELECT id, name, stock FROM products WHERE id = ?');
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    flash_set('error', 'Sản phẩm không tồn tại.');
    redirect('index.php');
}

$stock = (int) $product['stock'];
if ($stock < 1) {
    flash_set('error', 'Sản phẩm "' . $product['name'] . '" đã hết hàng.');
    redirect('product.php?id=' . $product['id']);
}

$cart    = cart_get();
$current = isset($cart[$product_id]) ? (int) $cart[$product_id] : 0;
$new_qty = $current + $qty;

if ($new_qty > $stock) {
    $cart[$product_id] = $stock;
    $_SESSION['cart']  = $cart;
    flash_set('warning', 'Chỉ còn ' . $stock . ' sản phẩm "' . $product['name'] . '", giỏ hàng đã đặt ở mức tối đa.');
} else {
    $cart[$product_id] = $new_qty;
    $_SESSION['cart']  = $cart;
    flash_set('success', 'Đã thêm "' . $product['name'] . '" vào giỏ hàng.');
}

redirect('cart.php');
