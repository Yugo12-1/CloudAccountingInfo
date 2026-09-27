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
 * キャッシュの保持禁止する
 */
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

/*
 * scriptディレクトリの絶対パスと必要なクラスの読み込み
 */
$scriptDir = dirname(__DIR__);

require_once $scriptDir . '/Support/HospitalResolver.php';
require_once $scriptDir . '/Support/LogManager.php';
require_once $scriptDir . '/Authentication/AuthenticationManager.php';
require_once $scriptDir . '/Repository/AccountingRepository.php';
require_once $scriptDir . '/Service/AnalysisService.php';
require_once $scriptDir . '/Controller/AnalysisController.php';

/*
 * hospitalKeyからHospitalContextを生成
 */
$configDirectory = $scriptDir . '/Config/Hospitals';

$hospitalResolver = new HospitalResolver($configDirectory);

$hospitalContext = $hospitalResolver->gfResolveKeyToContext($hospitalKey);

/*
 * 必要なクラスの作成
 */
$logRootDir = $scriptDir . '/Logs';

$logger = new LogManager($logRootDir);

$dbConnect = new DBConnection($hospitalContext);

$authenticator = new AuthenticationManager($logger);

$accountingRepository = new AccountingRepository(
    $logger,
    $dbConnect,
    $hospitalContext->gfGetHospitalID()
);

$service = new AnalysisService(
    $logger,
    $authenticator,
    $accountingRepository,
    $hospitalContext->gfGetHospitalID()
);

$controller = new AnalysisController($service);

/*
 * ログイン済みかどうかをコントローラーに依頼
 */
$isLogin = $controller->gfIsLogin();

if (!$isLogin) {
    $controller->gfGoToLoginPage();
}

/*
 * ログインした医療機関と現在のURLが異なる場合
 */
if ($authenticator->gfGetHospitalID()
    !== $hospitalContext->gfGetHospitalID()
) {
    header('Location: login.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>会計分析</title>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="../js/analysis.js" defer></script>
    </head>
    <body>
        <a href="./accounting.php">会計情報一覧に戻る</a>
        <h1>月別分析ダッシュボード</h1>
        <div class="month-selector">
            <button id="prevMonthButton"></button>
            <span id="selectedMonth"></span>
            <button id="nextMonthButton"></button>
        </div>
        <div style="width: 400px;">
            <canvas id="payKindChart"></canvas>
        </div>
        <div style="width: 400px;">
            <canvas id="hourlyCountsChart"></canvas>
        </div>
        <div style="width: 400px;">
            <canvas id="gokeiCountsChart"></canvas>
        </div>
    </body>
</html>