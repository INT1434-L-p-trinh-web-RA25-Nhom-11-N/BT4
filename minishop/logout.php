<?php
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$_SESSION = [];
session_regenerate_id(true);

flash_set('success', 'Bạn đã đăng xuất.');
redirect('index.php');
