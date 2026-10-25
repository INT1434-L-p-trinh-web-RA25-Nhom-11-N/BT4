<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$user = current_user();
$id   = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $user['id']]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    $page_title = 'Không tìm thấy đơn hàng';
    require __DIR__ . '/includes/header.php';
    ?>
    <h2>Không tìm thấy đơn hàng</h2>
    <p>Đơn hàng không tồn tại hoặc không thuộc tài khoản của bạn.</p>
    <p><a href="<?= e(url('orders.php')) ?>">Quay lại danh sách đơn</a></p>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('SELECT status FROM orders WHERE id = ? AND user_id = ? FOR UPDATE');
        $stmt->execute([$order['id'], $user['id']]);
        $current = $stmt->fetch();

        if (!$current || $current['status'] !== 'pending') {
            $pdo->rollBack();
            flash_set('error', 'Chỉ hủy được đơn đang ở trạng thái "Chờ xử lý".');
            redirect('order-detail.php?id=' . $order['id']);
        }

        $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ?");
        $stmt->execute([$order['id'], $user['id']]);

        $stmt = $pdo->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = ?');
        $stmt->execute([$order['id']]);
        $restock = $pdo->prepare('UPDATE products SET stock = stock + ? WHERE id = ?');
        foreach ($stmt->fetchAll() as $line) {
            $restock->execute([$line['quantity'], $line['product_id']]);
        }

        $pdo->commit();
        flash_set('success', 'Đã hủy đơn #' . $order['id'] . ' và hoàn lại tồn kho.');
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Cancel order error: ' . $ex->getMessage());
        flash_set('error', 'Không hủy được đơn hàng do lỗi hệ thống. Vui lòng thử lại.');
    }
    redirect('order-detail.php?id=' . $order['id']);
}

$stmt = $pdo->prepare(
    'SELECT product_id, product_name, price, quantity FROM order_items WHERE order_id = ? ORDER BY id'
);
$stmt->execute([$order['id']]);
$lines = $stmt->fetchAll();

$page_title = 'Đơn hàng #' . $order['id'];
require __DIR__ . '/includes/header.php';
?>
<h2>Đơn hàng #<?= (int) $order['id'] ?></h2>

<p>
    Ngày đặt: <?= e($order['created_at']) ?><br>
    Trạng thái: <span class="status status-<?= e($order['status']) ?>"><?= e(order_status_label($order['status'])) ?></span>
</p>

<h3>Thông tin giao hàng</h3>
<p>
    Người nhận: <?= e($order['ship_name']) ?><br>
    Điện thoại: <?= e($order['ship_phone']) ?><br>
    Địa chỉ: <?= e($order['ship_address']) ?>
</p>

<h3>Sản phẩm</h3>
<table>
    <thead>
        <tr><th>Sản phẩm</th><th>Đơn giá</th><th>Số lượng</th><th>Thành tiền</th></tr>
    </thead>
    <tbody>
        <?php foreach ($lines as $line): ?>
            <tr>
                <td><?= e($line['product_name']) ?></td>
                <td><?= e(format_price($line['price'])) ?></td>
                <td><?= (int) $line['quantity'] ?></td>
                <td><?= e(format_price($line['price'] * $line['quantity'])) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr><th colspan="3">Tổng tiền</th><th><?= e(format_price($order['total'])) ?></th></tr>
    </tfoot>
</table>

<?php if ($order['status'] === 'pending'): ?>
    <form method="post" class="cancel-form">
        <input type="hidden" name="action" value="cancel">
        <button type="submit" class="danger" data-confirm="Bạn chắc chắn muốn hủy đơn này?">Hủy đơn</button>
    </form>
<?php endif; ?>

<p><a href="<?= e(url('orders.php')) ?>">Quay lại danh sách đơn</a></p>
<?php require __DIR__ . '/includes/footer.php'; ?>
