<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    header('Location: accounting.php');
    exit();
}

// セッション変数をすべて削除
$_SESSION = [];

// セッション自体を削除
session_destroy();

// ログイン画面へ戻す
header('Location: login.php');
exit();