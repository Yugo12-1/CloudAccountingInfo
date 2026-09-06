<?php

// ここでセッションを開始する
session_start();

$parentDir = dirname(__DIR__);

require_once $parentDir . '/Support/ConfigReader.php';
require_once $parentDir . '/DBManager/DBConnection.php';
require_once $parentDir . '/Authentication/AuthenticationManager.php';
require_once $parentDir . '/Repository/AdminRepository.php';
require_once $parentDir . '/Service/LoginService.php';
require_once $parentDir . '/Controller/LoginController.php';

// 必要なクラスを作成する
$iniFilePath = $parentDir . '/SettingFiles/settings.ini';
$configReader = new ConfigReader($iniFilePath);
$dbConnect = new DBConnection($configReader);
$authenticator = new AuthenticationManager();
$repository = new AdminRepository($dbConnect);
$service = new LoginService($repository, $authenticator);
$controller = new LoginController($service);

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    
    $loginID = $_POST['loginID'] ?? '';
    $loginPassword = $_POST['loginPassword'] ?? '';
    
    $controller->gfLogin($loginID, $loginPassword);
}

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width" />
        <title>login</title>
    <style>
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }
    </style>
    </head>
    <body>
        <div>
            <h1>クラウド会計情報システム</h1>
        </div>
    <form action="" method="post" class="form-example">
        <div class="form-example" >
            <label for="loginID">ログインID</label>
            <input type="text" name="loginID" id="loginID" required />
        </div>
        <div class="form-example" >
            <label for="loginPassword">パスワード</label>
            <input type="password" name="loginPassword" id="loginPassword" required />
        </div>
        <div class="form-example" >
            <input type="submit" value="ログイン" />
        </div>
    </form>
    </body>
</html>