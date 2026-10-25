<?php
require_once __DIR__ . '/includes/init.php';

$category_id = isset($_GET['category']) ? (int) $_GET['category'] : 0;
$keyword     = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$sort        = isset($_GET['sort']) && is_string($_GET['sort']) ? $_GET['sort'] : '';

if ($sort === 'price_asc') {
    $order_by = 'price ASC';
} elseif ($sort === 'price_desc') {
    $order_by = 'price DESC';
} else {
    $sort     = '';
    $order_by = 'id ASC';
}

$sql    = 'SELECT id, name, price, stock, image FROM products WHERE 1 = 1';
$params = [];

if ($category_id > 0) {
    $sql     .= ' AND category_id = ?';
    $params[] = $category_id;
}
if ($keyword !== '') {
    $sql     .= ' AND name LIKE ?';
    $params[] = '%' . $keyword . '%';
}
$sql .= ' ORDER BY ' . $order_by;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

$page_title = 'Trang chủ';
require __DIR__ . '/includes/header.php';
?>
<section>
    <h2>Sản phẩm nổi bật</h2>
    <p>Những món đồ công nghệ được sinh viên chọn mua nhiều nhất tại MiniShop.</p>
    <form class="filter-form" method="get" action="<?= e(url('index.php')) ?>">
        <label for="q">Tìm kiếm:</label>
        <input type="search" id="q" name="q" value="<?= e($keyword) ?>" placeholder="Tên sản phẩm">

        <label for="category">Danh mục:</label>
        <select id="category" name="category">
            <option value="0">Tất cả</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= (int) $c['id'] ?>"<?= (int) $c['id'] === $category_id ? ' selected' : '' ?>>
                    <?= e($c['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="sort">Sắp xếp:</label>
        <select id="sort" name="sort">
            <option value="">Mặc định</option>
            <option value="price_asc"<?= $sort === 'price_asc' ? ' selected' : '' ?>>Giá tăng dần</option>
            <option value="price_desc"<?= $sort === 'price_desc' ? ' selected' : '' ?>>Giá giảm dần</option>
        </select>

        <button type="submit">Lọc</button>
        <a href="<?= e(url('index.php')) ?>">Xóa lọc</a>
    </form>
    <p class="result-count">Tìm thấy <strong><?= count($products) ?></strong> sản phẩm</p>

    <?php if (count($products) === 0): ?>
        <p>Không có sản phẩm nào phù hợp. Hãy thử từ khóa hoặc danh mục khác.</p>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $p): ?>
                <?php render_product_card($p); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
