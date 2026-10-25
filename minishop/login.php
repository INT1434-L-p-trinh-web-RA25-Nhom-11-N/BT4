<?php
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    redirect('index.php');
}

$email  = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($email === '') {
        $errors['email'] = 'Vui lòng nhập email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email không hợp lệ.';
    }
    if ($password === '') {
        $errors['password'] = 'Vui lòng nhập mật khẩu.';
    }

    if (count($errors) === 0) {
        $stmt = $pdo->prepare('SELECT id, fullname, email, password FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        if ($row && password_verify($password, $row['password'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id'       => (int) $row['id'],
                'fullname' => $row['fullname'],
                'email'    => $row['email'],
            ];
            flash_set('success', 'Xin chào, ' . $row['fullname'] . '!');

            redirect(pop_redirect_after_login());
        }
        $errors['general'] = 'Email hoặc mật khẩu không đúng.';
    }
}

$page_title = 'Đăng nhập';
require __DIR__ . '/includes/header.php';
?>
<h2>Đăng nhập tài khoản</h2>

<?php if (isset($errors['general'])): ?>
    <p class="error-message" role="alert"><?= e($errors['general']) ?></p>
<?php endif; ?>

<form class="form" method="post" novalidate>
    <p>
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" required>
        <?php field_error($errors, 'email'); ?>
    </p>

    <p>
        <label for="password">Mật khẩu:</label><br>
        <input type="password" id="password" name="password" required>
        <?php field_error($errors, 'password'); ?>
    </p>

    <button type="submit">Đăng nhập</button>
</form>

<p>Chưa có tài khoản? <a href="<?= e(url('register.php')) ?>">Đăng ký</a></p>
<?php require __DIR__ . '/includes/footer.php'; ?>
