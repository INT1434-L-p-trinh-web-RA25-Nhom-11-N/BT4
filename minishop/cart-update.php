<?php
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('cart.php');
}

$cart = cart_get();

if (isset($_POST['clear'])) {
    unset($_SESSION['cart']);
    flash_set('success', 'Đã xóa toàn bộ giỏ hàng.');

} elseif (isset($_POST['remove'])) {
    $id = (int) $_POST['remove'];
    unset($cart[$id]);
    $_SESSION['cart'] = $cart;
    flash_set('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');

} elseif (count($cart) > 0) {
    $qtys = (isset($_POST['qty']) && is_array($_POST['qty'])) ? $_POST['qty'] : [];

    $ids   = array_keys($cart);
    $marks = implode(',', array_fill(0, count($ids), '?'));
    $stmt  = $pdo->prepare("SELECT id, stock FROM products WHERE id IN ($marks)");
    $stmt->execute($ids);
    $stocks = [];
    foreach ($stmt->fetchAll() as $row) {
        $stocks[(int) $row['id']] = (int) $row['stock'];
    }

    $capped = false;
    foreach ($cart as $id => $old_qty) {
        if (!isset($stocks[$id])) {
            unset($cart[$id]); 
            continue;
        }
        if (!isset($qtys[$id])) {
            continue;
        }
        $q = (int) $qtys[$id];
        if ($q <= 0) {
            unset($cart[$id]);
        } else {
            if ($q > $stocks[$id]) {
                $q      = $stocks[$id];
                $capped = true;
            }
            if ($q > 99) {
                $q = 99;
            }
            if ($q <= 0) {
                unset($cart[$id]);
            } else {
                $cart[$id] = $q;
            }
        }
    }
    $_SESSION['cart'] = $cart;

    if ($capped) {
        flash_set('warning', 'Một số sản phẩm vượt quá tồn kho nên đã được điều chỉnh.');
    } else {
        flash_set('success', 'Đã cập nhật giỏ hàng.');
    }
}

redirect('cart.php');
