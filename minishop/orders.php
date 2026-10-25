<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$user = current_user();

$stmt = $pdo->prepare(
    'SELECT id, total, status, created_at FROM orders WHERE user_id = ? ORDER BY id DESC'
);
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();

$page_title = 'Đơn hàng của tôi';
require __DIR__ . '/includes/header.php';
?>
<h2>Đơn hàng của tôi</h2>

<?php if (count($orders) === 0): ?>
    <p>Bạn chưa có đơn hàng nào.</p>
    <p><a href="<?= e(url('index.php')) ?>">Bắt đầu mua sắm</a></p>
<?php else: ?>
    <table>
        <thead>
            <tr><th>Mã đơn</th><th>Ngày đặt</th><th>Tổng tiền</th><th>Trạng thái</th><th></th></tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td>#<?= (int) $o['id'] ?></td>
                    <td><?= e($o['created_at']) ?></td>
                    <td><?= e(format_price($o['total'])) ?></td>
                    <td><span class="status status-<?= e($o['status']) ?>"><?= e(order_status_label($o['status'])) ?></span></td>
                    <td><a href="<?= e(url('order-detail.php?id=' . (int) $o['id'])) ?>">Xem chi tiết</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
