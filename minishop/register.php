<?php
// Khuôn mẫu xử lý form (bước 1): nạp init.php
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    redirect('index.php');
}

// Bước 2: khởi tạo biến cho từng trường và mảng $errors rỗng
$fullname = '';
$email    = '';
$phone    = '';
$errors   = [];

// Bước 3: nếu là POST thì đọc và kiểm tra từng trường (kiểm tra phía máy chủ)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname         = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
    $email            = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
    $phone            = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $password         = isset($_POST['password']) ? $_POST['password'] : '';
    $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

    if ($fullname === '') {
        $errors['fullname'] = 'Vui lòng nhập họ và tên.';
    } elseif (mb_strlen($fullname) > 100) {
        $errors['fullname'] = 'Họ và tên tối đa 100 ký tự.';
    }

    if ($email === '') {
        $errors['email'] = 'Vui lòng nhập email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $errors['email'] = 'Email không hợp lệ.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Email này đã được đăng ký.';
        }
    }

    if ($phone !== '' && !preg_match('/^0[0-9]{9,10}$/', $phone)) {
        $errors['phone'] = 'Số điện thoại phải gồm 10 hoặc 11 chữ số và bắt đầu bằng 0.';
    }

    if (strlen($password) < 8) {
        $errors['password'] = 'Mật khẩu phải có ít nhất 8 ký tự.';
    }
    if ($password_confirm !== $password) {
        $errors['password_confirm'] = 'Mật khẩu nhập lại không khớp.';
    }
    if (count($errors) === 0) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO users (fullname, email, password, phone) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([
                $fullname,
                $email,
                password_hash($password, PASSWORD_DEFAULT), // băm mật khẩu, không lưu mật khẩu thô
                $phone !== '' ? $phone : null,
            ]);
            flash_set('success', 'Đăng ký thành công. Hãy đăng nhập.');
            redirect('login.php');
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') {
                $errors['email'] = 'Email này đã được đăng ký.';
            } else {
                throw $ex;
            }
        }
    }
}
$page_title = 'Đăng ký';
require __DIR__ . '/includes/header.php';
?>
<h2>Đăng ký thành viên</h2>
<form class="form" method="post" novalidate>
    <p>
        <label for="fullname">Họ và tên:</label><br>
        <input type="text" id="fullname" name="fullname" value="<?= e($fullname) ?>" required>
        <?php field_error($errors, 'fullname'); ?>
    </p>

    <p>
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" required>
        <?php field_error($errors, 'email'); ?>
    </p>

    <p>
        <label for="phone">Số điện thoại (không bắt buộc):</label><br>
        <input type="tel" id="phone" name="phone" value="<?= e($phone) ?>">
        <?php field_error($errors, 'phone'); ?>
    </p>

    <p>
        <label for="password">Mật khẩu (ít nhất 8 ký tự):</label><br>
        <input type="password" id="password" name="password" required>
        <?php field_error($errors, 'password'); ?>
    </p>

    <p>
        <label for="password_confirm">Nhập lại mật khẩu:</label><br>
        <input type="password" id="password_confirm" name="password_confirm" required>
        <?php field_error($errors, 'password_confirm'); ?>
    </p>

    <button type="submit">Đăng ký</button>
</form>

<p>Đã có tài khoản? <a href="<?= e(url('login.php')) ?>">Đăng nhập</a></p>
<?php require __DIR__ . '/includes/footer.php'; ?>
