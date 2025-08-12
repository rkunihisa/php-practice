<?php
session_start();

if(empty($_SESSION['formdata'])) {
    header('Location: contact.php');
    exit();
}

$dsn = 'mysql:host=db;dbname=formdb;charset=utf8mb4';
$db_user = 'user';
$db_pass = 'userpass';

$name = $_SESSION['formdata']['name'];
$email = $_SESSION['formdata']['email'];
$inquiry = $_SESSION['formdata']['inquiry'];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $stmt = $pdo->prepare(
        "INSERT INTO contacts (name, email, inquiry) VALUES (?, ?, ?)"
    );
    $stmt->execute([$name, $email, $inquiry]);

    $message = 'お問い合わせを受け付け、DBに保存しました。<br>'
             . 'お名前：' . htmlspecialchars($name) . '<br>'
             . 'メールアドレス：' . htmlspecialchars($email) . '<br>'
             . '内容：<br>' . nl2br(htmlspecialchars($inquiry));
} catch (PDOException $e) {
    $errors[] = 'データベースエラー: ' . htmlspecialchars($e->getMessage());
}

unset($_SESSION['formdata']);
?>

<p>お問合せありがとうございました。</p>
<button><a href="contact.php">問い合わせ画面に戻る</a></button>


