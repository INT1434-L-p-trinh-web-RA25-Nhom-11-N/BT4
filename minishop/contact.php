<?php
require_once __DIR__ . '/includes/init.php';

$user = current_user();

$fullname = $user !== null ? $user['fullname'] : '';
$email    = $user !== null ? $user['email'] : '';
$subject  = '';
$message  = '';
$errors   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
    $email    = isset($_POST['email']) ? trim($_POST['email']) : '';
    $subject  = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message  = isset($_POST['message']) ? trim($_POST['message']) : '';

    if ($fullname === '') {
        $errors['fullname'] = 'Vui lòng nhập họ và tên.';
    } elseif (mb_strlen($fullname) > 100) {
        $errors['fullname'] = 'Họ và tên tối đa 100 ký tự.';
    }

    if ($email === '') {
        $errors['email'] = 'Vui lòng nhập email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $errors['email'] = 'Email không hợp lệ.';
    }

    if ($subject === '') {
        $errors['subject'] = 'Vui lòng nhập tiêu đề.';
    } elseif (mb_strlen($subject) > 200) {
        $errors['subject'] = 'Tiêu đề tối đa 200 ký tự.';
    }

    if (mb_strlen($message) < 10) {
        $errors['message'] = 'Nội dung tin nhắn phải có ít nhất 10 ký tự.';
    } elseif (mb_strlen($message) > 5000) {
        $errors['message'] = 'Nội dung tối đa 5000 ký tự.';
    }

    if (count($errors) === 0) {
        $stmt = $pdo->prepare(
            'INSERT INTO contacts (fullname, email, subject, message) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$fullname, $email, $subject, $message]);

        flash_set('success', 'Cảm ơn bạn! MiniShop đã nhận được tin nhắn và sẽ phản hồi sớm.');
        redirect('contact.php');
    }
}

$page_title = 'Liên hệ';
require __DIR__ . '/includes/header.php';
?>
<h2>Liên hệ với MiniShop</h2>
<p>Hãy để lại lời nhắn, chúng tôi sẽ phản hồi sớm nhất có thể.</p>

<form class="form" id="contact-form" method="post" novalidate>
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
        <label for="subject">Tiêu đề:</label><br>
        <input type="text" id="subject" name="subject" value="<?= e($subject) ?>" required>
        <?php field_error($errors, 'subject'); ?>
    </p>

    <p>
        <label for="message">Nội dung:</label><br>
        <textarea id="message" name="message" rows="6" cols="40" required><?= e($message) ?></textarea>
        <span id="message-error" class="error-message" role="alert"><?= e(isset($errors['message']) ? $errors['message'] : '') ?></span>
    </p>

    <button type="submit">Gửi</button>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
