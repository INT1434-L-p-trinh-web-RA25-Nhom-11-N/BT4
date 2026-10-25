<?php
require_once __DIR__ . '/includes/init.php';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare(
    'SELECT p.id, p.category_id, p.name, p.description, p.price, p.stock, p.image,
            c.name AS category_name
     FROM products p
     JOIN categories c ON c.id = p.category_id
     WHERE p.id = ?'
);
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    $page_title = 'Không tìm thấy sản phẩm';
    require __DIR__ . '/includes/header.php';
    ?>
    <h2>Không tìm thấy sản phẩm</h2>
    <p>Sản phẩm bạn tìm không tồn tại hoặc đã bị xóa.</p>
    <p><a href="<?= e(url('index.php')) ?>">Quay về trang chủ</a></p>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}
$stmt = $pdo->prepare(
    'SELECT id, name, price, stock, image
     FROM products
     WHERE category_id = ? AND id <> ?
     ORDER BY id
     LIMIT 3'
);
$stmt->execute([$product['category_id'], $product['id']]);
$related = $stmt->fetchAll();

$page_title = $product['name'];
require __DIR__ . '/includes/header.php';
?>
<section class="product-detail">
    <img src="<?= e(url('images/' . $product['image'])) ?>" alt="<?= e($product['name']) ?>" width="320">
    <div>
        <h2><?= e($product['name']) ?></h2>
        <p>Danh mục:
            <a href="<?= e(url('index.php?category=' . (int) $product['category_id'])) ?>"><?= e($product['category_name']) ?></a>
        </p>
        <p><?= e($product['description']) ?></p>
        <p class="price"><?= e(format_price($product['price'])) ?></p>
        <?php if ((int) $product['stock'] > 0): ?>
            <p>Còn <?= (int) $product['stock'] ?> sản phẩm</p>
            <form class="inline-form" method="post" action="<?= e(url('cart-add.php')) ?>">
                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                <label for="qty">Số lượng:</label>
                <input type="number" id="qty" name="qty" min="1" max="<?= (int) $product['stock'] ?>" value="1">
                <button type="submit">Thêm vào giỏ</button>
            </form>
        <?php else: ?>
            <p class="out-of-stock">Hết hàng</p>
        <?php endif; ?>
        <p><a href="<?= e(url('cart.php')) ?>">Xem giỏ hàng</a></p>
    </div>
</section>

<section>
    <h2>Sản phẩm cùng danh mục</h2>
    <?php if (count($related) === 0): ?>
        <p>Chưa có sản phẩm nào khác trong danh mục này.</p>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($related as $r): ?>
                <?php render_product_card($r); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
