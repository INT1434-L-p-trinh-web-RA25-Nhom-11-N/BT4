<?php
$page_title = isset($page_title) ? $page_title : 'Trang chủ';
$user       = current_user();

$current_page = basename($_SERVER['SCRIPT_NAME']);
$same_menu    = [
    'product.php'      => 'index.php',  
    'checkout.php'     => 'cart.php',    
    'order-detail.php' => 'orders.php',  
];
if (isset($same_menu[$current_page])) {
    $current_page = $same_menu[$current_page];
}

$nav_items = [
    'index.php' => 'Trang chủ',
    'about.php' => 'Giới thiệu',
];
if ($user !== null) {
    $nav_items['orders.php']  = 'Đơn hàng';
    $nav_items['profile.php'] = 'Tài khoản';
} else {
    $nav_items['register.php'] = 'Đăng ký';
    $nav_items['login.php']    = 'Đăng nhập';
}
$nav_items['cart.php']    = 'Giỏ hàng';
$nav_items['contact.php'] = 'Liên hệ';

$flash = flash_get(); 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiniShop - <?= e($page_title) ?></title>
    <link rel="stylesheet" href="<?= e(url('css/style.css')) ?>">
</head>
<body>
    <header>
        <h1>MiniShop</h1>
        <p><em>Cửa hàng công nghệ dành cho sinh viên</em></p>
        <nav class="main-nav">
            <?php foreach ($nav_items as $file => $label): ?>
                <a href="<?= e(url($file)) ?>"<?= $file === $current_page ? ' class="active"' : '' ?>>
                    <?= e($label) ?>
                    <?php if ($file === 'cart.php'): ?>(<span id="cart-count"><?= cart_count() ?></span>)<?php endif; ?>
                </a>
            <?php endforeach; ?>
            <?php if ($user !== null): ?>
                <form method="post" action="<?= e(url('logout.php')) ?>">
                    <button type="submit" class="link-button">Đăng xuất (<?= e($user['fullname']) ?>)</button>
                </form>
            <?php endif; ?>
        </nav>
    </header>

    <hr>

    <main>
    <?php if ($flash !== null): ?>
        <div class="flash flash-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
    <?php endif; ?>
