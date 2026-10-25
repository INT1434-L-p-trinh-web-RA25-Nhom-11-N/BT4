<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$user  = current_user();
$items = cart_load_items($pdo);

if (count($items) === 0) {
    flash_set('error', 'Giỏ hàng đang trống.');
    redirect('cart.php');
}
$stmt = $pdo->prepare('SELECT phone, address FROM users WHERE id = ?');
$stmt->execute([$user['id']]);
$account = $stmt->fetch();

$ship_name    = $user['fullname'];
$ship_phone   = ($account && $account['phone'] !== null) ? $account['phone'] : '';
$ship_address = ($account && $account['address'] !== null) ? $account['address'] : '';
$errors       = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ship_name    = isset($_POST['ship_name']) ? trim($_POST['ship_name']) : '';
    $ship_phone   = isset($_POST['ship_phone']) ? trim($_POST['ship_phone']) : '';
    $ship_address = isset($_POST['ship_address']) ? trim($_POST['ship_address']) : '';

    if ($ship_name === '') {
        $errors['ship_name'] = 'Vui lòng nhập tên người nhận.';
    } elseif (mb_strlen($ship_name) > 100) {
        $errors['ship_name'] = 'Tên người nhận tối đa 100 ký tự.';
    }

    if (!preg_match('/^0[0-9]{9,10}$/', $ship_phone)) {
        $errors['ship_phone'] = 'Số điện thoại phải gồm 10 hoặc 11 chữ số và bắt đầu bằng 0.';
    }

    if (mb_strlen($ship_address) < 5) {
        $errors['ship_address'] = 'Vui lòng nhập địa chỉ giao hàng (ít nhất 5 ký tự).';
    } elseif (mb_strlen($ship_address) > 255) {
        $errors['ship_address'] = 'Địa chỉ tối đa 255 ký tự.';
    }

    if (count($errors) === 0) {
        $cart = cart_get();
        ksort($cart); 
        try {
            $pdo->beginTransaction();

            $lines = [];
            $total = 0;
            $lock  = $pdo->prepare('SELECT id, name, price, stock FROM products WHERE id = ? FOR UPDATE');
            foreach ($cart as $product_id => $qty) {
                $lock->execute([$product_id]);
                $p = $lock->fetch();
                if (!$p) {
                    throw new RuntimeException('Một sản phẩm trong giỏ không còn tồn tại.');
                }
                if ((int) $p['stock'] < $qty) {
                    throw new RuntimeException(
                        'Sản phẩm "' . $p['name'] . '" chỉ còn ' . (int) $p['stock'] . ' trong kho.'
                    );
                }
                $lines[] = ['id' => (int) $p['id'], 'name' => $p['name'], 'price' => $p['price'], 'qty' => (int) $qty];
                $total  += $p['price'] * $qty;
            }
            $stmt = $pdo->prepare(
                'INSERT INTO orders (user_id, total, status, ship_name, ship_phone, ship_address)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$user['id'], $total, 'pending', $ship_name, $ship_phone, $ship_address]);
            $order_id = (int) $pdo->lastInsertId();
            $insert_item = $pdo->prepare(
                'INSERT INTO order_items (order_id, product_id, product_name, price, quantity)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $dec_stock = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
            foreach ($lines as $line) {
                $insert_item->execute([$order_id, $line['id'], $line['name'], $line['price'], $line['qty']]);
                $dec_stock->execute([$line['qty'], $line['id'], $line['qty']]);
                if ($dec_stock->rowCount() !== 1) {
                    throw new RuntimeException('Sản phẩm "' . $line['name'] . '" vừa hết hàng.');
                }
            }

            $pdo->commit();

            unset($_SESSION['cart']);
            flash_set('success', 'Đặt hàng thành công! Mã đơn của bạn là #' . $order_id . '.');
            redirect('order-detail.php?id=' . $order_id);

        } catch (RuntimeException $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors['general'] = $ex->getMessage();
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Checkout error: ' . $ex->getMessage());
            $errors['general'] = 'Không đặt được hàng do lỗi hệ thống. Vui lòng thử lại sau.';
        }

        $items = cart_load_items($pdo);
    }
}

$total = cart_total($items);

$page_title = 'Đặt hàng';
require __DIR__ . '/includes/header.php';
?>
<h2>Xác nhận đặt hàng</h2>

<?php if (isset($errors['general'])): ?>
    <p class="error-message" role="alert"><?= e($errors['general']) ?></p>
<?php endif; ?>

<table class="cart-table">
    <thead>
        <tr><th>Sản phẩm</th><th>Đơn giá</th><th>Số lượng</th><th>Thành tiền</th></tr>
    </thead>
    <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?= e($item['name']) ?></td>
                <td><?= e(format_price($item['price'])) ?></td>
                <td><?= (int) $item['qty'] ?></td>
                <td><?= e(format_price($item['subtotal'])) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr><th colspan="3">Tổng tiền</th><th><?= e(format_price($total)) ?></th></tr>
    </tfoot>
</table>

<h3>Thông tin giao hàng</h3>
<form class="form" method="post" novalidate>
    <p>
        <label for="ship_name">Tên người nhận:</label><br>
        <input type="text" id="ship_name" name="ship_name" value="<?= e($ship_name) ?>" required>
        <?php field_error($errors, 'ship_name'); ?>
    </p>

    <p>
        <label for="ship_phone">Số điện thoại:</label><br>
        <input type="tel" id="ship_phone" name="ship_phone" value="<?= e($ship_phone) ?>" required>
        <?php field_error($errors, 'ship_phone'); ?>
    </p>

    <p>
        <label for="ship_address">Địa chỉ giao hàng:</label><br>
        <input type="text" id="ship_address" name="ship_address" value="<?= e($ship_address) ?>" required>
        <?php field_error($errors, 'ship_address'); ?>
    </p>

    <button type="submit"<?= count($items) === 0 ? ' disabled' : '' ?>>Xác nhận đặt hàng</button>
    <a href="<?= e(url('cart.php')) ?>">Quay lại giỏ hàng</a>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
