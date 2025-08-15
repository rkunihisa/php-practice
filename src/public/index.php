<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
use \Firebase\JWT\JWT;

define('JWT_SECRET', 'your_jwt_secret_key');

// ログイン処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    // 仮の認証（本番はDB等でチェックする）
    if ($user === 'test' && $pass === 'password') {
        $payload = [
            'user' => $user,
            'exp' => time() + 3600 // 1時間有効
        ];
        $jwt = JWT::encode($payload, JWT_SECRET, 'HS256');
        $_SESSION['jwt'] = $jwt;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'ログイン失敗';
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head><meta charset="UTF-8"><title>ログイン</title></head>
<body>
<form method="post">
    <input name="user" placeholder="ユーザー名"><br>
    <input name="pass" type="password" placeholder="パスワード"><br>
    <button type="submit">ログイン</button>
</form>
<?php if (!empty($error)) echo "<div style='color:red;'>$error</div>"; ?>
</body>
</html>
