<?php
session_start();

if(empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

$message = '';
$errors = [];
$name = '';
$email = '';
$inquiry = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(empty($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
        printf("POSTトークン: %s\n", $_POST['token']);
        printf("SESSIONトークン: %s\n", $_SESSION['token']);
        die('不正なリクエストです。(CSRFトークン不一致）');
    }
    unset($_SESSION['token']);

    // 必須チェック
    $name  = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $inquiry = isset($_POST['inquiry']) ? trim($_POST['inquiry']) : '';

    // バリデーション
    $errors = [];
    if ($name === '') {
        $errors[] = 'お名前を入力してください。';
    } elseif (!preg_match('/^[a-zA-Z0-9._\-]+$/', $name)) {
        $errors[] = 'お名前は半角英数と「.」「_」「-」のみ使用できます。';
    } elseif (mb_strlen($name) > 10) {
        $errors[] = 'お名前は10文字以内で入力してください。';
    }

    if ($email === '') {
        $errors[] = 'メールアドレスを入力してください。';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = '正しいメールアドレスを入力してください。';
    }

    if ($inquiry === '') {
        $errors[] = 'お問い合わせ内容を入力してください。';
    }

    if (empty($errors)) {
        $_SESSION['formdata']=[
            'name' => $name,
            'email' => $email,
            'inquiry' => $inquiry,
        ];
        header('Location: contact_confirm.php');
        exit();
    } else {
        $_SESSION['token'] = bin2hex(random_bytes(32));
    }
} else {
    // GETリクエスト時はCSRFトークンを再生成
    $_SESSION['token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>お問い合わせフォーム</title>
</head>
<body>
    <h1>お問い合わせフォーム</h1>
    <?php if (!empty($message)): ?>
        <div style="color: green;"><?= $message ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <ul style="color: red;">
        <?php foreach($errors as $err): ?>
            <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" action="">
        <input type="hidden" name="token" value="<?= htmlspecialchars($_SESSION['token'], ENT_QUOTES, 'UTF-8') ?>">
        <div>
        <label>お名前 <input type="text" name="name" maxlength="10" value="<?= htmlspecialchars($name) ?>"></label>
        </div>
        <div>
        <label>メールアドレス <input type="email" name="email" value="<?= htmlspecialchars($email) ?>"></label>
        </div>
        <div>
        <label>お問い合わせ内容 <br>
            <textarea name="inquiry" rows="4" cols="40"><?= htmlspecialchars($inquiry) ?></textarea>
        </label>
        </div>
        <div>
        <button type="submit">送信</button>
        </div>
    </form>
</body>
</html>
