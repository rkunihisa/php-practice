<?php
// DB接続用のPDOインスタンスを返す関数
function pdo(): PDO {
    // static変数でPDOインスタンスを使い回す（シングルトン）
    static $pdo = null;
    if ($pdo === null) {
        // 接続情報
        $dsn  = 'mysql:host=db;dbname=formdb;charset=utf8mb4';
        $user = 'user';
        $pass = 'userpass';
        // PDOインスタンス生成（例外モード・連想配列モード）
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // エラー時は例外を投げる
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // デフォルトは連想配列
        ]);
    }
    return $pdo;
}
