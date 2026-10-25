<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$user = current_user();

$stmt = $pdo->prepare('SELECT fullname, email, phone, address FROM users WHERE id = ?');
$stmt->execute([$user['id']]);
$account = $stmt->fetch();

if (!$account) {
    $_SESSION = [];
    session_regenerate_id(true);
    flash_set('error', 'Không tìm thấy tài khoản.');
    redirect('login.php');
}

$fullname = $account['fullname'];
$phone    = $account['phone'] !== null ? $account['phone'] : '';
$address  = $account['address'] !== null ? $account['address'] : '';
$errors   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
    $phone    = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $address  = isset($_POST['address']) ? trim($_POST['address']) : '';

    if ($fullname === '') {
        $errors['fullname'] = 'Vui lòng nhập họ và tên.';
    } elseif (mb_strlen($fullname) > 100) {
        $errors['fullname'] = 'Họ và tên tối đa 100 ký tự.';
    }

    if ($phone !== '' && !preg_match('/^0[0-9]{9,10}$/', $phone)) {
        $errors['phone'] = 'Số điện thoại phải gồm 10 hoặc 11 chữ số và bắt đầu bằng 0.';
    }

    if (mb_strlen($address) > 255) {
        $errors['address'] = 'Địa chỉ tối đa 255 ký tự.';
    }

    if (count($errors) === 0) {
        $stmt = $pdo->prepare('UPDATE users SET fullname = ?, phone = ?, address = ? WHERE id = ?');
        $stmt->execute([
            $fullname,
            $phone !== '' ? $phone : null,
            $address !== '' ? $address : null,
            $user['id'],
        ]);

        // Cập nhật cả tên trong session để thanh điều hướng đổi theo ngay
        $_SESSION['user']['fullname'] = $fullname;

        flash_set('success', 'Đã cập nhật thông tin tài khoản.');
        redirect('profile.php');
    }
}

$page_title = 'Tài khoản';
require __DIR__ . '/includes/header.php';
?>
<h2>Tài khoản của tôi</h2>

<form class="form" method="post" novalidate>
    <p>
        <label for="email">Email (không thể đổi):</label><br>
        <input type="email" id="email" value="<?= e($account['email']) ?>" readonly>
    </p>

    <p>
        <label for="fullname">Họ và tên:</label><br>
        <input type="text" id="fullname" name="fullname" value="<?= e($fullname) ?>" required>
        <?php field_error($errors, 'fullname'); ?>
    </p>

    <p>
        <label for="phone">Số điện thoại:</label><br>
        <input type="tel" id="phone" name="phone" value="<?= e($phone) ?>">
        <?php field_error($errors, 'phone'); ?>
    </p>

    <p>
        <label for="address">Địa chỉ:</label><br>
        <input type="text" id="address" name="address" value="<?= e($address) ?>">
        <?php field_error($errors, 'address'); ?>
    </p>

    <button type="submit">Lưu thay đổi</button>
</form>

<p><a href="<?= e(url('orders.php')) ?>">Xem đơn hàng của tôi</a></p>
<?php require __DIR__ . '/includes/footer.php'; ?>
