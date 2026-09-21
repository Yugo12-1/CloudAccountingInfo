<?php

// 型チェックを厳格（Strict Mode）にする」ための宣言
declare(strict_types=1);

date_default_timezone_set('Asia/Tokyo');

/*
 * 医療機関別のlogin.phpから呼び出されたことを確認する
 */

if (!isset($hospitalKey) || !is_string($hospitalKey)) {
    throw new RuntimeException(
        '医療機関キーが指定されていません'
    );
}

/*
 * セッション開始
 */
session_start();

/*
 * scriptディレクトリの絶対パス
 */
$scriptDir = dirname(__DIR__);

require_once $scriptDir . '/Support/HospitalResolver.php';
require_once $scriptDir . '/Support/LogManager.php';
require_once $scriptDir . '/DBManager/DBConnection.php';
require_once $scriptDir . '/Authentication/AuthenticationManager.php';
require_once $scriptDir . '/Repository/AdminRepository.php';
require_once $scriptDir . '/Service/LoginService.php';
require_once $scriptDir . '/Controller/LoginController.php';

/*
 * 医療機関別INIファイルの格納場所
 */
$configDirectory = $scriptDir . '/Config/Hospitals';

/*
 * hospitalKeyからHospitalContextを生成
 */
$hospitalResolver = new HospitalResolver($configDirectory);

$hospitalContext = $hospitalResolver->gfResolveKeyToContext($hospitalKey);


/*
 * ログイン処理に必要なクラスの生成
 */
$logDir = $scriptDir . '/Logs';

$logger = new LogManager($logDir);

$dbConnect = new DBConnection($hospitalContext);

$authenticator = new AuthenticationManager($logger);

$repository = new AdminRepository(
    $logger,
    $dbConnect
);

$service = new LoginService(
    $repository,
    $authenticator,
    $hospitalContext->gfGetHospitalID()
);

$controller = new LoginController($service);

/*
 * ログインフォームが送信された場合
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginID = $_POST['loginID'] ?? '';
    $loginPassword = $_POST['loginPassword'] ?? '';

    $controller->gfLogin(
        $loginID,
        $loginPassword
    );
}

?>


<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0"
        >
        <title>
            <?php echo htmlspecialchars(
                $hospitalContext->gfGetDisplayName(),
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            ); ?>
        | ログイン
        </title>
        <link rel="stylesheet" href="../css/login.css">
    </head>
    <body>
        <header class="site-header">
            <div class="header-content">
                <img src="../img/medifo_logo1.png" alt="製品ロゴ">
            </div>
        </header>
        <main class="login-area">
            <div class="login-content">
                <div class="login-header">
                    <h1>クラウド会計情報システム</h1>
                    <p class="hospital-name">
                        <?php echo htmlspecialchars(
                            $hospitalContext->gfGetDisplayName(),
                            ENT_QUOTES | ENT_SUBSTITUTE,
                            'UTF-8'
                        ); ?>
                    </p>
                </div>
                <?php if (!empty($_SESSION['loginError'])): ?>
                    <p class="login-error">
                        <?php echo htmlspecialchars(
                            $_SESSION['loginError'],
                            ENT_QUOTES | ENT_SUBSTITUTE,
                            'UTF-8'
                        ); ?>
                    </p>
                <?php unset($_SESSION['loginError']); ?>
                <?php endif; ?>
                <div class="login-container">
                    <form action="" method="post">
                        <div class="form-fields">
                            <div class="form-item" >
                                <label for="loginID">ログインID</label>
                                <input type="text" name="loginID" id="loginID" required />
                            </div>
                            <div class="form-item" >
                                <label for="loginPassword">パスワード</label>
                                <input type="password" name="loginPassword" id="loginPassword" required />
                            </div>
                        </div>
                        <div class="form-action" >
                            <input type="submit" value="ログイン" />
                        </div>
                    </form>  
                </div>
            </div>
        </main>
        <footer class="site-footer">
            <small>© 2026 intequa.Co.,Ltd. All rights reserved.</small>
        </footer>
    </body>
</html>