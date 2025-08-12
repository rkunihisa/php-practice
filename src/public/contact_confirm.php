<?php
session_start();

if(empty($_SESSION['formdata'])) {
    header('Location: contact.php');
    exit();
}

$formdata = $_SESSION['formdata'];
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Location: contact_complete.php');
    exit();
}
?>

<form method="post">
    <div>お名前：<?= htmlspecialchars($formdata['name'], ENT_QUOTES, 'UTF-8') ?></div>
    <div>メールアドレス：<?= htmlspecialchars($formdata['email'], ENT_QUOTES, 'UTF-8') ?></div>
    <div>お問い合わせ内容：<?= nl2br(htmlspecialchars($formdata['inquiry'], ENT_QUOTES, 'UTF-8')) ?></div>
    <div>
        <button type="submit">送信</button>
    </div>
</form>
<button><a href="contact.php">修正する</a></button>
