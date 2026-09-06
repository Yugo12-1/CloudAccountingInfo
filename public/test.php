<?php

try {
    $dsn = 'mysql:host=localhost;dbname;tbl_administrator;charset=utf8mb4';
    $user = 'admin';
    $password = 'admin1201';

    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // 例外を投げる
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   // 配列形式
        PDO::ATTR_EMULATE_PREPARES => false                 // 実行計画を最適化
    ]);

    echo "接続成功";
} catch (PDOException $e) {
    echo '接続失敗: ' . $e->getMessage();
}

