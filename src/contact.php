<?php
// 送信後の処理
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        // 問い合わせ内容確認（本来はここでDB保存やメール送信を行う）
        $message = 'お問い合わせを受け付けました。<br>'
                 . 'お名前：' . htmlspecialchars($name) . '<br>'
                 . 'メールアドレス：' . htmlspecialchars($email) . '<br>'
                 . '内容：<br>' . nl2br(htmlspecialchars($inquiry));
    }
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
        <div style="color: green;"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <ul style="color: red;">
        <?php foreach($errors as $err): ?>
            <li><?php echo htmlspecialchars($err); ?></li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" action="">
        <div>
        <label>お名前 <input type="text" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>"></label>
        </div>
        <div>
        <label>メールアドレス <input type="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"></label>
        </div>
        <div>
        <label>お問い合わせ内容 <br>
            <textarea name="inquiry" rows="4" cols="40"><?php echo isset($inquiry) ? htmlspecialchars($inquiry) : ''; ?></textarea>
        </label>
        </div>
        <div>
        <button type="submit">送信</button>
        </div>
    </form>
</body>
</html>
