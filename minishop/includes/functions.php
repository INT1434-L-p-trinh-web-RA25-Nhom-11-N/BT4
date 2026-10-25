<?php

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function format_price($amount): string
{
    return number_format((float) $amount, 0, ',', '.') . 'đ';
}

function url(string $path = ''): string
{
    return BASE_URL . ltrim($path, '/');
}
ender_product_card(array $p)
{
    $stock = isset($p['stock']) ? (int) $p['stock'] : null;
    ?>
    <article>
        <h3><?= e($p['name']) ?></h3>
        <img src="<?= e(url('images/' . $p['image'])) ?>" alt="<?= e($p['name']) ?>" width="200">
        <p><strong><?= e(format_price($p['price'])) ?></strong></p>
        <a href="<?= e(url('product.php?id=' . (int) $p['id'])) ?>">Xem chi tiết</a>
        <?php if ($stock === 0): ?>
            <p class="out-of-stock">Hết hàng</p>
        <?php elseif ($stock !== null): ?>
            <form class="inline-form" method="post" action="<?= e(url('cart-add.php')) ?>">
                <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                <input type="hidden" name="qty" value="1">
                <button type="submit">Thêm vào giỏ</button>
            </form>
        <?php endif; ?>
    </article>
    <?php
}

function field_error(array $errors, string $key)
{
    if (isset($errors[$key])) {
        echo '<span class="error-message" role="alert">' . e($errors[$key]) . '</span>';
    }
}

function redirect(string $path)
{
    header('Location: ' . url($path));
    exit;
}

function flash_set(string $type, string $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get()
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function current_user()
{
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function require_login()
{
    if (is_logged_in()) {
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $uri = $_SERVER['REQUEST_URI'];
        if (strpos($uri, BASE_URL) === 0) {
            // Lưu đường dẫn tương đối, ví dụ: order-detail.php?id=3
            $_SESSION['redirect_after_login'] = substr($uri, strlen(BASE_URL));
        }
    }

    flash_set('error', 'Vui lòng đăng nhập để tiếp tục.');
    redirect('login.php');
}
function pop_redirect_after_login(): string
{
    $target = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : '';
    unset($_SESSION['redirect_after_login']);

    $valid_format = preg_match('/^[A-Za-z0-9_-]+\.php(\?[A-Za-z0-9_=&%.-]*)?$/', $target) === 1;
    $is_auth_page = preg_match('/^(login|register|logout)\.php/', $target) === 1;

    return ($valid_format && !$is_auth_page) ? $target : 'index.php';
}
function cart_get(): array
{
    return (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) ? $_SESSION['cart'] : [];
}

function cart_count(): int
{
    return (int) array_sum(cart_get());
}

function cart_load_items(PDO $pdo): array
{
    $cart = cart_get();
    if (count($cart) === 0) {
        return [];
    }

    $ids   = array_keys($cart);
    $marks = implode(',', array_fill(0, count($ids), '?'));
    $stmt  = $pdo->prepare(
        "SELECT id, name, price, image, stock FROM products WHERE id IN ($marks) ORDER BY id"
    );
    $stmt->execute($ids);

    $items = [];
    foreach ($stmt->fetchAll() as $row) {
        $row['qty']      = (int) $cart[$row['id']];
        $row['subtotal'] = $row['price'] * $row['qty'];
        $items[]         = $row;
    }
    return $items;
}

function cart_total(array $items)
{
    $total = 0;
    foreach ($items as $item) {
        $total += $item['subtotal'];
    }
    return $total;
}

function order_status_label(string $status): string
{
    $labels = [
        'pending'    => 'Chờ xử lý',
        'processing' => 'Đang xử lý',
        'completed'  => 'Hoàn tất',
        'cancelled'  => 'Đã hủy',
    ];
    return isset($labels[$status]) ? $labels[$status] : $status;
}
