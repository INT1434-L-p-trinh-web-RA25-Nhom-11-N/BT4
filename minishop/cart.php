<?php
require_once __DIR__ . '/includes/init.php';

$items = cart_load_items($pdo);
$total = cart_total($items);

$page_title = 'Giỏ hàng';
require __DIR__ . '/includes/header.php';
?>
<h2>Giỏ hàng của bạn</h2>

<?php if (count($items) === 0): ?>
    <p>Giỏ hàng đang trống.</p>
    <p><a href="<?= e(url('index.php')) ?>">Tiếp tục mua sắm</a></p>
<?php else: ?>
    <form method="post" action="<?= e(url('cart-update.php')) ?>">
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Đơn giá</th>
                    <th>Số lượng</th>
                    <th>Thành tiền</th>
                    <th><span class="visually-hidden">Xóa</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><a href="<?= e(url('product.php?id=' . (int) $item['id'])) ?>"><?= e($item['name']) ?></a></td>
                        <td><?= e(format_price($item['price'])) ?></td>
                        <td>
                            <input type="number" name="qty[<?= (int) $item['id'] ?>]"
                                   min="0" max="<?= (int) $item['stock'] ?>" value="<?= (int) $item['qty'] ?>"
                                   aria-label="Số lượng <?= e($item['name']) ?>">
                            <?php if ((int) $item['stock'] < (int) $item['qty']): ?>
                                <span class="error-message">Chỉ còn <?= (int) $item['stock'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= e(format_price($item['subtotal'])) ?></td>
                        <td><button type="submit" name="remove" value="<?= (int) $item['id'] ?>" class="secondary">Xóa</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3">Tổng tiền</th>
                    <th colspan="2"><?= e(format_price($total)) ?></th>
                </tr>
            </tfoot>
        </table>

        <p class="cart-actions">
            <button type="submit" name="update" value="1">Cập nhật giỏ hàng</button>
            <button type="submit" name="clear" value="1" class="secondary"
                    data-confirm="Xóa toàn bộ giỏ hàng?">Xóa giỏ hàng</button>
            <a class="button" href="<?= e(url('checkout.php')) ?>">Đặt hàng</a>
        </p>
    </form>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
